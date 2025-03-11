<?php

use WP_STATISTICS\Country;
use WP_STATISTICS\IP;
use WP_STATISTICS\Pages;
use WP_Statistics\Service\Analytics\DeviceDetection\DeviceHelper;
use WP_Statistics\Service\Analytics\DeviceDetection\UserAgent;
use WP_Statistics\Service\Geolocation\GeolocationFactory;
use WP_Statistics\Service\Admin\PrivacyAudit\Faqs\RequireConsent;
use WP_STATISTICS\TimeZone;
use WP_STATISTICS\User;

/**
 * Get Current User IP
 */
function wp_statistics_get_user_ip()
{
    return IP::getIP();
}

/**
 * Get Current User Data
 *
 * @throws Exception
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
    if (User::is_login()) {
        $data['user'] = User::get();
    }

    // Return
    return $data;
}

/**
 * Get User Statistics Data By IP
 *
 * @param bool $ip
 * @return array
 * @throws Exception
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
        'flag' => Country::flag($country)
    );

    // Get User City
    $data['city'] = $location['city'];

    return $data;
}

/**
 * Get Current Users online
 *
 * @param array $options
 * @return mixed
 */
function wp_statistics_useronline($options = array())
{
    global $wpdb;

    //Check Parameter
    $defaults = array(
        /**
         * Type Of Page in WordPress
         * @See Frontend\get_page_type
         *
         * -- Acceptable values --
         *
         * post     -> WordPress Post single page From All of public post Type
         * page     -> WordPress page single page
         * product  -> WooCommerce product single page
         * home     -> Home Page website
         * category -> WordPress Category Page
         * post_tag -> WordPress Post Tags Page
         * tax      -> WordPress Term Page for all Taxonomies
         * author   -> WordPress Users page
         * 404      -> 404 Not Found Page
         * archive  -> WordPress Archive Page
         * all      -> All Site Page
         *
         */
        'type'         => 'all',
        /**
         * WordPress Query object ID
         * @example array('type' => 'product', 'ID' => 5)
         */
        'ID'           => 0,
        /**
         * Get number of logged users or all users
         *
         * -- Acceptable values --
         * false  -> Get Number of all users
         * true   -> Get Number of all logged users in wordpress
         */
        'logged_users' => false,
        /**
         * Get number User From Custom Country
         *
         * -- Acceptable values --
         * ISO Country Code -> For Get List @See \wp-statistics\includes\functions\country-code.php
         *
         */
        'location'     => 'all',
        /**
         * Search Filter by User agent name
         * e.g : Firefox , Chrome , Safari , Unknown ..
         * @see wp_statistics_get_browser_list()
         *
         */
        'agent'        => 'all',
        /**
         * Search filter by User Operating System name
         * e.g : Windows, iPad, Macintosh, Unknown, ..
         *
         */
        'platform'     => 'all',
        /**
         * Return Of Data
         *
         * -- Acceptable values --
         * count -> Get number of user online
         * all   -> Get List of User Online data
         */
        'return'       => 'count'
    );

    // Parse incoming $args into an array and merge it with $defaults
    $arg = wp_parse_args($options, $defaults);

    //Basic SQL
    $type_request = ($arg['return'] == "all" ? '*' : 'COUNT(*)');
    $sql          = "SELECT {$type_request} FROM " . WP_STATISTICS\DB::table('useronline');

    //Check Where Condition
    $where = [];

    //Check Type of Page
    if ($arg['type'] != "all") {
        $where[] = "`type`='" . $arg['type'] . "' AND `page_id` = " . $arg['ID'];
    }

    //Check Custom user
    if ($arg['logged_users'] === true) {
        $where[] = "`user_id` > 0";
    }

    //Check Location
    if ($arg['location'] != "all") {
        $ISOCountryCode = Country::getList();
        if (array_key_exists($arg['location'], $ISOCountryCode)) {
            $where[] = "`location` = '" . $arg['location'] . "'";
        }
    }

    //Check User Agent
    if ($arg['agent'] != "all") {
        $where[] = "`agent` = '" . $arg['agent'] . "'";
    }

    //Check User Platform
    if ($arg['platform'] != "all") {
        $where[] = "`platform` = '" . $arg['platform'] . "'";
    }

    //Push Conditions to SQL
    if (!empty($where)) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }

    //Return Number od user Online
    return ($arg['return'] == "count" ? $wpdb->get_var($sql) : $wpdb->get_results($sql)); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
}

