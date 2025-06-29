<?php

namespace WP_STATISTICS;

use WP_Statistics\Service\Analytics\VisitorProfile;
use WP_Statistics\Utils\Request;

class Exclusion
{
    /**
     * Array for storing options.
     *
     * @access private
     * @var array
     * @static
     */
    private static $options = [];

    /**
     * Get Exclusion List
     *
     * @return array
     */
    public static function exclusion_list()
    {
        return array(
            'ajax'            => __('Ajax', 'wp-statistics'),
            'cronjob'         => __('Cron job', 'wp-statistics'),
            'robot'           => __('Robot', 'wp-statistics'),
            'BrokenFile'      => __('Broken Link', 'wp-statistics'),
            'ip match'        => __('IP Match', 'wp-statistics'),
            'self referral'   => __('Self Referral', 'wp-statistics'),
            'login page'      => __('Login Page', 'wp-statistics'),
            'admin page'      => __('Admin Page', 'wp-statistics'),
            'referrer_spam'   => __('Referrer Spam', 'wp-statistics'),
            'feed'            => __('Feed', 'wp-statistics'),
            '404'             => __('404', 'wp-statistics'),
            'excluded url'    => __('Excluded URL', 'wp-statistics'),
            'user role'       => __('User Role', 'wp-statistics'),
            'geoip'           => __('Geolocation', 'wp-statistics'),
            'robot_threshold' => __('Robot threshold', 'wp-statistics'),
            'xmlrpc'          => __('XML-RPC', 'wp-statistics'),
            'cross site'      => __('Cross site Request', 'wp-statistics'),
            'pre flight'      => __('Pre-flight Request', 'wp-statistics'),
        );
    }

    /**
     * Check to see if the user wants us to record why we're excluding hits.
     *
     * @return mixed
     */
    public static function record_active()
    {
        return !empty(self::$options['record_exclusions']);
    }

    /**
     * Checks exclusion tracking visits and visitors.
     * @param $visitorProfile VisitorProfile
     */
    public static function check($visitorProfile)
    {

        // Create Default Object
        $exclude = array('exclusion_match' => false, 'exclusion_reason' => '');

        // Get List Of Exclusion WP Statistics
        $exclusion_list = apply_filters('wp_statistics_exclusion_list', array_keys(Exclusion::exclusion_list()));

        if (empty(self::$options)) {
            self::$options = Option::getOptions();
        }

        // Check Exclusion
        foreach ($exclusion_list as $list) {
            $method = 'exclusion_' . strtolower(str_replace(array("-", " "), "_", $list));

            // Check if method exists
            if (method_exists(self::class, $method)) {
                $check = call_user_func([self::class, $method], $visitorProfile);

                if ($check) {
                    $exclude = array('exclusion_match' => true, 'exclusion_reason' => $list);
                    break;
                }
            }
        }

        return apply_filters('wp_statistics_exclusion', $exclude, $visitorProfile);
    }

    /**
     * Record Exclusion in WP Statistics DB.
     *
     * @param array $exclusion
     */
    public static function record($exclusion = array())
    {
        global $wpdb;

        // If we're not storing exclusions, just return.
        if (self::record_active() != true) {
            return;
        }

        // Check Exist this Exclusion in this day
        $result = $wpdb->query(
            $wpdb->prepare("UPDATE `" . DB::table('exclusions') . "` SET `count` = `count` + 1 WHERE `date` = %s AND `reason` = %s", TimeZone::getCurrentDate('Y-m-d'), $exclusion['exclusion_reason'])
        );

        if (!$result) {
            $insert = $wpdb->insert(
                DB::table('exclusions'),
                array(
                    'date'   => TimeZone::getCurrentDate('Y-m-d'),
                    'reason' => $exclusion['exclusion_reason'],
                    'count'  => 1,
                )
            );

            if (!$insert) {
                if (!empty($wpdb->last_error)) {
                    \WP_Statistics::log($wpdb->last_error);
                }
            }

            do_action('wp_statistics_save_exclusion', $exclusion, $wpdb->insert_id);
        }
    }

    /**
     * Detect if we're running an ajax request.
     */
    public static function exclusion_ajax()
    {
        // White list actions
        if (Helper::isBypassAdBlockersRequest() || Request::compare('action', 'wp_statistics_event')) {
            return false;
        }

        return (defined('DOING_AJAX') and DOING_AJAX);
    }

    /**
     * Detect if we're running an WordPress CronJob.
     */
    public static function exclusion_cronjob()
    {
        return (defined('DOING_CRON') && DOING_CRON === true) || (function_exists('wp_doing_cron') && wp_doing_cron() === true);
    }

    /**
     * Detect if WordPress Feed.
     */
    public static function exclusion_feed()
    {
        return !empty(self::$options['exclude_feeds']) && is_feed();
    }

