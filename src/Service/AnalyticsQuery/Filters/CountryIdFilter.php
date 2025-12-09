<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Country ID filter - filters by country ID.
 *
 * @since 15.0.0
 */
class CountryIdFilter extends AbstractFilter
{
    protected $name               = 'country_id';
    protected $column             = 'sessions.country_id';
    protected $type               = 'integer';
    protected $supportedOperators = ['is', 'is_not', 'in', 'not_in', 'gt', 'gte', 'lt', 'lte'];
}
