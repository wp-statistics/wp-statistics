<?php

namespace WP_STATISTICS;

use WP_Statistics\Service\Analytics\VisitorProfile;

class Pages
{
    /**
     * Check Active Record Pages
     *
     * @return mixed
     */
    public static function active()
    {
        return (has_filter('wp_statistics_active_pages')) ? apply_filters('wp_statistics_active_pages', true) : Option::get('pages');
    }

    /**
     * Get WordPress Page Type
     */
    public static function get_page_type()
    {

        //Set Default Option
        $current_page = array("type" => "unknown", "id" => 0, "search_query" => '');

        //Check Query object
        $id = get_queried_object_id();
        if (is_numeric($id) and $id > 0) {
            $current_page['id'] = $id;
        }

        //WooCommerce Product
        if (class_exists('WooCommerce')) {
            if (is_product()) {
                return wp_parse_args(array("type" => "product"), $current_page);
            }
        }

        //Home Page or Front Page
        if (is_front_page() || is_home()) {
            return wp_parse_args(array("type" => "home"), $current_page);
        }

        //attachment View
        if (is_attachment()) {
            $current_page['type'] = "attachment";
        }

        //is Archive Page
        if (is_archive()) {
            $current_page['type'] = "archive";
        }

        //Single Post From All Post Type
        if (is_singular()) {
            $post_type = get_post_type();
            if ($post_type != 'post') {
                $post_type = 'post_type_' . $post_type;
            }
            $current_page['type'] = $post_type;
        }

        //Single Page
        if (is_page()) {
            $current_page['type'] = "page";
        }

        //Category Page
        if (is_category()) {
            $current_page['type'] = "category";
        }

        //Tag Page
        if (is_tag()) {
            $current_page['type'] = "post_tag";
        }

        //is Custom Term From Taxonomy
        if (is_tax()) {
            $current_page['type'] = "tax";
        }

        //is Author Page
        if (is_author()) {
            $current_page['type'] = "author";
        }

        //is search page
        $search_query = sanitize_text_field(get_search_query(false));
        if (trim($search_query) != "") {
            return array("type" => "search", "id" => 0, "search_query" => $search_query);
        }

        //is 404 Page
        if (is_404()) {
            $current_page['type'] = "404";
        }

        // Add WordPress Feed
        if (is_feed()) {
            $current_page['type'] = "feed";
        }

        // Add WordPress Login Page
        if (Helper::is_login_page()) {
            $current_page['type'] = "loginpage";
        }

        return apply_filters('wp_statistics_current_page', $current_page);
    }

    /**
     * Check Track All Page WP Statistics
     *
     * @return bool
     */
    public static function is_track_all_page()
    {
        return apply_filters('wp_statistics_track_all_pages', Option::get('track_all_pages') || is_single() || is_page() || is_front_page());
    }

    /**
     * Get Page Url
     *
     * @return bool|mixed|string
     */
    public static function get_page_uri()
    {

        // Get the site's path from the URL.
        $site_uri     = wp_parse_url(site_url(), PHP_URL_PATH);
        $site_uri_len = strlen($site_uri ? $site_uri : '');

        // Get the site's path from the URL.
        $home_uri     = wp_parse_url(home_url(), PHP_URL_PATH);
        $home_uri_len = strlen($home_uri ? $home_uri : '');

        // Get the current page URI.
        $page_uri = sanitize_url(wp_unslash($_SERVER["REQUEST_URI"]));

        /*
         * We need to check which URI is longer in case one contains the other.
         * For example home_uri might be "/site/wp" and site_uri might be "/site".
         * In that case we want to check to see if the page_uri starts with "/site/wp" before
         * we check for "/site", but in the reverse case, we need to swap the order of the check.
         */
        if ($site_uri_len > $home_uri_len) {
            if (substr($page_uri, 0, $site_uri_len) == $site_uri) {
                $page_uri = substr($page_uri, $site_uri_len);
            }

            if (substr($page_uri, 0, $home_uri_len) == $home_uri) {
                $page_uri = substr($page_uri, $home_uri_len);
            }
        } else {
            if (substr($page_uri, 0, $home_uri_len) == $home_uri) {
                $page_uri = substr($page_uri, $home_uri_len);
            }

            if (substr($page_uri, 0, $site_uri_len) == $site_uri) {
                $page_uri = substr($page_uri, $site_uri_len);
            }
        }

        // Sanitize the page URI.
        $page_uri = sanitize_url($page_uri);

        // If we're at the root (aka the URI is blank), let's make sure to indicate it.
        if ($page_uri == '') {
            $page_uri = '/';
        }

        return apply_filters('wp_statistics_page_uri', $page_uri);
    }