    /**
     * Detect if WordPress 404 Page.
     */
    public static function exclusion_404()
    {
        if (!empty(self::$options['exclude_404s'])) {

            if (Helper::is_rest_request() && isset($_REQUEST['source_type']) && $_REQUEST['source_type'] == '404') {
                return true;
            }

            return is_404();
        }
    }

    /**
     * Detect if robot threshold.
     * @param $visitorProfile VisitorProfile
     */
    public static function exclusion_robot_threshold($visitorProfile)
    {
        $robotThreshold = ! empty(self::$options['robot_threshold']) ? intval(self::$options['robot_threshold']) : 0;

        if ($robotThreshold <= 0) {
            return false;
        }

        $visitor = $visitorProfile->isIpActiveToday();

        if (!$visitor) {
            return false;
        }

        return ($visitor->hits + 1 > $robotThreshold);
    }

    /**
     * Detect if Exclude WordPress User role.
     */
    public static function exclusion_user_role()
    {
        $current_user = false;

        if (Helper::is_rest_request() && isset($GLOBALS['wp_statistics_user_id'])) {
            $user_id = $GLOBALS['wp_statistics_user_id'];

            if ($user_id) {
                $current_user = get_user_by('id', $user_id);
            }
        } elseif (is_user_logged_in()) {
            $current_user = wp_get_current_user();
        }

        if ($current_user) {
            foreach ($current_user->roles as $role) {
                $option_name = 'exclude_' . str_replace(' ', '_', strtolower($role));
                if (!empty(self::$options[$option_name])) {
                    return true;
                }
            }
        } else {
            // Guest visitor
            if (!empty(self::$options['exclude_anonymous_users'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Detects if current URL opened by the visitor should be excluded.
     *
     * @param VisitorProfile $visitorProfile VisitorProfile
     *
     * @return bool
     */
    public static function exclusion_excluded_url($visitorProfile)
    {
        $excludedUrls = self::$options['excluded_urls'] ?? '';

        if (!empty($excludedUrls)) {
            $requestUri = $visitorProfile->getRequestUri();
            $delimiter  = strpos($requestUri, '?');

            // Remove query parameters from the request URI
            if ($delimiter > 0) {
                $requestUri = substr($requestUri, 0, $delimiter);
            }

            // Strip slashes from the beginning and the end of the request URI
            $requestUri = trim($requestUri, '/\\');

            // Decode request URI since input URLs will be decoded too
            $requestUri = urldecode($requestUri);

            foreach (explode("\n", $excludedUrls) as $url) {
                // Sanitize input URL
                $url = wp_make_link_relative($url);
                $url = trim($url);
                $url = trim($url, '/\\');
                $url = urldecode($url);

                if (strlen($url) > 2) {
                    // Check if the URL contains a wildcard (*)
                    if (strpos($url, '*') !== false) {
                        // Escape special characters for regex, then replace '*' with '.*' for wildcards
                        $pattern = str_replace('\*', '.*', preg_quote($url, '/'));

                        // Adjust the pattern to allow wildcards at both ends or in the middle
                        if (preg_match('/^' . $pattern . '$/i', $requestUri)) {
                            return true;
                        }
                    } else {
                        // Exact match check
                        if (strtolower($url) == strtolower($requestUri)) {
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     * Detect if Referrer Spam.
     * @param $visitorProfile VisitorProfile
     */
    public static function exclusion_referrer_spam($visitorProfile)
    {
        // Check to see if we're excluding referrer spam.
        if (!empty(self::$options['referrerspam'])) {
            $referrer = $visitorProfile->getReferrer();

            // Pull the referrer spam list from the database.
            $referrer_spam_list = explode("\n", self::$options['referrerspamlist'] ?? '');

            // Check to see if we match any of the robots.
            foreach ($referrer_spam_list as $item) {
                $item = trim($item);

                // If the match case is less than 4 characters long, it might match too much so don't execute it.
                if (strlen($item) > 3) {
                    if (stripos($referrer, $item) !== false) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Detect if Self Referral WordPress.
     * @param $visitorProfile VisitorProfile
     */
    public static function exclusion_self_referral($visitorProfile)
    {
        $userAgent  = $visitorProfile->getHttpUserAgent();
        $version    = Helper::get_wordpress_version();

        return in_array($userAgent, [
            'WordPress/' . $version . '; ' . get_home_url(null, '/'),
            'WordPress/' . $version . '; ' . get_home_url()
        ]);
    }

    /**
     * Detect if WordPress Login Page.
     */
    public static function exclusion_login_page()
    {
        return !empty(self::$options['exclude_loginpage']) && Helper::is_login_page();
    }

    /**
     * Detect if WordPress Admin Page.
     * @param $visitorProfile VisitorProfile
     */
    public static function exclusion_admin_page($visitorProfile)
    {

        $requestUri = $visitorProfile->getRequestUri();

        if (isset($_SERVER['SERVER_NAME']) and isset($requestUri)) {

            // Remove Query From Url
            $url = Helper::RemoveQueryStringUrl($_SERVER['SERVER_NAME'] . $requestUri);

            if (!Helper::isBypassAdBlockersRequest() && !Request::compare('action', 'wp_statistics_event') && stripos($url, 'wp-admin') !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Detect if IP Match.
     *
     * @throws \Exception
     */
    public static function exclusion_iP_match()
    {
        if (empty(self::$options['exclude_ip'])) {
            return false;
        }

        // Pull the sub nets from the database.
        $SubNets = explode("\n", self::$options['exclude_ip']);

        // Check in Loop
        foreach ($SubNets as $subnet) {

            // Sanitize SubNet
            $subnet = trim($subnet);

            // The shortest ip address is 1.1.1.1, anything less must be a malformed entry.
            if (strlen($subnet) > 6) {

                // Check in Range
                if (IP::checkIPRange(array($subnet))) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Detect if Broken Link.
     * @param $visitorProfile VisitorProfile
     */
    public static function exclusion_brokenfile($visitorProfile)
    {
        // Check is 404
        if (is_404()) {

            $requestUri = $visitorProfile->getRequestUri();

            //Check Current Page
            if (isset($_SERVER["HTTP_HOST"]) and isset($requestUri)) {

                //Get Full Url Page
                $page_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER["HTTP_HOST"]}{$requestUri}";

                //Check Link file
                $page_url = wp_parse_url($page_url, PHP_URL_PATH);
                $ext      = pathinfo($page_url, PATHINFO_EXTENSION);
                if (!empty($ext) and $ext != 'php') {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Detect if Robots.
     * @param $visitorProfile VisitorProfile
     */
    public static function exclusion_robot($visitorProfile)
    {
        $rawUserAgent = $visitorProfile->getHttpUserAgent();

        // Check user ip is empty or not user agent
        if (empty($rawUserAgent) || empty($visitorProfile->getIp())) {
            return true;
        }

        $userAgent = $visitorProfile->getUserAgent();

        if ($userAgent->isBot()) {
            return true;
        }

        if (!$userAgent->isBrowserDetected() && !$userAgent->isPlatformDetected()) {
            return true;
        }

        // Pull the robots from the database.
        $robots = explode("\n", self::$options['robotlist'] ?? '');
        $robots = apply_filters('wp_statistics_exclusion_robots', $robots);

        // Check to see if we match any of the robots.
        foreach ($robots as $robot) {
            $robot = trim($robot);

            // If the match case is less than 4 characters long, it might match too much so don't execute it.
            if (strlen($robot) > 3) {
                if (stripos($rawUserAgent, $robot) !== false) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Detect if GeoIP include or exclude country.
     *
     * @param VisitorProfile VisitorProfile
     * @throws \Exception
     */
    public static function exclusion_geoip($visitorProfile)
    {
        static $excludedCountries = null;
        static $includedCountries = null;

        if ($excludedCountries === null) {
            $excluded_option   = self::$options['excluded_countries'] ?? '';
            $excludedCountries = empty($excluded_option) ? [] :
                array_flip(array_filter(explode("\n", strtoupper(str_replace("\r\n", "\n", $excluded_option)))));
        }

        if ($includedCountries === null) {
            $included_option = self::$options['included_countries'] ?? '';

            if (empty($included_option)) {
                $includedCountries = [];
            } else {
                $included_countries_string = trim(strtoupper(str_replace("\r\n", "\n", $included_option)));
                $includedCountries = $included_countries_string === '' ? [] :
                    array_flip(array_filter(explode("\n", $included_countries_string)));
            }
        }

        if ( empty($excludedCountries) && empty($includedCountries) ) {
            return false;
        }

        $location = $visitorProfile->getCountry();

        if (empty($location)) {
            return false;
        }

        $location = strtoupper($location);

        if (isset($excludedCountries[$location])) {
            return true;
        }

        return !empty($includedCountries) && !isset($includedCountries[$location]);
    }

    /**
     *  Detect if XML-RPC
     */
    public static function exclusion_xmlrpc()
    {
        return (defined('XMLRPC_REQUEST') && XMLRPC_REQUEST === true);
    }

    /**
     * Detect if Cross Site
     */
    public static function exclusion_cross_site()
    {
        return isset($_SERVER['HTTP_SEC_FETCH_SITE']) && 'cross-site' === $_SERVER['HTTP_SEC_FETCH_SITE'];
    }

    /**
     * Detect if Pre Flight
     */
    public static function exclusion_pre_flight()
    {
        return isset($_SERVER['REQUEST_METHOD'], $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'], $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'], $_SERVER['HTTP_ORIGIN']) && 'OPTIONS' === $_SERVER['REQUEST_METHOD'];
    }
}
