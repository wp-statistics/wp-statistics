<?php

namespace WP_Statistics\Service\AnalyticsQuery\GroupBy;

/**
 * Referrer channel group by - groups by referrer channel (e.g., search, social, direct).
 *
 * @since 15.0.0
 */
class ReferrerChannelGroupBy extends AbstractGroupBy
{
    protected $name         = 'referrer_channel';
    protected $column       = 'referrers.channel';
    protected $alias        = 'referrer_channel';
    protected $extraColumns = [];
    protected $joins        = [
        'table' => 'referrers',
        'alias' => 'referrers',
        'on'    => 'sessions.referrer_id = referrers.ID',
        'type'  => 'INNER',
    ];
    protected $groupBy      = 'referrers.channel';
}
