<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Total sessions filter - filters by total number of sessions.
 *
 * This filters visitors based on their total session count,
 * calculated as COUNT of sessions for each visitor.
 *
 * @since 15.0.0
 */
class TotalSessionsFilter extends AbstractFilter
{
    /**
     * Filter key for API requests.
     *
     * @var string Filter identifier: filters[total_sessions]=...
     */
    protected $name = 'total_sessions';

    /**
     * SQL column for WHERE clause.
     *
     * This is overridden by getColumn() to use dynamic table prefix.
     *
     * @var string Column path: subquery for COUNT(sessions)
     */
    protected $column = '';

    /**
     * Value type for sanitization.
     *
     * @var string Data type: integer
     */
    protected $type = 'integer';

    /**
     * UI input component type.
     *
     * @var string Input type: number
     */
    protected $inputType = 'number';

    /**
     * Allowed comparison operators.
     *
     * @var array Operators: gt, lt, between
     */
    protected $supportedOperators = ['gt', 'lt', 'between'];

    /**
     * Pages where this filter is available.
     *
     * @var array Groups: visitors
     */
    protected $groups = ['visitors'];

    /**
     * Required base table to enable this filter.
     *
     * @var string|null Table name: sessions
     */
    protected $requirement = 'sessions';

    /**
     * Required JOINs to access the column.
     *
     * @var array JOIN: sessions -> visitors
     */
    protected $joins = [
        'table' => 'visitors',
        'alias' => 'visitors',
        'on'    => 'sessions.visitor_id = visitors.ID',
        'type'  => 'LEFT',
    ];

    /**
     * Get the SQL column for WHERE clause.
     *
     * Uses a subquery to count sessions for the visitor.
     *
     * @return string
     */
    public function getColumn(): string
    {
        global $wpdb;
        $sessionsTable = $wpdb->prefix . 'statistics_sessions';
        return "(SELECT COUNT(*) FROM `{$sessionsTable}` ts WHERE ts.visitor_id = visitors.ID)";
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return esc_html__('Total Sessions', 'wp-statistics');
    }
}
