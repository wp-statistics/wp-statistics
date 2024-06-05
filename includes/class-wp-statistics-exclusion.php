<?php

namespace WP_STATISTICS;

use Jaybizzle\CrawlerDetect\CrawlerDetect;
use WP_Statistics\Service\Analytics\VisitorProfile;

class Exclusion
{
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
            'CrawlerDetect'   => __('Crawler Detect', 'wp-statistics'),
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
            'hostname'        => __('Host name', 'wp-statistics'),
            'geoip'           => __('GeoIP', 'wp-statistics'),
            'honeypot'        => __('Honeypot', 'wp-statistics'),
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
        return Option::get('record_exclusions');
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

        return apply_filters('wp_statistics_exclusion', $exclude);
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
        return (Option::get('exclude_feeds') and is_feed());
    }

    /**
     * Detect if WordPress 404 Page.
     */
    public static function exclusion_404()
    {
        if (Option::get('exclude_404s')) {

            if (Helper::is_rest_request() && isset($_REQUEST['current_page_type']) && $_REQUEST['current_page_type'] == '404') {
                return true;
            }

            return is_404();
        }
    }

    /**
     * Detect if honeypot.
     * @param $visitorProfile VisitorProfile
     */
    public static function exclusion_honeypot($visitorProfile)
    {
        $current_page = $visitorProfile->getCurrentPageType();
        return (Option::get('use_honeypot') && Option::get('honeypot_postid') > 0 && Option::get('honeypot_postid') == $current_page['id'] && $current_page['id'] > 0);
    }

    /**
     * Detect if robot threshold.
     * @param $visitorProfile VisitorProfile
     */
    public static function exclusion_robot_threshold($visitorProfile)
    {
        $visitor = $visitorProfile->isIpActiveToday();

        return ($visitor != false and Option::get('robot_threshold') > 0 && $visitor->hits + 1 > Option::get('robot_threshold'));
    }

    /**
     * Detect if Exclude WordPress User role.
     */
    public static function exclusion_user_role()
    {
        $current_user = false;

        if (Helper::is_rest_request()) {
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

                if (Option::get($option_name) == true) {
                    return true;
                }
            }
        } else {
            // Guest visitor

            if (Option::get('exclude_anonymous_user') == true)
                return true;
        }

        return false;
    }

    /**
     * Detect if Excluded URL.
     * @param $visitorProfile VisitorProfile
     */
    public static function exclusion_excluded_url($visitorProfile)
    {

        if (Option::get('excluded_urls')) {
            $script    = $visitorProfile->getRequestUri();
            $delimiter = strpos($script, '?');

            if ($delimiter > 0) {
                $script = substr($script, 0, $delimiter);
            }

            $excluded_urls = explode("\n", Option::get('excluded_urls'));
            foreach ($excluded_urls as $url) {
                $this_url = trim($url);

                if (strlen($this_url) > 2) {
                    if (stripos($script, $this_url) === 0) {
                        return true;
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
        if (Option::get('referrerspam')) {
            $referrer = $visitorProfile->getReferrer();

            // Pull the referrer spam list from the database.
            $referrer_spam_list = explode("\n", Option::get('referrerspamlist'));

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
     * Detect if Crawler.
     */
    public static function exclusion_crawlerdetect()
    {
        $CrawlerDetect = new CrawlerDetect;
        if ($CrawlerDetect->isCrawler()) {
            return true;
        }

        return false;
    }

    /**
     * Detect if Self Referral WordPress.
     * @param $visitorProfile VisitorProfile
     */
    public static function exclusion_self_referral($visitorProfile)
    {
        return $visitorProfile->getHttpUserAgent() == 'WordPress/' . Helper::get_wordpress_version() . '; ' . get_home_url(null, '/') || $visitorProfile->getHttpUserAgent() == 'WordPress/' . Helper::get_wordpress_version() . '; ' . get_home_url();
    }

    /**
     * Detect if WordPress Login Page.
     */
    public static function exclusion_login_page()
    {
        return (Option::get('exclude_loginpage') and Helper::is_login_page());
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
            if (stristr($url, "wp-admin") != false) {
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

        // Pull the sub nets from the database.
        $SubNets = explode("\n", Option::get('exclude_ip'));

        // Check in Loop
        foreach ($SubNets as $subnet) {

            // Sanitize SubNet
            $subnet = trim($subnet);

            // The shortest ip address is 1.1.1.1, anything less must be a malformed entry.
            if (strlen($subnet) > 6) {

                // Check in Range
                if (IP::CheckIPRange(array($subnet))) {
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

        // Pull the robots from the database.
        $robots = explode("\n", Option::get('robotlist'));

        // Check to see if we match any of the robots.
        foreach ($robots as $robot) {
            $robot = trim($robot);

            // If the match case is less than 4 characters long, it might match too much so don't execute it.
            if (strlen($robot) > 3) {
                if (stripos($visitorProfile->getHttpUserAgent(), $robot) !== false) {
                    return true;
                }
            }
        }

        // Check user ip is empty or not user agent
        if (Option::get('corrupt_browser_info')) {
            if ($visitorProfile->getHttpUserAgent() == '' || $visitorProfile->getIp() == '') {
                return true;
            }

            $userAgent = $visitorProfile->getUserAgent();

            if (!$userAgent['isBrowserDetected'] && !$userAgent['isPlatformDetected']) {
                return true;
            }
        }

        return false;
    }

    /**
     * Detect if GeoIP include or exclude country.
     *
     * @param $visitorProfile VisitorProfile
     * @throws \Exception
     */
    public static function exclusion_geoip($visitorProfile)
    {
        // Get User Location
        $location = $visitorProfile->getCountry();

        // Grab the excluded/included countries lists, force the country codes to be in upper case to match what the GeoIP code uses.
        $excluded_countries        = explode("\n", strtoupper(str_replace("\r\n", "\n", Option::get('excluded_countries'))));
        $included_countries_string = trim(strtoupper(str_replace("\r\n", "\n", Option::get('included_countries'))));

        // We need to be really sure this isn't an empty string or explode will return an array with one entry instead of none.
        if ($included_countries_string == '') {
            $included_countries = array();
        } else {
            $included_countries = explode("\n", $included_countries_string);
        }

        // Check to see if the current location is in the excluded countries list.
        if (in_array($location, $excluded_countries)) {
            return true;
        } // Check to see if the current location is not the included countries list.
        else if (!in_array($location, $included_countries) && count($included_countries) > 0) {
            return true;
        }

        return false;
    }

    /**
     * Detect if Exclude Host name.
     * @param $visitorProfile VisitorProfile
     */
    public static function exclusion_hostname($visitorProfile)
    {
        // Get Host name List
        $excluded_host = explode("\n", Option::get('excluded_hosts'));

        // If there's nothing in the excluded host list, don't do anything.
        if (count($excluded_host) > 0) {
            $transient_name = 'wps_excluded_hostname_to_ip_cache';

            // Get the transient with the hostname cache.
            $hostname_cache = get_transient($transient_name);

            // If the transient has expired (or has never been set), create one now.
            if ($hostname_cache === false) {
                // Flush the failed cache variable.
                $hostname_cache = array();

                // Loop through the list of hosts and look them up.
                foreach ($excluded_host as $host) {
                    if (strpos($host, '.') > 0) {
                        $hostname_cache[$host] = gethostbyname($host . '.');
                    }
                }

                // Set the transient and store it for 1 hour.
                set_transient($transient_name, $hostname_cache, 360);
            }

            // Check if the current IP address matches one of the ones in the excluded hosts list.
            if (in_array($visitorProfile->getIp(), $hostname_cache)) {
                return true;
            }
        }

        return false;
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