/**
 * This function get the visit statistics for a given time frame
 *
 * @param $time
 * @param null $daily
 * @return int
 */
function wp_statistics_visit($time, $daily = null)
{
    global $wpdb;

    //Date Column Name in visits table
    $table_name  = WP_STATISTICS\DB::table('visit');
    $date_column = 'last_counter';

    //Prepare Selector Sql
    $selector = 'SUM(visit)';
    if ($daily == true) {
        $selector = '*';
    }

    //Generate Base Sql
    $sql = "SELECT " . $selector . " FROM `" . $table_name . "` ";

    //Create Sum Views variable
    $sum = 0;

    //Check if daily Report
    if ($daily === true) {

        // Check Sanitize Datetime
        if (TimeZone::isValidDate($time)) {
            $d = $time;
        } else {
            $d = TimeZone::getCurrentDate('Y-m-d', $time);
        }

        $result = $wpdb->get_row($sql . " WHERE `$date_column` = '" . $d . "'");  // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        if (null !== $result) {
            $sum = $result->visit;
        }
    } else {

        //Generate MySql Time Conditions
        if (is_array($time) && array_key_exists('start', $time) && array_key_exists('end', $time)) {
            $mysql_time_sql = WP_STATISTICS\Helper::mysql_time_conditions($date_column, '', $time);
            if (!empty($mysql_time_sql)) {
                $sql = $sql . ' WHERE ' . $mysql_time_sql;
            }
        } else {
            $mysql_time_sql = WP_STATISTICS\Helper::mysql_time_conditions($date_column, $time);
            if (!empty($mysql_time_sql)) {
                $sql = $sql . ' WHERE ' . $mysql_time_sql;
            }
        }

        //Request To database
        $result = $wpdb->get_var($sql); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

        //Custom Action
        if ($time == "total") {
            $result += WP_STATISTICS\Historical::get('visits');
        }

        $sum = $result;
    }

    return !is_numeric($sum) ? 0 : $sum;
}

/**
 * This function gets the visitor statistics for a given time frame.
 *
 * @param $time
 * @param null $daily
 * @param bool $count_only
 * @param array $options
 * @return int|null|string
 */
