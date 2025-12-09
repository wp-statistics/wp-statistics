<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Referrer name filter - filters by referrer name (e.g., Google, Facebook).
 *
 * @since 15.0.0
 */
class ReferrerNameFilter extends AbstractFilter
{
    protected $name   = 'referrer_name';
    protected $column = 'referrers.name';
    protected $type   = 'string';
    protected $joins  = [
        'table' => 'referrers',
        'alias' => 'referrers',
        'on'    => 'sessions.referrer_id = referrers.ID',
    ];
    protected $supportedOperators = ['is', 'is_not', 'in', 'not_in', 'contains', 'starts_with', 'ends_with'];
}