    /**
     * Sanitize Page Url For Push to Database
     * @param $visitorProfile VisitorProfile
     */
    public static function sanitize_page_uri($visitorProfile)
    {

        // Get Current WordPress Page
        $current_page = $visitorProfile->getCurrentPageType();

        // Get the current page URI.
        $page_uri = Pages::get_page_uri();

        // Get String Search Wordpress
        if (array_key_exists("search_query", $current_page) and !empty($current_page["search_query"])) {
            $page_uri = "?s=" . $current_page['search_query'];
        }

        // Sanitize for WordPress Login Page
        if ($current_page['type'] == "loginpage") {
            $page_uri = Helper::RemoveQueryStringUrl($page_uri);
        }

        // Check Strip Url Parameter
        if (array_key_exists("search_query", $current_page) === false) {
            $temp = explode('?', $page_uri);
            if ($temp !== false) {
                $page_uri = $temp[0];
            }
        }

        // Filter query parameters based on allowed query params list
        $page_uri = Helper::FilterQueryStringUrl($page_uri, Helper::get_query_params_allow_list());

        // Limit the URI length to 255 characters, otherwise we may overrun the SQL field size.
        return substr($page_uri, 0, 255);
    }

    /**
     * Record Page in Database
     * @param VisitorProfile $visitorProfile
     */
    public static function record($visitorProfile)
    {
        global $wpdb;

        // Get Current WordPress Page
        $current_page = $visitorProfile->getCurrentPageType();

        // If we didn't find a page id, we don't have anything else to do.
        if ($current_page['type'] == "unknown" || !isset($current_page['id'])) {
            return false;
        }

        // Get Page uri
        $page_uri = self::sanitize_page_uri($visitorProfile);

        // If the length of URI is more than 190 characters
        // Crop it, so it can be stored in the database
        if (strlen($page_uri) > 190) {
            $page_uri = substr($page_uri, 0, 190);
        }

        // Check if we have already been to this page today.
        $search_query = array_key_exists("search_query", $current_page) === true ? $wpdb->prepare("AND `uri` = %s", $page_uri) : "";
        $tablePage    = DB::table('pages');

        $query = $wpdb->prepare(
            "SELECT `page_id` FROM `{$tablePage}` WHERE `date` = %s {$search_query} AND `type` = %s AND `id` = %d",
            TimeZone::getCurrentDate('Y-m-d'),
            $current_page['type'],
            $current_page['id']
        );
        $exist = $wpdb->get_row($query, ARRAY_A);

        // Update Exist Page
        if (null !== $exist) {
            $query = $wpdb->prepare("UPDATE `{$tablePage}` SET `count` = `count` + 1 WHERE `date` = %s {$search_query} AND `type` = %s AND `id` = %d",
                TimeZone::getCurrentDate('Y-m-d'),
                $current_page['type'],
                $current_page['id']
            );

            $wpdb->query($query);
            $page_id = $exist['page_id'];

        } else {

            // Prepare Pages Data
            $pages = array(
                'uri'   => $page_uri,
                'date'  => TimeZone::getCurrentDate('Y-m-d'),
                'count' => 1,
                'id'    => $current_page['id'],
                'type'  => $current_page['type']
            );
            $pages = apply_filters('wp_statistics_pages_information', $pages);

            // Added to DB
            $page_id = self::save_page($pages);
        }

        return (isset($page_id) ? $page_id : false);
    }

    /**
     * Add new row to Pages Table
     *
     * @param array $page
     * @return int
     */
    public static function save_page($page = array())
    {
        global $wpdb;

        # Add Filter Insert ignore
        add_filter('query', array('\WP_STATISTICS\DB', 'insert_ignore'), 10);

        # Save to WordPress Database
        $insert = $wpdb->insert(
            DB::table('pages'),
            $page
        );
        if (!$insert) {
            if (!empty($wpdb->last_error)) {
                \WP_Statistics::log($wpdb->last_error);
            }
        }

        # Get Page ID
        $page_id = $wpdb->insert_id;

        # Remove ignore filter
        remove_filter('query', array('\WP_STATISTICS\DB', 'insert_ignore'), 10);

        # Do Action After Save New Visitor
        do_action('wp_statistics_save_page', $page_id, $page);

        return $page_id;
    }

