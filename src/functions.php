<?php
/**
 * WP Statistics Global Functions
 *
 * This file contains all global functions for WP Statistics including:
 * - Core plugin instance accessor (WP_Statistics())
 * - Template functions for theme developers (wp_statistics_*)
 *
 * These functions can be used in themes and plugins to display visitor counts,
 * page views, and other analytics data.
 *
 * @package    WP_Statistics
 * @since      1.0.0
 *
 * New in v15.0.0:
 * - Added wp_statistics_query() as the recommended way to query data
 * - Deprecated wp_statistics_visit(), wp_statistics_visitor(), wp_statistics_pages()
 *   in favor of wp_statistics_query()
 *
 * @see wp_statistics_query() The recommended function for querying statistics data.
 */

use WP_Statistics\Bootstrap;
use WP_Statistics\Components\DateRange;
use WP_Statistics\Components\DateTime;
use WP_Statistics\Components\Ip;
use WP_Statistics\Components\Country;
use WP_Statistics\Service\Analytics\DeviceDetection\DeviceHelper;
use WP_Statistics\Service\Analytics\DeviceDetection\UserAgent;
use WP_Statistics\Service\Geolocation\GeolocationFactory;
use WP_Statistics\Service\Admin\PrivacyAudit\Faqs\RequireConsent;
use WP_Statistics\Service\AnalyticsQuery\AnalyticsQueryHandler;
use WP_Statistics\Service\Logger\LoggerFactory;
use WP_STATISTICS\TimeZone;
use WP_Statistics\Utils\Page;
use WP_Statistics\Utils\Uri;
use WP_Statistics\Utils\User;

if (!function_exists('WP_Statistics')) {
    /**
     * Global function to get WP Statistics instance.
     *
     * Returns a compatibility object for backward compatibility.
     *
     * @return object
     */
    function WP_Statistics()
    {
        return new class {
            public function getBackgroundProcess($key)
            {
                return Bootstrap::getBackgroundProcess($key);
            }

            public function log($message, $level = 'info')
            {
                LoggerFactory::logger('file')->log($message, $level);
            }
        };
    }
}

/**
 * Execute an analytics query and return results.
 *
 * This is the recommended way to query statistics data in v15+.
 * Provides a simplified interface to the AnalyticsQuery system.
 *
 * @param array $args Query arguments:
 *   - 'sources'   (array)  Data sources: 'visitors', 'views', 'sessions', etc.
 *   - 'date_from' (string) Start date (Y-m-d format)
 *   - 'date_to'   (string) End date (Y-m-d format)
 *   - 'group_by'  (array)  Optional grouping: 'country', 'browser', 'page', etc.
 *   - 'filters'   (array)  Optional filters
 *   - 'format'    (string) Output format: 'flat', 'table', 'chart' (default: 'flat')
 *   - 'cache'     (bool)   Enable caching (default: false for template functions)
 *
 * @return array|null Query result data, or null on error
 *
 * @since 15.0.0
 *
 * @example
 * // Get total visitors for last 30 days
 * $result = wp_statistics_query([
 *     'sources'   => ['visitors'],
 *     'date_from' => date('Y-m-d', strtotime('-30 days')),
 *     'date_to'   => date('Y-m-d'),
 * ]);
 * $visitors = $result['visitors'] ?? 0;
 *
 * @example
 * // Get views grouped by country
 * $result = wp_statistics_query([
 *     'sources'   => ['views'],
 *     'group_by'  => ['country'],
 *     'date_from' => '2024-01-01',
 *     'date_to'   => '2024-01-31',
 *     'format'    => 'table',
 * ]);
 */
function wp_statistics_query($args = [])
{
    $defaults = [
        'sources'   => [],
        'date_from' => date('Y-m-d', strtotime('-30 days')),
        'date_to'   => date('Y-m-d'),
        'group_by'  => [],
        'filters'   => [],
        'format'    => 'flat',
        'cache'     => false,
    ];

    $args = wp_parse_args($args, $defaults);

    try {
        $handler = new AnalyticsQueryHandler($args['cache']);
        $result  = $handler->handle([
            'sources'   => $args['sources'],
            'date_from' => $args['date_from'],
            'date_to'   => $args['date_to'],
            'group_by'  => $args['group_by'],
            'filters'   => $args['filters'],
            'format'    => $args['format'],
        ]);

        return $result['data'] ?? null;
    } catch (Exception $e) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('WP Statistics Query Error: ' . $e->getMessage());
        }
        return null;
    }
}

