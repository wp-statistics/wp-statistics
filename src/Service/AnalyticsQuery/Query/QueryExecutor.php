<?php

namespace WP_Statistics\Service\AnalyticsQuery\Query;

use WP_Statistics\Service\AnalyticsQuery\Contracts\QueryInterface;
use WP_Statistics\Service\AnalyticsQuery\Contracts\QueryExecutorInterface;
use WP_Statistics\Service\AnalyticsQuery\Registry\SourceRegistry;
use WP_Statistics\Service\AnalyticsQuery\Registry\GroupByRegistry;
use WP_Statistics\Service\AnalyticsQuery\FilterBuilder;

/**
 * Executes analytics queries against the database.
 *
 * @since 15.0.0
 */
class QueryExecutor implements QueryExecutorInterface
{
    /**
     * WordPress database instance.
     *
     * @var \wpdb
     */
    private $wpdb;

    /**
     * Table prefix.
     *
     * @var string
     */
    private $tablePrefix;

    /**
     * Source registry.
     *
     * @var SourceRegistry
     */
    private $sourceRegistry;

    /**
     * Group by registry.
     *
     * @var GroupByRegistry
     */
    private $groupByRegistry;

    /**
     * Constructor.
     *
     * @param SourceRegistry    $sourceRegistry    Source registry.
     * @param GroupByRegistry $groupByRegistry Group by registry.
     */
    public function __construct(SourceRegistry $sourceRegistry, GroupByRegistry $groupByRegistry)
    {
        global $wpdb;
        $this->wpdb              = $wpdb;
        $this->tablePrefix       = $wpdb->prefix . 'statistics_';
        $this->sourceRegistry    = $sourceRegistry;
        $this->groupByRegistry = $groupByRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(QueryInterface $query): array
    {
        // Check if we can use summary tables for optimization
        if ($this->canUseSummaryTable($query)) {
            return $this->executeFromSummaryTable($query);
        }

        // Fallback to raw tables (current implementation)
        return $this->executeFromRawTables($query);
    }

    /**
     * Execute query from raw tables (original implementation).
     *
     * @param QueryInterface $query The query object.
     * @return array
     */
    private function executeFromRawTables(QueryInterface $query): array
    {
        $sql = $this->buildSql($query);

        // Prepare and execute main query
        $preparedSql = $sql['sql'];
        if (!empty($sql['params'])) {
            $preparedSql = $this->wpdb->prepare($sql['sql'], $sql['params']);
        }

        $rows = $this->wpdb->get_results($preparedSql, ARRAY_A);

        // Only execute count query if needed
        $total = 0;
        if ($query->needsCount()) {
            $countSql = $sql['count_sql'];
            if (!empty($sql['params'])) {
                $countSql = $this->wpdb->prepare($sql['count_sql'], $sql['params']);
            }

            $total = (int) $this->wpdb->get_var($countSql);
        } else {
            // For flat queries or when count not needed, use row count
            $total = count($rows);
        }

        // Apply groupBy post-processing
        $rows = $this->applyGroupByPostProcessing($query, $rows ?: []);

        // Normalize rows for week/month groupBy to add 'date' column
        // This ensures ChartFormatter can find the date labels
        $groupByNames = $query->getGroupBy();
        if (!empty($groupByNames) && in_array($groupByNames[0], ['week', 'month'], true)) {
            $rows = $this->normalizeRawTableRows($rows, $groupByNames[0]);
        }

        return [
            'rows'  => $rows,
            'total' => $total,
        ];
    }

    /**
     * Apply post-processing from groupBy handlers.
     *
     * @param QueryInterface $query The query object.
     * @param array          $rows  Query result rows.
     * @return array Processed rows.
     */
    private function applyGroupByPostProcessing(QueryInterface $query, array $rows): array
    {
        $groupByNames = $query->getGroupBy();

        foreach ($groupByNames as $groupByName) {
            $groupBy = $this->groupByRegistry->get($groupByName);
            if ($groupBy) {
                $rows = $groupBy->postProcess($rows, $this->wpdb);
            }
        }

        return $rows;
    }

    /**
     * Check if query can use summary tables.
     *
     * @param QueryInterface $query The query object.
     * @return bool
     */
    private function canUseSummaryTable(QueryInterface $query): bool
    {
        $sources  = $query->getSources();
        $groupBy  = $query->getGroupBy();
        $filters  = $query->getFilters();

        // Check if all sources support summary tables
        foreach ($sources as $sourceName) {
            $source = $this->sourceRegistry->get($sourceName);

            // If source doesn't exist or doesn't support summary tables, can't use summary
            if (!$source || !$source->supportsSummaryTable()) {
                return false;
            }
        }

        // Check if grouping is date-based only (or no grouping)
        if (!empty($groupBy)) {
            $allowedGroupBy = ['date', 'week', 'month'];
            if (!in_array($groupBy[0], $allowedGroupBy, true)) {
                return false;
            }
        }

        // Summary tables contain only aggregated data by date
        // Any filtering requires querying raw tables with dimensional data
        if (!empty($filters)) {
            return false;
        }

        return true;
    }

    /**
     * Execute query using summary tables with hybrid approach for today's data.
     *
     * @param QueryInterface $query The query object.
     * @return array
     */
    private function executeFromSummaryTable(QueryInterface $query): array
    {
        $dateTo   = $query->getDateTo();
        $dateFrom = $query->getDateFrom();
        $today    = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));

