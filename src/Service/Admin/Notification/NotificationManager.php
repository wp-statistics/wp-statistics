<?php

namespace WP_Statistics\Service\Admin\Notification;

use WP_Statistics\Components\Event;

class NotificationManager
{
    /**
     * NotificationManager constructor.
     *
     * Initializes hooks for AJAX callbacks, cron schedules,
     * and schedules the notification fetch event.
     */
    public function __construct()
    {
        add_action('admin_init', [$this, 'registerActions']);
        add_filter('cron_schedules', [$this, 'notificationCronIntervalsHook']);
        Event::schedule('wp_statistics_notification_hook', time(), 'every_three_days', [$this, 'fetchNotification']);
    }

    /**
     * Registers a custom cron schedule for notifications.
     *
     * @param array $schedules Existing cron schedules.
     * @return array Modified cron schedules with an added "every_three_days" interval.
     */
    public function notificationCronIntervalsHook($schedules)
    {
        $schedules['every_three_days'] = array(
            'interval' => 3 * 24 * 60 * 60,
            'display'  => __('Every 3 Days', 'wp-statistics')
        );
        return $schedules;
    }

    /**
     * Fetches new notifications.
     *
     * This method is triggered by the scheduled cron event
     * and retrieves new notifications.
     */
    public function fetchNotification()
    {
        $notificationFetcher = new NotificationFetcher();
        $notificationFetcher->fetchNotification();
    }

    /**
     * Registers notification actions.
     *
     * @return void
     */
    public function registerActions()
    {
        $notificationActions = new NotificationActions();

        $notificationActions->register();
    }
}