function wp_statistics_visitor($time, $daily = null, $count_only = false, $options = array())
{
    global $wpdb;

    //Check Parameter
    $defaults = array(
        /**
         * Type Of Page in WordPress
         * @See Frontend\get_page_type
         *
         * -- Acceptable values --
         *
         * post     -> WordPress Post single page From All of public post Type
         * page     -> WordPress page single page
         * product  -> WooCommerce product single page
         * home     -> Home Page website
         * category -> WordPress Category Page
         * post_tag -> WordPress Post Tags Page
         * tax      -> WordPress Term Page for all Taxonomies
         * author   -> WordPress Users page
         * 404      -> 404 Not Found Page
         * archive  -> WordPress Archive Page
         * all      -> All Site Page
         *
         */
        'type'     => 'all',
        /**
         * WordPress Query object ID
         * @example array('type' => 'product', 'ID' => 5)
         */
        'ID'       => 0,
        /**
         * Get number User From Custom Country
         *
         * -- Acceptable values --
         * ISO Country Code -> For Get List @See \wp-statistics\includes\functions\country-code.php
         *
         */
        'location' => 'all',
        /**
         * Search Filter by User agent name
         * e.g : Firefox , Chrome , Safari , Unknown ..
         * @see wp_statistics_get_browser_list()
         *
         */
        'agent'    => 'all',
        /**
         * Search filter by User Operating System name
         * e.g : Windows, iPad, Macintosh, Unknown, ..
         *
         */
        'platform' => 'all'
    );

    // Parse incoming $args into an array and merge it with $defaults
    $arg = wp_parse_args($options, $defaults);

    //Create History Visitors variable
    $history = 0;

    //Prepare Selector Sql
    $date_column = 'last_counter';
    $selector    = '*';
    if ($count_only == true) {
        $selector = 'count(last_counter)';
    }

    //Generate Base Sql
    if ($arg['type'] != "all" and WP_STATISTICS\Option::get('visitors_log') == true) {
        $sql = "SELECT {$selector} FROM `" . WP_STATISTICS\DB::table('visitor') . "` INNER JOIN `" . WP_STATISTICS\DB::table("visitor_relationships") . "` ON `" . WP_STATISTICS\DB::table("visitor_relationships") . "`.`visitor_id` = `" . WP_STATISTICS\DB::table('visitor') . "`.`ID`  INNER JOIN `" . WP_STATISTICS\DB::table('pages') . "` ON `" . WP_STATISTICS\DB::table('pages') . "`.`page_id` = `" . WP_STATISTICS\DB::table("visitor_relationships") . "` . `page_id`";
    } else {
        $sql = "SELECT {$selector} FROM `" . WP_STATISTICS\DB::table('visitor') . "`";
    }

    //Check Where Condition
    $where = [];

    //Check Type of Page
    if ($arg['type'] != "all" and WP_STATISTICS\Option::get('visitors_log') == true) {
        $where[] = "`" . WP_STATISTICS\DB::table('pages') . "`.`type`='" . $arg['type'] . "' AND `" . WP_STATISTICS\DB::table('pages') . "`.`page_id` = " . $arg['ID'];
    }

    //Check Location
    if ($arg['location'] != "all") {
        $ISOCountryCode = Country::getList();
        if (array_key_exists($arg['location'], $ISOCountryCode)) {
            $where[] = "`" . WP_STATISTICS\DB::table('visitor') . "`.`location` = '" . $arg['location'] . "'";
        }
    }

    //Check User Agent
    if ($arg['agent'] != "all") {
        $where[] = "`" . WP_STATISTICS\DB::table('visitor') . "`.`agent` = '" . $arg['agent'] . "'";
    }

    //Check User Platform
    if ($arg['platform'] != "all") {
        $where[] = "`" . WP_STATISTICS\DB::table('visitor') . "`.`platform` = '" . $arg['platform'] . "'";
    }

    //Check Date Time report
    if ($daily == true) {

        // Check Sanitize Datetime
        if (TimeZone::isValidDate($time)) {
            $d = $time;
        } else {
            $d = TimeZone::getCurrentDate('Y-m-d', $time);
        }

        //Get Only Current Day Visitors
        $where[] = "`" . WP_STATISTICS\DB::table('visitor') . "`.`last_counter` = '" . $d . "'";
    } else {

        //Generate MySql Time Conditions
        if (is_array($time) && array_key_exists('start', $time) && array_key_exists('end', $time)) {
            $mysql_time_sql = WP_STATISTICS\Helper::mysql_time_conditions($date_column, '', $time);
            if (!empty($mysql_time_sql)) {
                $sql = $sql . ' WHERE ' . $mysql_time_sql;
            }
        } else {
            $mysql_time_sql = WP_STATISTICS\Helper::mysql_time_conditions($date_column, $time);
            if (!empty($mysql_time_sql)) {
                $where[] = $mysql_time_sql;
            }
        }
    }

    //Push Conditions to SQL
    if (!empty($where)) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }

    //Custom Action
    if ($time == "total" and $arg['type'] == "all") {
        $history = WP_STATISTICS\Historical::get('visitors');
    }

    // Execute the SQL call, if we're only counting we can use get_var(), otherwise we use query().
    if ($count_only == true) {
        $sum = $wpdb->get_var($sql); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $sum += $history;
    } else {
        $sum = $wpdb->query($sql); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    }

    return $sum;
}

