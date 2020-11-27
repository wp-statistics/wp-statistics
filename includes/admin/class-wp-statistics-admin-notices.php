<?php

namespace WP_STATISTICS;

class Admin_Notices
{
    /**
     * List Of Admin Notice
     *
     * @var array
     */
    private static $core_notices = array(
        'use_cache_plugin',
        'enable_rest_api',
        'active_geo_ip',
        'donate_plugin',
        'active_collation',
        'disable_addons'
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
        if (is_admin() and !Helper::is_request('ajax')) {
            $list_notice = self::$core_notices;
            foreach ($list_notice as $notice) {
                self::{$notice}();
            }
        }
    }

    public function use_cache_plugin()
    {
        $plugin = Helper::is_active_cache_plugin();
        if (!Option::get('use_cache_plugin') and $plugin['status'] === true) {
            $text = ($plugin['plugin'] == "core" ? __('WP_CACHE is enable in your WordPress', 'wp-statistics') : sprintf(__('You are using %s plugin in WordPress', 'wp-statistics'), $plugin['plugin']));
            Helper::wp_admin_notice($text . ", " . sprintf(__('Please enable %1$sCache Setting%2$s in WP Statistics.', 'wp-statistics'), '<a href="' . Menus::admin_url('settings') . '">', '</a>'), 'warning', true);
        }
    }

    public function enable_rest_api()
    {

        if (Option::get('use_cache_plugin') and false === ($check_rest_api = get_transient('check-wp-statistics-rest'))) {

            // Check Connect To WordPress Rest API
            $status = true;
            $request = wp_remote_get(get_rest_url(null, RestAPI::$namespace . '/enable'), array('body' => array('connect' => 'wp-statistics'), 'timeout' => 30));
            if (is_wp_error($request)) {
                $status = false;
            }
            $body = wp_remote_retrieve_body($request);
            $data = json_decode($body, true);
            if (isset($data['error'])) {
                $status = false;
            }

            if ($status === true) {
                set_transient('check-wp-statistics-rest', array("status" => "enable"), 3 * HOUR_IN_SECONDS);
            } else {
                Helper::wp_admin_notice(sprintf(__('Here is an error associated with Connecting WordPress Rest API, Please Flushing rewrite rules or activate wp rest api for performance WP-Statistics Plugin Cache / Go %1$sSettings->Permalinks%2$s', 'wp-statistics'), '<a href="' . esc_url(admin_url('options-permalink.php')) . '">', '</a>'), 'warning', true);
            }
        }

    }

    public function active_geo_ip()
    {
        if (Menus::in_plugin_page() and !Option::get('geoip') and GeoIp::IsSupport() and User::Access('manage') and !Option::get('hide_notices')) {
            Helper::wp_admin_notice(sprintf(__('GeoIP collection is not enabled. Please go to <a href="%s">setting page</a> to enable GeoIP for getting more information and location (country) from the visitor.', 'wp-statistics'), Menus::admin_url('settings', array('tab' => 'externals-settings'))), 'warning', true);
        }
    }

    public function donate_plugin()
    {
        if (Menus::in_page('overview') and !Option::get('disable_donation_nag', false)) {
            Helper::wp_admin_notice(__('Have you thought about donating to WP Statistics?', 'wp-statistics') . ' <a href="http://wp-statistics.com/donate/" target="_blank">' . __('Donate Now!', 'wp-statistics') . '</a>', 'warning', true, 'wps-donate-notice');
        }
    }

    public function active_collation()
    {
        if (Menus::in_plugin_page() and User::Access('manage') and !Option::get('hide_notices')) {

            // Create Default Active List item
            $active_collation = array();

            // Check Active User Online
            if (!Option::get('useronline')) {
                $active_collation[] = __('online user tracking', 'wp-statistics');
            }

            // Check Active visits
            if (!Option::get('visits')) {
                $active_collation[] = __('hit tracking', 'wp-statistics');
            }

            // Check Active Visitors
            if (!Option::get('visitors')) {
                $active_collation[] = __('visitor tracking', 'wp-statistics');
            }

            if (count($active_collation) > 0) {
                Helper::wp_admin_notice(sprintf(__('The following features are disabled, please go to %ssettings page%s and enable them: %s', 'wp-statistics'), '<a href="' . Menus::admin_url('settings') . '">', '</a>', implode(__(',', 'wp-statistics'), $active_collation)), 'info', true);
            }
        }
    }

    public function disable_addons()
    {
        $option = get_option('wp_statistics_disable_addons_notice');
        if (!empty($option) and $option == "no") {
            Helper::wp_admin_notice(__("Your WP-Statistics's Add-On(s) are not compatible with the new version of WP-Statistics and disabled automatically, please try to update them.", "wp-statistics"), "info", true, "wp-statistics-disable-all-addons-admin-notice");
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
                                'wps_nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
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