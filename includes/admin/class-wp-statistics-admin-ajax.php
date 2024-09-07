<?php

namespace WP_STATISTICS;

use WP_Statistics\Components\DateRange;
use WP_Statistics\Models\VisitorsModel;
use WP_Statistics\Utils\Request;

class Ajax
{
    public function __construct()
    {
        add_action('init', [$this, 'registerAjaxCallbacks']);
    }

    /**
     * Register AJAX callbacks
     */
    public function registerAjaxCallbacks()
    {
        /**
         * List Of Setup Ajax request in WordPress
         */
        $list = [
            [
                'class'  => $this, 
                'action' => 'clear_user_agent_strings',
                'public' => false
            ],
            [
                'class'  => $this, 
                'action' => 'query_params_cleanup',
                'public' => false
            ],
            [
                'class'  => $this, 
                'action' => 'delete_agents',
                'public' => false
            ],
            [
                'class'  => $this, 
                'action' => 'delete_platforms',
                'public' => false
            ],
            [
                'class'  => $this, 
                'action' => 'delete_ip',
                'public' => false
            ],
            [
                'class'  => $this, 
                'action' => 'delete_user_ids',
                'public' => false
            ],
            [
                'class'  => $this, 
                'action' => 'purge_data',
                'public' => false
            ],
            [
                'class'  => $this, 
                'action' => 'purge_visitor_hits',
                'public' => false
            ],
            [
                'class'  => $this, 
                'action' => 'visitors_page_filters',
                'public' => false
            ],
            [
                'class'  => $this, 
                'action' => 'update_geoip_database',
                'public' => false
            ],
            [
                'class'  => $this, 
                'action' => 'admin_meta_box',
                'public' => false
            ],
            [
                'class'  => $this, 
                'action' => 'get_page_filter_items',
                'public' => false
            ],
            [
                'class'  => $this, 
                'action' => 'search_visitors',
                'public' => false
            ],
            [
                'class'  => $this, 
                'action' => 'store_date_range',
                'public' => false
            ]
        ];

        $list = apply_filters('wp_statistics_ajax_list', $list);

        foreach ($list as $item) {
            $class    = $item['class'];
            $action   = $item['action'];
            $callback = $action . '_action_callback';
            $isPublic = isset($item['public']) && $item['public'] == true ? true : false;

            // If callback exists in the class, register the action
            if (method_exists($class, $callback)) {
                add_action('wp_ajax_wp_statistics_' . $action, [$class, $callback]);
            
                // Register the AJAX callback publicly
                if ($isPublic) {
                    add_action('wp_ajax_nopriv_wp_statistics_' . $action, [$class, $callback]);
                }
            }
        }
    }

