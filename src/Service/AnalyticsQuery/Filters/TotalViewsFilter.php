<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Total views filter - filters by total page views.
 *
 * @since 15.0.0
 */
class TotalViewsFilter extends AbstractFilter
{
    protected $name               = 'total_views';
    protected $column             = 'visitors.views_count';
    protected $type               = 'integer';
    protected $inputType          = 'number';
    protected $supportedOperators = ['gt', 'lt', 'between'];
    protected $pages              = ['visitors'];

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return esc_html__('Total Views', 'wp-statistics');
    }
}
