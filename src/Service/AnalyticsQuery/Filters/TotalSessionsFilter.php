<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Total sessions filter - filters by total sessions.
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
     * @var string Column path: visitors.sessions_count
     */
    protected $column = 'visitors.sessions_count';

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
        return esc_html__('Total Sessions', 'wp-statistics');
    }
}
