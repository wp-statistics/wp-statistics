<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Resource ID filter - filters by resource ID.
 *
 * @since 15.0.0
 */
class ResourceIdFilter extends AbstractFilter
{
    /**
     * Filter key for API requests.
     *
     * @var string Filter identifier: filters[resource_id]=...
     */
    protected $name = 'resource_id';

    /**
     * SQL column for WHERE clause.
     *
     * @var string Column path: views.resource_id
     */
    protected $column = 'views.resource_id';

    /**
     * Value type for sanitization.
     *
     * @var string Data type: integer (WordPress post ID)
     */
    protected $type = 'integer';

    /**
     * Required base table to enable this filter.
     *
     * @var string|null Table name: views
     */
    protected $requirement = 'views';

    /**
     * Allowed comparison operators.
     *
     * @var array Operators: is, is_not, in, not_in, gt, gte, lt, lte
     */
    protected $supportedOperators = ['is', 'is_not', 'in', 'not_in', 'gt', 'gte', 'lt', 'lte'];

    /**
     * Pages where this filter is available.
     *
     * @var array Groups: views
     */
    protected $groups = ['views'];

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return esc_html__('Resource ID', 'wp-statistics');
    }
}