/**
 * This function returns the statistics for a given page.
 *
 * @param $time
 * @param string $page_uri
 * @param int $id
 * @param null $rangestartdate
 * @param null $rangeenddate
 * @param bool $type
 * @return int|null|string
 *
 * @todo    Replace all instances of this function with `ViewsModel->countViews()`.
 */
function wp_statistics_pages($time, $page_uri = '', $id = -1, $rangestartdate = null, $rangeenddate = null, $type = false)
{
    global $wpdb;

    //Date Column Name in visits table
    $table_name  = WP_STATISTICS\DB::table('pages');
    $date_column = 'date';

    // History Vars
    $history     = 0;
    $history_key = null;
    $history_id  = null;

    //Check Where Condition
    $where = [];

    //Check Query By Page ID or Page Url
    if ($type) {
        $query = $wpdb->prepare("`type` = %s", $type);

        if ($id != -1) {
            $query       .= $wpdb->prepare(" AND `id` = %d", $id);
            $history_key = 'page';
            $history_id  = absint($id);
        }

        if ($page_uri != '') {
            $page_uri_sql = esc_sql($page_uri);
            $query        .= $wpdb->prepare(" AND `URI` = %s", $page_uri_sql);
            $history_key  = 'uri';
            $history_id   = $page_uri;
        }

        $where[] = apply_filters('wp_statistics_pages_where_type_query', $query, $id, $type);
    } else {

        // If no page URI has been passed in, get the current page URI.
        if ($page_uri == '') {
            $page_uri = Pages::get_page_uri();
        }
        $page_uri_sql = esc_sql($page_uri);

        // If a page/post ID has been passed, use it to select the rows, otherwise use the URI.
        if ($id != -1) {
            $where[]     = "`id`= " . absint($id);
            $history_key = 'page';
            $history_id  = absint($id);
        } else {
            $where[]     = "`URI` = '{$page_uri_sql}'";
            $history_key = 'uri';
            $history_id  = $page_uri;
        }
    }

    //Custom Action
    if ($time == "total") {
        if ($history_key && $history_id) {
            $history = WP_STATISTICS\Historical::get($history_key, $history_id);
        }
    }

    //Prepare Time
    $time_array = array();
    if (is_numeric($time) || TimeZone::isValidDate($time)) {
        $time_array['is_day'] = true;
    }
    if (!is_null($rangestartdate) and !is_null($rangeenddate)) {
        $time_array = array('start' => $rangestartdate, 'end' => $rangeenddate);
    }

    //Check MySql Time Conditions
    $mysql_time_sql = WP_STATISTICS\Helper::mysql_time_conditions($date_column, $time, $time_array);
    if (!empty($mysql_time_sql)) {
        $where[] = $mysql_time_sql;
    }

    //Generate Base Sql
    $sql = "SELECT SUM(count) FROM {$table_name}";

    //Push Conditions to SQL
    if (!empty($where)) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }

    //Request Get data
    $sum = $wpdb->get_var($sql); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    $sum += $history;

    //Return Number Statistic
    return ($sum == '' ? 0 : $sum);
}

/**
 * Get top Pages between Time
 *
 * @param null $rangestartdate
 * @param null $rangeenddate
 * @param null $limit
 * @param null $post_type
 * @return array
 */
