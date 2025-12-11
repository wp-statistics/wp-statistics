<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Last seen filter - filters by last activity timestamp.
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
     * @var string Column path: visitors.last_hit
     */
    protected $column = 'visitors.last_hit';

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
     * @var array Operators: in_the_last, between, before, after
     */
    protected $supportedOperators = ['in_the_last', 'between', 'before', 'after'];

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
        return esc_html__('Last Seen', 'wp-statistics');
    }
}