/**
 * Get the current user's IP address.
 *
 * Returns the visitor's IP address, respecting privacy settings
 * and proxy configurations.
 *
 * @return string The visitor's IP address.
 * @since 1.0.0
 */
function wp_statistics_get_user_ip()
{
    return Ip::getCurrent();
}

/**
 * Get comprehensive data about the current visitor.
 *
 * Returns an array containing the visitor's location (country/city),
 * IP address, user agent information, and WordPress user data if logged in.
 *
 * @return array {
 *     Visitor data array.
 *     @type array  $country Location country data (code, name, flag).
 *     @type string $city    City name from geolocation.
 *     @type string $ip      Visitor's IP address.
 *     @type array  $agent   User agent details (browser, platform).
 *     @type array  $user    WordPress user data (if logged in).
 * }
 * @throws Exception If geolocation lookup fails.
 * @since 1.0.0
 */
function wp_statistics_get_current_user_data()
{

    // Get Current User country and City
    $data = wp_statistics_get_user_location();

    // Get Current User IP
    $data['ip'] = wp_statistics_get_user_ip();

    // Get User Agent contain Browser and Platform
    $data['agent'] = UserAgent::getUserAgent();

    // Get User info if Registered in WordPress
    if (User::isLoggedIn()) {
        $data['user'] = User::getInfo();
    }

    // Return
    return $data;
}

/**
 * Get geographic location data for an IP address.
 *
 * Uses the configured geolocation service to retrieve country and city
 * information for the specified IP address or the current visitor.
 *
 * @param string|false $ip IP address to look up, or false for current visitor.
 * @return array {
 *     Location data array.
 *     @type array  $country {
 *         @type string $code Country ISO code.
 *         @type string $name Country name.
 *         @type string $flag Country flag URL or emoji.
 *     }
 *     @type string $city City name from geolocation.
 * }
 * @throws Exception If geolocation service fails.
 * @since 1.0.0
 */
function wp_statistics_get_user_location($ip = false)
{
    $ip   = ($ip === false ? wp_statistics_get_user_ip() : $ip);
    $data = array(
        'country' => '',
        'city'    => '',
    );

    // Get the location
    $location = GeolocationFactory::getLocation($ip);
    $country  = $location['country'];

    $data['country'] = array(
        'code' => $location,
        'name' => Country::getName($country),
        'flag' => Country::getFlag($country)
    );

    // Get User City
    $data['city'] = $location['city'];

    return $data;
}

/**
 * Get count or list of currently online users.
 *
 * Retrieves real-time online user data using the v15 AnalyticsQuery system.
 * Online users are determined by sessions with ended_at within the last 5 minutes.
 * Supports filtering by page type, location, browser, and platform.
 *
 * @param array $options {
 *     Optional. Filter and return options.
 *     @type string $type         Page type filter: 'post', 'page', 'home', 'category', etc. Default 'all'.
 *     @type int    $ID           WordPress object ID when filtering by type. Default 0.
 *     @type bool   $logged_users True to count only logged-in users. Default false.
 *     @type string $location     ISO country code to filter by. Default 'all'.
 *     @type string $agent        Browser name to filter by. Default 'all'.
 *     @type string $platform     Operating system to filter by. Default 'all'.
 *     @type string $return       'count' for number, 'all' for full data. Default 'count'.
 * }
 * @return int|array User count or array of online user data.
 *
 * @since 1.0.0
 * @since 15.0.0 Updated to use AnalyticsQuery system with sessions table.
 */
