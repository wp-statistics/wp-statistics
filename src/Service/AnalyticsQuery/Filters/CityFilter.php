<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * City filter - filters by city name.
 *
 * @since 15.0.0
 */
class CityFilter extends AbstractFilter
{
    protected $name   = 'city';
    protected $column = 'cities.city_name';
    protected $type   = 'string';
    protected $joins  = [
        'table' => 'cities',
        'alias' => 'cities',
        'on'    => 'sessions.city_id = cities.ID',
    ];
}
