<?php

namespace WP_Statistics\Service\AnalyticsQuery\GroupBy;

/**
 * Region group by - groups by region/state.
 *
 * @since 15.0.0
 */
class RegionGroupBy extends AbstractGroupBy
{
    protected $name         = 'region';
    protected $column       = 'cities.region_name';
    protected $alias        = 'region_name';
    protected $extraColumns = [
        'cities.region_code AS region_code',
        'countries.ID AS country_id',
        'countries.code AS country_code',
        'countries.name AS country_name',
        'SUM(sessions.total_views) AS total_views',
    ];
    protected $joins        = [
        [
            'table' => 'cities',
            'alias' => 'cities',
            'on'    => 'sessions.city_id = cities.ID',
            'type'  => 'INNER',
        ],
        [
            'table' => 'countries',
            'alias' => 'countries',
            'on'    => 'cities.country_id = countries.ID',
            'type'  => 'INNER',
        ],
    ];
    protected $groupBy      = 'cities.region_code, cities.country_id';
}
