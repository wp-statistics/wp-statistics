<?php

namespace WP_Statistics\Service\Admin\NoticeHandler;

use WP_STATISTICS\DB;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Menus;
use WP_STATISTICS\Option;
use WP_STATISTICS\Schedule;
use WP_STATISTICS\User;
use WP_Statistics\Traits\TransientCacheTrait;

class GeneralNotices
{
    use TransientCacheTrait;

    /**
     * List Of Admin Notice
     *
     * @var array
     */
    private $coreNotices = [
        'checkTrackingMode',
        'performanceAndCleanUp',
        'memoryLimitCheck',
        'emailReportSchedule',
        'notifyDeprecatedHoneypotOption'
    ];

    /**
     * Initialize the notices.
     *
     * @return void
     */
    public function init()
    {
        $this->coreNotices = apply_filters('wp_statistics_admin_notices', $this->coreNotices);

        if ( ! is_admin() ) {
            return;
        }

        if (!Helper::is_request('ajax') && !Option::get('hide_notices') && User::Access('manage')) {
            foreach ($this->coreNotices as $notice) {
                if (method_exists($this, $notice)) {
                    call_user_func([$this, $notice]);
                }
            }
        }
    }

    /**
     * Notifies users about the deprecation of server-side tracking.
     *
     * @return void
     */
    private function checkTrackingMode()
    {
        if (Notice::isNoticeDismissed('deprecate_server_side_tracking')) {
            return;
        }

        if (!Menus::in_plugin_page()) {
            return;
        }

        $trackingMode = Option::get('use_cache_plugin');

        if ($trackingMode) {
            return;
        }

        $settingsUrl = Menus::admin_url('settings');
        $noticeText  = sprintf(
            wp_kses(
                /* translators: %s: settings URL */
                __('<b>WP Statistics Notice:</b> Server Side Tracking is less accurate and will be deprecated in <b>version 15</b>. Please switch to Client Side Tracking for better accuracy. <a href="%s">Update Tracking Settings</a>.', 'wp-statistics'),
                [
                    'b' => [],
                    'a' => ['href' => []],
                ]
            ),
            esc_url($settingsUrl)
        );

        Notice::addNotice($noticeText, 'deprecate_server_side_tracking', 'warning');
    }

    /**
     * Notifies users when database size exceeds threshold.
     *
     * @return void
     */
    private function performanceAndCleanUp()
    {
        if (Notice::isNoticeDismissed('performance_and_clean_up')) {
            return;
        }

        if (!Menus::in_plugin_page()) {
            return;
        }

        $totalRows = $this->getCachedResult('db_rows');

        if ($totalRows === false) {
            $totalDbRows = DB::getTableRows();
            $totalRows   = array_sum(array_column($totalDbRows, 'rows'));
            $this->setCachedResult('db_rows', $totalRows, WEEK_IN_SECONDS);
        }

        if ($totalRows > apply_filters('wp_statistics_notice_db_row_threshold', 500000)) {
            $settingsUrl      = admin_url('admin.php?page=wps_settings_page&tab=advanced-settings');
            $optimizationUrl  = admin_url('admin.php?page=wps_optimization_page');
            $documentationUrl = 'https://wp-statistics.com/resources/optimizing-database-size-for-improved-performance/';

            $message = sprintf(
                wp_kses(
                    /* translators: %1$s: Settings URL, %2$s: Optimization URL, %3$s: Documentation URL */
                    __('<b>WP Statistics Notice (Database Maintenance Recommended):</b> Your database has accumulated many records, which could slow down your site. To improve performance, go to <a href="%1$s">Settings → Data Management</a> to enable the option that stops recording old visitor data, and visit the <a href="%2$s">Optimization page</a> to clean up your database. This process only removes detailed old visitor logs but retains aggregated data. Your other data and overall statistics will remain unchanged. For more details, <a href="%3$s" target="_blank">click here</a>.', 'wp-statistics'),
                    [
                        'b' => [],
                        'a' => [
                            'href' => [],
                            'target' => [],
                        ],
                    ]
                ),
                esc_url($settingsUrl),
                esc_url($optimizationUrl),
                esc_url($documentationUrl)
            );

            Notice::addNotice($message, 'performance_and_clean_up', 'warning');
        }
    }

    /**
     * Notifies users when server memory is insufficient.
     *
     * @return void
     */
    public function memoryLimitCheck()
    {
        if (Notice::isNoticeDismissed('memory_limit_check')) {
            return;
        }

        if (! Menus::in_plugin_page()) {
            return;
        }

        if (! Helper::checkMemoryLimit()) {
            return;
        }

        Notice::addNotice(
            esc_html__('Your server memory limit is too low. Please contact your hosting provider to increase the memory limit.', 'wp-statistics'),
            'memory_limit_check',
            'warning'
        );
    }

    /**
     * Notifies users about invalid email report schedules.
     *
     * @return void
     */
    public function emailReportSchedule()
    {
        if (Notice::isNoticeDismissed('email_report_schedule')) {
            return;
        }

        if (Option::get('time_report') == '0') {
            return;
        }

        if (!wp_next_scheduled('wp_statistics_report_hook')) {
            return;
        }
        
        $timeReports       = Option::get('time_report');
        $schedulesInterval = Schedule::getSchedules();

        if (isset($schedulesInterval[$timeReports])) {
           return;
        }

        Notice::addNotice(
            sprintf(
                /* translators: %1$s: URL to the update settings page */
                wp_kses(
                    __('Please update your email report schedule due to new changes in our latest release: <a href="%1$s">Update Settings</a>.', 'wp-statistics'),
                    [
                        'a' => [
                            'href' => []
                        ]
                    ]
                ),
                esc_url(Menus::admin_url('settings', ['tab' => 'notifications-settings']))
            ),
            'email_report_schedule',
            'warning'
        );
    }

    /**
     * Notifies users about the removal of honeypot feature.
     * 
     * @return void
     */
    public function notifyDeprecatedHoneypotOption() {
        if (Notice::isNoticeDismissed('deprecated_honeypot')) {
            return;
        }

        if (! Menus::in_plugin_page()) {
            return;
        }

        if (empty(Option::get('use_honeypot'))) {
            return;
        }

        Notice::addNotice( 
            sprintf(
                wp_kses(
                    /* translators: %1$s: opening strong tag, %2$s: closing strong tag, %3$s: opening link tag, %4$s: Learn more text, %5$s: closing link tag */
                    esc_html__('The WP Statistics %1$sHoney Pot Trap Page%2$s option will be removed in version 14.13. %3$s%4$s%5$s.', 'wp-statistics'),
                   [
                        'strong' => [],
                        'a' => [
                            'href' => [],
                            'target' => [],
                        ],
                   ]
                ),
                '<strong>',
                '</strong>',
                '<a href="https://wp-statistics.com/resources/deprecating-the-honey-pot-trap-page-option/?utm_source=wp-statistics&utm_medium=link&utm_campaign=settings" target="_blank">',
                esc_html__('Learn more', 'wp-statistics'),
                '</a>'
            ),
            'deprecated_honeypot',
            'warning'
        );
    }
}
