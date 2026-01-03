<?php

namespace WP_Statistics\Service\Cron;

use WP_STATISTICS\Option;
use WP_Statistics\Components\Event;
use WP_Statistics\Utils\Request;
use WP_Statistics\Service\Cron\Events\DatabaseMaintenanceEvent;
use WP_Statistics\Service\Cron\Events\ReferrerSpamEvent;
use WP_Statistics\Service\Cron\Events\GeoIPUpdateEvent;
use WP_Statistics\Service\Cron\Events\DailySummaryEvent;
use WP_Statistics\Service\Cron\Events\LicenseEvent;
use WP_Statistics\Service\Cron\Events\ReferralsDatabaseEvent;
use WP_Statistics\Service\Cron\Events\NotificationEvent;

/**
 * Cron Manager for WP Statistics v15.
 *
 * Handles scheduling and management of all cron events.
 *
 * @since 15.0.0
 */
class CronManager
{
    /**
     * Event handlers.
     *
     * @var array
     */
    private $events = [];

    /**
     * Constructor.
     */
    public function __construct()
    {
        // Register custom cron schedules
        CronSchedules::register();

        // Initialize event handlers
        $this->initializeEvents();

        // Schedule events on init
        add_action('init', [$this, 'scheduleEvents']);
    }

    /**
     * Initialize all event handlers.
     *
     * @return void
     */
    private function initializeEvents()
    {
        // Note: Email reports are handled by EmailReportManager/EmailReportScheduler
        // using the 'wp_statistics_email_report' hook.
        $this->events = [
            'database_maintenance' => new DatabaseMaintenanceEvent(),
            'referrer_spam'        => new ReferrerSpamEvent(),
            'geoip_update'         => new GeoIPUpdateEvent(),
            'daily_summary'        => new DailySummaryEvent(),
            'license'              => new LicenseEvent(),
            'referrals_database'   => new ReferralsDatabaseEvent(),
            'notification'         => new NotificationEvent(),
        ];
    }

    /**
     * Schedule all cron events.
     *
     * @return void
     */
    public function scheduleEvents()
    {
        foreach ($this->events as $event) {
            $event->maybeSchedule();
            $event->registerCallback();
        }
    }

    /**
     * Unschedule all cron events.
     *
     * Used during plugin deactivation.
     *
     * @return void
     */
    public static function unscheduleAll()
    {
        $hooks = [
            'wp_statistics_dbmaint_hook',
            'wp_statistics_referrerspam_hook',
            'wp_statistics_geoip_hook',
            'wp_statistics_report_hook',           // Legacy email hook (cleanup)
            'wp_statistics_email_report',          // Current email hook
            'wp_statistics_queue_daily_summary',
            'wp_statistics_licenses_hook',
            'wp_statistics_check_licenses_status',
            'wp_statistics_referrals_db_hook',
            'wp_statistics_daily_cron_hook',
        ];

        foreach ($hooks as $hook) {
            $timestamp = wp_next_scheduled($hook);
            if ($timestamp) {
                wp_unschedule_event($timestamp, $hook);
            }
        }
    }

    /**
     * Get all scheduled events with their next run times.
     *
     * @return array Event information.
     */
    public static function getScheduledEvents()
    {
        $events = [];
        $hooks  = [
            'wp_statistics_dbmaint_hook'          => __('Database Maintenance', 'wp-statistics'),
            'wp_statistics_referrerspam_hook'     => __('Referrer Spam Update', 'wp-statistics'),
            'wp_statistics_geoip_hook'            => __('GeoIP Database Update', 'wp-statistics'),
            'wp_statistics_email_report'          => __('Email Report', 'wp-statistics'),
            'wp_statistics_queue_daily_summary'   => __('Daily Summary', 'wp-statistics'),
            'wp_statistics_licenses_hook'         => __('License Migration', 'wp-statistics'),
            'wp_statistics_check_licenses_status' => __('License Status Check', 'wp-statistics'),
            'wp_statistics_referrals_db_hook'     => __('Referrals Database', 'wp-statistics'),
            'wp_statistics_daily_cron_hook'       => __('Daily Tasks', 'wp-statistics'),
        ];

        foreach ($hooks as $hook => $label) {
            $nextRun = wp_next_scheduled($hook);
            $events[$hook] = [
                'label'    => $label,
                'hook'     => $hook,
                'next_run' => $nextRun ? date_i18n('Y-m-d H:i:s', $nextRun) : __('Not scheduled', 'wp-statistics'),
                'scheduled' => (bool) $nextRun,
            ];
        }

        return $events;
    }
}
