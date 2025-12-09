<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Referrer domain filter - filters by referrer domain.
 *
 * @since 15.0.0
 */
class ReferrerDomainFilter extends AbstractFilter
{
    protected $name   = 'referrer_domain';
    protected $column = 'referrers.domain';
    protected $type   = 'string';
    protected $joins  = [
        'table' => 'referrers',
        'alias' => 'referrers',
        'on'    => 'sessions.referrer_id = referrers.ID',
    ];
    protected $supportedOperators = ['is', 'is_not', 'in', 'not_in', 'contains', 'starts_with', 'ends_with'];
}
