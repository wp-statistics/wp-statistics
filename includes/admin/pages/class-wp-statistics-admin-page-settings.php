<?php

namespace WP_STATISTICS;

use WP_Statistics\Components\AssetNameObfuscator;
use WP_Statistics\Components\Singleton;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;
use WP_Statistics\Service\Geolocation\GeolocationFactory;
use WP_Statistics\Utils\Request;

class settings_page extends Singleton
{

    private static $redirectAfterSave = true;

    public function __construct()
    {

        // Save Setting Action
        add_action('admin_init', array($this, 'save'));

        // Check Access Level
        if (Menus::in_page('settings') and !User::Access('manage')) {
            wp_die(__('You do not have sufficient permissions to access this page.')); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        }
    }

    /**
     * Show Setting Page Html
     */
    public static function view()
    {

        // Add Class inf
        $args['class'] = 'wp-statistics-settings';
        $args['title'] = __('Settings', 'wp-statistics');

        // Check User Access To Save Setting
        $args['wps_admin'] = false;
        if (User::Access('manage')) {
            $args['wps_admin'] = true;
        }
        if ($args['wps_admin'] === false) {
            $args['wps_admin'] = 0;
        }

        // Get Permalink Structure
        $args['permalink'] = get_option('permalink_structure');

        // Get List All Options
        $args['wp_statistics_options'] = Option::getOptions();

        // Load Template
        Admin_Template::get_template(array('layout/header', 'layout/title', 'settings', 'layout/footer'), $args);
    }

    /**
     * Save Setting
     */
    public function save()
    {

        // Check Form Nonce
        if (isset($_POST['wp-statistics-nonce']) and wp_verify_nonce($_POST['wp-statistics-nonce'], 'update-options')) {

            // Check Reset Option WP Statistics
            self::reset_wp_statistics_options();

            // Get All List Options
            $wp_statistics_options = Option::getOptions();

            // Run Update Option
            $method_list = array(
                'general',
                'visitor_ip',
                'access_level',
                'exclusion',
                'external',
                'maintenance',
                'notification',
                'privacy',
                'advanced'
            );
            foreach ($method_list as $method) {
                $wp_statistics_options = self::{'save_' . $method . '_option'}($wp_statistics_options);
            }

            $wp_statistics_options = apply_filters('wp_statistics_options', $wp_statistics_options);

            // Save Option
            Option::save_options($wp_statistics_options);

            // Save Addons Options
            if (!empty($_POST['wps_addon_settings']) && is_array($_POST['wps_addon_settings'])) {
                foreach ($_POST['wps_addon_settings'] as $addon_name => $addon_options) {
                    if (!empty($addon_options) && is_array($addon_options)) {
                        self::save_addons_options($addon_name, $addon_options);
                    }
                }
            }

            // Trigger Save Settings Action
            do_action('wp_statistics_save_settings');

            // Get tab name for redirect to the current tab
            $tab = isset($_POST['tab']) && $_POST['tab'] ? sanitize_text_field($_POST['tab']) : 'general-settings';

            // Update Referrer Spam
            if (isset($_POST['update-referrer-spam'])) {
                $status = Referred::download_referrer_spam();
                if (is_bool($status)) {
                    if ($status === false) {
                        Notice::addFlashNotice(__("Error Encountered While Updating Spam Referrer Blacklist.", "wp-statistics"), "error");
                    } else {
                        Notice::addFlashNotice(__("Spam Referrer Blacklist Successfully Updated.", "wp-statistics"), "success");
                    }
                    self::$redirectAfterSave = false;
                }
            }

            if (self::$redirectAfterSave) {
                // Redirect User To Save Setting
                wp_redirect(add_query_arg(array(
                    'save_setting' => 'yes',
                    'tab'          => $tab,
                ), Menus::admin_url('settings')));

                // die
                exit;
            }
        }

        // Save Setting
        if (isset($_GET['save_setting'])) {
            Notice::addFlashNotice(__("Settings Successfully Saved.", "wp-statistics"), "success");
        }

        // Reset Setting
        if (isset($_GET['reset_settings'])) {
            Notice::addFlashNotice(__("All Settings Have Been Reset to Default.", "wp-statistics"), "success");
        }
    }

