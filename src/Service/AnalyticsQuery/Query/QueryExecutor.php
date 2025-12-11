<?php

namespace WP_Statistics\Service\AnalyticsQuery\Query;

use WP_Statistics\Globals\Option;
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
        $sql = $this->buildSql($query);

        // Prepare and execute main query
        $preparedSql = $sql['sql'];
        if (!empty($sql['params'])) {
            $preparedSql = $this->wpdb->prepare($sql['sql'], $sql['params']);
        }

        $rows = $this->wpdb->get_results($preparedSql, ARRAY_A);

        // Get total count for pagination
        $countSql = $sql['count_sql'];
        if (!empty($sql['params'])) {
            $countSql = $this->wpdb->prepare($sql['count_sql'], $sql['params']);
        }

        $total = (int) $this->wpdb->get_var($countSql);

        return [
            'rows'  => $rows ?: [],
            'total' => $total,
        ];
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
        $orderBy      = $this->validateOrderBy($query->getOrderBy(), $sources, $groupByNames);
        $order        = $query->getOrder();
        $perPage      = $query->getPerPage();
        $offset       = $query->getOffset();
        $attribution  = Option::getValue('attribution_model', 'first_touch');

        // Determine primary table
        $primaryTable = $this->determinePrimaryTable($sources, $groupByNames, $filters);
        $from         = $this->getFullTableName($primaryTable) . ' AS ' . $primaryTable;

        // Add session join if needed for views table (must come before group by joins)
        if ($primaryTable === 'views') {
            $joins = $this->addSessionJoinForViews($joins);
        }

        // Add group by columns and joins
        foreach ($groupByNames as $groupByName) {
            $groupByItem = $this->groupByRegistry->get($groupByName);
            if (!$groupByItem) {
                continue;
            }

            $select  = array_merge($select, $groupByItem->getSelectColumns($attribution));
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
                $select[] = $source->getExpressionWithAlias();
            }
        }

        // Add date range filter
        if ($dateFrom && $dateTo) {
            $dateColumn = $this->getDateColumn($primaryTable);
            $where[]    = "$dateColumn BETWEEN %s AND %s";
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
                $select[] = $source->getExpressionWithAlias();
            }
        }

        // Add session join if needed for views table (must come before filter joins)
        if ($primaryTable === 'views') {
            $joins = $this->addSessionJoinForViews($joins);
        }

        // Add date range filter
        if ($dateFrom && $dateTo) {
            $dateColumn = $this->getDateColumn($primaryTable);
            $where[]    = "$dateColumn BETWEEN %s AND %s";
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
     * @param string|null $orderBy      Requested order_by value.
     * @param array       $sources      Available source names (used as column aliases).
     * @param array       $groupByNames Available group_by names.
     * @return string|null Valid order_by value or null.
     */
    private function validateOrderBy(?string $orderBy, array $sources, array $groupByNames): ?string
    {
        // If no order_by specified, default to first source
        if ($orderBy === null) {
            return $sources[0] ?? null;
        }

        // Check if order_by is a valid source name (these become column aliases)
        if (in_array($orderBy, $sources, true)) {
            return $orderBy;
        }

        // Check if order_by is a valid group_by column
        foreach ($groupByNames as $groupByName) {
            $groupByItem = $this->groupByRegistry->get($groupByName);
            if ($groupByItem) {
                // Get the group by column to check if it matches
                $selectColumns = $groupByItem->getSelectColumns();
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
     * @param array $sources    Source names.
     * @param array $groupBy Group by names.
     * @param array $filters    Filter data.
     * @return string
     */
    private function determinePrimaryTable(array $sources, array $groupBy, array $filters): string
    {
        // Check sources
        foreach ($sources as $sourceName) {
            $source = $this->sourceRegistry->get($sourceName);
            if ($source && $source->getTable() === 'views') {
                return 'views';
            }
        }

        // Check group by
        foreach ($groupBy as $groupByName) {
            $groupByItem = $this->groupByRegistry->get($groupByName);
            if ($groupByItem && $groupByItem->getRequirement() === 'views') {
                return 'views';
            }
        }

        // Check filters
        if (FilterBuilder::requiresViewsTable($filters)) {
            return 'views';
        }

        return 'sessions';
    }

    /**
     * Determine the primary table based on sources only.
     *
     * @param array $sources Source names.
     * @return string
     */
    private function determinePrimaryTableForSources(array $sources): string
    {
        foreach ($sources as $sourceName) {
            $source = $this->sourceRegistry->get($sourceName);
            if ($source && $source->getTable() === 'views') {
                return 'views';
            }
        }

        return 'sessions';
    }

    /**
     * Get full table name with prefix.
     *
     * @param string $table Short table name.
     * @return string
     */
    private function getFullTableName(string $table): string
    {
        return $this->tablePrefix . $table;
    }

    /**
     * Get date column for a table.
     *
     * @param string $table Table name.
     * @return string
     */
    private function getDateColumn(string $table): string
    {
        switch ($table) {
            case 'sessions':
                return 'sessions.started_at';
            case 'views':
                return 'views.viewed_at';
            case 'visitors':
                return 'visitors.created_at';
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
