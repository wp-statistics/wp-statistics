<?php

namespace WP_Statistics\Service\AnalyticsQuery\GroupBy;

use WP_Statistics\Components\DateTime;

/**
 * Event record group by - returns individual event rows.
 *
 * Groups by events.ID to produce one row per event record,
 * enriched with page, visitor, and session info via joins.
 *
 * @since 15.0.0
 */
class EventRecordGroupBy extends AbstractGroupBy
{
    protected $name        = 'event_record';
    protected $column      = 'events.ID';
    protected $alias       = 'event_id';
    protected $groupBy     = 'events.ID';
    protected $order       = 'DESC';
    protected $requirement = 'events';

    protected $extraColumns = [
        'events.date AS event_date',
        'events.event_name AS event_name',
        'events.event_data AS event_data_raw',
        'resources.cached_title AS page_title',
        'resource_uris.uri AS page_uri',
        // Visitor/session info (for visitor-info column type)
        'countries.code AS country_code',
        'countries.name AS country_name',
        'cities.city_name AS city_name',
        'device_oss.name AS os_name',
        'device_browsers.name AS browser_name',
        'device_browser_versions.version AS browser_version',
        'visitors.hash AS visitor_hash',
        'visitors.ip AS visitor_ip',
        'sessions.user_id AS user_id',
    ];

    protected $joins = [
        // Page joins
        [
            'table' => 'resource_uris',
            'alias' => 'resource_uris',
            'on'    => 'events.resource_uri_id = resource_uris.ID',
            'type'  => 'LEFT',
        ],
        [
            'table' => 'resources',
            'alias' => 'resources',
            'on'    => 'resource_uris.resource_id = resources.ID',
            'type'  => 'LEFT',
        ],
        // Session + visitor joins
        [
            'table' => 'sessions',
            'alias' => 'sessions',
            'on'    => 'events.session_id = sessions.ID',
            'type'  => 'LEFT',
        ],
        [
            'table' => 'visitors',
            'alias' => 'visitors',
            'on'    => 'sessions.visitor_id = visitors.ID',
            'type'  => 'LEFT',
        ],
        // Geographic joins
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
        // Device joins
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
            'table' => 'device_browser_versions',
            'alias' => 'device_browser_versions',
            'on'    => 'sessions.device_browser_version_id = device_browser_versions.ID',
            'type'  => 'LEFT',
        ],
    ];

    protected $datetimeFields = ['event_date'];

    protected $postProcessedColumns = ['event_date_formatted', 'event_data_summary', 'user_login', 'user_role'];

    /**
     * {@inheritdoc}
     */
    public function postProcess(array $rows, \wpdb $wpdb): array
    {
        if (empty($rows)) {
            return $rows;
        }

        $rows = $this->convertDatetimeFields($rows);

        // Collect user IDs for batch lookup
        $userIds = [];
        foreach ($rows as $row) {
            if (!empty($row['user_id'])) {
                $userIds[] = (int) $row['user_id'];
            }
        }

        // Batch fetch user data
        $userData = [];
        if (!empty($userIds)) {
            $userIds = array_unique($userIds);
            $placeholders = implode(',', array_fill(0, count($userIds), '%d'));
            $query = $wpdb->prepare(
                "SELECT u.ID, u.user_login, um.meta_value AS role
                FROM {$wpdb->users} u
                LEFT JOIN {$wpdb->usermeta} um ON u.ID = um.user_id AND um.meta_key = '{$wpdb->prefix}capabilities'
                WHERE u.ID IN ($placeholders)",
                $userIds
            );
            $results = $wpdb->get_results($query, ARRAY_A);
            foreach ($results as $user) {
                $roles = maybe_unserialize($user['role']);
                $roleName = is_array($roles) ? array_key_first($roles) : '';
                $userData[(int) $user['ID']] = [
                    'user_login' => $user['user_login'],
                    'user_role'  => $roleName,
                ];
            }
        }

        foreach ($rows as &$row) {
            // Format date
            if (!empty($row['event_date'])) {
                $row['event_date_formatted'] = DateTime::format($row['event_date'], ['include_time' => true]);
            } else {
                $row['event_date_formatted'] = null;
            }

            // Build event data summary
            $row['event_data_summary'] = $this->formatEventDataSummary(
                $row['event_name'] ?? '',
                $row['event_data_raw'] ?? ''
            );

            // Add user info
            $userId = !empty($row['user_id']) ? (int) $row['user_id'] : null;
            if ($userId && isset($userData[$userId])) {
                $row['user_login'] = $userData[$userId]['user_login'];
                $row['user_role'] = $userData[$userId]['user_role'];
            } else {
                $row['user_login'] = null;
                $row['user_role'] = null;
            }
        }

        return $rows;
    }

    private function formatEventDataSummary(string $eventName, string $rawJson): array
    {
        $data = json_decode($rawJson, true);
        if (!is_array($data) || empty($data)) {
            return [];
        }

        // Known key labels for built-in events
        $builtInLabels = [
            'tu'   => 'URL',
            'ev'   => 'Text',
            'eid'  => 'Element ID',
            'ec'   => 'Class',
            'fn'   => 'Filename',
            'fx'   => 'Extension',
            'wcdl' => 'WooCommerce',
            'df'   => 'Download File',
            'dk'   => 'Download Key',
        ];

        // Keys to skip (internal/noise)
        $skipKeys = ['et', 'pid', 'mb'];

        $pairs = [];
        foreach ($data as $key => $value) {
            if (in_array($key, $skipKeys, true)) {
                continue;
            }
            if (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            }
            if ($value === '' || $value === null) {
                continue;
            }

            $label = $builtInLabels[$key] ?? ucwords(str_replace('_', ' ', $key));
            $pairs[] = ['key' => $label, 'value' => (string) $value];
        }

        return $pairs;
    }
}
