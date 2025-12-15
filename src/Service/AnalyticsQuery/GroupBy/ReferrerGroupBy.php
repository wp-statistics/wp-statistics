<?php

namespace WP_Statistics\Service\AnalyticsQuery\GroupBy;

/**
 * Referrer group by - groups by referrer.
 *
 * @since 15.0.0
 */
class ReferrerGroupBy extends AbstractGroupBy
{
    protected $name         = 'referrer';
    protected $column       = 'referrers.domain';
    protected $alias        = 'referrer_domain';
    protected $extraColumns = [
        'referrers.ID AS referrer_id',
        'referrers.channel AS referrer_channel',
        'referrers.name AS referrer_name',
    ];
    protected $joins        = [
        'table' => 'referrers',
        'alias' => 'referrers',
        'on'    => 'sessions.referrer_id = referrers.ID',
        'type'  => 'INNER',
    ];
    protected $groupBy      = 'referrers.ID';
}
