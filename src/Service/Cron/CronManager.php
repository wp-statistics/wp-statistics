<?php

namespace WP_Statistics\Service\Cron;

use WP_Statistics\Components\Event;
use WP_Statistics\Utils\Request;
use WP_Statistics\Service\Cron\Events\DatabaseMaintenanceEvent;
use WP_Statistics\Service\Cron\Events\ReferrerSpamEvent;
use WP_Statistics\Service\Cron\Events\GeoIPUpdateEvent;
use WP_Statistics\Service\Cron\Events\DailySummaryEvent;
use WP_Statistics\Service\Cron\Events\LicenseEvent;
use WP_Statistics\Service\Cron\Events\ReferralsDatabaseEvent;
use WP_Statistics\Service\Cron\Events\NotificationEvent;
use WP_Statistics\Service\Cron\Events\EmailReportEvent;

/**
 * Cron Manager for WP Statistics v15.
 *
 * Handles scheduling and management of all cron events.
 * Implements centralized event management with admin visibility.
 *
 * @since 15.0.0
 */
class CronManager
{
    /**
     * Event handlers.
     *
     * @var ScheduledEventInterface[]
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

        // Listen for settings changes to reschedule events
        add_action('wp_statistics_settings_updated', [$this, 'onSettingsUpdated']);
    }

    /**
     * Initialize all event handlers.
     *
     * @return void
     */
    private function initializeEvents()
    {
        $this->events = [
            'database_maintenance' => new DatabaseMaintenanceEvent(),
            'referrer_spam'        => new ReferrerSpamEvent(),
            'geoip_update'         => new GeoIPUpdateEvent(),
            'daily_summary'        => new DailySummaryEvent(),
            'license'              => new LicenseEvent(),
            'referrals_database'   => new ReferralsDatabaseEvent(),
            'notification'         => new NotificationEvent(),
            'email_report'         => new EmailReportEvent(),
        ];

        /**
         * Allow add-ons to register additional scheduled events.
         *
         * @param CronManager $manager The cron manager instance.
         */
        do_action('wp_statistics_register_cron_events', $this);
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
            // Current v15 hooks
            'wp_statistics_dbmaint_hook',
            'wp_statistics_referrerspam_hook',
            'wp_statistics_geoip_hook',
            'wp_statistics_email_report',
            'wp_statistics_queue_daily_summary',
            'wp_statistics_licenses_hook',
            'wp_statistics_check_licenses_status',
            'wp_statistics_referrals_db_hook',
            'wp_statistics_daily_cron_hook',

            // Optional hooks (self-managed but cleanup on deactivation)
            'wp_statistics_anonymized_share_data_hook',

            // Legacy hooks (v14 cleanup)
            'wp_statistics_report_hook',
            'wp_statistics_notification_hook',
            'wp_statistics_dbmaint_visitor_hook',
            'wp_statistics_marketing_campaign_hook',
            'wp_statistics_add_visit_hook',
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

    /**
     * Register an event handler.
     *
     * @param string $key Event key.
     * @param ScheduledEventInterface $event Event handler.
     * @return void
     */
    public function registerEvent(string $key, ScheduledEventInterface $event): void
    {
        $this->events[$key] = $event;
    }

    /**
     * Get an event handler by key.
     *
     * @param string $key Event key.
     * @return ScheduledEventInterface|null
     */
    public function getEvent(string $key): ?ScheduledEventInterface
    {
        return $this->events[$key] ?? null;
    }

    /**
     * Get all event handlers.
     *
     * @return ScheduledEventInterface[]
     */
    public function getEvents(): array
    {
        return $this->events;
    }

    /**
     * Get detailed information about all events.
     *
     * @return array
     */
    public function getEventsInfo(): array
    {
        $info = [];

        foreach ($this->events as $key => $event) {
            $info[$key] = $event->getInfo();
        }

        return $info;
    }

    /**
     * Handle settings update - reschedule affected events.
     *
     * @param array $updatedSettings Updated settings keys.
     * @return void
     */
    public function onSettingsUpdated(array $updatedSettings = []): void
    {
        // Map settings to events that need rescheduling
        $settingsToEvents = [
            'time_report'       => 'email_report',
            'email_list'        => 'email_report',
            'schedule_dbmaint'  => 'database_maintenance',
            'schedule_geoip'    => 'geoip_update',
            'schedule_referrerspam' => 'referrer_spam',
        ];

        $eventsToReschedule = [];

        foreach ($updatedSettings as $setting) {
            if (isset($settingsToEvents[$setting])) {
                $eventsToReschedule[$settingsToEvents[$setting]] = true;
            }
        }

        // Reschedule affected events
        foreach (array_keys($eventsToReschedule) as $eventKey) {
            if (isset($this->events[$eventKey])) {
                $this->events[$eventKey]->reschedule();
            }
        }
    }

    /**
     * Reschedule a specific event.
     *
     * @param string $key Event key.
     * @return bool True if rescheduled, false if event not found.
     */
    public function rescheduleEvent(string $key): bool
    {
        if (!isset($this->events[$key])) {
            return false;
        }

        $this->events[$key]->reschedule();
        return true;
    }

    /**
     * Reschedule all events.
     *
     * @return void
     */
    public function rescheduleAll(): void
    {
        foreach ($this->events as $event) {
            $event->reschedule();
        }
    }

    /**
     * Get the email report event.
     *
     * @return Events\EmailReportEvent|null
     */
    public function getEmailReportEvent(): ?Events\EmailReportEvent
    {
        $event = $this->getEvent('email_report');

        if ($event instanceof Events\EmailReportEvent) {
            return $event;
        }

        return null;
    }
}
