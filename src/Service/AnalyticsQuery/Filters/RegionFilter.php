<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Region filter - filters by region/state.
 *
 * @since 15.0.0
 */
class RegionFilter extends AbstractFilter
{
    protected $name   = 'region';
    protected $column = 'cities.region_name';
    protected $type   = 'string';
    protected $joins  = [
        'table' => 'cities',
        'alias' => 'cities',
        'on'    => 'sessions.city_id = cities.ID',
    ];
    protected $supportedOperators = ['is', 'is_not', 'in', 'not_in', 'contains', 'starts_with', 'ends_with'];

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return esc_html__('Region', 'wp-statistics');
    }
}