function wp_statistics_useronline($options = array())
{
    $defaults = array(
        'type'         => 'all',
        'ID'           => 0,
        'logged_users' => false,
        'location'     => 'all',
        'agent'        => 'all',
        'platform'     => 'all',
        'return'       => 'count'
    );

    $arg = wp_parse_args($options, $defaults);

    // Online threshold: sessions with ended_at within the last 5 minutes
    $now            = gmdate('Y-m-d H:i:s');
    $fiveMinutesAgo = gmdate('Y-m-d H:i:s', time() - 300);

    // Build filters array for AnalyticsQuery
    $filters = [];

    // Filter by logged-in users
    if ($arg['logged_users'] === true) {
        $filters['logged_in'] = ['value' => '1', 'operator' => 'is'];
    }

    // Filter by country code - need to look up country ID
    if ($arg['location'] !== 'all') {
        $countryId = _wp_statistics_get_country_id_by_code($arg['location']);
        if ($countryId) {
            $filters['country'] = ['value' => $countryId, 'operator' => 'is'];
        }
    }

    // Filter by browser name - need to look up browser ID
    if ($arg['agent'] !== 'all') {
        $browserId = _wp_statistics_get_browser_id_by_name($arg['agent']);
        if ($browserId) {
            $filters['browser'] = ['value' => $browserId, 'operator' => 'is'];
        }
    }

    // Filter by OS name - need to look up OS ID
    if ($arg['platform'] !== 'all') {
        $osId = _wp_statistics_get_os_id_by_name($arg['platform']);
        if ($osId) {
            $filters['os'] = ['value' => $osId, 'operator' => 'is'];
        }
    }

    // Filter by page ID (resource_id filter)
    if ($arg['type'] !== 'all' && !empty($arg['ID'])) {
        $filters['resource_id'] = ['value' => (int) $arg['ID'], 'operator' => 'is'];
    }

    // Use AnalyticsQuery for consistency with rest of v15 codebase
    $result = wp_statistics_query([
        'sources'   => ['visitors'],
        'group_by'  => ['online_visitor'],
        'date_from' => $fiveMinutesAgo,
        'date_to'   => $now,
        'filters'   => $filters,
        'format'    => 'table',
        'cache'     => false,
    ]);

    if ($arg['return'] === 'count') {
        // Return total count from meta
        return (int) ($result['meta']['total_rows'] ?? 0);
    }

    // Return list of online users - transform to legacy format for backward compatibility
    $rows = $result['data'] ?? [];

    return array_map(function ($row) {
        return [
            'visitor_id'     => $row['visitor_id'] ?? null,
            'ip'             => $row['ip_address'] ?? null,
            'created'        => $row['first_visit'] ?? null,
            'timestamp'      => $row['last_visit'] ?? null,
            'user_id'        => $row['user_id'] ?? null,
            'hits'           => $row['total_views'] ?? 0,
            'hash'           => $row['visitor_hash'] ?? null,
            'location'       => $row['country_code'] ?? null,
            'country_name'   => $row['country_name'] ?? null,
            'region'         => $row['region_name'] ?? null,
            'city'           => $row['city_name'] ?? null,
            'agent'          => $row['browser_name'] ?? null,
            'version'        => $row['browser_version'] ?? null,
            'platform'       => $row['os_name'] ?? null,
            'device'         => $row['device_type_name'] ?? null,
            'referred'       => $row['referrer_domain'] ?? null,
            'source_channel' => $row['referrer_channel'] ?? null,
            'display_name'   => $row['user_login'] ?? null,
            'user_email'     => $row['user_email'] ?? null,
        ];
    }, $rows);
}

/**
 * Get country ID by ISO country code.
 *
 * @internal
 * @param string $code ISO country code (e.g., 'US', 'GB').
 * @return int|null Country ID or null if not found.
 */
function _wp_statistics_get_country_id_by_code($code)
{
    global $wpdb;
    $table = $wpdb->prefix . 'statistics_countries';
    return $wpdb->get_var($wpdb->prepare("SELECT ID FROM {$table} WHERE code = %s LIMIT 1", strtoupper($code)));
}

/**
 * Get browser ID by name.
 *
 * @internal
 * @param string $name Browser name (e.g., 'Chrome', 'Firefox').
 * @return int|null Browser ID or null if not found.
 */
function _wp_statistics_get_browser_id_by_name($name)
{
    global $wpdb;
    $table = $wpdb->prefix . 'statistics_device_browsers';
    return $wpdb->get_var($wpdb->prepare("SELECT ID FROM {$table} WHERE name = %s LIMIT 1", $name));
}

/**
 * Get OS ID by name.
 *
 * @internal
 * @param string $name OS name (e.g., 'Windows', 'macOS').
 * @return int|null OS ID or null if not found.
 */
function _wp_statistics_get_os_id_by_name($name)
{
    global $wpdb;
    $table = $wpdb->prefix . 'statistics_device_oss';
    return $wpdb->get_var($wpdb->prepare("SELECT ID FROM {$table} WHERE name = %s LIMIT 1", $name));
}

