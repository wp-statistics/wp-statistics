<?php

namespace WP_STATISTICS;

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
                'action' => 'close_notice',
                'public' => false
            ],
            [
                'class'  => $this, 
                'action' => 'close_overview_ads',
                'public' => false
            ],
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
                'action' => 'empty_table',
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
     * Setup an AJAX action to close the notice on the overview page.
     */
    public function close_notice_action_callback()
    {

        if (Helper::is_request('ajax') and User::Access('manage') and isset($_REQUEST['notice'])) {

            // Check Refer Ajax
            check_ajax_referer('wp_rest', 'wps_nonce');

            // Check Type Of Notice
            switch ($_REQUEST['notice']) {
                case 'donate':
                    Option::update('disable_donation_nag', true);
                    break;

                case 'suggestion':
                    Option::update('disable_suggestion_nag', true);
                    break;

                case 'disable_all_addons':
                    update_option('wp_statistics_disable_addons_notice', 'yes');
                    break;

                case 'disable_cleanup_db':
                    Option::update('disable_db_cleanup_notice', true);
                    break;

                case 'disable_php_version_check':
                    Option::update('disable_php_version_check_notice', true);
                    break;
            }

            Option::update('admin_notices', false);
        }

        wp_die();
    }

    /**
     * Close Overview Ads
     */
    public function close_overview_ads_action_callback()
    {

        if (Helper::is_request('ajax') and isset($_REQUEST['ads_id'])) {

            // Check Security Nonce
            check_ajax_referer('wp_rest', 'wps_nonce');

            // Update Option
            $get_opt         = get_option('wp_statistics_overview_page_ads');
            $get_opt['view'] = sanitize_text_field($_REQUEST['ads_id']);
            update_option('wp_statistics_overview_page_ads', $get_opt, 'no');
        }
        exit;
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
     * Setup an AJAX action to empty a table in the optimization page.
     */
    public function empty_table_action_callback()
    {

        // Check Ajax Request
        if (!Helper::is_request('ajax')) {
            exit;
        }

        //Check isset Table-post
        if (!isset($_POST['table-name'])) {
            esc_html_e('Kindly select the items you want to work with.', 'wp-statistics');
            exit;
        }

        // Check Refer Ajax
        check_ajax_referer('wp_rest', 'wps_nonce');

        //Check Valid Table name
        $table_name    = sanitize_text_field($_POST['table-name']);
        $list_db_table = DB::table('all', 'historical');

        if (!array_key_exists($table_name, $list_db_table) and $table_name != 'all') {
            esc_html_e('Unauthorized access!', 'wp-statistics');
            exit;
        }

        if (User::Access('manage')) {

            if ($table_name == "all") {
                $x_tbl = 1;
                foreach ($list_db_table as $tbl_key => $tbl_name) {
                    echo ($x_tbl > 1 ? '<br>' : '') . DB::EmptyTable($tbl_name); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                    $x_tbl++;
                }
            } else {
                echo DB::EmptyTable(DB::table($table_name)); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
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
                $filter['browsers'][$key] = $se;
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
            $platforms_list     = RestAPI::request(array('route' => 'metabox', 'params' => array('name' => 'platforms', 'number' => 15, 'order' => 'DESC')));

            for ($x = 0; $x < count($platforms_list['platform_name']); $x++) {
                $filter['platform'][$platforms_list['platform_name'][$x]] = $platforms_list['platform_name'][$x];
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

            // Sanitize GeoIP Name
            $geoip_name = sanitize_text_field($_POST['update_action']);

            // When GeoIP is enabled, then user can update the GeoIP database
            if ($geoip_name == "country" && Option::get("geoip") !== 'on') {
                esc_html_e('Please first enable GeoIP Collection and save settings!', 'wp-statistics');
            } elseif ($geoip_name == "city" && Option::get("geoip_city") !== 'on') {
                esc_html_e('Please first enable GeoIP City and save settings!', 'wp-statistics');
            }

            $result = GeoIP::download($geoip_name, "update");

            if ($result) {
                esc_html_e($result["notice"]);
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
}

new Ajax;
