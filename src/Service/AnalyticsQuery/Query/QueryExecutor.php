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
        $select      = [];
        $joins       = [];
        $where       = [];
        $params      = [];
        $groupBy     = [];

        $sources     = $query->getSources();
        $groupBy  = $query->getGroupBy();
        $filters     = $query->getFilters();
        $dateFrom    = $query->getDateFrom();
        $dateTo      = $query->getDateTo();
        $orderBy     = $query->getOrderBy() ?? ($sources[0] ?? null);
        $order       = $query->getOrder();
        $perPage     = $query->getPerPage();
        $offset      = $query->getOffset();

        // Determine primary table
        $primaryTable = $this->determinePrimaryTable($sources, $groupBy, $filters);
        $from         = $this->getFullTableName($primaryTable) . ' AS ' . $primaryTable;

        // Add group by columns and joins
        foreach ($groupBy as $groupByName) {
            $groupByItem = $this->groupByRegistry->get($groupByName);
            if (!$groupByItem) {
                continue;
            }

            $select  = array_merge($select, $groupByItem->getSelectColumns());
            $joins   = array_merge($joins, $this->normalizeJoins($groupByItem->getJoins()));

            if ($groupByItem->getGroupBy()) {
                $groupBy[] = $groupByItem->getGroupBy();
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
            $params[]   = $dateFrom . ' 00:00:00';
            $params[]   = $dateTo . ' 23:59:59';
        }

        // Add session join if needed for views table (must come before filter joins)
        if ($primaryTable === 'views') {
            $joins = $this->addSessionJoinForViews($joins);
        }

        // Add custom filters
        if (!empty($filters)) {
            $filterResult = FilterBuilder::build($filters);
            $where        = array_merge($where, $filterResult['conditions']);
            $params       = array_merge($params, $filterResult['params']);
            $joins        = array_merge($joins, $this->normalizeJoins($filterResult['joins']));
        }

        // Build main SQL
        $sql = $this->assembleSql($select, $from, $joins, $where, $groupBy, $orderBy, $order, $perPage, $offset);

        // Build count SQL
        $countSql = $this->assembleCountSql($from, $joins, $where, $groupBy);

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

        // Add date range filter
        if ($dateFrom && $dateTo) {
            $dateColumn = $this->getDateColumn($primaryTable);
            $where[]    = "$dateColumn BETWEEN %s AND %s";
            $params[]   = $dateFrom . ' 00:00:00';
            $params[]   = $dateTo . ' 23:59:59';
        }

        // Add session join if needed for views table (must come before filter joins)
        if ($primaryTable === 'views') {
            $joins = $this->addSessionJoinForViews($joins);
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
}
