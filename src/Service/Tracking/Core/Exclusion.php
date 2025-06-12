<?php

namespace WP_STATISTICS\Service\Tracking\Core;

use WP_STATISTICS\DB;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Option;
use WP_Statistics\Service\Analytics\VisitorProfile;
use WP_STATISTICS\TimeZone;
use WP_STATISTICS\Utils\Request;
use WP_Statistics\Records\RecordFactory;

/**
 * Class Exclusion
 *
 * Handles visitor exclusion logic for tracking purposes.
 */
class Exclusion
{
    /**
     * Cached exclusion map to avoid re-building on each call.
     *
     * @var array<string, array<string, string>>|null
     */
    private static $exclusionMap = null;

    /**
     * Stores plugin options used for exclusion logic.
     *
     * @var array
     */
    private static $options = [];

    /**
     * Cached compiled URL regex patterns for excluded URLs.
     *
     * @var array|null
     */
    private static $excludedUrlPatterns = null;

    /**
     * Returns the default exclusion map with reasons and corresponding check methods.
     *
     * @return array<string, array<string, string>> Associative array of exclusion keys to message and method.
     */
    private static function getExclusionMap()
    {
        return [
            'ajax' => [
                'message' => esc_html__('Ajax', 'wp-statistics'),
                'method'  => 'exclusionAjax',
            ],

            'cronjob' => [
                'message' => esc_html__('Cron Job', 'wp-statistics'),
                'method'  => 'exclusionCronjob',
            ],

            'robot' => [
                'message' => esc_html__('Robot', 'wp-statistics'),
                'method'  => 'exclusionRobot',
            ],

            'broken_file' => [
                'message' => esc_html__('Broken Link', 'wp-statistics'),
                'method'  => 'exclusionBrokenFile',
            ],

            'ip_match' => [
                'message' => esc_html__('IP Match', 'wp-statistics'),
                'method'  => 'exclusionIpMatch',
            ],

            'self_referral' => [
                'message' => esc_html__('Self Referral', 'wp-statistics'),
                'method'  => 'exclusionSelfReferral',
            ],

            'login_page' => [
                'message' => esc_html__('Login Page', 'wp-statistics'),
                'method'  => 'exclusionLoginPage',
            ],

            'admin_page' => [
                'message' => esc_html__('Admin Page', 'wp-statistics'),
                'method'  => 'exclusionAdminPage',
            ],

            'referrer_spam' => [
                'message' => esc_html__('Referrer Spam', 'wp-statistics'),
                'method'  => 'exclusionReferrerSpam',
            ],

            'feed' => [
                'message' => esc_html__('Feed', 'wp-statistics'),
                'method'  => 'exclusionFeed',
            ],

            '404' => [
                'message' => esc_html__('404', 'wp-statistics'),
                'method'  => 'exclusion404',
            ],

            'excluded_url' => [
                'message' => esc_html__('Excluded URL', 'wp-statistics'),
                'method'  => 'exclusionExcludedUrl',
            ],

            'user_role' => [
                'message' => esc_html__('User Role', 'wp-statistics'),
                'method'  => 'exclusionUserRole',
            ],

            'geoip' => [
                'message' => esc_html__('Geolocation', 'wp-statistics'),
                'method'  => 'exclusionGeoIp',
            ],

            'robot_threshold' => [
                'message' => esc_html__('Robot Threshold', 'wp-statistics'),
                'method'  => 'exclusionRobotThreshold',
            ],

            'xmlrpc' => [
                'message' => esc_html__('XML-RPC', 'wp-statistics'),
                'method'  => 'exclusionXmlRpc',
            ],

            'cross_site' => [
                'message' => esc_html__('Cross Site Request', 'wp-statistics'),
                'method'  => 'exclusionCrossSite',
            ],

            'pre_flight' => [
                'message' => esc_html__('Pre-flight Request', 'wp-statistics'),
                'method'  => 'exclusionPreFlight',
            ],
        ];
    }

    /**
     * Returns the list of exclusion keys, filtered by developers.
     *
     * @return string[] List of exclusion reason keys.
     */
    public static function getExclusionList()
    {
        if (self::$exclusionMap === null) {
            self::$exclusionMap = self::getExclusionMap();
        }

        $keys = array_keys(self::$exclusionMap);
        return (array)apply_filters('wp_statistics_exclusion_list', $keys);
    }

    /**
     * Checks if exclusion recording is enabled in settings.
     *
     * @return bool True if recording exclusions is active, false otherwise.
     */
    public static function isRecordActive()
    {
        return (bool)Option::get('record_exclusions');
    }

