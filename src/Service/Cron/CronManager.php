<?php

namespace WP_Statistics\Service\Cron;

use WP_Statistics\Components\Event;
use WP_Statistics\Utils\Request;
use WP_Statistics\Service\Cron\Events\DatabaseMaintenanceEvent;
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
 * Uses lazy loading to defer event instantiation until needed.
 *
 * @since 15.0.0
 */
class CronManager
{
    /**
     * Instantiated event handlers (lazy loaded).
     *
     * @var ScheduledEventInterface[]
     */
    private $events = [];

    /**
     * Event class names for lazy loading.
     *
     * @var array<string, string>
     */
    private $eventClasses = [];

    /**
     * Whether defaults have been registered.
     *
     * @var bool
     */
    private $defaultsRegistered = false;

    /**
     * Constructor.
     */
    public function __construct()
    {
        // Register custom cron schedules
        CronSchedules::register();

        // Initialize event class names (lazy loading)
        $this->initializeEvents();

        // Schedule events on init
        add_action('init', [$this, 'scheduleEvents']);

        // Listen for settings changes to reschedule events
        add_action('wp_statistics_settings_updated', [$this, 'onSettingsUpdated']);
    }

    /**
     * Initialize event class names for lazy loading.
     *
     * @return void
     */
    private function initializeEvents()
    {
        if ($this->defaultsRegistered) {
            return;
        }

        // Register class names only - no instantiation yet
        $this->eventClasses = [
            'database_maintenance' => DatabaseMaintenanceEvent::class,
            'geoip_update'         => GeoIPUpdateEvent::class,
            'daily_summary'        => DailySummaryEvent::class,
            'license'              => LicenseEvent::class,
            'referrals_database'   => ReferralsDatabaseEvent::class,
            'notification'         => NotificationEvent::class,
            'email_report'         => EmailReportEvent::class,
        ];

        $this->defaultsRegistered = true;

        /**
         * Allow add-ons to register additional scheduled events.
         *
         * @param CronManager $manager The cron manager instance.
         */
        do_action('wp_statistics_register_cron_events', $this);
    }

    /**
     * Resolve an event instance (lazy loading).
     *
     * @param string $key Event key.
     * @return ScheduledEventInterface|null
     */
    private function resolve(string $key): ?ScheduledEventInterface
    {
        // Already instantiated
        if (isset($this->events[$key])) {
            return $this->events[$key];
        }

        // Create instance from class name
        if (isset($this->eventClasses[$key])) {
            $this->events[$key] = new $this->eventClasses[$key]();
            return $this->events[$key];
        }

        return null;
    }

    /**
     * Resolve all events (instantiate all).
     *
     * @return void
     */
    private function resolveAll(): void
    {
        foreach (array_keys($this->eventClasses) as $key) {
            $this->resolve($key);
        }
    }

    /**
     * Schedule all cron events.
     *
     * Note: This resolves all events as scheduling requires checking each one.
     *
     * @return void
     */
    public function scheduleEvents()
    {
        $this->resolveAll();

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
            'wp_statistics_referrerspam_hook',
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
            'wp_statistics_dbmaint_hook' => [
                'label'      => __('Database Maintenance', 'wp-statistics'),
                'recurrence' => 'daily',
            ],
            'wp_statistics_geoip_hook' => [
                'label'      => __('GeoIP Database Update', 'wp-statistics'),
                'recurrence' => 'monthly',
            ],
            'wp_statistics_email_report' => [
                'label'      => __('Email Report', 'wp-statistics'),
                'recurrence' => 'daily',
            ],
            'wp_statistics_queue_daily_summary' => [
                'label'      => __('Daily Summary', 'wp-statistics'),
                'recurrence' => 'daily',
            ],
            'wp_statistics_licenses_hook' => [
                'label'      => __('License Migration', 'wp-statistics'),
                'recurrence' => 'daily',
            ],
            'wp_statistics_check_licenses_status' => [
                'label'      => __('License Status Check', 'wp-statistics'),
                'recurrence' => 'twicedaily',
            ],
            'wp_statistics_referrals_db_hook' => [
                'label'      => __('Referrals Database', 'wp-statistics'),
                'recurrence' => 'weekly',
            ],
            'wp_statistics_daily_cron_hook' => [
                'label'      => __('Daily Tasks', 'wp-statistics'),
                'recurrence' => 'daily',
            ],
        ];

