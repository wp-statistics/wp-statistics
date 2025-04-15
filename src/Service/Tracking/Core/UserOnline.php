<?php

namespace WP_STATISTICS\Service\Tracking\Core;

use WP_STATISTICS\Abstracts\BaseTracking;
use WP_STATISTICS\Country;
use WP_STATISTICS\Option;
use WP_STATISTICS\Pages;
use WP_STATISTICS\DB;
use WP_STATISTICS\Helper;
use WP_STATISTICS\IP;
use WP_STATISTICS\Menus;
use WP_STATISTICS\Referred;
use WP_Statistics\Service\Analytics\DeviceDetection\DeviceHelper;
use WP_Statistics\Service\Analytics\VisitorProfile;
use WP_STATISTICS\User;
use WP_STATISTICS\Visitor;

/**
 * Tracks and manages real-time user sessions.
 *
 * This class handles detecting, recording, updating, and cleaning up records
 * for users currently online on the website.
 */
class UserOnline extends BaseTracking
{
    /**
     * Option key used to store the last reset timestamp in the database.
     *
     * @var string
     */
    protected $resetOptionKey = 'wp_statistics_check_user_online';

    /**
     * Number of seconds after which a user is considered offline.
     *
     * @var int
     */
    protected $resetUserTime = 65;

    /**
     * Initialize hooks for resetting online user records.
     *
     * @return void
     */
    public function __construct()
    {
        add_action('init', [$this, 'reset']);
    }

    /**
     * Check if user online tracking is currently enabled.
     *
     * @return bool True if tracking is active, false otherwise.
     */
    public static function isActive()
    {
        if (has_filter('wp_statistics_active_user_online')) {
            return apply_filters('wp_statistics_active_user_online', true);
        }

        return Option::get('useronline', true);
    }

    /**
     * Cleans up expired online user sessions based on configured timeout.
     *
     * @return void
     */
    public function reset()
    {
        global $wpdb;

        if (! self::isActive()) {
            return;
        }

        $now = $this->getCurrentTimestamp();
        $timeout = apply_filters('wp_statistics_reset_user_online_time', $this->resetUserTime);
        $cutoff = $now - $timeout;

        $lastRun = get_option($this->resetOptionKey);
        if (is_numeric($lastRun) && ($lastRun + $timeout) > $now) {
            return;
        }

        $wpdb->query(
            $wpdb->prepare("DELETE FROM `" . DB::table('useronline') . "` WHERE `timestamp` < %d", $cutoff)
        );

        update_option($this->resetOptionKey, $now);
    }

    /**
     * Record or update a user as online.
     *
     * @param VisitorProfile|null $profile Visitor profile instance (optional).
     * @param array $args Additional data to include in the record.
     * @return void
     */
    public function record($profile = null, $args = [])
    {
        global $wpdb;

        $profile = $this->resolveProfile($profile);
        $ip = $profile->getProcessedIPForStorage();

        $exists = $wpdb->get_var(
            $wpdb->prepare("SELECT COUNT(*) FROM `" . DB::table('useronline') . "` WHERE `ip` = %s", $ip)
        );

        if (! $exists) {
            $this->insert($profile, $args);
        } else {
            $this->update($profile, $args);
        }
    }

    /**
     * Insert a new online user record into the database.
     *
     * @param VisitorProfile $profile Visitor data to record.
     * @param array $args Additional data to merge with defaults.
     * @return void
     */
    protected function insert(VisitorProfile $profile, $args = [])
    {
        global $wpdb;

        $page = $profile->getCurrentPageType();
        $agent = $profile->getUserAgent();
        $pageId = Pages::getPageId($page['type'], $page['id']);

        $data = [
            'ip'         => $profile->getProcessedIPForStorage(),
            'timestamp'  => $this->getCurrentTimestamp(),
            'created'    => $this->getCurrentTimestamp(),
            'date'       => $this->getCurrentDate(),
            'referred'   => $profile->getReferrer(),
            'agent'      => $agent->getBrowser(),
            'platform'   => $agent->getPlatform(),
            'version'    => $agent->getVersion(),
            'location'   => $profile->getCountry(),
            'region'     => $profile->getRegion(),
            'continent'  => $profile->getContinent(),
            'city'       => $profile->getCity(),
            'user_id'    => $profile->getUserId(),
            'page_id'    => $pageId,
            'type'       => $page['type'],
            'visitor_id' => $profile->getVisitorId(),
        ];

        $data = apply_filters('wp_statistics_user_online_information', wp_parse_args($args, $data));

        $inserted = $wpdb->insert(DB::table('useronline'), $data);

        if (! $inserted && ! empty($wpdb->last_error)) {
            \WP_Statistics::log($wpdb->last_error, 'warning');
        }

        do_action('wp_statistics_save_user_online', $wpdb->insert_id, $data);
    }

