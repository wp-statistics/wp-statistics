<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Country filter - filters by country code.
 *
 * @since 15.0.0
 */
class CountryFilter extends AbstractFilter
{
    protected $name   = 'country';
    protected $column = 'countries.code';
    protected $type   = 'string';
    protected $joins  = [
        'table' => 'countries',
        'alias' => 'countries',
        'on'    => 'sessions.country_id = countries.ID',
    ];
}
