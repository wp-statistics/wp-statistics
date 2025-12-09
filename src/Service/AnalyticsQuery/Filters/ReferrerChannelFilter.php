<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Referrer channel filter - filters by referrer channel (e.g., search, social, direct).
 *
 * @since 15.0.0
 */
class ReferrerChannelFilter extends AbstractFilter
{
    protected $name   = 'referrer_channel';
    protected $column = 'referrers.channel';
    protected $type   = 'string';
    protected $joins  = [
        'table' => 'referrers',
        'alias' => 'referrers',
        'on'    => 'sessions.referrer_id = referrers.ID',
    ];
    protected $supportedOperators = ['is', 'is_not', 'in', 'not_in'];
}
