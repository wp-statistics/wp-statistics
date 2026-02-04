<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * IP filter - filters by visitor IP address.
 *
 * @since 15.0.0
 */
class IpFilter extends AbstractFilter
{
    /**
     * Filter key for API requests.
     *
     * @var string Filter identifier: filters[ip]=...
     */
    protected $name = 'ip';

    /**
     * SQL column for WHERE clause.
     *
     * @var string Column path: visitors.ip
     */
    protected $column = 'visitors.ip';

    /**
     * Value type for sanitization.
     *
     * @var string Data type: string
     */
    protected $type = 'string';

    /**
     * UI input component type.
     *
     * @var string Input type: text
     */
    protected $inputType = 'text';

    /**
     * Allowed comparison operators.
     *
     * @var array Operators: is, is_not, contains
     */
    protected $supportedOperators = ['is', 'is_not', 'contains'];

    /**
     * Pages where this filter is available.
     *
     * @var array Groups: visitors
     */
    protected $groups = ['visitors', 'views'];

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return esc_html__('IP Address/Hash', 'wp-statistics');
    }
}