/**
 * This function get the visit statistics for a given time frame.
 *
 * Returns total page views (hits) for the specified time period.
 *
 * @param string|int $time Time period: 'today', 'yesterday', 'week', 'month', 'year', 'total', or days ago
 * @param bool|null $daily If true and $time is numeric, treats $time as days ago
 * @return int Total page views count
 *
 * @deprecated 15.0.0 Use wp_statistics_query() with sources: ['views'] instead.
 * @see wp_statistics_query()
 */
function wp_statistics_visit($time, $daily = null)
{
    // Map legacy time ranges to date range
    if ($time === 'week') {
        $time = '7days';
    } elseif ($time === 'month') {
        $time = '30days';
    } elseif ($time === 'year') {
        $time = '12months';
    } elseif (is_numeric($time) && $daily) {
        $time = DateTime::get("$time days");
    }

    $dateRange = DateRange::resolveDate($time);

    $result = wp_statistics_query([
        'sources'   => ['views'],
        'date_from' => $dateRange['from'],
        'date_to'   => $dateRange['to'],
    ]);

    return (int) ($result['views'] ?? 0);
}

/**
 * This function gets the visitor statistics for a given time frame.
 *
 * Returns unique visitor count for the specified time period.
 *
 * @param string|int $time Time period: 'today', 'yesterday', 'week', 'month', 'year', 'total', or days ago
 * @param bool|null $daily If true, treats $time as a specific date
 * @param bool $count_only If true, returns count only; otherwise returns query result
 * @param array $options Additional filter options (type, ID, location, agent, platform)
 * @return int|null|string Visitor count or query result
 *
 * @since 1.0.0
 * @since 15.0.0 Refactored to use AnalyticsQuery.
 */
function wp_statistics_visitor($time, $daily = null, $count_only = false, $options = array())
{
    // Check Parameters
    $defaults = array(
        'type'     => 'all',
        'ID'       => 0,
        'location' => 'all',
        'agent'    => 'all',
        'platform' => 'all'
    );

    $arg = wp_parse_args($options, $defaults);

    // Map legacy time ranges to date range
    if ($time === 'week') {
        $time = '7days';
    } elseif ($time === 'month') {
        $time = '30days';
    } elseif ($time === 'year') {
        $time = '12months';
    } elseif (is_numeric($time) && $daily) {
        $time = DateTime::get("$time days");
    } elseif ($daily === true && TimeZone::isValidDate($time)) {
        // Specific date provided
    }

    // Handle date range arrays
    if (is_array($time) && isset($time['start'], $time['end'])) {
        $dateRange = [
            'from' => $time['start'],
            'to'   => $time['end']
        ];
    } else {
        $dateRange = DateRange::resolveDate($time);
    }

    // Build filters
    $filters = [];

    // Location filter
    if ($arg['location'] !== 'all' && !empty($arg['location'])) {
        $filters['country'] = $arg['location'];
    }

    // Browser filter
    if ($arg['agent'] !== 'all' && !empty($arg['agent'])) {
        $filters['browser'] = $arg['agent'];
    }

    // Platform/OS filter
    if ($arg['platform'] !== 'all' && !empty($arg['platform'])) {
        $filters['os'] = $arg['platform'];
    }

    // Page type filter
    if ($arg['type'] !== 'all' && !empty($arg['type']) && $arg['ID'] > 0) {
        $filters['resource_type'] = $arg['type'];
        $filters['resource_id'] = $arg['ID'];
    }

    $result = wp_statistics_query([
        'sources'   => ['visitors'],
        'date_from' => $dateRange['from'],
        'date_to'   => $dateRange['to'],
        'filters'   => $filters,
    ]);

    return (int) ($result['visitors'] ?? 0);
}

/**
 * This function returns the statistics for a given page.
 *
 * Returns total page views for a specific page/post by ID or URI.
 *
 * @param string $time Time period: 'today', 'yesterday', 'total', 'range', or date string
 * @param string $page_uri Page URI to filter by
 * @param int $id Post/page ID to filter by (-1 for none)
 * @param string|null $rangestartdate Range start date (Y-m-d format)
 * @param string|null $rangeenddate Range end date (Y-m-d format)
 * @param string|bool $type Post type to filter by
 * @return int Total page views count
 *
 * @since 1.0.0
 * @since 15.0.0 Refactored to use AnalyticsQuery.
 */
