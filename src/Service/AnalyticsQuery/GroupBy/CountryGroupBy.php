<?php

namespace WP_Statistics\Service\AnalyticsQuery\GroupBy;

/**
 * Country group by - groups by country.
 *
 * @since 15.0.0
 */
class CountryGroupBy extends AbstractGroupBy
{
    protected $name         = 'country';
    protected $column       = 'countries.name';
    protected $alias        = 'country_name';
    protected $extraColumns = [
        'countries.ID AS country_id',
        'countries.code AS country_code',
        'countries.continent_code AS country_continent_code',
        'countries.continent AS country_continent',
        'SUM(sessions.total_views) AS total_views',
    ];
    protected $joins        = [
        [
            'table' => 'countries',
            'alias' => 'countries',
            'on'    => 'sessions.country_id = countries.ID',
            'type'  => 'INNER',
        ],
    ];
    protected $groupBy      = 'countries.ID';
}
