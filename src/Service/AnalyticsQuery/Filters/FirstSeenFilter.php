<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * First seen filter - filters by first visit date.
 *
 * @since 15.0.0
 */
class FirstSeenFilter extends AbstractFilter
{
    /**
     * Filter key for API requests.
     *
     * @var string Filter identifier: filters[first_seen]=...
     */
    protected $name = 'first_seen';

    /**
     * SQL column for WHERE clause.
     *
     * @var string Column path: visitors.first_hit
     */
    protected $column = 'visitors.first_hit';

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
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return esc_html__('First Seen', 'wp-statistics');
    }
}
