<?php

namespace WP_Statistics\Service\AnalyticsQuery\GroupBy;

/**
 * City group by - groups by city.
 *
 * @since 15.0.0
 */
class CityGroupBy extends AbstractGroupBy
{
    protected $name         = 'city';
    protected $column       = 'cities.city_name';
    protected $alias        = 'city';
    protected $extraColumns = [
        'countries.code AS country_code',
        'cities.region_name AS region',
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
    protected $groupBy      = 'cities.ID';
}