        // Extract just the date part for comparison (dates may include time component)
        $dateToDate   = substr($dateTo, 0, 10);
        $dateFromDate = substr($dateFrom, 0, 10);

        // Note: System constraint - to_date max is today (cannot be future)

        // Scenario 1: Date range is entirely historical (to_date < today)
        if ($dateToDate < $today) {
            return $this->executeFromSummaryTableOnly($query);
        }

        // Scenario 2: to_date == today - use HYBRID approach
        if ($dateToDate === $today) {
            return $this->executeHybridQuery($query, $dateFromDate, $yesterday, $today);
        }

        // Fallback (shouldn't happen due to system constraint, but safe)
        return $this->executeFromRawTables($query);
    }

    /**
     * Execute query from summary tables only (historical data).
     *
     * @param QueryInterface $query The query object.
     * @return array
     */
    private function executeFromSummaryTableOnly(QueryInterface $query): array
    {
        $groupBy = $query->getGroupBy();

        // Determine which table to use
        // - summary_totals: Site-wide aggregates (no groupBy, or time-series groupBy like date/week/month)
        // - summary: Per-resource aggregates (when grouping by resource/page)
        $timeSeriesGroupBy = ['date', 'week', 'month'];
        $useTimeSeries = empty($groupBy) ||
                         (count($groupBy) === 1 && in_array($groupBy[0], $timeSeriesGroupBy, true));
        $tableName = $useTimeSeries ? 'summary_totals' : 'summary';

        // Build SQL for summary table
        $sql = $this->buildSummaryTableSql($query, $tableName);

        // Prepare and execute
        $preparedSql = $sql['sql'];
        if (!empty($sql['params'])) {
            $preparedSql = $this->wpdb->prepare($sql['sql'], $sql['params']);
        }

        $rows = $this->wpdb->get_results($preparedSql, ARRAY_A);

        // Check if result is empty or contains only NULL values
        // SUM() returns a single row with NULL when no matching rows exist
        $hasRealData = false;
        if (!empty($rows)) {
            foreach ($rows as $row) {
                foreach ($row as $value) {
                    if ($value !== null && $value !== '') {
                        $hasRealData = true;
                        break 2;
                    }
                }
            }
        }

        // Fallback to raw tables if summary table has no data for this period
        // This handles cases where summary table hasn't been populated yet
        if (!$hasRealData) {
            return $this->executeFromRawTables($query);
        }

        // Add calculated metrics if needed
        $rows = $this->addCalculatedMetrics($rows, $query->getSources());

        // Handle week/month aggregation if needed
        if (!empty($groupBy) && in_array($groupBy[0], ['week', 'month'], true)) {
            $rows = $this->aggregateToTimeframe($rows, $groupBy[0]);
        }

        return [
            'rows'  => $rows ?: [],
            'total' => count($rows),
        ];
    }

    /**
     * Execute hybrid query (summary tables for historical + raw for today).
     *
     * @param QueryInterface $query          The query object.
     * @param string         $dateFrom       Original date from.
     * @param string         $historicalTo   Yesterday (last date in summary).
     * @param string         $today          Today's date.
     * @return array
     */
    private function executeHybridQuery(QueryInterface $query, string $dateFrom, string $historicalTo, string $today): array
    {
        $groupBy = $query->getGroupBy();
        $rows    = [];

        // Part 1: Query summary tables for historical data (from_date to yesterday)
        if ($dateFrom <= $historicalTo) {
            // Create query with modified date range (historical only - up to yesterday)
            $historicalQuery = $query->withDateRange($dateFrom, $historicalTo);

            // Use summary_totals for time-series queries, summary for per-resource queries
            $timeSeriesGroupBy = ['date', 'week', 'month'];
            $useTimeSeries = empty($groupBy) ||
                             (count($groupBy) === 1 && in_array($groupBy[0], $timeSeriesGroupBy, true));
            $tableName = $useTimeSeries ? 'summary_totals' : 'summary';
            $sql = $this->buildSummaryTableSql($historicalQuery, $tableName);

            $preparedSql = $sql['sql'];
            if (!empty($sql['params'])) {
                $preparedSql = $this->wpdb->prepare($sql['sql'], $sql['params']);
            }

            $historicalRows = $this->wpdb->get_results($preparedSql, ARRAY_A);

            // Check if result is empty or contains only NULL values
            // SUM() returns a single row with NULL when no matching rows exist
            $hasRealData = false;
            if (!empty($historicalRows)) {
                foreach ($historicalRows as $row) {
                    foreach ($row as $value) {
                        if ($value !== null && $value !== '') {
                            $hasRealData = true;
                            break 2;
                        }
                    }
                }
            }

            // Fallback to raw tables if summary table has no data for this period
            if (!$hasRealData) {
                $historicalResult = $this->executeFromRawTables($historicalQuery);
                $historicalRows   = $historicalResult['rows'];
            }

            $rows = array_merge($rows, $historicalRows);
        }

        // Part 2: Query raw tables for today's data only
        $todayQuery = $query->withDateRange($today, $today);

        $todayResult = $this->executeFromRawTables($todayQuery);
        $todayRows   = $todayResult['rows'];

        // Normalize raw table rows to have 'date' column for week/month groupBy
        // Raw tables return 'week_start' for weekly and 'month' for monthly,
        // but summary tables and aggregateToTimeframe expect 'date' column
        if (!empty($groupBy) && in_array($groupBy[0], ['week', 'month'], true)) {
            $todayRows = $this->normalizeRawTableRows($todayRows, $groupBy[0]);
        }

        $rows = array_merge($rows, $todayRows);

        // Part 3: Post-process all results
        $rows = $this->addCalculatedMetrics($rows, $query->getSources());

        // Handle week/month aggregation if needed
        if (!empty($groupBy) && in_array($groupBy[0], ['week', 'month'], true)) {
            $rows = $this->aggregateToTimeframe($rows, $groupBy[0]);
        }

        return [
            'rows'  => $rows ?: [],
            'total' => count($rows),
        ];
    }

    /**
     * Build SQL for summary table query.
     *
     * @param QueryInterface $query     The query object.
     * @param string         $tableName Table name (summary or summary_totals).
     * @return array ['sql' => string, 'params' => array]
     */
    private function buildSummaryTableSql(QueryInterface $query, string $tableName): array
    {
        $sources  = $query->getSources();
        $groupBy  = $query->getGroupBy();
        $dateFrom = $query->getDateFrom();
        $dateTo   = $query->getDateTo();
        $page     = $query->getPage();
        $perPage  = $query->getPerPage();
        $offset   = ($page - 1) * $perPage;

        // For time-series groupBy (date, week, month), always order by date ASC
        // This ensures chart data is in chronological order
        $timeSeriesGroupBy = ['date', 'week', 'month'];
        if (!empty($groupBy) && in_array($groupBy[0], $timeSeriesGroupBy, true)) {
            $orderBy = 'date';
            $order   = 'ASC';
        } else {
            $orderBy = $query->getOrderBy() ?: $sources[0] ?? 'visitors';
            $order   = $query->getOrder();
        }

        $select = [];
        $params = [];

        // For time-series groupBy (date, week, month), always select date column
        // Week/month will be aggregated from daily data by aggregateToTimeframe()
        $timeSeriesGroupBy = ['date', 'week', 'month'];
        $isTimeSeries = !empty($groupBy) && in_array($groupBy[0], $timeSeriesGroupBy, true);
        if ($isTimeSeries) {
            $select[] = 'date';
        }

        // Map sources to summary table columns
        $sourceMapping = [
            'visitors'             => 'SUM(visitors) AS visitors',
            'views'                => 'SUM(views) AS views',
            'sessions'             => 'SUM(sessions) AS sessions',
            'bounce_rate'          => 'ROUND(SUM(bounces) / NULLIF(SUM(sessions), 0) * 100, 2) AS bounce_rate',
            'avg_session_duration' => 'ROUND(SUM(total_duration) / NULLIF(SUM(sessions), 0), 2) AS avg_session_duration',
            'pages_per_session'    => 'ROUND(SUM(views) / NULLIF(SUM(sessions), 0), 2) AS pages_per_session',
            'total_duration'       => 'SUM(total_duration) AS total_duration',
        ];

        foreach ($sources as $source) {
            if (isset($sourceMapping[$source])) {
                $select[] = $sourceMapping[$source];
            }
            // avg_time_on_page will be calculated after query if needed
        }

        // Build SQL
        $sql = "SELECT\n    " . implode(",\n    ", $select);
        $sql .= "\nFROM " . $this->getFullTableName($tableName);

        // Add date range filter
        if ($dateFrom && $dateTo) {
            $sql .= "\nWHERE date >= %s AND date <= %s";
            $params[] = $dateFrom;
            $params[] = $dateTo;
        }

        // Add GROUP BY if needed (for time-series, always group by date)
        if ($isTimeSeries) {
            $sql .= "\nGROUP BY date";
        }

        // Add ORDER BY
        if ($orderBy) {
            $sql .= "\nORDER BY $orderBy $order";
        }

        // Add LIMIT and OFFSET
        $sql .= "\nLIMIT $perPage OFFSET $offset";

        return [
            'sql'    => $sql,
            'params' => $params,
        ];
    }

    /**
     * Add calculated metrics to results.
     *
     * @param array $rows    Result rows.
     * @param array $sources Requested sources.
     * @return array
     */
    private function addCalculatedMetrics(array $rows, array $sources): array
    {
        $needsPagesPerSession = in_array('pages_per_session', $sources, true);
        $needsAvgTimeOnPage   = in_array('avg_time_on_page', $sources, true);

        if (!$needsPagesPerSession && !$needsAvgTimeOnPage) {
            return $rows;
        }

        foreach ($rows as &$row) {
            if ($needsPagesPerSession) {
                $sessions = isset($row['sessions']) ? (int) $row['sessions'] : 0;
                $views    = isset($row['views']) ? (int) $row['views'] : 0;
                $row['pages_per_session'] = $sessions > 0 ? round($views / $sessions, 2) : 0;
            }

            if ($needsAvgTimeOnPage) {
                $views        = isset($row['views']) ? (int) $row['views'] : 0;
                $totalDuration = isset($row['total_duration']) ? (int) $row['total_duration'] : 0;
                $row['avg_time_on_page'] = $views > 0 ? round($totalDuration / $views, 2) : 0;
            }
        }

        return $rows;
    }

    /**
     * Aggregate daily results to week/month timeframe.
     *
     * @param array  $rows      Daily result rows.
     * @param string $timeframe 'week' or 'month'.
     * @return array
     */
    private function aggregateToTimeframe(array $rows, string $timeframe): array
    {
        if (empty($rows)) {
            return $rows;
        }

        $aggregated = [];

        foreach ($rows as $row) {
            $date = $row['date'] ?? null;
            if (!$date) {
                continue;
            }

            $dateTime = new \DateTime($date);

            // Determine the key and display date for aggregation
            if ($timeframe === 'week') {
                // Get Monday of this week as the label (matches ChartFormatter::generateDateLabels)
                $dateTime->modify('monday this week');
                $key         = $dateTime->format('Y-W');
                $displayDate = $dateTime->format('Y-m-d');
            } elseif ($timeframe === 'month') {
                // Use Y-m format (matches ChartFormatter::generateDateLabels)
                $key         = $dateTime->format('Y-m');
                $displayDate = $dateTime->format('Y-m');
            } else {
                continue;
            }

            // Initialize if not exists
            if (!isset($aggregated[$key])) {
                $aggregated[$key] = [
                    'date'                => $displayDate, // Use consistent format for matching
                    'visitors'            => 0,
                    'views'               => 0,
                    'sessions'            => 0,
                    'bounces'             => 0,
                    'total_duration'      => 0,
                ];
            }

            // Aggregate values
            $aggregated[$key]['visitors']       += (int) ($row['visitors'] ?? 0);
            $aggregated[$key]['views']          += (int) ($row['views'] ?? 0);
            $aggregated[$key]['sessions']       += (int) ($row['sessions'] ?? 0);
            $aggregated[$key]['total_duration'] += (int) ($row['total_duration'] ?? 0);
        }

        // Recalculate derived metrics
        foreach ($aggregated as &$row) {
            $sessions = $row['sessions'];
            $views    = $row['views'];

            // Recalculate bounce_rate if sessions exist
            if ($sessions > 0 && isset($row['bounces'])) {
                $row['bounce_rate'] = round($row['bounces'] / $sessions * 100, 2);
            } else {
                $row['bounce_rate'] = 0;
            }

            // Recalculate avg_session_duration
            if ($sessions > 0) {
                $row['avg_session_duration'] = round($row['total_duration'] / $sessions, 2);
            } else {
                $row['avg_session_duration'] = 0;
            }

            // Recalculate pages_per_session if needed
            if ($sessions > 0) {
                $row['pages_per_session'] = round($views / $sessions, 2);
            } else {
                $row['pages_per_session'] = 0;
            }

            // Recalculate avg_time_on_page if needed
            if ($views > 0) {
                $row['avg_time_on_page'] = round($row['total_duration'] / $views, 2);
            } else {
                $row['avg_time_on_page'] = 0;
            }
        }

        return array_values($aggregated);
    }

    /**
     * Normalize raw table rows to have a 'date' column for consistency with summary table data.
     *
     * Raw table queries with week/month groupBy return different column WP_Statistics_names:
     * - Weekly: 'week' (YEARWEEK), 'week_start' (Y-m-d)
     * - Monthly: 'month' (Y-m)
     *
     * This method adds a 'date' column so aggregateToTimeframe can process them.
     *
     * @param array  $rows      Raw table result rows.
     * @param string $timeframe 'week' or 'month'.
     * @return array Normalized rows with 'date' column.
     */
    private function normalizeRawTableRows(array $rows, string $timeframe): array
    {
        foreach ($rows as &$row) {
            if ($timeframe === 'week') {
                // Use week_start if available, otherwise calculate from week column
                if (isset($row['week_start'])) {
                    $row['date'] = $row['week_start'];
                } elseif (isset($row['week'])) {
                    // Convert YEARWEEK (e.g., 202553) back to a date
                    $year = (int) substr($row['week'], 0, 4);
                    $week = (int) substr($row['week'], 4);
                    $dateTime = new \DateTime();
                    $dateTime->setISODate($year, $week);
                    $row['date'] = $dateTime->format('Y-m-d');
                }
            } elseif ($timeframe === 'month') {
                // Monthly data from raw tables already uses 'month' column in Y-m format
                if (isset($row['month'])) {
                    $row['date'] = $row['month'];
                }
            }
        }

        return $rows;
    }

    /**
     * {@inheritdoc}
     */
    public function executeTotals(QueryInterface $query): array
    {
        $sql = $this->buildTotalsSql($query);

        $preparedSql = $sql['sql'];
        if (!empty($sql['params'])) {
            $preparedSql = $this->wpdb->prepare($sql['sql'], $sql['params']);
        }

        $result = $this->wpdb->get_row($preparedSql, ARRAY_A);

        return $result ?: [];
    }

    /**
     * Build SQL query from Query object.
     *
     * @param QueryInterface $query The query object.
     * @return array ['sql' => string, 'params' => array, 'count_sql' => string]
     */
    private function buildSql(QueryInterface $query): array
    {
        $select             = [];
        $joins              = [];
        $where              = [];
        $params             = [];
        $groupByExpressions = [];

        $sources      = $query->getSources();
        $groupByNames = $query->getGroupBy();
        $filters      = $query->getFilters();
        $dateFrom     = $query->getDateFrom();
        $dateTo       = $query->getDateTo();

        $order        = $query->getOrder();
        $page         = $query->getPage();
        $perPage      = $query->getPerPage();
        $offset       = ($page - 1) * $perPage;
        $requestedColumns = $query->getColumns() ?: [];
        $orderBy      = $this->validateOrderBy($query->getOrderBy(), $sources, $groupByNames, $requestedColumns);

        // Determine primary table
        $primaryTable = $this->determinePrimaryTable($sources, $groupByNames, $filters);
        $from         = $this->getFullTableName($primaryTable) . ' AS ' . $primaryTable;

        // Add session join if needed for views table (must come before group by joins)
        // Only add if sources, groupBy, or filters actually need session data
        if ($primaryTable === 'views' && $this->needsSessionJoin($sources, $groupByNames, $filters)) {
            $joins = $this->addSessionJoinForViews($joins);
        }

        // Add resources join if needed for views table (for comments source, etc.)
        if ($primaryTable === 'views' && $this->needsResourcesJoin($sources, $groupByNames)) {
            $joins = $this->addResourcesJoinForViews($joins);
        }

        // Add group by columns and joins (pass requested columns for optimization)
        foreach ($groupByNames as $groupByName) {
            $groupByItem = $this->groupByRegistry->get($groupByName);
            if (!$groupByItem) {
                continue;
            }

            $select  = array_merge($select, $groupByItem->getSelectColumns($requestedColumns));
            $joins   = array_merge($joins, $this->normalizeJoins($groupByItem->getJoins()));

            if ($groupByItem->getGroupBy()) {
                $groupByExpressions[] = $groupByItem->getGroupBy();
            }

            if ($groupByItem->getFilter()) {
                $where[] = $groupByItem->getFilter();
            }
        }

        // Add source expressions
        foreach ($sources as $sourceName) {
            $source = $this->sourceRegistry->get($sourceName);
            if ($source) {
                $source->setContext($groupByNames, $filters, $dateFrom, $dateTo);
                $select[] = $source->getExpressionWithAlias();
            }
        }

        // Add date range filter
        if ($dateFrom && $dateTo) {
            $dateColumn = $this->getDateColumn($primaryTable, $groupByNames, $sources);
            $where[]    = "$dateColumn >= %s";
            $where[]    = "$dateColumn <= %s";
            $params[]   = $this->formatDateTimeStart($dateFrom);
            $params[]   = $this->formatDateTimeEnd($dateTo);
        }

        // Add custom filters
        if (!empty($filters)) {
            $filterResult = FilterBuilder::build($filters);
            $where        = array_merge($where, $filterResult['conditions']);
            $params       = array_merge($params, $filterResult['params']);
            $joins        = array_merge($joins, $this->normalizeJoins($filterResult['joins']));
        }

        // Build main SQL
        $sql = $this->assembleSql($select, $from, $joins, $where, $groupByExpressions, $orderBy, $order, $perPage, $offset);

        // Build count SQL
        $countSql = $this->assembleCountSql($from, $joins, $where, $groupByExpressions);

        return [
            'sql'       => $sql,
            'params'    => $params,
            'count_sql' => $countSql,
        ];
    }

    /**
     * Build SQL for totals query (no group by).
     *
     * @param QueryInterface $query The query object.
     * @return array ['sql' => string, 'params' => array]
     */
    private function buildTotalsSql(QueryInterface $query): array
    {
        $select  = [];
        $joins   = [];
        $where   = [];
        $params  = [];

        $sources  = $query->getSources();
        $filters  = $query->getFilters();
        $dateFrom = $query->getDateFrom();
        $dateTo   = $query->getDateTo();

        // Determine primary table
        $primaryTable = $this->determinePrimaryTableForSources($sources);
        if (FilterBuilder::requiresViewsTable($filters)) {
            $primaryTable = 'views';
        }

        $from = $this->getFullTableName($primaryTable) . ' AS ' . $primaryTable;

        // Add source expressions only
        foreach ($sources as $sourceName) {
            $source = $this->sourceRegistry->get($sourceName);
            if ($source) {
                $source->setContext([], $filters, $dateFrom, $dateTo);
                $select[] = $source->getExpressionWithAlias();
            }
        }

        // Add session join if needed for views table (must come before filter joins)
        // Only add if sources or filters actually need session data
        if ($primaryTable === 'views' && $this->needsSessionJoin($sources, [], $filters)) {
            $joins = $this->addSessionJoinForViews($joins);
        }

        // Add resources join if needed for views table (for comments source, etc.)
        if ($primaryTable === 'views' && $this->needsResourcesJoin($sources, [])) {
            $joins = $this->addResourcesJoinForViews($joins);
        }

        // Add date range filter
        if ($dateFrom && $dateTo) {
            $dateColumn = $this->getDateColumn($primaryTable, [], $sources);
            $where[]    = "$dateColumn >= %s";
            $where[]    = "$dateColumn <= %s";
            $params[]   = $this->formatDateTimeStart($dateFrom);
            $params[]   = $this->formatDateTimeEnd($dateTo);
        }

        // Add custom filters
        if (!empty($filters)) {
            $filterResult = FilterBuilder::build($filters);
            $where        = array_merge($where, $filterResult['conditions']);
            $params       = array_merge($params, $filterResult['params']);
            $joins        = array_merge($joins, $this->normalizeJoins($filterResult['joins']));
        }

        // Build SQL
        $sql = "SELECT\n    " . implode(",\n    ", $select);
        $sql .= "\nFROM " . $from;

        foreach ($joins as $join) {
            $joinType = $join['type'] ?? 'LEFT';
            $sql .= "\n{$joinType} JOIN {$join['table']} AS {$join['alias']}";
            $sql .= "\n    ON {$join['on']}";
        }

        if (!empty($where)) {
            $sql .= "\nWHERE " . implode("\n    AND ", $where);
        }

        return [
            'sql'    => $sql,
            'params' => $params,
        ];
    }

    /**
     * Validate and resolve the ORDER BY field.
     *
     * Ensures the order_by value is a valid source name or group_by column.
     * If invalid, falls back to the first source or null.
     *
     * @param string|null $orderBy          Requested order_by value.
     * @param array       $sources          Available source WP_Statistics_names (used as column aliases).
     * @param array       $groupByNames     Available group_by WP_Statistics_names.
     * @param array       $requestedColumns Requested columns to include in SELECT.
     * @return string|null Valid order_by value or null.
     */
    private function validateOrderBy(?string $orderBy, array $sources, array $groupByNames, array $requestedColumns = []): ?string
    {
        // If no order_by specified, default to first source
        if ($orderBy === null) {
            return $sources[0] ?? null;
        }

        // Check if order_by is a valid source name (these become column aliases)
        if (in_array($orderBy, $sources, true)) {
            return $orderBy;
        }

        // Check if order_by is a valid group_by column that's actually in the SELECT
        foreach ($groupByNames as $groupByName) {
            $groupByItem = $this->groupByRegistry->get($groupByName);
            if ($groupByItem) {
                // Use requestedColumns to get only the columns that will be in SELECT
                $selectColumns = $groupByItem->getSelectColumns($requestedColumns);
                foreach ($selectColumns as $selectColumn) {
                    // Extract alias from "expression AS alias" format
                    if (preg_match('/\s+AS\s+(\w+)$/i', $selectColumn, $matches)) {
                        if ($matches[1] === $orderBy) {
                            return $orderBy;
                        }
                    }
                }
            }
        }

        // Invalid order_by, fall back to first source
        return $sources[0] ?? null;
    }

    /**
     * Determine the primary table.
     *
     * @param array $sources    Source WP_Statistics_names.
     * @param array $groupBy Group by WP_Statistics_names.
     * @param array $filters    Filter data.
     * @return string
     */
    private function determinePrimaryTable(array $sources, array $groupBy, array $filters): string
    {
        // Check sources
        foreach ($sources as $sourceName) {
            $source = $this->sourceRegistry->get($sourceName);
            if ($source) {
                $table = $source->getTable();
                if ($table === 'exclusions') {
                    return 'exclusions';
                }
                if ($table === 'events') {
                    return 'events';
                }
                if ($table === 'views') {
                    return 'views';
                }
            }
        }

        // Check group by
        foreach ($groupBy as $groupByName) {
            $groupByItem = $this->groupByRegistry->get($groupByName);
            if ($groupByItem) {
                $requirement = $groupByItem->getRequirement();
                if ($requirement === 'exclusions') {
                    return 'exclusions';
                }
                if ($requirement === 'events') {
                    return 'events';
                }
                if ($requirement === 'views') {
                    return 'views';
                }
            }
        }

        // Check filters
        if (FilterBuilder::requiresEventsTable($filters)) {
            return 'events';
        }
        if (FilterBuilder::requiresViewsTable($filters)) {
            return 'views';
        }

        return 'sessions';
    }

    /**
     * Determine the primary table based on sources only.
     *
     * @param array $sources Source WP_Statistics_names.
     * @return string
     */
    private function determinePrimaryTableForSources(array $sources): string
    {
        foreach ($sources as $sourceName) {
            $source = $this->sourceRegistry->get($sourceName);
            if ($source) {
                $table = $source->getTable();
                if ($table === 'exclusions') {
                    return 'exclusions';
                }
                if ($table === 'events') {
                    return 'events';
                }
                if ($table === 'views') {
                    return 'views';
                }
            }
        }

        return 'sessions';
    }

    /**
     * Get full table name with prefix.
     *
     * @param string $table Short table name, full WordPress core table name, or wp:tablename for WP core tables.
     * @return string
     */
    private function getFullTableName(string $table): string
    {
        // If table already has WordPress prefix (e.g., wp_users), don't add statistics prefix
        if (strpos($table, $this->wpdb->prefix) === 0) {
            return $table;
        }

        // If table starts with "wp:" prefix, use WordPress prefix instead of statistics prefix
        // e.g., "wp:users" becomes "wp_users" (or whatever the WP prefix is)
        if (strpos($table, 'wp:') === 0) {
            $wpTable = substr($table, 3); // Remove "wp:" prefix
            return $this->wpdb->prefix . $wpTable;
        }

        return $this->tablePrefix . $table;
    }

    /**
     * Get date column for a table.
     *
     * Special handling for online_visitor groupBy and online_visitors source
     * which need to filter by ended_at (last activity) instead of started_at.
     *
     * @param string $table Table name.
     * @param array  $groupByNames GroupBy WP_Statistics_names for context-specific column selection.
     * @param array  $sourceNames Source WP_Statistics_names for context-specific column selection.
     * @return string
     */
    private function getDateColumn(string $table, array $groupByNames = [], array $sourceNames = []): string
    {
        // Special case: online_visitor groupBy or online_visitors source uses ended_at for date filtering
        if (in_array('online_visitor', $groupByNames, true) || in_array('online_visitors', $sourceNames, true)) {
            return 'sessions.ended_at';
        }

        switch ($table) {
            case 'sessions':
                return 'sessions.started_at';
            case 'views':
                return 'views.viewed_at';
            case 'visitors':
                return 'visitors.created_at';
            case 'events':
                return 'events.date';
            case 'exclusions':
                return 'exclusions.date';
            default:
                return 'sessions.started_at';
        }
    }

    /**
     * Normalize joins array.
     *
     * @param array $joins Join configurations.
     * @return array
     */
    private function normalizeJoins(array $joins): array
    {
        $normalized = [];
        foreach ($joins as $join) {
            if (!isset($join['alias'])) {
                continue;
            }
            $normalized[$join['alias']] = [
                'table' => $this->getFullTableName($join['table']),
                'alias' => $join['alias'],
                'on'    => $join['on'],
                'type'  => $join['type'] ?? 'LEFT',
            ];
        }
        return $normalized;
    }

    /**
     * Check if session join is needed for views query.
     *
     * @param array $sources      Source WP_Statistics_names.
     * @param array $groupByNames Group by WP_Statistics_names.
     * @param array $filters      Filter key-value pairs.
     * @return bool
     */
    private function needsSessionJoin(array $sources, array $groupByNames, array $filters = []): bool
    {
        // Check if any source requires sessions table
        $sessionDependentSources = ['visitors', 'sessions', 'bounce_rate', 'avg_session_duration',
                                     'pages_per_session', 'total_duration'];

        foreach ($sources as $sourceName) {
            if (in_array($sourceName, $sessionDependentSources, true)) {
                return true;
            }

            $source = $this->sourceRegistry->get($sourceName);
            if ($source && $source->getTable() === 'sessions') {
                return true;
            }
        }

        // Check if any groupBy requires sessions table or visitor data
        $sessionDependentGroupBy = ['date', 'month', 'week', 'hour', 'country', 'city', 'continent',
                                     'browser', 'os', 'device_type', 'language', 'resolution',
                                     'referrer', 'visitor'];

        foreach ($groupByNames as $groupByName) {
            if (in_array($groupByName, $sessionDependentGroupBy, true)) {
                return true;
            }
        }

        // Check if any filter requires sessions table via its joins
        foreach (array_keys($filters) as $filterKey) {
            if (FilterBuilder::isAllowed($filterKey)) {
                $config = FilterBuilder::getConfig($filterKey);
                if ($config && isset($config['joins'])) {
                    foreach ($config['joins'] as $join) {
                        if (isset($join['on']) && strpos($join['on'], 'sessions.') !== false) {
                            return true;
                        }
                    }
                }
                // Also check requirement property
                $requirement = FilterBuilder::getRequirement($filterKey);
                if ($requirement === 'sessions') {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Add session join for views table.
     *
     * @param array $joins Existing joins.
     * @return array
     */
    private function addSessionJoinForViews(array $joins): array
    {
        if (!isset($joins['sessions'])) {
            $joins['sessions'] = [
                'table' => $this->getFullTableName('sessions'),
                'alias' => 'sessions',
                'on'    => 'views.session_id = sessions.ID',
                'type'  => 'INNER',
            ];
        }
        return $joins;
    }

    /**
     * Check if resources join is needed for views query.
     *
     * The resources join is needed when:
     * - The 'comments' source is used (requires resources.cached_author_id for filtering)
     * - Any groupBy or filter that references the resources table
     *
     * @param array $sources      Source names.
     * @param array $groupByNames Group by names.
     * @return bool
     */
    private function needsResourcesJoin(array $sources, array $groupByNames): bool
    {
        // Check if comments or published_content source is used - they need resources table
        // published_content uses resources.cached_author_id for author-context detection
        if (in_array('comments', $sources, true) || in_array('published_content', $sources, true)) {
            return true;
        }

        // Check if any groupBy needs resources table
        $resourcesDependentGroupBy = ['page', 'author', 'post_type'];
        foreach ($groupByNames as $groupByName) {
            if (in_array($groupByName, $resourcesDependentGroupBy, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Add resources join for views table.
     *
     * Adds the necessary joins to access the resources table:
     * views -> resource_uris -> resources
     *
     * @param array $joins Existing joins.
     * @return array
     */
    private function addResourcesJoinForViews(array $joins): array
    {
        if (!isset($joins['resource_uris'])) {
            $joins['resource_uris'] = [
                'table' => $this->getFullTableName('resource_uris'),
                'alias' => 'resource_uris',
                'on'    => 'views.resource_uri_id = resource_uris.ID',
                'type'  => 'LEFT',
            ];
        }

        if (!isset($joins['resources'])) {
            $joins['resources'] = [
                'table' => $this->getFullTableName('resources'),
                'alias' => 'resources',
                'on'    => 'resource_uris.resource_id = resources.ID AND resources.is_deleted = 0',
                'type'  => 'LEFT',
            ];
        }

        return $joins;
    }

    /**
     * Assemble the main SQL query.
     *
     * @param array       $select   SELECT columns.
     * @param string      $from     FROM clause.
     * @param array       $joins    JOIN clauses.
     * @param array       $where    WHERE conditions.
     * @param array       $groupBy  GROUP BY columns.
     * @param string|null $orderBy  ORDER BY field.
     * @param string      $order    ORDER direction.
     * @param int         $limit    LIMIT value.
     * @param int         $offset   OFFSET value.
     * @return string
     */
    private function assembleSql(
        array $select,
        string $from,
        array $joins,
        array $where,
        array $groupBy,
        ?string $orderBy,
        string $order,
        int $limit,
        int $offset
    ): string {
        $sql = "SELECT\n    " . implode(",\n    ", $select);
        $sql .= "\nFROM " . $from;

        foreach ($joins as $join) {
            $joinType = $join['type'] ?? 'LEFT';
            $sql .= "\n{$joinType} JOIN {$join['table']} AS {$join['alias']}";
            $sql .= "\n    ON {$join['on']}";
        }

        if (!empty($where)) {
            $sql .= "\nWHERE " . implode("\n    AND ", $where);
        }

        if (!empty($groupBy)) {
            $sql .= "\nGROUP BY " . implode(", ", $groupBy);
        }

        if ($orderBy) {
            $sql .= "\nORDER BY $orderBy $order";
        }

        $sql .= "\nLIMIT $limit OFFSET $offset";

        return $sql;
    }

    /**
     * Assemble the count SQL query.
     *
     * @param string $from    FROM clause.
     * @param array  $joins   JOIN clauses.
     * @param array  $where   WHERE conditions.
     * @param array  $groupBy GROUP BY columns.
     * @return string
     */
    private function assembleCountSql(string $from, array $joins, array $where, array $groupBy): string
    {
        if (empty($groupBy)) {
            $sql = "SELECT COUNT(*) as total";
        } else {
            $sql = "SELECT COUNT(DISTINCT " . implode(", ", $groupBy) . ") as total";
        }

        $sql .= "\nFROM " . $from;

        foreach ($joins as $join) {
            $joinType = $join['type'] ?? 'LEFT';
            $sql .= "\n{$joinType} JOIN {$join['table']} AS {$join['alias']}";
            $sql .= "\n    ON {$join['on']}";
        }

        if (!empty($where)) {
            $sql .= "\nWHERE " . implode("\n    AND ", $where);
        }

        return $sql;
    }

    /**
     * Format date/datetime for start of range.
     *
     * @param string $date Date or datetime string.
     * @return string Formatted datetime.
     */
    private function formatDateTimeStart(string $date): string
    {
        // If already has time component, use as-is
        if (preg_match('/\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2}/', $date)) {
            return $date;
        }
        // Otherwise add start of day time
        return $date . ' 00:00:00';
    }

    /**
     * Format date/datetime for end of range.
     *
     * @param string $date Date or datetime string.
     * @return string Formatted datetime.
     */
    private function formatDateTimeEnd(string $date): string
    {
        // If already has time component, use as-is
        if (preg_match('/\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2}/', $date)) {
            return $date;
        }
        // Otherwise add end of day time
        return $date . ' 23:59:59';
    }
}