    /**
     * Get Page information
     *
     * @param $page_id
     * @param string $type
     * @return array
     */
    public static function get_page_info($page_id, $type = 'post', $slug = false)
    {

        //Create Empty Object
        $arg      = array();
        $defaults = array(
            'link'      => '',
            'edit_link' => '',
            'object_id' => $page_id,
            'title'     => '-',
            'meta'      => array()
        );

        if (!empty($type)) {
            switch ($type) {
                case "product":
                case "attachment":
                case "post":
                case "page":
                    $arg = array(
                        'title'     => esc_html(get_the_title($page_id)),
                        'link'      => get_the_permalink($page_id),
                        'edit_link' => get_edit_post_link($page_id),
                        'meta'      => array(
                            'post_type' => get_post_type($page_id)
                        )
                    );
                    break;
                case "category":
                case "post_tag":
                case "tax":
                    $term = get_term($page_id);
                    if (!is_wp_error($term) and $term !== null) {
                        $arg = array(
                            'title'     => esc_html($term->name),
                            'link'      => (is_wp_error(get_term_link($page_id)) === true ? '' : get_term_link($page_id)),
                            'edit_link' => get_edit_term_link($page_id),
                            'meta'      => array(
                                'taxonomy'         => $term->taxonomy,
                                'term_taxonomy_id' => $term->term_taxonomy_id,
                                'count'            => $term->count
                            )
                        );
                    }
                    break;
                case "home":
                    $arg = array(
                        'title' => $page_id ? sprintf(__('Home Page: %s', 'wp-statistics'), get_the_title($page_id)) : __('Home Page', 'wp-statistics'),
                        'link'  => get_site_url()
                    );
                    break;
                case "author":
                    $user_info = get_userdata($page_id);
                    $arg       = array(
                        'title'     => ($user_info->display_name != "" ? esc_html($user_info->display_name) : esc_html($user_info->first_name . ' ' . $user_info->last_name)),
                        'link'      => get_author_posts_url($page_id),
                        'edit_link' => get_edit_user_link($page_id),
                    );
                    break;
                case "feed":
                    $arg['title'] = __('Feed', 'wp-statistics');
                    break;
                case "loginpage":
                    $arg['title'] = __('Login Page', 'wp-statistics');
                    break;
                case "search":
                    $arg['title'] = __('Search Page', 'wp-statistics');
                    break;
                case "404":
                    $arg['title'] = sprintf(__('404 not found (%s)', 'wp-statistics'), esc_html(substr($slug, 0, 20)));
                    break;
                case "archive":
                    if ($slug) {
                        $post_type   = trim($slug, '/');
                        $post_object = get_post_type_object($post_type);

                        if ($post_object instanceof \WP_Post_Type) {
                            $arg['title'] = sprintf(__('Post Archive: %s', 'wp-statistics'), $post_object->labels->name);
                            $arg['link']  = get_post_type_archive_link($post_type);
                        } else {
                            $arg['title'] = sprintf(__('Post Archive: %s', 'wp-statistics'), $slug);
                            $arg['link']  = home_url($slug);
                        }
                    } else {
                        $arg['title'] = __('Post Archive', 'wp-statistics');
                    }

                    break;
                default:
                    $arg = array(
                        'title'     => esc_html(get_the_title($page_id)),
                        'link'      => get_the_permalink($page_id),
                        'edit_link' => get_edit_post_link($page_id),
                        'meta'      => array(
                            'post_type' => get_post_type($page_id)
                        )
                    );
                    break;
            }
        }

        return wp_parse_args($arg, $defaults);
    }

