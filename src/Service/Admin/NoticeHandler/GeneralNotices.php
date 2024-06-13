<?php

namespace WP_Statistics\Service\Admin\NoticeHandler;

use WP_STATISTICS\Api\v2\Hit;
use WP_STATISTICS\DB;
use WP_STATISTICS\GeoIP;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Hits;
use WP_STATISTICS\Menus;
use WP_STATISTICS\Option;
use WP_STATISTICS\RestAPI;
use WP_STATISTICS\User;

class GeneralNotices
{
    /**
     * List Of Admin Notice
     *
     * @var array
     */
    private $core_notices = array(
        'use_cache_plugin',
        'active_geo_ip',
        'donate_plugin',
        'active_collation',
        'performance_and_clean_up',
        'memory_limit_check',
        'php_version_check',
    );

    public function init()
    {
        $this->core_notices = apply_filters('wp_statistics_admin_notices', $this->core_notices);

        if (is_admin() && !Helper::is_request('ajax') && !Option::get('hide_notices')) {
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
            Notice::addNotice($text . ", " . sprintf(__('Enable %1$sCache Compatibility%2$s to Correct This or Dismiss if Counts Are Accurate. Check out <a href=\"%3$s\" target=\"_blank\">this article</a> to disable this notice permanently.', 'wp-statistics'), '<a href="' . Menus::admin_url('settings') . '">', '</a>', 'https://wp-statistics.com/resources/how-to-disable-cache-notice-in-admin'), 'use_cache_plugin', 'warning');
        }
    }

    private function active_geo_ip()
    {
        if (Menus::in_plugin_page() and !Option::get('geoip') and GeoIp::IsSupport() and User::Access('manage')) {
            Notice::addNotice(sprintf(__('GeoIP collection is not enabled. Please go to <a href="%s">setting page</a> to enable GeoIP for getting more information and location (country) from the visitor.', 'wp-statistics'), Menus::admin_url('settings', array('tab' => 'externals-settings'))), 'active_geo_ip', 'warning');
        }
    }

    private function donate_plugin()
    {
        if (Menus::in_page('overview')) {
            Notice::addNotice(__('Have you thought about donating to WP Statistics?', 'wp-statistics') . ' <a href="https://wp-statistics.com/donate/" target="_blank">' . __('Donate Now!', 'wp-statistics') . '</a>', 'donate_plugin');
        }
    }

    private function active_collation()
    {
        if (Menus::in_plugin_page() and User::Access('manage')) {

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
                Notice::addNotice(sprintf(__('Certain features are currently turned off. Please visit the %1$ssettings page%2$s to activate them: %3$s', 'wp-statistics'), '<a href="' . Menus::admin_url('settings') . '">', '</a>', implode(__(',', 'wp-statistics'), $active_collation)), 'active_collation');
            }
        }
    }

    private function performance_and_clean_up()
    {
        $totalDbRows = DB::getTableRows();
        $totalRows   = array_sum(array_column($totalDbRows, 'rows'));

        if ($totalRows > apply_filters('wp_statistics_notice_db_row_threshold', 300000)) {
            $settingsUrl      = admin_url('admin.php?page=wps_settings_page&tab=maintenance-settings');
            $optimizationUrl  = admin_url('admin.php?page=wps_optimization_page');
            $documentationUrl = 'https://wp-statistics.com/resources/optimizing-database-size-for-improved-performance/';

            $message = sprintf(
                __('<b>Notice: Database Maintenance Recommended:</b> Your database has accumulated many records, which could slow down your site. To improve performance, go to <a href="%1$s">Settings → Data Management</a> to enable the option that stops recording old visitor data, and visit the <a href="%2$s">Optimization page</a> to clean up your database. This process only removes detailed old visitor logs but retains aggregated data. Your other data and overall statistics will remain unchanged. For more details, <a href="%3$s" target="_blank">click here</a>.', 'wp-statistics'),
                esc_url($settingsUrl),
                esc_url($optimizationUrl),
                esc_url($documentationUrl)
            );

            Notice::addNotice($message, 'performance_and_clean_up', 'warning');
        }
    }

    public function memory_limit_check()
    {
        if (Menus::in_page('optimization') and User::Access('manage')) {
            if (Helper::checkMemoryLimit()) {
                Notice::addNotice(__('Your server memory limit is too low. Please contact your hosting provider to increase the memory limit.', 'wp-statistics'), 'memory_limit_check', 'warning');
            }
        }
    }

    public function php_version_check()
    {
        if (version_compare(PHP_VERSION, '7.2', '<') && !Option::get('disable_php_version_check_notice')) {
            Notice::addNotice(__('<b>WP Statistics Plugin: PHP Version Update Alert</b> Starting with <b>Version 15</b>, WP Statistics will require <b>PHP 7.2 or higher</b>. Please upgrade your PHP version to ensure uninterrupted use of the plugin.'), 'php_version_check', 'warning');
        }
    }
}