function wp_statistics_pages($time, $page_uri = '', $id = -1, $rangestartdate = null, $rangeenddate = null, $type = false)
{
    // Handle date range
    if (!is_null($rangestartdate) && !is_null($rangeenddate)) {
        $dateRange = [
            'from' => $rangestartdate,
            'to'   => $rangeenddate
        ];
    } elseif ($time === 'range') {
        // For 'range' time, dates must be provided
        $dateRange = DateRange::resolveDate('total');
    } else {
        // Map legacy time periods
        if ($time === 'week') {
            $time = '7days';
        } elseif ($time === 'month') {
            $time = '30days';
        } elseif ($time === 'year') {
            $time = '12months';
        }
        $dateRange = DateRange::resolveDate($time);
    }

    // Build filters
    $filters = [];

    // Page ID filter
    if ($id !== -1 && $id > 0) {
        $filters['resource_id'] = absint($id);
    }

    // Page URI filter
    if (!empty($page_uri)) {
        $filters['uri'] = $page_uri;
    } elseif ($id === -1 && empty($page_uri)) {
        // Get current page URI if nothing specified
        $filters['uri'] = Uri::get();
    }

    // Post type filter
    if ($type) {
        $filters['resource_type'] = $type;
    }

    $result = wp_statistics_query([
        'sources'   => ['views'],
        'date_from' => $dateRange['from'],
        'date_to'   => $dateRange['to'],
        'filters'   => $filters,
    ]);

    return (int) ($result['views'] ?? 0);
}

/**
 * Get top pages by view count within a date range.
 *
 * Retrieves the most viewed pages/posts sorted by total views.
 *
 * @param string|null $rangestartdate Start date (Y-m-d format), or null for all time.
 * @param string|null $rangeenddate   End date (Y-m-d format), or null for all time.
 * @param int|null    $limit          Maximum number of results. Default 5.
 * @param string|null $post_type      Post type to filter by, or null for all types.
 * @return array {
 *     @type int   $0 The limit used.
 *     @type array $1 Array of page data arrays [uri, views, page_id, title, url].
 * }
 *
 * @deprecated 15.0.0 This function uses legacy v14 database structure (pages table).
 *                    Consider using wp_statistics_query() with group_by: ['page'] instead.
 *
 * @since 1.0.0
 */
function wp_statistics_get_top_pages($rangestartdate = null, $rangeenddate = null, $limit = null, $post_type = null)
{
    $spliceLimit = ($limit !== null ? (int) $limit : 5);

    // Build query using v15 tables
    $query = \WP_Statistics\Utils\Query::select([
        'resource_uris.uri',
        'resources.resource_id AS page_id',
        'resources.resource_type AS page_type',
        'resources.cached_title AS title',
        'COUNT(*) AS view_count',
    ])
        ->from('views')
        ->join('resource_uris', ['views.resource_uri_id', 'resource_uris.ID'])
        ->join('resources', ['resource_uris.resource_id', 'resources.ID'], [], 'LEFT');

    // Apply date range filter
    if ($rangestartdate !== null && $rangeenddate !== null) {
        $query->where('views.viewed_at', '>=', $rangestartdate . ' 00:00:00')
            ->where('views.viewed_at', '<=', $rangeenddate . ' 23:59:59');
    }

    // Apply post type filter
    if ($post_type !== null) {
        if (is_array($post_type)) {
            $query->whereIn('resources.resource_type', $post_type);
        } else {
            $query->where('resources.resource_type', '=', $post_type);
        }
    }

    // Group and order
    $query->groupBy('resource_uris.ID')
        ->orderBy('view_count', 'DESC')
        ->perPage(1, $spliceLimit);

    $result = $query->getAll();
    $uris   = [];

    if (!empty($result)) {
        foreach ($result as $row) {
            $uri       = $row->uri ?? '';
            $pageId    = $row->page_id ?? 0;
            $pageType  = $row->page_type ?? '';
            $title     = $row->title ?? '';
            $viewCount = (int) ($row->view_count ?? 0);

            // Get page URL
            $pageUrl = '';
            if (!empty($pageId) && !empty($pageType)) {
                $pageInfo = Page::getInfo($pageId, $pageType);
                $pageUrl  = $pageInfo['link'] ?? '';

                // Use cached title from getInfo if DB title is empty
                if (empty($title)) {
                    $title = $pageInfo['title'] ?? '';
                }
            }

            // Fallback for page URL
            if (empty($pageUrl)) {
                $pageUrl = path_join(get_site_url(), $uri);
            }

            // Fallback for title
            if (empty($title)) {
                if ($uri === '/') {
                    $title = get_bloginfo();
                } else {
                    $resolvedId = Page::uriToId($uri);
                    $post       = $resolvedId ? get_post($resolvedId) : null;
                    $title      = $post ? esc_html($post->post_title) : '-';
                }
            }

            $title = mb_substr($title, 0, 200, 'utf-8');
            if (empty($title)) {
                $title = '-';
            }

            $uris[] = [
                urldecode_deep($uri),
                $viewCount,
                $pageId,
                $title,
                $pageUrl,
            ];
        }
    }

    return [$spliceLimit, $uris];
}

