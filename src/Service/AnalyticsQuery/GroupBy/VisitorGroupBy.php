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
        'MIN(sessions.started_at) AS first_visit',
        'MAX(sessions.started_at) AS last_visit',
        'COUNT(DISTINCT sessions.ID) AS total_sessions',
        'SUM(sessions.total_views) AS total_views',
        'MAX(sessions.user_id) AS user_id',
        'MAX(sessions.ip) AS ip_address',
        'MAX(countries.code) AS country_code',
        'MAX(countries.name) AS country_name',
        'MAX(cities.city_name) AS city',
        'MAX(cities.region_name) AS region',
        'MAX(device_types.name) AS device_type',
        'MAX(device_oss.name) AS os',
        'MAX(device_browsers.name) AS browser',
        'MAX(referrers.domain) AS referrer',
        'MAX(referrers.channel) AS referrer_channel',
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
