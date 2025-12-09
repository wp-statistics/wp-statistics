<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Referrer filter - filters by referrer domain.
 *
 * @since 15.0.0
 */
class ReferrerFilter extends AbstractFilter
{
    protected $name   = 'referrer';
    protected $column = 'referrers.domain';
    protected $type   = 'string';
    protected $joins  = [
        'table' => 'referrers',
        'alias' => 'referrers',
        'on'    => 'sessions.referrer_id = referrers.ID',
    ];
}
