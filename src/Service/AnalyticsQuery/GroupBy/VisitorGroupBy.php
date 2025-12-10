<?php

namespace WP_Statistics\Service\AnalyticsQuery\GroupBy;

/**
 * Visitor group by - groups by visitor.
 *
 * @since 15.0.0
 */
class VisitorGroupBy extends AbstractGroupBy
{
    protected $name         = 'visitor';
    protected $column       = 'visitors.ID';
    protected $alias        = 'visitor_id';
    protected $extraColumns = [
        'visitors.hash AS visitor_hash',
        'visitors.first_hit AS first_visit',
        'visitors.last_hit AS last_visit',
        'visitors.sessions_count AS total_sessions',
        'visitors.views_count AS total_views',
        'MAX(sessions.user_id) AS user_id',
        'MAX(sessions.ip) AS ip_address',
        'countries.code AS country_code',
        'countries.name AS country_name',
        'cities.name AS city',
        'cities.region_name AS region',
        'device_types.name AS device_type',
        'device_oss.name AS os',
        'device_browsers.name AS browser',
        'referrers.domain AS referrer',
        'referrers.channel AS referrer_channel',
    ];
    protected $joins        = [
        [
            'table' => 'visitors',
            'alias' => 'visitors',
            'on'    => 'sessions.visitor_id = visitors.ID',
            'type'  => 'INNER',
        ],
        [
            'table' => 'countries',
            'alias' => 'countries',
            'on'    => 'sessions.country_id = countries.ID',
            'type'  => 'LEFT',
        ],
        [
            'table' => 'cities',
            'alias' => 'cities',
            'on'    => 'sessions.city_id = cities.ID',
            'type'  => 'LEFT',
        ],
        [
            'table' => 'device_types',
            'alias' => 'device_types',
            'on'    => 'sessions.device_type_id = device_types.ID',
            'type'  => 'LEFT',
        ],
        [
            'table' => 'device_oss',
            'alias' => 'device_oss',
            'on'    => 'sessions.device_os_id = device_oss.ID',
            'type'  => 'LEFT',
        ],
        [
            'table' => 'device_browsers',
            'alias' => 'device_browsers',
            'on'    => 'sessions.device_browser_id = device_browsers.ID',
            'type'  => 'LEFT',
        ],
        [
            'table' => 'referrers',
            'alias' => 'referrers',
            'on'    => 'sessions.referrer_id = referrers.ID',
            'type'  => 'LEFT',
        ],
    ];
    protected $groupBy      = 'visitors.ID';
}