/**
 * Returns all unique user agents (browsers) in the database.
 *
 * @param string|null $rangestartdate Start date (Y-m-d format), or null for all time.
 * @param string|null $rangeenddate   End date (Y-m-d format), or null for all time.
 * @return array Array of browser names.
 *
 * @since 1.0.0
 * @since 15.0.0 Refactored to use AnalyticsQuery.
 */
function wp_statistics_ua_list($rangestartdate = null, $rangeenddate = null)
{
    // Handle date range
    if ($rangestartdate !== null && $rangeenddate !== null) {
        // Handle CURDATE() as a special case
        if ($rangeenddate === 'CURDATE()') {
            $rangeenddate = date('Y-m-d');
        }
        $dateRange = [
            'from' => $rangestartdate,
            'to'   => $rangeenddate
        ];
    } else {
        // All time
        $dateRange = DateRange::resolveDate('total');
    }

    $result = wp_statistics_query([
        'sources'   => ['visitors'],
        'date_from' => $dateRange['from'],
        'date_to'   => $dateRange['to'],
        'group_by'  => ['browser'],
        'format'    => 'table',
    ]);

    $browsers       = [];
    $defaultBrowser = DeviceHelper::getBrowserList();

    if (is_array($result)) {
        foreach ($result as $row) {
            $browserName = $row['browser_name'] ?? '';
            // Check Browser is defined in wp-statistics
            if (!empty($browserName) && array_key_exists(strtolower($browserName), $defaultBrowser)) {
                $browsers[] = esc_html($browserName);
            }
        }
    }

    return $browsers;
}

/**
 * Count User By User Agent
 *
 * @param $agent
 * @param null $rangestartdate
 * @param null $rangeenddate
 * @return mixed
 */
function wp_statistics_useragent($agent, $rangestartdate = null, $rangeenddate = null)
{
    // Handle date range
    if ($rangestartdate !== null && $rangeenddate !== null) {
        $dateRange = [
            'from' => $rangestartdate,
            'to'   => $rangeenddate
        ];
    } elseif ($rangestartdate !== null) {
        // Single date
        $dateRange = [
            'from' => $rangestartdate,
            'to'   => $rangestartdate
        ];
    } else {
        // All time
        $dateRange = DateRange::resolveDate('total');
    }

    $result = wp_statistics_query([
        'sources'   => ['visitors'],
        'date_from' => $dateRange['from'],
        'date_to'   => $dateRange['to'],
        'filters'   => ['browser' => $agent],
    ]);

    return (int) ($result['visitors'] ?? 0);
}

/**
 * Returns all unique platform (OS) types from the database.
 *
 * @param string|null $rangestartdate Start date (Y-m-d format), or null for all time.
 * @param string|null $rangeenddate   End date (Y-m-d format), or null for all time.
 * @return array Array of platform/OS names.
 *
 * @since 1.0.0
 * @since 15.0.0 Refactored to use AnalyticsQuery.
 */
function wp_statistics_platform_list($rangestartdate = null, $rangeenddate = null)
{
    // Handle date range
    if ($rangestartdate !== null && $rangeenddate !== null) {
        $dateRange = [
            'from' => $rangestartdate,
            'to'   => $rangeenddate
        ];
    } else {
        // All time
        $dateRange = DateRange::resolveDate('total');
    }

    $result = wp_statistics_query([
        'sources'   => ['visitors'],
        'date_from' => $dateRange['from'],
        'date_to'   => $dateRange['to'],
        'group_by'  => ['os'],
        'format'    => 'table',
    ]);

    $platforms = [];

    if (is_array($result)) {
        foreach ($result as $row) {
            $osName = $row['os_name'] ?? '';
            if (!empty($osName)) {
                $platforms[] = esc_html($osName);
            }
        }
    }

    return $platforms;
}

