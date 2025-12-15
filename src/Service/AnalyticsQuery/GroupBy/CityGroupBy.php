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
    protected $alias        = 'city_name';
    protected $extraColumns = [
        'cities.ID AS city_id',
        'cities.region_code AS city_region_code',
        'cities.region_name AS city_region_name',
        'cities.country_id AS city_country_id',
        'countries.code AS country_code',
        'countries.name AS country_name',
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
