<?php

namespace WP_STATISTICS;

use WP_Statistics\Service\Analytics\DeviceDetection\DeviceHelper;
use WP_Statistics\Service\Analytics\VisitorProfile;

class UserOnline
{
    /**
     * Check Users Online Option name
     *
     * @var string
     */
    public static $check_user_online_opt = 'wp_statistics_check_user_online';

    /**
     * Default User Reset Time User Online
     *
     * @var int
     */
    public static $reset_user_time = 65; # Second

    /**
     * UserOnline constructor.
     */
    public function __construct()
    {
        # Reset User Online Count
        add_action('init', array($this, 'reset_user_online'));
    }

    /**
     * Check Active User Online System
     *
     * @return mixed
     */
    public static function active()
    {
        /**
         * Disable/Enable User Online for Custom request
         *
         * @example add_filter('wp_statistics_active_user_online', function(){ if( is_page() ) { return false; } });
         */
        return (has_filter('wp_statistics_active_user_online')) ? apply_filters('wp_statistics_active_user_online', true) : Option::get('useronline');
    }

    /**
     * Reset Online User Process By Option time
     *
     * @return string
     */
    public function reset_user_online()
    {
        global $wpdb;

        //Check User Online is Active in this WordPress
        if (self::active()) {

            //Get Not timestamp
            $now = TimeZone::getCurrentTimestamp();

            // Set the default seconds a user needs to visit the site before they are considered offline.
            $reset_time = apply_filters('wp_statistics_reset_user_online_time', self::$reset_user_time);

            // We want to delete users that are over the number of seconds set by the admin.
            $time_diff = (int)$now - (int)$reset_time;

            //Last check Time
            $wps_run = get_option(self::$check_user_online_opt);

            if (isset($wps_run) and is_numeric($wps_run)) {
                if (($wps_run + $reset_time) > $now) {
                    return;
                }
            }

            // Call the deletion query.
            $wpdb->query(
                $wpdb->prepare("DELETE FROM `" . DB::table('useronline') . "` WHERE timestamp < %s", $time_diff)
            );

            //Update Last run this Action
            update_option(self::$check_user_online_opt, $now);
        }
    }

    /**
     * Record Users Online
     *
     * @param array $args
     * @param $visitorProfile VisitorProfile
     * @throws \Exception
     */
    public static function record($visitorProfile = null, $args = array())
    {
        if (!$visitorProfile) {
            $visitorProfile = new VisitorProfile();
        }

        # Get User IP
        $user_ip = $visitorProfile->getProcessedIPForStorage();

        # Check Current Use Exist online list
        $user_online = self::is_ip_online($user_ip);

        # Check Users Exist in Online list
        if ($user_online === false) {

            # Added New Online User
            self::add_user_online($visitorProfile, $args);

        } else {

            # Update current User Time
            self::update_user_online($visitorProfile, $args);

        }
    }

    /**
     * Check IP is online
     *
     * @param bool $user_ip
     * @return bool
     */
    public static function is_ip_online($user_ip = false)
    {
        global $wpdb;
        $user_online = $wpdb->query(
            $wpdb->prepare("SELECT * FROM `" . DB::table('useronline') . "` WHERE `ip` = %s", $user_ip)
        );
        return (!$user_online ? false : $user_online);
    }

    /**
     * Add User Online to Database
     *
     * @param array $args
     * @param VisitorProfile $visitorProfile
     * @throws \Exception
     */
    public static function add_user_online($visitorProfile, $args = array())
    {
        global $wpdb;

        $current_page = $visitorProfile->getCurrentPageType();
        $user_agent   = $visitorProfile->getUserAgent();
        $pageId       = Pages::getPageId($current_page['type'], $current_page['id']);

        //Prepare User online Data
        $user_online = array(
            'ip'        => $visitorProfile->getProcessedIPForStorage(),
            'timestamp' => TimeZone::getCurrentTimestamp(),
            'created'   => TimeZone::getCurrentTimestamp(),
            'date'      => TimeZone::getCurrentDate(),
            'referred'  => $visitorProfile->getReferrer(),
            'agent'     => $user_agent->getBrowser(),
            'platform'  => $user_agent->getPlatform(),
            'version'   => $user_agent->getVersion(),
            'location'  => $visitorProfile->getCountry(),
            'region'    => $visitorProfile->getRegion(),
            'continent' => $visitorProfile->getContinent(),
            'city'      => $visitorProfile->getCity(),
            'user_id'   => $visitorProfile->getUserId(),
            'page_id'   => $pageId,
            'type'      => $current_page['type'],
            'visitor_id'=> $visitorProfile->getVisitorId()
        );
        $user_online = apply_filters('wp_statistics_user_online_information', wp_parse_args($args, $user_online));

        # Insert the user in to the database.
        $insert = $wpdb->insert(
            DB::table('useronline'),
            $user_online
        );

        if (!$insert) {
            if (!empty($wpdb->last_error)) {
                \WP_Statistics::log($wpdb->last_error, 'warning');
            }
        }

        # Get User Online ID
        $user_online_id = $wpdb->insert_id;

        # Action After Save User Online
        do_action('wp_statistics_save_user_online', $user_online_id, $user_online);
    }

