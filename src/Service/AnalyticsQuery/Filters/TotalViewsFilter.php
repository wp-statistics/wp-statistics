<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Total views filter - filters by total page views.
 *
 * @since 15.0.0
 */
class TotalViewsFilter extends AbstractFilter
{
    /** @var string Filter identifier for API requests: filters[total_views]=... */
    protected $name = 'total_views';

    /** @var string SQL column: total page view count for the visitor from visitors table */
    protected $column = 'visitors.views_count';

    /** @var string Data type: integer for view count comparisons */
    protected $type = 'integer';

    /** @var string UI component: number input for view count entry */
    protected $inputType = 'number';

    /** @var array Supported operators: greater than, less than, and range */
    protected $supportedOperators = ['gt', 'lt', 'between'];

    /** @var array Available on: visitors page for visitor engagement analysis */
    protected $groups = ['visitors'];

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return esc_html__('Total Views', 'wp-statistics');
    }
}
