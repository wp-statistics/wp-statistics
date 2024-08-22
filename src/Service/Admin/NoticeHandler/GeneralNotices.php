<?php

namespace WP_Statistics\Service\Admin\NoticeHandler;

use WP_STATISTICS\DB;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Menus;
use WP_STATISTICS\Option;
use WP_STATISTICS\Schedule;
use WP_STATISTICS\User;

class GeneralNotices
{
    /**
     * List Of Admin Notice
     *
     * @var array
     */
    private $core_notices = array(
        'check_tracking_mode',
        'performance_and_clean_up',
        'memory_limit_check',
        'php_version_check',
        'email_report_schedule',
    );

    public function init()
    {
        $this->core_notices = apply_filters('wp_statistics_admin_notices', $this->core_notices);

        if (is_admin() && !Helper::is_request('ajax') && !Option::get('hide_notices') && User::Access('manage')) {
            foreach ($this->core_notices as $notice) {
                if (method_exists($this, $notice)) {
                    call_user_func([$this, $notice]);
                }
            }
        }
    }

    private function check_tracking_mode()
    {
        if (Menus::in_plugin_page()) {
            $trackingMode = Option::get('use_cache_plugin');

            if (!$trackingMode) {
                $settingsUrl = Menus::admin_url('settings');
                $noticeText  = sprintf('<b>WP Statistics Notice:</b> Server Side Tracking is less accurate and will be deprecated in <b>version 15</b>. Please switch to Client Side Tracking for better accuracy. <a href="%s">Update Tracking Settings</a>.', $settingsUrl);
                Notice::addNotice($noticeText, 'deprecate_server_side_tracking', 'warning');
            }
        }
    }

    private function performance_and_clean_up()
    {
        if (Menus::in_plugin_page()) {
            $totalDbRows = DB::getTableRows();
            $totalRows   = array_sum(array_column($totalDbRows, 'rows'));

            if ($totalRows > apply_filters('wp_statistics_notice_db_row_threshold', 500000)) {
                $settingsUrl      = admin_url('admin.php?page=wps_settings_page&tab=maintenance-settings');
                $optimizationUrl  = admin_url('admin.php?page=wps_optimization_page');
                $documentationUrl = 'https://wp-statistics.com/resources/optimizing-database-size-for-improved-performance/';

                $message = sprintf(
                    __('<b>WP Statistics Notice (Database Maintenance Recommended):</b> Your database has accumulated many records, which could slow down your site. To improve performance, go to <a href="%1$s">Settings → Data Management</a> to enable the option that stops recording old visitor data, and visit the <a href="%2$s">Optimization page</a> to clean up your database. This process only removes detailed old visitor logs but retains aggregated data. Your other data and overall statistics will remain unchanged. For more details, <a href="%3$s" target="_blank">click here</a>.', 'wp-statistics'),
                    esc_url($settingsUrl),
                    esc_url($optimizationUrl),
                    esc_url($documentationUrl)
                );

                Notice::addNotice($message, 'performance_and_clean_up', 'warning');
            }
        }
    }

    public function memory_limit_check()
    {
        if (Menus::in_plugin_page()) {
            if (Helper::checkMemoryLimit()) {
                Notice::addNotice(__('Your server memory limit is too low. Please contact your hosting provider to increase the memory limit.', 'wp-statistics'), 'memory_limit_check', 'warning');
            }
        }
    }

    public function php_version_check()
    {
        if (version_compare(PHP_VERSION, '7.2', '<')) {
            Notice::addNotice(__('<b>WP Statistics Important Notice:</b> Final Version for Your Current PHP Version <b>14.11</b> of WP Statistics will be the <b>last</b> update compatible with your current PHP version. To continue receiving the latest features and security updates, please upgrade your PHP to version 7.2 or higher.'), 'php_version_check', 'warning');
        }
    }

    public function email_report_schedule()
    {
        if (wp_next_scheduled('wp_statistics_report_hook') && Option::get('time_report') != '0') {
            $timeReports       = Option::get('time_report');
            $schedulesInterval = Schedule::getSchedules();

            if (!isset($schedulesInterval[$timeReports])) {
                Notice::addNotice(sprintf(
                    __('Please update your email report schedule due to new changes in our latest release: <a href="%1$s">Update Settings</a>.', 'wp-statistics'),
                    Menus::admin_url('settings', array('tab' => 'notifications-settings'))
                ), 'email_report_schedule', 'warning');
            }
        }
    }
}