    /**
     * Update an existing online user session.
     *
     * @param VisitorProfile $profile Visitor data used for update.
     * @param array $args Optional fields to override default update fields.
     * @return void
     */
    protected function update(VisitorProfile $profile, $args = [])
    {
        global $wpdb;

        $page = $profile->getCurrentPageType();
        $userId = $profile->getUserId();

        $existing = $profile->isIpActiveToday();
        if (! empty($existing)) {
            $userId = $existing->user_id;
        }

        $pageId = Pages::getPageId($page['type'], $page['id']);

        $data = [
            'timestamp' => $this->getCurrentTimestamp(),
            'date'      => $this->getCurrentDate(),
            'user_id'   => $userId,
            'page_id'   => $pageId,
            'type'      => $page['type'],
        ];

        $data = apply_filters('wp_statistics_update_user_online_data', wp_parse_args($args, $data));

        $wpdb->update(
            DB::table('useronline'),
            $data,
            ['ip' => $profile->getProcessedIPForStorage()]
        );

        do_action('wp_statistics_update_user_online', $userId, $data);
    }

    /**
     * Get User Online List By Custom Query.
     *
     * @param array $args {
     *     Optional. Query arguments.
     *
     *     @type string $sql Custom SQL query to use instead of default.
     *     @type int    $per_page Number of results to retrieve.
     *     @type int    $offset Offset for pagination.
     *     @type string $fields Fields to retrieve: 'all', 'count', or custom fields.
     *     @type string $order Order direction ('ASC' or 'DESC').
     *     @type string $orderby Field to order by.
     * }
     * @return array Array of online user records.
     */
    public function get($args = [])
    {
        global $wpdb;

        $defaults = [
            'sql'      => '',
            'per_page' => 10,
            'offset'   => 0,
            'fields'   => 'all',
            'order'    => 'DESC',
            'orderby'  => 'ID'
        ];

        $args = wp_parse_args($args, $defaults);

        $SQL = 'SELECT';
        if ($args['fields'] === 'count') {
            $SQL .= ' COUNT(*)';
        } elseif ($args['fields'] === 'all') {
            $SQL .= ' *';
        } else {
            $SQL .= ' ' . $args['fields'];
        }

        $SQL .= ' FROM `' . DB::table('useronline') . '`';

        if ($args['fields'] === 'count') {
            return $wpdb->get_var($SQL);
        }

        if (empty($args['sql'])) {
            $args['sql'] = 'SELECT * FROM `' . DB::table('useronline') . '` ORDER BY ID DESC';
        }

        $args['sql'] = esc_sql($args['sql']) . $wpdb->prepare(' LIMIT %d, %d', $args['offset'], $args['per_page']);
        $result = $wpdb->get_results($args['sql']);

        $list = [];
        foreach ($result as $items) {
            $ip = esc_html($items->ip);
            $agent = esc_html($items->agent);
            $platform = esc_html($items->platform);

            $item = [
                'referred' => Referred::get_referrer_link($items->referred),
                'agent'    => $agent,
                'platform' => $platform,
                'version'  => $items->version,
            ];

            if ($items->user_id > 0 && User::exists($items->user_id)) {
                $user_data = User::get($items->user_id);
                $item['user'] = [
                    'ID'         => $items->user_id,
                    'user_email' => $user_data['user_email'],
                    'user_login' => $user_data['user_login'],
                    'name'       => User::get_name($items->user_id)
                ];
            }

            $item['page'] = Visitor::get_page_by_id($items->page_id);

            $item['browser'] = [
                'name' => $agent,
                'logo' => DeviceHelper::getPlatformLogo($agent),
                'link' => Menus::admin_url('visitors', ['agent' => $agent])
            ];

            if (IP::IsHashIP($ip)) {
                $item['ip'] = ['value' => substr($ip, 6, 10), 'link' => Menus::admin_url('visitors', ['ip' => urlencode($ip)])];
            } else {
                $item['ip'] = ['value' => $ip, 'link' => Menus::admin_url('visitors', ['ip' => $ip])];
                $item['map'] = Helper::geoIPTools($ip);
            }

            $item['country'] = [
                'location' => $items->location,
                'flag'     => Country::flag($items->location),
                'name'     => Country::getName($items->location)
            ];
            $item['city'] = $items->city;
            $item['region'] = $items->region;
            $item['single_url'] = Menus::admin_url('visitors', ['type' => 'single-visitor', 'visitor_id' => $items->visitor_id]);

            $currentTime = current_time('timestamp');
            $timeDiff = $items->timestamp - $items->created;

            if ($items->timestamp == $items->created) {
                $timeDiff = $currentTime - $items->created;
            }

            if ($timeDiff < 0) {
                $timeDiff = abs($timeDiff);
            }

            if ($timeDiff < 1) {
                $item['online_for'] = '00:00:00';
            } elseif ($timeDiff >= 3600) {
                $item['online_for'] = gmdate('H:i:s', $timeDiff);
            } elseif ($timeDiff >= 60) {
                $item['online_for'] = '00:' . gmdate('i:s', $timeDiff);
            } else {
                $item['online_for'] = '00:00:' . str_pad($timeDiff, 2, '0', STR_PAD_LEFT);
            }

            $list[] = $item;
        }

        return $list;
    }
}