    /**
     * Setup an AJAX action to delete an agent in the optimization page.
     */
    public function delete_agents_action_callback()
    {
        global $wpdb;

        if (Helper::is_request('ajax') and User::Access('manage')) {

            // Check Refer Ajax
            check_ajax_referer('wp_rest', 'wps_nonce');

            // Check Exist
            if (isset($_POST['agent-name'])) {

                // Get User Agent
                $agent = sanitize_text_field($_POST['agent-name']);

                // Remove Type Of Agent
                $result = $wpdb->query($wpdb->prepare("DELETE FROM `" . DB::table('visitor') . "` WHERE `agent` = %s", $agent));

                // Show Result
                if ($result) {
                    echo sprintf(__('Successfully deleted %s agent data.', 'wp-statistics'), '<code>' . esc_attr($agent) . '</code>'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                } else {
                    esc_html_e('Couldn’t find agent data to delete.', 'wp-statistics');
                }
            } else {
                esc_html_e('Kindly select the items you want to work with.', 'wp-statistics');
            }
        } else {
            esc_html_e('Unauthorized access!', 'wp-statistics');
        }

        exit;
    }

    /**
     * Setup an AJAX action to delete a platform in the optimization page.
     */
    public function delete_platforms_action_callback()
    {
        global $wpdb;

        if (Helper::is_request('ajax') and User::Access('manage')) {

            // Check Refer Ajax
            check_ajax_referer('wp_rest', 'wps_nonce');

            // Check Isset Platform
            if (isset($_POST['platform-name'])) {

                // Get User Platform
                $platform = sanitize_text_field($_POST['platform-name']);

                // Delete List
                $result = $wpdb->query($wpdb->prepare("DELETE FROM `" . DB::table('visitor') . "` WHERE `platform` = %s", $platform));

                // Return Result
                if ($result) {
                    echo sprintf(__('Successfully deleted %s platform data.', 'wp-statistics'), '<code>' . esc_attr($platform) . '</code>'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                } else {
                    esc_html_e('Couldn’t find platform data to delete.', 'wp-statistics');
                }
            } else {
                esc_html_e('Kindly select the items you want to work with.', 'wp-statistics');
            }
        } else {
            esc_html_e('Unauthorized access!', 'wp-statistics');
        }

        exit;
    }

    /**
     * Setup an AJAX action to delete a ip in the optimization page.
     */
    public function delete_ip_action_callback()
    {
        global $wpdb;

        if (Helper::is_request('ajax') and User::Access('manage')) {

            // Check Refer Ajax
            check_ajax_referer('wp_rest', 'wps_nonce');

            // Check Isset IP
            if (isset($_POST['ip-address'])) {

                // Sanitize IP Address
                $ip_address = sanitize_text_field($_POST['ip-address']);

                // Delete IP
                $result = $wpdb->query($wpdb->prepare("DELETE FROM `" . DB::table('visitor') . "` WHERE `ip` = %s", $ip_address));

                if ($result) {
                    echo sprintf(__('Successfully deleted %s IP data.', 'wp-statistics'), '<code>' . esc_attr($ip_address) . '</code>'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                } else {
                    esc_html_e('Couldn’t find any IP address data to delete.', 'wp-statistics');
                }
            } else {
                esc_html_e('Kindly select the items you want to work with.', 'wp-statistics');
            }
        } else {
            esc_html_e('Unauthorized access!', 'wp-statistics');
        }

        exit;
    }

    /**
     * Setup an AJAX action to delete user id data from visitors table.
     */
    public function delete_user_ids_action_callback()
    {
        global $wpdb;

        if (Helper::is_request('ajax') and User::Access('manage')) {

            // Check Refer Ajax
            check_ajax_referer('wp_rest', 'wps_nonce');

            // Delete user ids
            $result = $wpdb->query("UPDATE `" . DB::table('visitor') . "` SET `user_id` = 0");

            if ($result) {
                esc_html_e('Successfully deleted User ID data.', 'wp-statistics');
            } else {
                esc_html_e('Couldn’t find any user ID data to delete.', 'wp-statistics');
            }

        } else {
            esc_html_e('Unauthorized access!', 'wp-statistics');
        }

        exit;
    }

    /**
     * Setup an AJAX action to clear UAStrings data from visitors table.
     */
    public function clear_user_agent_strings_action_callback()
    {
        global $wpdb;

        if (Helper::is_request('ajax') and User::Access('manage')) {

            // Check Refer Ajax
            check_ajax_referer('wp_rest', 'wps_nonce');

            // Delete UAStrings
            $result = $wpdb->query("UPDATE `" . DB::table('visitor') . "` SET `UAString` = NULL");

            if ($result) {
                esc_html_e('Successfully deleted user agent strings data.', 'wp-statistics');
            } else {
                esc_html_e('Couldn’t find any user agent strings data to delete.', 'wp-statistics');
            }

        } else {
            esc_html_e('Unauthorized access!', 'wp-statistics');
        }

        exit;
    }

    /**
     * Setup an AJAX action to clean up query parameters from pages table.
     */
    public function query_params_cleanup_action_callback()
    {
        global $wpdb;

        if (Helper::is_request('ajax') and User::Access('manage')) {

            // Check Refer Ajax
            check_ajax_referer('wp_rest', 'wps_nonce');

            // Get allowed query params
            $allowedQueryParams = Helper::get_query_params_allow_list();

            // Get all rows from pages table
            $pages = $wpdb->get_results("SELECT * FROM `" . DB::table('pages') . "`");
            if ($pages) {
                // Update query strings based on allow list
                foreach ($pages as $page) {
                    $wpdb->update(
                        DB::table('pages'),
                        ['uri' => Helper::FilterQueryStringUrl($page->uri, $allowedQueryParams)],
                        ['page_id' => $page->page_id]
                    );
                }

                _e('Successfully removed query string parameter data from \'pages\' table. <br>', 'wp-statistics');
            } else {
                _e('Couldn\'t find any user query string parameter data to delete from \'pages\' table. <br>', 'wp-statistics');
            }


            // Get all rows from visitors table
            $referrers = $wpdb->get_results("SELECT * FROM " . DB::table('visitor'));
            if ($referrers) {
                // Update query strings based on allow list
                foreach ($referrers as $referrer) {
                    $wpdb->update(
                        DB::table('visitor'),
                        ['referred' => Helper::FilterQueryStringUrl($referrer->referred, $allowedQueryParams)],
                        ['ID' => $referrer->ID]
                    );
                }

                esc_html_e('Successfully removed query string parameter data from \'visitor\' table.', 'wp-statistics');
            } else {
                esc_html_e('Couldn\'t find any user query string parameter data to delete from \'visitor\' table.', 'wp-statistics');
            }

        } else {
            esc_html_e('Unauthorized access!', 'wp-statistics');
        }

        exit;
    }

    /**
     * Setup an AJAX action to purge old data in the optimization page.
     */
    public function purge_data_action_callback()
    {

        if (Helper::is_request('ajax') and User::Access('manage')) {

            // Check Refer Ajax
            check_ajax_referer('wp_rest', 'wps_nonce');

            // Check Number Day
            $purge_days = 0;
            if (isset($_POST['purge-days'])) {
                $purge_days = intval(sanitize_text_field($_POST['purge-days']));
            }

            echo Purge::purge_data($purge_days); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        } else {
            esc_html_e('Unauthorized access!', 'wp-statistics');
        }

        exit;
    }

    /**
     * Setup an AJAX action to purge visitors with more than a defined number of hits.
     */
    public function purge_visitor_hits_action_callback()
    {

        if (Helper::is_request('ajax') and User::Access('manage')) {

            // Check Refer Ajax
            check_ajax_referer('wp_rest', 'wps_nonce');

            // Check Number Day
            $purge_hits = 10;
            if (isset($_POST['purge-hits'])) {
                $purge_hits = intval(sanitize_text_field($_POST['purge-hits']));
            }

            if ($purge_hits < 10) {
                esc_html_e('View count must be 10 or more!', 'wp-statistics');
            } else {
                echo Purge::purge_visitor_hits($purge_hits); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            }
        } else {
            esc_html_e('Unauthorized access!', 'wp-statistics');
        }

        exit;
    }

    /**
     * Show Page Visitors Filter
     */
    public function visitors_page_filters_action_callback()
    {

        if (Helper::is_request('ajax') and isset($_REQUEST['page'])) {

            // Run only Visitors Page
            if ($_REQUEST['page'] != "visitors") {
                exit;
            }

            // Check Refer Ajax
            check_ajax_referer('wp_rest', 'wps_nonce');

            // Create Output object
            $filter = array();

            // Browsers
            $filter['browsers'] = array();
            $browsers           = UserAgent::BrowserList();
            foreach ($browsers as $key => $se) {
                $filter['browsers'][$se] = $se;
            }

            // Location
            $filter['location'] = array();
            $country_list       = Country::getList();
            foreach ($country_list as $key => $name) {
                $filter['location'][$key] = $name;
            }

            // Push First "000" Unknown to End of List
            $first_key = key($filter['location']);
            $first_val = $filter['location'][$first_key];
            unset($filter['location'][$first_key]);
            $filter['location'][$first_key] = $first_val;

            // Platforms
            $filter['platform'] = array();
            $platforms_list     = UserAgent::getPlatformsList();

            foreach ($platforms_list as $platform) {
                $filter['platform'][$platform] = $platform;
            }

            // Referrer
            $filter['referrer'] = array();
            $referrer_list      = Referred::getList(array('min' => 50, 'limit' => 300));
            foreach ($referrer_list as $site) {
                $filter['referrer'][$site->domain] = $site->domain;
            }

            // User
            $filter['users'] = array();
            $user_list       = Visitor::get_users_visitor();
            foreach ($user_list as $user_id => $user_inf) {
                $filter['users'][$user_id] = $user_inf['user_login'] . " #" . $user_id . "";
            }

            // Send Json
            wp_send_json($filter);
        }
        exit;
    }

    /**
     * Setup an AJAX action to update geoIP database.
     */
    public function update_geoip_database_action_callback()
    {

        if (Helper::is_request('ajax') and User::Access('manage')) {
            // Check Refer Ajax
            check_ajax_referer('wp_rest', 'wps_nonce');

            $result = GeoIP::download('update');

            if ($result) {
                esc_html_e($result['notice']);
            }

        } else {
            esc_html_e('Unauthorized access!', 'wp-statistics');
        }

        exit;
    }

    /**
     * Setup Admin Meta box render ajax
     */
    public function admin_meta_box_action_callback()
    {
        if (Helper::is_request('ajax') and User::Access('read')) {

            // Check Refer Ajax
            check_ajax_referer('wp_rest', 'wps_nonce');

            $metaboxName = sanitize_text_field($_GET['name']);

            // Check Exist MetaBox Name
            if (in_array($metaboxName, array_keys(Meta_Box::getList())) and Meta_Box::metaBoxClassExist($metaboxName)) {

                $parameters = [];
                foreach ($_GET as $key => $value) {
                    if ($value && !in_array($key, ['action', 'wps_nonce', '_'])) {
                        $parameters[$key] = sanitize_text_field($value);
                    }
                }

                $class = Meta_Box::getMetaBoxClass($metaboxName);

                wp_send_json($class::get($parameters));

            } else {
                wp_send_json(array('code' => 'not_found_meta_box', 'message' => __('Invalid MetaBox Name in Request.', 'wp-statistics')), 400);
            }
        }

        exit;
    }

    /**
     * Get page filter items
     */
    public function get_page_filter_items_action_callback()
    {
        if (Helper::is_request('ajax') and User::Access('read')) {

            check_ajax_referer('wp_rest', 'wps_nonce');

            $paged          = Request::get('paged', 1, 'number');
            $postType       = Request::get('post_type', array_values(Helper::get_list_post_type()));
            $authorId       = Request::get('author_id', '', 'number');
            $search         = Request::get('search', '');
            $page           = Request::get('page');
            $selectedPost   = Request::get('post_id', false, 'number');

            if (!$page) {
                wp_send_json([
                    'code'      => 'not_found_page', 
                    'message'   => esc_html__('Invalid Page in Request.', 'wp-statistics')
                ], 400);
            }

            $query = new \WP_Query([
                'post_status'    => 'publish', 
                'posts_per_page' => 10,
                'paged'          => $paged,
                'post_type'      => $postType,
                'author'         => $authorId,
                's'              => $search
            ]);

            $posts = [];
            if ($query->have_posts()) {
                if ($paged == 1 && empty($search)) {
                    $allOption = [
                        'id'    => Menus::admin_url($page),
                        'text'  => esc_html__('All', 'wp-statistics')
                    ];

                    if (!$selectedPost) {
                        $allOption['selected'] = true;
                    }

                    $posts[] = $allOption;
                }

                while ($query->have_posts()) {
                    $query->the_post();

                    $option = [
                        'id'   => add_query_arg(['pid' => get_the_ID()], Menus::admin_url($page)),
                        'text' => get_the_title()
                    ];

                    if ($selectedPost == get_the_ID()) {
                        $option['selected'] = true;
                    }

                    $posts[] = $option;
                }
            }

            wp_send_json([
                'results'       => $posts,
                'pagination'    => [
                    'more' => $query->max_num_pages > $paged ? true : false
                ]
            ]);
        }

        exit;
    }

    public function search_visitors_action_callback()
    {
        if (Helper::is_request('ajax') and User::Access('read')) {

            check_ajax_referer('wp_rest', 'wps_nonce');

            $results = [];
            $search  = Request::get('search', '');

            $visitorsModel  = new VisitorsModel();
            $visitors       = $visitorsModel->searchVisitors([
                'ip'          => $search,
                'username'    => $search,
                'email'       => $search
            ]);

            foreach ($visitors as $visitor) {
                $option = [
                    'id'   => Menus::admin_url('visitors', ['type' => 'single-visitor', 'visitor_id' => $visitor->ID]),
                    'text' => sprintf(esc_html__('Visitor (#%s)', 'wp-statistics'), $visitor->ID)
                ];
    
                $results[] = $option;
            }

            wp_send_json(['results' => $results]);
        }

        exit;
    }

    public function store_date_range_action_callback()
    {
        if (Helper::is_request('ajax')) {
            check_ajax_referer('wp_rest', 'wps_nonce');

            $date = Request::get('date', [], 'array');
            DateRange::store($date);
            
            wp_send_json_success(['message' => esc_html__('Date range has been stored successfully.', 'wp-statistics')]);

        }

        exit;
    }
}

new Ajax;