    /**
     * Determines whether the given visitor should be excluded based on configured rules.
     *
     * @param VisitorProfile $visitorProfile Visitor profile instance.
     * @return array{exclusion_match: bool, exclusion_reason: string}
     */
    public static function check($visitorProfile)
    {
        $exclude = ['exclusion_match' => false, 'exclusion_reason' => ''];

        if (empty(self::$options)) {
            self::$options = Option::getOptions();
        }

        $exclusionList = self::getExclusionList();

        foreach ($exclusionList as $reason) {
            if (!isset(self::$exclusionMap[$reason])) {
                continue;
            }

            $method = self::$exclusionMap[$reason]['method'];

            if (!$method || !method_exists(__CLASS__, $method)) {
                continue;
            }

            if (self::$method($visitorProfile)) {
                return apply_filters(
                    'wp_statistics_exclusion',
                    ['exclusion_match' => true, 'exclusion_reason' => $reason],
                    $visitorProfile
                );
            }
        }

        return apply_filters('wp_statistics_exclusion', $exclude, $visitorProfile);
    }

    /**
     * Records the exclusion occurrence in the database.
     *
     * @param array{exclusion_match: bool, exclusion_reason: string} $exclusion Exclusion details.
     * @return void
     */
    public static function record($exclusion = [])
    {
        if (!self::isRecordActive()) {
            return;
        }

        $date   = TimeZone::getCurrentDate('Y-m-d');
        $reason = $exclusion['exclusion_reason'] ?? '';

        $record = RecordFactory::exclusion()->get([
            'date'   => $date,
            'reason' => $reason
        ]);

        if ($record) {
            RecordFactory::exclusion($record)->update([
                'count' => $record->count + 1
            ]);

            return;
        }

        $id = RecordFactory::exclusion()->insert([
            'date'   => $date,
            'reason' => $reason,
            'count'  => 1
        ]);

        if ($id) {
            do_action('wp_statistics_save_exclusion', $exclusion, $id);
        }
    }

    /**
     * Exclude AJAX requests from tracking.
     *
     * @param VisitorProfile $visitorProfile Visitor profile instance.
     * @return bool True when AJAX and not a bypass request.
     */
    public static function exclusionAjax($visitorProfile)
    {
        if (Helper::isBypassAdBlockersRequest() || Request::compare('action', 'wp_statistics_event')) {
            return false;
        }

        return defined('DOING_AJAX') && DOING_AJAX;
    }

    /**
     * Exclude WP-Cron jobs from tracking.
     *
     * @param VisitorProfile $visitorProfile Visitor profile instance.
     * @return bool True when executing cron.
     */
    public static function exclusionCronjob($visitorProfile)
    {
        if (defined('DOING_CRON') && DOING_CRON === true) {
            return true;
        }

        if (function_exists('wp_doing_cron') && wp_doing_cron() === true) {
            return true;
        }

        return false;
    }

    /**
     * Exclude feed requests when configured.
     *
     * @param VisitorProfile $visitorProfile Visitor profile instance.
     * @return bool True when feed and feeds are excluded.
     */
    public static function exclusionFeed($visitorProfile)
    {
        if (!Option::get('exclude_feeds')) {
            return false;
        }

        return is_feed();
    }

    /**
     * Exclude 404 responses when configured.
     *
     * @param VisitorProfile $visitorProfile Visitor profile instance.
     * @return bool True on 404 when exclusion enabled.
     */
    public static function exclusion404($visitorProfile)
    {
        if (!Option::get('exclude_404s')) {
            return false;
        }

        if (Helper::is_rest_request() && ($_REQUEST['source_type'] ?? '') === '404') {
            return true;
        }

        return is_404();
    }

    /**
     * Exclude visitors exceeding a hit threshold.
     *
     * @param VisitorProfile $visitorProfile Visitor profile instance.
     * @return bool True when hits exceed threshold.
     */
    public static function exclusionRobotThreshold($visitorProfile)
    {
        $threshold = intval(self::$options['robot_threshold'] ?? 0);

        if ($threshold <= 0) {
            return false;
        }

        $visitorStats = $visitorProfile->isIpActiveToday();

        if (!$visitorStats) {
            return false;
        }

        return ($visitorStats->hits + 1) > $threshold;
    }