    /**
     * Get Top number of Hits Pages
     *
     * @param array $args
     * @return array|int|mixed
     */
    public static function getTop($args = array())
    {
        global $wpdb;

        // Define the array of defaults
        $defaults = array(
            'per_page' => 10,
            'paged'    => 1,
            'from'     => '',
            'to'       => '',
            'ago'      => '',
            'type'     => '',
        );

        $args = wp_parse_args($args, $defaults);

        // Check Default
        if (empty($args['from']) and empty($args['to'])) {
            if (array_key_exists($args['ago'], TimeZone::getDateFilters())) {
                $dateFilter   = TimeZone::calculateDateFilter($args['ago']);
                $args['from'] = $dateFilter['from'];
                $args['to']   = $dateFilter['to'];
            }
        }

        // Prepare Count Day
        if (!empty($args['from']) and !empty($args['to'])) {
            $count_day = TimeZone::getNumberDayBetween($args['from'], $args['to']);
        } else {
            if (is_numeric($args['ago']) and $args['ago'] > 0) {
                $count_day = $args['ago'];
            } else {
                $count_day = 30;
            }
        }

        // Get time ago Days Or Between Two Days
        if (!empty($args['from']) and !empty($args['to'])) {
            $days_list = TimeZone::getListDays(array('from' => $args['from'], 'to' => $args['to']));
        } else {
            if (is_numeric($args['ago']) and $args['ago'] > 0) {
                $days_list = TimeZone::getListDays(array('from' => TimeZone::getTimeAgo($args['ago'])));
            } else {
                $days_list = TimeZone::getListDays(array('from' => TimeZone::getTimeAgo($count_day)));
            }
        }

        // Get List Of Days
        $days_time_list = array_keys($days_list);

        // Date Time SQL
        $DateTimeSql = $wpdb->prepare("WHERE (`pages`.`date` BETWEEN %s AND %s)", reset($days_time_list), end($days_time_list));

        // Post Type SQL
        $postTypeSql = '';

        if (!empty($args['type'])) {
            if ($args['type'] == 'page') {
                $postTypeSql = $wpdb->prepare(" AND `pages`.`type` IN (%s, %s)", $args['type'], 'home');
            } else {
                $postTypeSql = $wpdb->prepare(" AND `pages`.`type` = %s", $args['type']);
            }
        }

        // Generate SQL
        $selectSql = "SELECT `pages`.`date`,`pages`.`uri`,`pages`.`id`,`pages`.`type`, SUM(`pages`.`count`) AS `count_sum` FROM `" . DB::table('pages') . "` `pages` {$DateTimeSql} {$postTypeSql}";

        // Group pages with ID of 0 by type and URI, and group the rest of pages by ID
        $sql = "
            ($selectSql AND `pages`.`id` != 0 GROUP BY `pages`.`id`)
            UNION
            ($selectSql AND `pages`.`id` = 0 GROUP BY `pages`.`uri`, `pages`.`type`)
            ORDER BY `count_sum` DESC
        ";

        // Get List Of Pages
        $list   = array();
        $result = $wpdb->get_results($sql . " LIMIT " . ($args['paged'] - 1) * $args['per_page'] . "," . $args['per_page']); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared	

        foreach ($result as $item) {
            // Lookup the post title.
            $page_info = Pages::get_page_info($item->id, $item->type, $item->uri);

            // Push to list
            $list[] = array(
                'title'     => esc_html($page_info['title']),
                'link'      => $page_info['link'],
                'str_url'   => esc_url(urldecode($item->uri)),
                'hits_page' => Menus::admin_url('pages', array('ID' => $item->id, 'type' => $item->type)),
                'number'    => number_format_i18n($item->count_sum)
            );
        }

        return $list;
    }

    /**
     * Count Number Page in DB Table
     *
     * @param string $group_by
     * @param array $args
     * @return mixed
     */
    public static function TotalCount($group_by = 'uri', $args = array())
    {
        global $wpdb;
        $where = [];

        // Date
        if (isset($args['from']) and isset($args['to']) and !empty($args['from']) and !empty($args['to'])) {
            $where[] = $wpdb->prepare("`date` BETWEEN %s AND %s", $args['from'], $args['to']);
        }

        if (!empty($args['type'])) {
            $where[] = $wpdb->prepare("`type` = %s", $args['type']);
        }

        $where = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

        // Return
        return $wpdb->get_var("SELECT COUNT(*) FROM (SELECT COUNT(page_id) FROM `" . DB::table('pages') . "` `pages` {$where} GROUP BY `{$group_by}`) AS totalCount"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
    }

    /**
     * Get Post Type by ID
     *
     * @param $post_id
     * @return string
     */
    public static function get_post_type($post_id)
    {
        $post_type = get_post_type($post_id);
        return (in_array($post_type, array("post", "page", "product", "attachment")) ? $post_type : "post_type_" . $post_type);
    }

    /**
     * Convert Url to Page ID
     *
     * @param $uri
     * @return int
     */
    public static function uri_to_id($uri)
    {
        global $wpdb;
        $result = $wpdb->get_var(
            $wpdb->prepare("SELECT id FROM `" . DB::table('pages') . "` WHERE `uri` = %s and id > 0 ORDER BY date DESC", $uri)
        );
        if ($result == 0) {
            $result = 0;
        }

        return $result;
    }

    public static function checkIfPageIsHome($postID = false)
    {
        if (get_option('show_on_front') == 'page') {
            if (get_option('page_on_front') == $postID or get_option('page_for_posts') == $postID) {
                return true;
            }
        }
        return false;
    }
}