    /**
     * Convert input name to Option
     *
     * @param $name
     * @return mixed
     */
    public static function input_name_to_option($name)
    {
        return str_replace("wps_", "", $name);
    }

    /**
     * Save Privacy Option
     *
     * @param $wp_statistics_options
     * @return mixed
     */
    public static function save_privacy_option($wp_statistics_options)
    {
        $wps_option_list = array(
            'wps_anonymize_ips',
            'wps_hash_ips',
            'wps_privacy_audit',
            'wps_store_ua',
            'wps_consent_level_integration',
            'wps_anonymous_tracking',
            'wps_do_not_track',
        );

        // If the IP hash's are enabled, disable storing the complete user agent.
        if (array_key_exists('wps_hash_ips', $_POST)) {
            $_POST['wps_store_ua'] = '';
        }

        foreach ($wps_option_list as $option) {
            $wp_statistics_options[self::input_name_to_option($option)] = (isset($_POST[$option]) ? $_POST[$option] : '');
        }

        return $wp_statistics_options;
    }

    /**
     * Save Notification
     *
     * @param $wp_statistics_options
     * @return mixed
     */
    public static function save_notification_option($wp_statistics_options)
    {

        if (isset($_POST['wps_time_report'])) {
            Schedule::rescheduleEvent('wp_statistics_report_hook', $_POST['wps_time_report'], Option::get('time_report'));
        }

        $wps_option_list = array(
            "wps_stats_report",
            "wps_time_report",
            "wps_send_report",
            "wps_content_report",
            "wps_email_free_content_header",
            "wps_email_free_content_footer",
            "wps_email_list",
            "wps_upgrade_report"
        );

        foreach ($wps_option_list as $option) {

            $value = '';

            if (isset($_POST[$option])) {
                if (in_array($option, ['wps_content_report', 'wps_email_free_content_header', 'wps_email_free_content_footer'])) {
                    $value = stripslashes(wp_kses_post($_POST[$option]));
                } else {
                    $value = stripslashes(sanitize_textarea_field($_POST[$option]));
                }
            }

            $wp_statistics_options[self::input_name_to_option($option)] = $value;
        }

        return $wp_statistics_options;
    }

    /**
     * Save maintenance Option
     *
     * @param $wp_statistics_options
     * @return mixed
     */
    public static function save_maintenance_option($wp_statistics_options)
    {
        $wps_option_list = array(
            'wps_schedule_dbmaint',
            'wps_schedule_dbmaint_days',
        );
        foreach ($wps_option_list as $option) {
            $wp_statistics_options[self::input_name_to_option($option)] = (isset($_POST[$option]) ? sanitize_text_field($_POST[$option]) : '');
        }

        return $wp_statistics_options;
    }

    /**
     * Save External Option
     *
     * @param $wp_statistics_options
     * @return mixed
     */
    public static function save_external_option($wp_statistics_options)
    {

        $wps_option_list = array(
            'wps_geoip_license_type',
            'wps_geoip_location_detection_method',
            'wps_geoip_license_key',
            'wps_geoip_dbip_license_key_option',
            'wps_update_geoip',
            'wps_schedule_geoip',
            'wps_auto_pop',
            'wps_private_country_code',
            'wps_referrerspam',
            'wps_schedule_referrerspam',
            'wps_share_anonymous_data',
        );

        // For country codes we always use upper case, otherwise default to 000 which is 'unknown'.
        if (array_key_exists('wps_private_country_code', $_POST)) {
            $_POST['wps_private_country_code'] = trim(strtoupper(sanitize_text_field($_POST['wps_private_country_code'])));
        } else {
            $_POST['wps_private_country_code'] = GeolocationFactory::getProviderInstance()->getDefaultPrivateCountryCode();
        }

        if ($_POST['wps_private_country_code'] == '') {
            $_POST['wps_private_country_code'] = GeolocationFactory::getProviderInstance()->getDefaultPrivateCountryCode();
        }

        foreach ($wps_option_list as $option) {
            $wp_statistics_options[self::input_name_to_option($option)] = (isset($_POST[$option]) ? $_POST[$option] : '');
        }

        // Check Update Referrer Spam List
        if (isset($_POST['wps_referrerspam'])) {
            $status = Referred::download_referrer_spam();
            if (is_bool($status) and $status === false) {
                $wp_statistics_options['referrerspam'] = '';
            }
        }

        return $wp_statistics_options;
    }