function wp_statistics_get_top_pages($rangestartdate = null, $rangeenddate = null, $limit = null, $post_type = null)
{
    global $wpdb;

    $spliceLimit = ($limit != null ? $limit : 5);
    $limit       = null;

    // Get every unique URI from the pages database.
    if ($rangestartdate != null && $rangeenddate != null) {
        $whereType = ($post_type != null ? $wpdb->prepare(" AND `type`=%s", $post_type) : '');
        $result    = $wpdb->get_results(
            $wpdb->prepare("SELECT `uri`,`id`,`type` FROM " . \WP_STATISTICS\DB::table('pages') . " WHERE `date` BETWEEN %s AND %s {$whereType} GROUP BY `id`" . ($limit != null ? ' LIMIT ' . $limit : ''), $rangestartdate, $rangeenddate), // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
            ARRAY_N);
    } else {
        $limitQuery = '';
        if ($limit) {
            $limitQuery = $wpdb->prepare(" LIMIT %d", $limit);
        }
        $whereType = ($post_type != null ? $wpdb->prepare(" WHERE `type`=%s", $post_type) : '');
        $result    = $wpdb->get_results("SELECT `uri`, `id`, `type` FROM " . \WP_STATISTICS\DB::table('pages') . " {$whereType} GROUP BY `id` {$limitQuery}", ARRAY_N); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    }

    $total = 0;
    $uris  = array();

    // Now get the total page visit count for each unique URI.
    foreach ($result as $out) {
        //Prepare item
        list($url, $page_id, $page_type) = $out;

        // Check if item is of specific post type (string or part of an array) or if post type is set to null
        if (is_null($post_type) || $page_type == $post_type || (is_array($post_type) && in_array($page_type, $post_type))) {
            // Increment the total number of results.
            $total++;

            //Get Page Title
            $page_info = Pages::get_page_info($page_id, $page_type);
            $title     = mb_substr($page_info['title'], 0, 200, "utf-8");
            $page_url  = $page_info['link'];

            // Check age Title if page id or type not exist
            if ($page_info['link'] == "") {
                $page_url = path_join(get_site_url(), $url);
                $id       = WP_STATISTICS\Pages::uri_to_id($out[0]);
                $post     = get_post($id);
                if (is_object($post)) {
                    $title = esc_html($post->post_title);
                } else {
                    if ($out[0] == '/') {
                        $title = get_bloginfo();
                    } else {
                        $title = '';
                    }
                }
            }

            //Check Title is empty
            if (empty($title)) {
                $title = '-';
            }

            // Add the current post to the array.
            if ($rangestartdate != null && $rangeenddate != null) {
                $uris[] = array(
                    urldecode_deep($out[0]),
                    wp_statistics_pages('range', $out[0], -1, $rangestartdate, $rangeenddate, $post_type),
                    $page_id,
                    $title,
                    $page_url,
                );
            } else {
                $uris[] = array(
                    urldecode_deep($out[0]),
                    wp_statistics_pages('total', $out[0], -1, $rangestartdate, $rangeenddate, $post_type),
                    $page_id,
                    $title,
                    $page_url
                );
            }
        }
    }

    // If we have more than one result, let's sort them using usort.
    if (count($uris) > 1) {
        usort($uris, array('\WP_STATISTICS\Helper', 'compare_uri_hits_int'));
    }

    array_splice($uris, $spliceLimit);

    return array($spliceLimit, $uris);
    // return array($total, $uris);
}

/**
 * Returns all unique user agents in the database.
 *
 * @param null $rangestartdate
 * @param null $rangeenddate
 * @return array
 */