        foreach ($hooks as $hook => $info) {
            $nextRun   = wp_next_scheduled($hook);
            $scheduled = (bool) $nextRun;

            $events[$hook] = [
                'label'      => $info['label'],
                'hook'       => $hook,
                'recurrence' => $info['recurrence'],
                'next_run'   => $nextRun ? date_i18n('Y-m-d\TH:i:s', $nextRun) : null,
                'scheduled'  => $scheduled,
                'enabled'    => $scheduled, // Task is enabled if it's scheduled
            ];
        }

        return $events;
    }

    /**
     * Register an event handler instance.
     *
     * @param string $key Event key.
     * @param ScheduledEventInterface $event Event handler.
     * @return void
     */
    public function registerEvent(string $key, ScheduledEventInterface $event): void
    {
        // Remove from class registry if it was there (instance takes precedence)
        unset($this->eventClasses[$key]);

        $this->events[$key] = $event;
    }

    /**
     * Register an event class for lazy loading.
     *
     * @param string $key Event key.
     * @param string $className Fully qualified class name.
     * @return void
     */
    public function registerEventClass(string $key, string $className): void
    {
        $this->eventClasses[$key] = $className;
    }

    /**
     * Check if an event exists.
     *
     * @param string $key Event key.
     * @return bool
     */
    public function hasEvent(string $key): bool
    {
        return isset($this->events[$key]) || isset($this->eventClasses[$key]);
    }

    /**
     * Get an event handler by key (lazy loading).
     *
     * @param string $key Event key.
     * @return ScheduledEventInterface|null
     */
    public function getEvent(string $key): ?ScheduledEventInterface
    {
        return $this->resolve($key);
    }

    /**
     * Get all event keys.
     *
     * @return array
     */
    public function getEventKeys(): array
    {
        return array_unique(array_merge(
            array_keys($this->events),
            array_keys($this->eventClasses)
        ));
    }

    /**
     * Get all event handlers.
     *
     * Note: This resolves all events.
     *
     * @return ScheduledEventInterface[]
     */
    public function getEvents(): array
    {
        $this->resolveAll();
        return $this->events;
    }

    /**
     * Get detailed information about all events.
     *
     * Note: This resolves all events.
     *
     * @return array
     */
    public function getEventsInfo(): array
    {
        $info = [];

        foreach ($this->getEventKeys() as $key) {
            $event = $this->resolve($key);
            if ($event) {
                $info[$key] = $event->getInfo();
            }
        }

        return $info;
    }

    /**
     * Handle settings update - reschedule affected events.
     *
     * Only resolves the specific events that need rescheduling.
     *
     * @param array $updatedSettings Updated settings keys.
     * @return void
     */
    public function onSettingsUpdated(array $updatedSettings = []): void
    {
        // Map settings to events that need rescheduling
        $settingsToEvents = [
            'time_report'                      => 'email_report',
            'email_list'                       => 'email_report',
            'schedule_dbmaint'                 => 'database_maintenance',
            'geoip_location_detection_method'  => 'geoip_update',
        ];

        $eventsToReschedule = [];

        foreach ($updatedSettings as $setting) {
            if (isset($settingsToEvents[$setting])) {
                $eventsToReschedule[$settingsToEvents[$setting]] = true;
            }
        }

        // Reschedule affected events (lazy - only resolves needed events)
        foreach (array_keys($eventsToReschedule) as $eventKey) {
            $event = $this->resolve($eventKey);
            if ($event) {
                $event->reschedule();
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
        $event = $this->resolve($key);
        if (!$event) {
            return false;
        }

        $event->reschedule();
        return true;
    }

    /**
     * Reschedule all events.
     *
     * Note: This resolves all events.
     *
     * @return void
     */
    public function rescheduleAll(): void
    {
        foreach ($this->getEvents() as $event) {
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