    /**
     * Save Exclude Option
     *
     * @param $wp_statistics_options
     * @return mixed
     */
    public static function save_exclusion_option($wp_statistics_options)
    {

        // Save Exclude Role
        foreach (User::get_role_list() as $role) {
            $role_post                                                     = 'wps_exclude_' . str_replace(" ", "_", strtolower($role));
            $wp_statistics_options[self::input_name_to_option($role_post)] = (isset($_POST[$role_post]) ? $_POST[$role_post] : '');
        }

        // Save Exclusion
        $wps_option_list = array(
            'wps_record_exclusions',
            'wps_robotlist',
            'wps_query_params_allow_list',
            'wps_exclude_ip',
            'wps_exclude_loginpage',
            'wps_excluded_countries',
            'wps_included_countries',
            'wps_robot_threshold',
            'wps_exclude_feeds',
            'wps_excluded_urls',
            'wps_exclude_404s',
        );

        foreach ($wps_option_list as $option) {
            $wp_statistics_options[self::input_name_to_option($option)] = (isset($_POST[$option]) ? sanitize_textarea_field($_POST[$option]) : '');
        }

        return $wp_statistics_options;
    }

    /**
     * Save Access Level Option
     *
     * @param $wp_statistics_options
     * @return mixed
     */
    public static function save_access_level_option($wp_statistics_options)
    {
        $wps_option_list = array('wps_read_capability', 'wps_manage_capability');
        foreach ($wps_option_list as $option) {
            $capability = !empty($_POST[$option]) ? sanitize_text_field($_POST[$option]) : '';

            if (!User::checkUserCapability($capability)) {
                continue;
            }

            $wp_statistics_options[self::input_name_to_option($option)] = $capability;
        }

        return $wp_statistics_options;
    }

    /**
     * Save Visitor IP Option
     *
     * @param $wp_statistics_options
     * @return mixed
     */
    public static function save_visitor_ip_option($wp_statistics_options)
    {
        $ipMethod = IP::$default_ip_method;

        if (isset($_POST['ip_method']) and !empty($_POST['ip_method'])) {

            // Check Custom Header
            if ($_POST['ip_method'] == 'CUSTOM_HEADER') {
                if (trim($_POST['user_custom_header_ip_method']) != "") {
                    $ipMethod = sanitize_text_field($_POST['user_custom_header_ip_method']);

                    if (!isset($_SERVER[$ipMethod]) || empty($_SERVER[$ipMethod])) {
                        Notice::addFlashNotice(__('Custom header IP detection failed or the specified custom header is empty. Will switch to default detection method (Sequential IP Detection).', 'wp-statistics'), 'error');
                        $ipMethod = IP::$default_ip_method; // Switch to default method if custom header fails
                    }
                }
            } else {
                $ipMethod = sanitize_text_field($_POST['ip_method']);
            }
        }

        $wp_statistics_options['ip_method'] = $ipMethod;
        return $wp_statistics_options;
    }

