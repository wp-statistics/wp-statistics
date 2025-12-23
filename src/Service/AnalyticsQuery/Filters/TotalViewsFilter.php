<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Total views filter - filters by total page views.
 *
 * @since 15.0.0
 */
class TotalViewsFilter extends AbstractFilter
{
    /**
     * Filter key for API requests.
     *
     * @var string Filter identifier: filters[total_views]=...
     */
    protected $name = 'total_views';

    /**
     * SQL column for WHERE clause.
     *
     * @var string Column path: sessions.total_views
     */
    protected $column = 'sessions.total_views';

    /**
     * Required base table for this filter.
     *
     * @var string Requires sessions table to be joined.
     */
    protected $requirement = 'sessions';

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
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return esc_html__('Total Views', 'wp-statistics');
    }
}