/**
 * Returns the count of a given platform in the database.
 *
 * @param $platform
 * @param null $rangestartdate
 * @param null $rangeenddate
 * @return mixed
 */
function wp_statistics_platform($platform, $rangestartdate = null, $rangeenddate = null)
{
    // Handle date range
    if ($rangestartdate !== null && $rangeenddate !== null) {
        $dateRange = [
            'from' => $rangestartdate,
            'to'   => $rangeenddate
        ];
    } else {
        // All time
        $dateRange = DateRange::resolveDate('total');
    }

    $result = wp_statistics_query([
        'sources'   => ['visitors'],
        'date_from' => $dateRange['from'],
        'date_to'   => $dateRange['to'],
        'filters'   => ['os' => $platform],
    ]);

    return (int) ($result['visitors'] ?? 0);
}

/**
 * Returns all unique versions for a given agent from the database.
 *
 * @param string      $agent          Browser name to filter by.
 * @param string|null $rangestartdate Start date (Y-m-d format), or null for all time.
 * @param string|null $rangeenddate   End date (Y-m-d format), or null for all time.
 * @return array Array of browser version strings.
 *
 * @deprecated 15.0.0 This function uses legacy v14 database structure.
 *                    Consider using wp_statistics_query() with group_by: ['browser'] instead.
 *
 * @since 1.0.0
 */
function wp_statistics_agent_version_list($agent, $rangestartdate = null, $rangeenddate = null)
{
    // Build query using v15 tables
    $query = \WP_Statistics\Utils\Query::select([
        'DISTINCT device_browser_versions.version'
    ])
        ->from('sessions')
        ->join('device_browsers', ['sessions.device_browser_id', 'device_browsers.ID'])
        ->join('device_browser_versions', ['sessions.device_browser_version_id', 'device_browser_versions.ID'])
        ->where('device_browsers.name', '=', $agent);

    // Apply date range filter
    if ($rangestartdate !== null && $rangeenddate !== null) {
        $query->where('sessions.started_at', '>=', $rangestartdate . ' 00:00:00')
            ->where('sessions.started_at', '<=', $rangeenddate . ' 23:59:59');
    }

    $query->orderBy('device_browser_versions.version', 'ASC');

    $result   = $query->getAll();
    $versions = [];

    if (!empty($result)) {
        foreach ($result as $row) {
            $versions[] = $row->version ?? '';
        }
    }

    return $versions;
}

/**
 * Returns the count for a given agent/version pair from the database.
 *
 * @param string      $agent          Browser name to filter by.
 * @param string      $version        Browser version to filter by.
 * @param string|null $rangestartdate Start date (Y-m-d format), or null for all time.
 * @param string|null $rangeenddate   End date (Y-m-d format), or null for all time.
 * @return int Count of visitors with the given browser/version.
 *
 * @deprecated 15.0.0 This function uses legacy v14 database structure.
 *                    Consider using wp_statistics_query() with browser filter instead.
 *
 * @since 1.0.0
 */
function wp_statistics_agent_version($agent, $version, $rangestartdate = null, $rangeenddate = null)
{
    // Build query using v15 tables
    $query = \WP_Statistics\Utils\Query::select([
        'COUNT(DISTINCT sessions.visitor_id)'
    ])
        ->from('sessions')
        ->join('device_browsers', ['sessions.device_browser_id', 'device_browsers.ID'])
        ->join('device_browser_versions', ['sessions.device_browser_version_id', 'device_browser_versions.ID'])
        ->where('device_browsers.name', '=', $agent)
        ->where('device_browser_versions.version', '=', $version);

    // Apply date range filter
    if ($rangestartdate !== null && $rangeenddate !== null) {
        $query->where('sessions.started_at', '>=', $rangestartdate . ' 00:00:00')
            ->where('sessions.started_at', '<=', $rangeenddate . ' 23:59:59');
    }

    return (int) ($query->getVar() ?? 0);
}

/**
 * Return the SQL WHERE clause for getting the search engine.
 *
 * @param string $search_engine Search engine name or 'all'.
 * @return string SQL WHERE clause fragment.
 *
 * @deprecated 15.0.0 This function returns raw SQL fragments.
 *                    Use wp_statistics_get_search_engine_query() or wp_statistics_query() instead.
 *
 * @since 1.0.0
 */