    /**
     * Update User Online
     * @param $visitorProfile VisitorProfile
     */
    public static function update_user_online($visitorProfile, $args = array())
    {
        global $wpdb;

        $current_page = $visitorProfile->getCurrentPageType();
        $user_id      = $visitorProfile->getUserId();
        $sameVisitor  = $visitorProfile->isIpActiveToday();

        if(! empty($sameVisitor)) {
            $user_id = $sameVisitor->user_id;
        }
        
        $pageId = Pages::getPageId($current_page['type'], $current_page['id']);

        //Prepare User online Update data
        $user_online = array(
            'timestamp' => TimeZone::getCurrentTimestamp(),
            'date'      => TimeZone::getCurrentDate(),
            'user_id'   => $user_id,
            'page_id'   => $pageId,
            'type'      => $current_page['type']
        );
        $user_online = apply_filters('wp_statistics_update_user_online_data', wp_parse_args($args, $user_online));

        # Update the database with the new information.
        $wpdb->update(DB::table('useronline'), $user_online, array(
            'ip' => $visitorProfile->getProcessedIPForStorage()
        ));

        # Action After Update User Online
        do_action('wp_statistics_update_user_online', $user_id, $user_online);
    }

    /**
     * Get User Online List By Custom Query
     *
     * @param array $arg
     * @return array
     * @throws \Exception
     */
    public static function get($arg = array())
    {
        global $wpdb;

        // Define the array of defaults
        $defaults = array(
            'sql'      => '',
            'per_page' => 10,
            'offset'   => 0,
            'fields'   => 'all',
            'order'    => 'DESC',
            'orderby'  => 'ID'
        );
        $args     = wp_parse_args($arg, $defaults);

        // Prepare SQL
        $args['sql'] = null;
        $SQL         = "SELECT";

        // Check Fields
        if ($args['fields'] == "count") {
            $SQL .= " COUNT(*)";
        } elseif ($args['fields'] == "all") {
            $SQL .= " *";
        } else {
            $SQL .= $args['fields'];
        }
        $SQL .= " FROM `" . DB::table('useronline') . "`";

        // Check Count
        if ($args['fields'] == "count") {
            return $wpdb->get_var($SQL); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        }

        // Prepare Query
        if (empty($args['sql'])) {
            $args['sql'] = "SELECT * FROM `" . DB::table('useronline') . "` ORDER BY ID DESC";
        }

        // Set Pagination
        $args['sql'] = esc_sql($args['sql']) . $wpdb->prepare(" LIMIT %d, %d", $args['offset'], $args['per_page']);

        // Send Request
        $result = $wpdb->get_results($args['sql']); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

        // Get List
        $list = array();
        foreach ($result as $items) {

            $ip       = esc_html($items->ip);
            $agent    = esc_html($items->agent);
            $platform = esc_html($items->platform);

            $item = array(
                'referred' => Referred::get_referrer_link($items->referred),
                'agent'    => $agent,
                'platform' => $platform,
                'version'  => $items->version,
            );

            // Add User information
            if ($items->user_id > 0 and User::exists($items->user_id)) {
                $user_data    = User::get($items->user_id);
                $item['user'] = array(
                    'ID'         => $items->user_id,
                    'user_email' => $user_data['user_email'],
                    'user_login' => $user_data['user_login'],
                    'name'       => User::get_name($items->user_id)
                );
            }

            // Page info
            $item['page'] = Visitor::get_page_by_id($items->page_id);

            // Push Browser
            $item['browser'] = array(
                'name' => $agent,
                'logo' => DeviceHelper::getPlatformLogo($agent),
                'link' => Menus::admin_url('visitors', array('agent' => $agent))
            );

            // Push IP
            if (IP::IsHashIP($ip)) {
                $item['ip'] = array('value' => substr($ip, 6, 10), 'link' => Menus::admin_url('visitors', array('ip' => urlencode($ip))));
            } else {
                $item['ip']  = array('value' => $ip, 'link' => Menus::admin_url('visitors', array('ip' => $ip)));
                $item['map'] = Helper::geoIPTools($ip);
            }

            $item['country'] = array('location' => $items->location, 'flag' => Country::flag($items->location), 'name' => Country::getName($items->location));
            $item['city']    = $items->city;
            $item['region']  = $items->region;

            $item['single_url'] = Menus::admin_url('visitors', ['type' => 'single-visitor', 'visitor_id' => $items->visitor_id]);

            // Online For Time
            $current_time = current_time('timestamp'); // Fetch current server time in WordPress format
            $time_diff    = $items->timestamp - $items->created;

            if ($items->timestamp == $items->created) {
                $time_diff = $current_time - $items->created;
            }

            // Ensure time_diff is positive and log the real time difference
            if ($time_diff < 0) {
                $time_diff = abs($time_diff);
            }

            if ($time_diff < 1) {
                $item['online_for'] = "00:00:00";
            } else if ($time_diff >= 3600) {
                $item['online_for'] = gmdate("H:i:s", $time_diff); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
            } else if ($time_diff >= 60) {
                $item['online_for'] = "00:" . gmdate("i:s", $time_diff); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
            } else {
                $item['online_for'] = "00:00:" . str_pad($time_diff, 2, "0", STR_PAD_LEFT); // Display seconds correctly
            }

            $list[] = $item;
        }

        return $list;
    }


}