function wp_statistics_ua_list($rangestartdate = null, $rangeenddate = null)
{
    global $wpdb;

    if ($rangestartdate != null && $rangeenddate != null) {
        if ($rangeenddate == 'CURDATE()') {
            $result = $wpdb->get_results(
                $wpdb->prepare("SELECT DISTINCT agent FROM `" . \WP_STATISTICS\DB::table('visitor') . "` WHERE `last_counter` BETWEEN %s AND CURDATE()", $rangestartdate),
                ARRAY_N);
        } else {
            $result = $wpdb->get_results(
                $wpdb->prepare("SELECT DISTINCT agent FROM `" . \WP_STATISTICS\DB::table('visitor') . "` WHERE `last_counter` BETWEEN %s AND %s", $rangestartdate, $rangeenddate),
                ARRAY_N);
        }
    } else {
        $result = $wpdb->get_results(
            "SELECT DISTINCT agent FROM  `" . \WP_STATISTICS\DB::table('visitor') . "` ",
            ARRAY_N);
    }

    $Browsers        = array();
    $default_browser = DeviceHelper::getBrowserList();

    foreach ($result as $out) {

        //Check Browser is defined in wp-statistics
        if (array_key_exists(strtolower($out[0]), $default_browser)) {
            $Browsers[] = esc_html($out[0]);
        }
    }

    return $Browsers;
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
    global $wpdb;

    if ($rangestartdate != null || $rangeenddate != null) {
        if ($rangeenddate == null) {
            $result = $wpdb->get_var(
                $wpdb->prepare("SELECT COUNT(*) FROM  `" . \WP_STATISTICS\DB::table('visitor') . "` WHERE `agent` = %s AND `last_counter` = %s", $agent, $rangestartdate)
            );
        } else {
            $result = $wpdb->get_var(
                $wpdb->prepare("SELECT COUNT(*) FROM  `" . \WP_STATISTICS\DB::table('visitor') . "` WHERE `agent` = %s AND `last_counter` BETWEEN %s AND %s", $agent, $rangestartdate, $rangeenddate)
            );
        }
    } else {
        $result = $wpdb->get_var(
            $wpdb->prepare("SELECT COUNT(*) FROM  `" . \WP_STATISTICS\DB::table('visitor') . "` WHERE `agent` = %s", $agent)
        );
    }

    return $result;
}

/**
 * Returns all unique platform types from the database.
 *
 * @param null $rangestartdate
 * @param null $rangeenddate
 * @return array
 */
function wp_statistics_platform_list($rangestartdate = null, $rangeenddate = null)
{
    global $wpdb;

    if ($rangestartdate != null && $rangeenddate != null) {
        $result = $wpdb->get_results(
            $wpdb->prepare("SELECT DISTINCT platform FROM  `" . \WP_STATISTICS\DB::table('visitor') . "`  WHERE `last_counter` BETWEEN %s AND %s", $rangestartdate, $rangeenddate),
            ARRAY_N);
    } else {
        $result = $wpdb->get_results(
            "SELECT DISTINCT platform FROM  `" . \WP_STATISTICS\DB::table('visitor') . "` ",
            ARRAY_N);
    }

    $Platforms = array();
    foreach ($result as $out) {
        $Platforms[] = esc_html($out[0]);
    }

    return $Platforms;
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
    global $wpdb;

    if ($rangestartdate != null && $rangeenddate != null) {
        $result = $wpdb->get_var(
            $wpdb->prepare("SELECT COUNT(platform) FROM `" . \WP_STATISTICS\DB::table('visitor') . "` WHERE `platform` = %s AND `last_counter` BETWEEN %s AND %s", $platform, $rangestartdate, $rangeenddate)
        );
    } else {
        $result = $wpdb->get_var(
            $wpdb->prepare("SELECT COUNT(platform) FROM `" . \WP_STATISTICS\DB::table('visitor') . "` WHERE `platform` = %s", $platform)
        );
    }

    return $result;
}

/**
 * Returns all unique versions for a given agent from the database.
 *
 * @param $agent
 * @param null $rangestartdate
 * @param null $rangeenddate
 * @return array
 */
function wp_statistics_agent_version_list($agent, $rangestartdate = null, $rangeenddate = null)
{
    global $wpdb;

    if ($rangestartdate != null && $rangeenddate != null) {
        $result = $wpdb->get_results(
            $wpdb->prepare("SELECT DISTINCT `version` FROM `" . \WP_STATISTICS\DB::table('visitor') . "` WHERE agent = %s AND `last_counter` BETWEEN %s AND %s", $agent, $rangestartdate, $rangeenddate),
            ARRAY_N);
    } else {
        $result = $wpdb->get_results(
            $wpdb->prepare("SELECT DISTINCT `version` FROM `" . \WP_STATISTICS\DB::table('visitor') . "` WHERE agent = %s", $agent),
            ARRAY_N);
    }

    $Versions = array();
    foreach ($result as $out) {
        $Versions[] = $out[0];
    }

    return $Versions;
}

