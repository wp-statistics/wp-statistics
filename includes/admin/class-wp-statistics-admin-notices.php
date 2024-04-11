<?php

namespace WP_STATISTICS;

class Admin_Notices
{
    /**
     * List Of Admin Notice
     *
     * @var array
     */
    private $core_notices = array(
        'use_cache_plugin',
        'enable_rest_api',
        'active_geo_ip',
        'donate_plugin',
        'active_collation',
        'disable_addons',
        'performance_and_clean_up',
        'memory_limit_check',
        'php_version_check',
    );

    /**
     * Admin Notice constructor.
     */
    public function __construct()
    {
        add_action('admin_notices', array($this, "setup"), 20, 2);
    }

    public function setup()
    {
        $this->core_notices = apply_filters('wp_statistics_admin_notices', $this->core_notices);

        if (is_admin() && !Helper::is_request('ajax')) {
            foreach ($this->core_notices as $notice) {
                if (method_exists($this, $notice)) {
                    call_user_func([$this, $notice]);
                }
            }
        }
    }

    private function use_cache_plugin()
    {
        $plugin = Helper::is_active_cache_plugin();
        if (!Option::get('use_cache_plugin') and $plugin['status'] === true) {
            $text = ($plugin['plugin'] == "core" ? __('WP Statistics might not count the stats since <code>WP_CACHE</code> is detected in <code>wp-config.php</code>', 'wp-statistics') : sprintf(__('Potential Inaccuracy Due to <b>%s</b> Plugin', 'wp-statistics'), $plugin['plugin']));
            Helper::wp_admin_notice($text . ", " . sprintf(__('Enable %1$sCache Compatibility%2$s to Correct This or Dismiss if Counts Are Accurate. Check out <a href=\“%3$s\” target=\“_blank\“>this article</a> to disable this notice permanently.', 'wp-statistics'), '<a href="' . Menus::admin_url('settings') . '">', '</a>', 'https://wp-statistics.com/resources/how-to-disable-cache-notice-in-admin/'), 'warning', true);
        }
    }

    private function enable_rest_api()
    {
        if (isset($_GET['page']) and $_GET['page'] === 'wps_overview_page' and Option::get('use_cache_plugin') and false === ($check_rest_api = get_transient('wps_check_rest_api'))) {

            // Check Connect To WordPress Rest API
            $status  = false;
            $message = '';

            $params = array_merge(array(
                '_'                  => time(),
                Hits::$rest_hits_key => 'yes',
            ), Helper::getHitsDefaultParams());

            $requestUrl = add_query_arg($params, get_rest_url(null, RestAPI::$namespace . '/' . Api\v2\Hit::$endpoint));
            $request    = wp_remote_get($requestUrl, array('timeout' => 30, 'sslverify' => false));

            if (is_wp_error($request)) {
                $status  = false;
                $message = $request->get_error_message();
            } else {
                $body = wp_remote_retrieve_body($request);
                $data = json_decode($body, true);
                if (isset($data['status']) && $data['status'] == true) {
                    $status = true;
                }
            }

            if ($status === true) {
                set_transient('wps_check_rest_api', array("status" => "enable"), 3 * HOUR_IN_SECONDS);
            } else {
                $error_msg = __('<b>Warning:</b> WP REST API Connection Error Detected.', 'wp-statistics') . ' ';
                if (!empty($message)) {
                    $error_msg .= '<br />' . $message . '<br />';
                }
                $error_msg .= sprintf(__('Flush Rewrite Rules by Updating Permalink in %1$sSettings → Permalinks%2$s and Verify WP REST API is Enabled.', 'wp-statistics'), '<a href="' . esc_url(admin_url('options-permalink.php')) . '">', '</a>');
                Helper::wp_admin_notice($error_msg, 'warning', true);
            }
        }

    }

    private function active_geo_ip()
    {
        if (Menus::in_plugin_page() and !Option::get('geoip') and GeoIp::IsSupport() and User::Access('manage') and !Option::get('hide_notices')) {
            Helper::wp_admin_notice(sprintf(__('GeoIP collection is not enabled. Please go to <a href="%s">setting page</a> to enable GeoIP for getting more information and location (country) from the visitor.', 'wp-statistics'), Menus::admin_url('settings', array('tab' => 'externals-settings'))), 'warning', true);
        }
    }

    private function donate_plugin()
    {
        if (Menus::in_page('overview') and !Option::get('disable_donation_nag', false)) {
            Helper::wp_admin_notice(__('Have you thought about donating to WP Statistics?', 'wp-statistics') . ' <a href="https://wp-statistics.com/donate/" target="_blank">' . __('Donate Now!', 'wp-statistics') . '</a>', 'warning', true, 'wps-donate-notice');
        }
    }