    /**
     * Save General Options
     *
     * @param $wp_statistics_options
     * @return mixed
     */
    public static function save_general_option($wp_statistics_options)
    {
        $wps_option_list = array(
            'wps_useronline',
            'wps_visits',
            'wps_visitors',
            'wps_visitors_log',
            'wps_enable_user_column',
            'wps_bypass_ad_blockers',
            'wps_pages',
            'wps_use_cache_plugin',
            'wps_attribution_model',
            'wps_show_hits',
            'wps_display_hits_position',
            'wps_menu_bar',
            'wps_coefficient',
            'wps_hide_notices',
            'wps_charts_previous_period',
            'wps_display_notifications'
        );

        foreach ($wps_option_list as $option) {
            $optionValue                                                = isset($_POST[$option]) ? sanitize_text_field($_POST[$option]) : '';
            $wp_statistics_options[self::input_name_to_option($option)] = $optionValue;
        }

        // Save Views Column & View Chart Metabox
        foreach (array('wps_disable_column', 'wps_disable_editor', 'wps_disable_dashboard') as $option) {
            $wps_disable_column                                         = isset($_POST[$option]) && sanitize_text_field($_POST[$option]) == '1' ? '' : '1';
            $wp_statistics_options[self::input_name_to_option($option)] = $wps_disable_column;
        }

        //Flush Rewrite Use Cache Plugin
        if (isset($_POST['wps_use_cache_plugin'])) {
            flush_rewrite_rules();
        }

        /**
         * Remove old hash format assets if bypassed ad-blocker is disabled.
         *
         * @version 14.9.2
         */
        if (empty($_REQUEST['wps_bypass_ad_blockers']) && Option::get('bypass_ad_blockers')) {
            $assetNameObfuscator = new AssetNameObfuscator();
            $assetNameObfuscator->deleteAllHashedFiles();
            $assetNameObfuscator->deleteDatabaseOption();
        }

        return $wp_statistics_options;
    }

    /**
     * Save Addons Options
     *
     * @param $addon_name
     * @param $addon_options
     */
    public static function save_addons_options($addon_name, $addon_options)
    {
        $options = [];
        foreach ($addon_options as $option_name => $option_value) {
            if (in_array($option_name, ['wps_about_widget_content', 'email_content_header', 'email_content_footer'])) {
                $options[$option_name] = wp_kses_post($option_value);
            } else {
                if (is_array($option_value)) {
                    $options[$option_name] = array_map('sanitize_text_field', $option_value);
                } else {
                    $options[$option_name] = sanitize_text_field($option_value);
                }
            }

            // Update time_report option based on the value of email_stats_time_range option in Advanced Reporting
            if ($option_name === 'email_stats_time_range' && Request::compare('tab', 'advanced-reporting-settings')) {
                Schedule::rescheduleEvent('wp_statistics_report_hook', $options[$option_name], Option::get('time_report'));
                Option::update('time_report', $options[$option_name]);
            }
        }

        Option::saveByAddon($options, $addon_name);
    }

    /**
     * Reset WP Statistics Option
     */
    public static function reset_wp_statistics_options()
    {

        if (isset($_POST['wps_reset_plugin'])) {

            if (is_multisite()) {
                $sites = Helper::get_wp_sites_list();
                foreach ($sites as $blog_id) {
                    switch_to_blog($blog_id);
                    self::reset_option();
                    restore_current_blog();
                }
            } else {
                self::reset_option();
            }

            wp_redirect(add_query_arg(array('reset_settings' => 'yes'), Menus::admin_url('settings')));
            exit;
        }
    }

    /**
     * Reset WP Statistics Option
     */
    public static function reset_option()
    {
        global $wpdb;

        // Get Default Option
        $default_options = Option::defaultOption();

        // Delete the wp_statistics option.
        update_option(Option::$opt_name, array());

        // Delete the user options.
        $wpdb->query("DELETE FROM {$wpdb->prefix}usermeta WHERE meta_key LIKE 'wp_statistics%'");

        // Update Option
        update_option(Option::$opt_name, $default_options);
    }

    public static function save_advanced_option($wp_statistics_options)
    {
        $wps_option_list = [
            'wps_delete_data_on_uninstall'
        ];

        foreach ($wps_option_list as $option) {
            $wp_statistics_options[self::input_name_to_option($option)] = isset($_POST[$option]) ? true : false;
        }

        return $wp_statistics_options;
    }
}

settings_page::instance();