/**
 * Returns the statistics for a given agent/version pair from the database.
 *
 * @param $agent
 * @param $version
 * @param null $rangestartdate
 * @param null $rangeenddate
 * @return mixed
 */
function wp_statistics_agent_version($agent, $version, $rangestartdate = null, $rangeenddate = null)
{
    global $wpdb;

    if ($rangestartdate != null && $rangeenddate != null) {
        $result = $wpdb->get_var(
            $wpdb->prepare("SELECT COUNT(version) FROM `" . \WP_STATISTICS\DB::table('visitor') . "` WHERE agent = %s AND version = %s AND `last_counter` BETWEEN %s AND %s", $agent, $version, $rangestartdate, $rangeenddate)
        );
    } else {
        $result = $wpdb->get_var(
            $wpdb->prepare("SELECT COUNT(version) FROM `" . \WP_STATISTICS\DB::table('visitor') . "` WHERE agent = %s AND version = %s", $agent, $version)
        );
    }

    return $result;
}

/**
 * Return the SQL WHERE clause for getting the search engine.
 *
 * @param string $search_engine
 * @return bool|string
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
 */
function wp_statistics_get_search_engine_query($search_engine = 'all', $time = 'total', $search_by = 'query', $range = [])
{
    global $wpdb;

    //Prepare Table Name
    $table_name = \WP_STATISTICS\DB::table('visitor');

    //Date Column table
    $date_column = 'last_counter';

    // Get a complete list of search engines
    if ($search_by == "query") {
        $search_query = wp_statistics_searchengine_query($search_engine);
    }

    //Generate Base Sql
    $sql = "SELECT COUNT(ID) FROM {$table_name} WHERE ({$search_query})";

    // Check Sanitize Datetime
    if (TimeZone::isValidDate($time)) {
        if (empty($range)) $range = ['is_day' => true];
    } else {
        if (empty($range)) $range = ['current_date' => true];
    }

    $mysql_time_sql = WP_STATISTICS\Helper::mysql_time_conditions($date_column, $time, $range);

    //Generate MySql Time Conditions
    if (!empty($mysql_time_sql)) {
        $sql = $sql . ' AND (' . $mysql_time_sql . ')';
    }

    //Request Data
    return $wpdb->get_var($sql); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
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
 * Return Refer List
 *
 * @param null $time
 * @param array $range
 * @return int
 */
function wp_statistics_referrer($time = null, $range = [])
{
    global $wpdb;

    $sql = "SELECT `referred` FROM `" . \WP_STATISTICS\DB::table('visitor') . "` WHERE referred <> ''";

    // Check Sanitize Datetime
    if (TimeZone::isValidDate($time)) {
        if (empty($range)) $range = ['is_day' => true];
    } else {
        if (empty($range)) $range = ['current_date' => true];
    }

    $mysql_time_sql = WP_STATISTICS\Helper::mysql_time_conditions('last_counter', $time, $range);

    //Generate MySql Time Conditions
    if (!empty($mysql_time_sql)) {
        $sql = $sql . ' AND (' . $mysql_time_sql . ')';
    }

    $result = $wpdb->get_results($sql); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

    $urls = array();
    foreach ($result as $item) {
        $url = wp_parse_url($item->referred);
        if (empty($url['host']) || stripos(get_bloginfo('url'), $url['host']) !== false) {
            continue;
        }
        $urls[] = $url['scheme'] . '://' . $url['host'];
    }
    $get_urls = array_count_values($urls);

    return count($get_urls);
}

/**
 * Checks if consent is required for collecting user statistics.
 *
 * This function evaluates several conditions that determine whether consent
 * is needed to collect and store user data for statistics purposes. If any
 * of the conditions are not met, it indicates that consent is required.
 *
 * @return bool Returns true if consent is required, false otherwise.
 * @since 14.10.1
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