function wp_statistics_searchengine_query($search_engine = 'all')
{
    global $wpdb;

    $search_query = '';
    // Are we getting results for all search engines or a specific one?
    if (strtolower($search_engine) == 'all') {
        $search_query .= "`source_channel` in ('search')";
    } else {
        // Are we getting results for all search engines or a specific one?
        $search_query .= $wpdb->prepare("`source_name` = %s", $search_engine);
    }

    return $search_query;
}

/**
 * Get Search engine Statistics
 *
 * @param string $search_engine
 * @param string $time
 * @param string $search_by [query / name]
 * @param array $range
 * @return mixed
 *
 * @since 1.0.0
 * @since 15.0.0 Refactored to use AnalyticsQuery.
 */
function wp_statistics_get_search_engine_query($search_engine = 'all', $time = 'total', $search_by = 'query', $range = [])
{
    // Handle date range
    if (!empty($range) && isset($range['start'], $range['end'])) {
        $dateRange = [
            'from' => $range['start'],
            'to'   => $range['end']
        ];
    } else {
        // Map legacy time periods
        if ($time === 'week') {
            $time = '7days';
        } elseif ($time === 'month') {
            $time = '30days';
        } elseif ($time === 'year') {
            $time = '12months';
        }
        $dateRange = DateRange::resolveDate($time);
    }

    // Build filters
    $filters = [
        'source_channel' => 'search'
    ];

    // Filter by specific search engine
    if (strtolower($search_engine) !== 'all') {
        $filters['source_name'] = $search_engine;
    }

    $result = wp_statistics_query([
        'sources'   => ['searches'],
        'date_from' => $dateRange['from'],
        'date_to'   => $dateRange['to'],
        'filters'   => $filters,
    ]);

    return (int) ($result['searches'] ?? 0);
}

/**
 * This function will return the statistics for a given search engine.
 *
 * @param string $search_engine
 * @param string $time
 * @param array $range
 * @return mixed
 */
function wp_statistics_searchengine($search_engine = 'all', $time = 'total', $range = [])
{
    return wp_statistics_get_search_engine_query($search_engine, $time, $search_by = 'query', $range);
}

/**
 * Return count of unique referrer domains.
 *
 * @param string|null $time  Time period: 'today', 'yesterday', etc. or date string (Y-m-d).
 * @param array       $range Optional date range configuration.
 * @return int Number of unique referrer domains.
 *
 * @since 1.0.0
 * @since 15.0.0 Refactored to use AnalyticsQuery.
 */
function wp_statistics_referrer($time = null, $range = [])
{
    // Handle date range
    if (!empty($range) && isset($range['start'], $range['end'])) {
        $dateRange = [
            'from' => $range['start'],
            'to'   => $range['end']
        ];
    } elseif (TimeZone::isValidDate($time)) {
        // Specific date provided
        $dateRange = [
            'from' => $time,
            'to'   => $time
        ];
    } else {
        // Use current date or resolve time period
        $dateRange = DateRange::resolveDate($time ?? 'today');
    }

    $result = wp_statistics_query([
        'sources'   => ['visitors'],
        'date_from' => $dateRange['from'],
        'date_to'   => $dateRange['to'],
        'group_by'  => ['referrer'],
        'format'    => 'table',
    ]);

    // Count unique external referrer domains
    $siteUrl = get_bloginfo('url');
    $count   = 0;

    if (is_array($result)) {
        foreach ($result as $row) {
            $domain = $row['referrer_domain'] ?? '';
            // Exclude internal referrals
            if (!empty($domain) && stripos($siteUrl, $domain) === false) {
                $count++;
            }
        }
    }

    return $count;
}

/**
 * Check if user consent is required for collecting statistics.
 *
 * Evaluates privacy settings to determine whether explicit user consent
 * is needed before collecting and storing visitor data. Useful for GDPR
 * compliance when implementing consent management.
 *
 * @return bool True if consent is required (privacy settings not fully configured), false otherwise.
 * @since 14.10.1
 *
 * @example
 * if (wp_statistics_needs_consent()) {
 *     // Show consent banner before tracking
 * }
 */
function wp_statistics_needs_consent()
{
    // Get the current status of the consent requirement
    $status = RequireConsent::getStatus();

    // Check if consent is required
    if ($status == 'warning') {
        return true; // Consent is required
    }

    // Return false if consent is not required
    return false;
}