    private function active_collation()
    {
        if (Menus::in_plugin_page() and User::Access('manage') and !Option::get('hide_notices')) {

            // Create Default Active List item
            $active_collation = array();

            // Check Active User Online
            if (!Option::get('useronline')) {
                $active_collation[] = __('Display Online Users', 'wp-statistics');
            }

            // Check Active visits
            if (!Option::get('visits')) {
                $active_collation[] = __('Track Page Views', 'wp-statistics');
            }

            // Check Active Visitors
            if (!Option::get('visitors')) {
                $active_collation[] = __('Monitor Unique Visitors', 'wp-statistics');
            }

            if (count($active_collation) > 0) {
                Helper::wp_admin_notice(sprintf(__('Certain features are currently turned off. Please visit the %1$ssettings page%2$s to activate them: %3$s', 'wp-statistics'), '<a href="' . Menus::admin_url('settings') . '">', '</a>', implode(__(',', 'wp-statistics'), $active_collation)), 'info', true);
            }
        }
    }

    private function disable_addons()
    {
        $option = get_option('wp_statistics_disable_addons_notice');
        if (!empty($option) and $option == "no") {
            Helper::wp_admin_notice(__('Your WP Statistics Add-On(s) need WP Statistics version 12.6.13 or higher. Please update your WP Statistics plugin.', 'wp-statistics'), 'info', true, 'wp-statistics-disable-all-addons-admin-notice');
            ?>
            <script>
                jQuery(document).ready(function ($) {
                    $(document).on("click", "#wp-statistics-disable-all-addons-admin-notice button.notice-dismiss", function (e) {
                        e.preventDefault();
                        jQuery.ajax({
                            url: ajaxurl,
                            type: "post",
                            data: {
                                'action': 'wp_statistics_close_notice',
                                'notice': 'disable_all_addons',
                                'wps_nonce': '<?php echo wp_create_nonce('wp_rest'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>'
                            },
                            datatype: 'json'
                        });
                    });
                });
            </script>
            <?php
        }
    }

    private function performance_and_clean_up()
    {
        $totalDbRows = DB::getTableRows();
        $totalRows   = array_sum(array_column($totalDbRows, 'rows'));

        if ($totalRows > apply_filters('wp_statistics_notice_db_row_threshold', 300000) and !Option::get('disable_db_cleanup_notice')) {
            $settingsUrl      = admin_url('admin.php?page=wps_settings_page&tab=maintenance-settings');
            $optimizationUrl  = admin_url('admin.php?page=wps_optimization_page');
            $documentationUrl = 'https://wp-statistics.com/resources/optimizing-database-size-for-improved-performance/';

            $message = sprintf(
                __('Attention: Your database has accumulated a significant number of records, which may impact your site\'s performance. To address this, consider visiting <a href="%1$s">Settings &gt; Data Management</a> where you can enable the option to prevent recording old data. You can also perform an immediate database clean-up on the <a href="%2$s">Optimization page</a>. For more information, <a href="%3$s" target="_blank">click here</a>.', 'wp-statistics'),
                esc_url($settingsUrl),
                esc_url($optimizationUrl),
                esc_url($documentationUrl)
            );

            Helper::wp_admin_notice($message, 'warning', true, 'wp-statistics-disable-cleanup-db-notice');
            ?>
            <script>
                jQuery(document).ready(function ($) {
                    $(document).on("click", "#wp-statistics-disable-cleanup-db-notice button.notice-dismiss", function (e) {
                        e.preventDefault();
                        jQuery.ajax({
                            url: ajaxurl,
                            type: "post",
                            data: {
                                'action': 'wp_statistics_close_notice',
                                'notice': 'disable_cleanup_db',
                                'wps_nonce': '<?php echo wp_create_nonce('wp_rest'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>'
                            },
                            datatype: 'json'
                        });
                    });
                });
            </script>
            <?php
        }
    }

    public function memory_limit_check()
    {
        if (Menus::in_page('optimization') and User::Access('manage') and !Option::get('hide_notices')) {
            if (Helper::checkMemoryLimit()) {
                Helper::wp_admin_notice(__('Your server memory limit is too low. Please contact your hosting provider to increase the memory limit.', 'wp-statistics'), 'warning', true);
            }
        }
    }

    public function php_version_check()
    {
        if (version_compare(PHP_VERSION, '7.2', '<') && !Option::get('disable_php_version_check_notice')) {
            Helper::wp_admin_notice(__('<b>WP Statistics Plugin: PHP Version Update Alert</b> Starting with <b>Version 15</b>, WP Statistics will require <b>PHP 7.2 or higher</b>. Please upgrade your PHP version to ensure uninterrupted use of the plugin.'), 'warning', true, 'wp-statistics-disable-php_version_check-notice');
            ?>
            <script>
                jQuery(document).ready(function ($) {
                    $(document).on("click", "#wp-statistics-disable-php_version_check-notice button.notice-dismiss", function (e) {
                        e.preventDefault();
                        jQuery.ajax({
                            url: ajaxurl,
                            type: "post",
                            data: {
                                'action': 'wp_statistics_close_notice',
                                'notice': 'disable_php_version_check',
                                'wps_nonce': '<?php echo wp_create_nonce('wp_rest'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>'
                            },
                            datatype: 'json'
                        });
                    });
                });
            </script>
            <?php
        }
    }
}

new Admin_Notices;