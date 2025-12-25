<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Views per session filter - filters by average pages viewed per session.
 *
 * This filters visitors based on their average page views per session,
 * calculated as AVG(sessions.total_views) across all their sessions.
 *
 * @since 15.0.0
 */
class ViewsPerSessionFilter extends AbstractFilter
{
    /**
     * Filter key for API requests.
     *
     * @var string Filter identifier: filters[views_per_session]=...
     */
    protected $name = 'views_per_session';

    /**
     * SQL column for WHERE clause.
     *
     * This is overridden by getColumn() to use dynamic table prefix.
     *
     * @var string Column path: subquery for AVG(total_views)
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
     * @var array Operators: gt, gte, lt, lte
     */
    protected $supportedOperators = ['gt', 'gte', 'lt', 'lte'];

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
     * Uses a subquery to calculate AVG(total_views) for the visitor.
     *
     * @return string
     */
    public function getColumn(): string
    {
        global $wpdb;
        $sessionsTable = $wpdb->prefix . 'statistics_sessions';
        return "ROUND((SELECT AVG(vps.total_views) FROM `{$sessionsTable}` vps WHERE vps.visitor_id = visitors.ID), 2)";
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return esc_html__('Views per Session', 'wp-statistics');
    }
}