    /**
     * Exclude users with specific roles or anonymous if configured.
     *
     * @param VisitorProfile $visitorProfile Visitor profile instance.
     * @return bool True when user or anonymous matches exclusion.
     */
    public static function exclusionUserRole($visitorProfile)
    {
        $currentUser = null;

        if (Helper::is_rest_request() && isset($GLOBALS['wp_statistics_user_id'])) {
            $currentUser = get_user_by('id', $GLOBALS['wp_statistics_user_id']);
        } elseif (is_user_logged_in()) {
            $currentUser = wp_get_current_user();
        }

        if ($currentUser) {
            foreach ($currentUser->roles as $role) {
                if (!empty(self::$options['exclude_' . str_replace(' ', '_', strtolower($role))])) {
                    return true;
                }
            }

            return false;
        }

        return !empty(self::$options['exclude_anonymous_users']);
    }

    /**
     * Exclude URLs matching configured patterns.
     *
     * @param VisitorProfile $visitorProfile Visitor profile instance.
     * @return bool True when request URI matches an excluded pattern.
     */
    public static function exclusionExcludedUrl($visitorProfile)
    {
        if (self::$excludedUrlPatterns === null) {
            self::$excludedUrlPatterns = self::compileExcludedUrls(self::$options['excluded_urls'] ?? '');
        }

        $requestUri = urldecode(trim(explode('?', $visitorProfile->getRequestUri())[0], '/\\'));

        foreach (self::$excludedUrlPatterns as $pattern) {
            if (preg_match($pattern, $requestUri)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Compiles newline-separated URL patterns into regex array.
     *
     * @param string $urls Multiline string of URL patterns.
     * @return array<int, string> Array of regex patterns.
     */
    private static function compileExcludedUrls($urls)
    {
        $patterns = [];

        foreach (explode("\n", $urls) as $url) {
            $url = trim(urldecode(trim($url, '/\\')));

            if (strpos($url, '*') !== false) {
                $patterns[] = '/^' . str_replace('\\*', '.*', preg_quote($url, '/', '/'))
                    . '$/i';
            } elseif (strlen($url) > 2) {
                $patterns[] = '/^' . preg_quote($url, '/') . '$/i';
            }
        }

        return $patterns;
    }

    /**
     * Excludes referrer spam based on configured list.
     *
     * @param VisitorProfile $visitorProfile Visitor profile instance.
     * @return bool True when referrer contains a spam entry.
     */
    public static function exclusionReferrerSpam($visitorProfile)
    {
        if (empty(self::$options['referrerspam'])) {
            return false;
        }

        $referrer = $visitorProfile->getReferrer();
        $spamList = explode("\n", self::$options['referrerspamlist'] ?? '');

        foreach ($spamList as $spamEntry) {
            $spamEntry = trim($spamEntry);

            if (strlen($spamEntry) > 3 && stripos($referrer, $spamEntry) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Exclude self referrals from WordPress core.
     *
     * @param VisitorProfile $visitorProfile Visitor profile instance.
     * @return bool True on core self-referral UA matches.
     */
    public static function exclusionSelfReferral($visitorProfile)
    {
        $currentUserAgent   = $visitorProfile->getHttpUserAgent();
        $wordpressUserAgent = 'WordPress/' . Helper::get_wordpress_version();
        $homeUrl            = rtrim(get_home_url(), '/');

        if ($currentUserAgent === $wordpressUserAgent . '; ' . $homeUrl) {
            return true;
        }

        if ($currentUserAgent === $wordpressUserAgent . '; ' . $homeUrl . '/') {
            return true;
        }

        return false;
    }

    /**
     * Excludes tracking on login page when configured.
     *
     * @param VisitorProfile $visitorProfile Visitor profile instance.
     * @return bool True on login page if exclusion enabled.
     */
    public static function exclusionLoginPage($visitorProfile)
    {
        if (!Option::get('exclude_loginpage')) {
            return false;
        }

        return Helper::is_login_page();
    }

    /**
     * Excludes admin pages from tracking.
     *
     * @param VisitorProfile $visitorProfile Visitor profile instance.
     * @return bool True when wp-admin detected in request URI.
     */
    public static function exclusionAdminPage($visitorProfile)
    {
        $requestUri = $visitorProfile->getRequestUri();

        if (!isset($_SERVER['SERVER_NAME'], $requestUri)) {
            return false;
        }

        $fullUrl = Helper::RemoveQueryStringUrl($_SERVER['SERVER_NAME'] . $requestUri);

        if (Helper::isBypassAdBlockersRequest() || Request::compare('action', 'wp_statistics_event')) {
            return false;
        }

        return stripos($fullUrl, 'wp-admin') !== false;
    }

    /**
     * Excludes IPs matching configured ranges.
     *
     * @param VisitorProfile $visitorProfile Visitor profile instance.
     * @return bool True when client IP is in excluded range.
     */
    public static function exclusionIpMatch($visitorProfile)
    {
        if (empty(self::$options['exclude_ip'])) {
            return false;
        }

        $ipRanges = explode("\n", self::$options['exclude_ip']);

        foreach ($ipRanges as $subnet) {
            $subnet = trim($subnet);

            if (strlen($subnet) > 6 && IP::checkIPRange([$subnet])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Excludes broken file requests on 404 errors.
     *
     * @param VisitorProfile $visitorProfile Visitor profile instance.
     * @return bool True when 404 and file extension present.
     */
    public static function exclusionBrokenFile($visitorProfile)
    {
        if (!is_404()) {
            return false;
        }

        $requestUri = $visitorProfile->getRequestUri();

        if (!isset($_SERVER['HTTP_HOST'], $requestUri)) {
            return false;
        }

        $scheme    = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
        $url       = "$scheme://{$_SERVER['HTTP_HOST']}{$requestUri}";
        $path      = wp_parse_url($url, PHP_URL_PATH);
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        if (empty($extension) || strtolower($extension) === 'php') {
            return false;
        }

        return true;
    }

    /**
     * Excludes bots based on UA or detection library.
     *
     * @param VisitorProfile $visitorProfile Visitor profile instance.
     * @return bool True when UA or IP is identified as a bot.
     */
    public static function exclusionRobot($visitorProfile)
    {
        $httpUserAgent   = $visitorProfile->getHttpUserAgent();
        $ipAddress       = $visitorProfile->getIp();
        $userAgentObject = $visitorProfile->getUserAgent();
        $robotPatterns   = explode("\n", self::$options['robotlist'] ?? '');

        foreach ($robotPatterns as $pattern) {
            $pattern = trim($pattern);

            if (strlen($pattern) > 3 && stripos($httpUserAgent, $pattern) !== false) {
                return true;
            }
        }

        if ($httpUserAgent === '' || $ipAddress === '') {
            return true;
        }

        if ($userAgentObject->isBot()) {
            return true;
        }

        if (!$userAgentObject->isBrowserDetected() && !$userAgentObject->isPlatformDetected()) {
            return true;
        }

        return false;
    }

    /**
     * Excludes by geographic location based on GeoIP settings.
     *
     * @param VisitorProfile $visitorProfile Visitor profile instance.
     * @return bool True when country is excluded or not in included list.
     */
    public static function exclusionGeoIp($visitorProfile)
    {
        static $excludedCountries = null;
        static $includedCountries = null;

        if ($excludedCountries === null) {
            $excludedCountries = array_flip(array_filter(
                explode("\n", strtoupper(str_replace("\r\n", "\n", self::$options['excluded_countries'] ?? '')))
            ));
        }

        if ($includedCountries === null) {
            $countriesString   = strtoupper(str_replace("\r\n", "\n", self::$options['included_countries'] ?? ''));
            $includedCountries = $countriesString === '' ? [] : array_flip(array_filter(explode("\n", $countriesString)));
        }

        if (empty($excludedCountries) && empty($includedCountries)) {
            return false;
        }

        $countryCode = strtoupper($visitorProfile->getCountry() ?? '');

        if ($countryCode === '') {
            return false;
        }

        if (isset($excludedCountries[$countryCode])) {
            return true;
        }

        return !empty($includedCountries) && !isset($includedCountries[$countryCode]);
    }

    /**
     * Excludes XML-RPC requests from tracking.
     *
     * @param VisitorProfile $visitorProfile Visitor profile instance.
     * @return bool True on XML-RPC requests.
     */
    public static function exclusionXmlRpc($visitorProfile)
    {
        return defined('XMLRPC_REQUEST') && XMLRPC_REQUEST === true;
    }

    /**
     * Excludes CORS pre-flight OPTIONS requests.
     *
     * @param VisitorProfile $visitorProfile Visitor profile instance.
     * @return bool True when HTTP method is OPTIONS with CORS headers.
     */
    public static function exclusionPreFlight($visitorProfile)
    {
        if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'OPTIONS') {
            return false;
        }

        return isset(
            $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'],
            $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'],
            $_SERVER['HTTP_ORIGIN']
        );
    }
}
