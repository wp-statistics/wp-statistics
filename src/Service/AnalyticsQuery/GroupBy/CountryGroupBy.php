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
    protected $alias        = 'country';
    protected $extraColumns = [
        'countries.code AS country_code',
    ];
    protected $joins        = [
        'table' => 'countries',
        'alias' => 'countries',
        'on'    => 'sessions.country_id = countries.ID',
        'type'  => 'INNER',
    ];
    protected $groupBy      = 'countries.ID';
}
