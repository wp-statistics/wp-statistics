<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Continent filter - filters by continent.
 *
 * @since 15.0.0
 */
class ContinentFilter extends AbstractFilter
{
    protected $name   = 'continent';
    protected $column = 'countries.continent_code';
    protected $type   = 'string';
    protected $joins  = [
        'table' => 'countries',
        'alias' => 'countries',
        'on'    => 'sessions.country_id = countries.ID',
    ];
    protected $supportedOperators = ['is', 'is_not', 'in', 'not_in'];
}
