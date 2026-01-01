<?php

namespace WP_Statistics\Service\AnalyticsQuery\GroupBy;

/**
 * Continent group by - groups by continent.
 *
 * @since 15.0.0
 */
class ContinentGroupBy extends AbstractGroupBy
{
    protected $name         = 'continent';
    protected $column       = 'countries.continent';
    protected $alias        = 'continent_name';
    protected $extraColumns = [
        'countries.continent_code AS continent_code',
    ];
    protected $joins        = [
        'table' => 'countries',
        'alias' => 'countries',
        'on'    => 'sessions.country_id = countries.ID',
        'type'  => 'INNER',
    ];
    protected $groupBy      = 'countries.continent_code';
}
