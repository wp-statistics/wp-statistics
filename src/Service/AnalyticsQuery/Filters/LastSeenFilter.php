<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Last seen filter - filters by last activity timestamp.
 *
 * This filters visitors based on their most recent session start time,
 * calculated as MAX(sessions.started_at) for each visitor.
 *
 * @since 15.0.0
 */
class LastSeenFilter extends AbstractFilter
{
    /**
     * Filter key for API requests.
     *
     * @var string Filter identifier: filters[last_seen]=...
     */
    protected $name = 'last_seen';

    /**
     * SQL column for WHERE clause.
     *
     * This is overridden by getColumn() to use dynamic table prefix.
     *
     * @var string Column path: subquery for MAX(started_at)
     */
    protected $column = '';

    /**
     * Value type for sanitization.
     *
     * @var string Data type: date
     */
    protected $type = 'date';

    /**
     * UI input component type.
     *
     * @var string Input type: date
     */
    protected $inputType = 'date';

    /**
     * Allowed comparison operators.
     *
     * @var array Operators: between, before, after
     */
    protected $supportedOperators = ['between', 'before', 'after'];

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
     * Uses a subquery to get the MAX(started_at) for the visitor.
     *
     * @return string
     */
    public function getColumn(): string
    {
        global $wpdb;
        $sessionsTable = $wpdb->prefix . 'statistics_sessions';
        return "(SELECT MAX(ls.started_at) FROM `{$sessionsTable}` ls WHERE ls.visitor_id = visitors.ID)";
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return esc_html__('Last Seen', 'wp-statistics');
    }
}
