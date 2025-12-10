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
    protected $alias        = 'referrer';
    protected $extraColumns = [
        'referrers.channel AS referrer_type',
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
