<?php

namespace WP_Statistics\Service\Admin\Notification;

use WP_Statistics\Components\Event;
use WP_STATISTICS\Option;

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
        if (Option::get('display_notifications')) {
            add_action('admin_init', [$this, 'registerActions']);
            Event::schedule('wp_statistics_notification_hook', time(), 'daily', [$this, 'fetchNotification']);
        } else {
            Event::unschedule('wp_statistics_notification_hook');
        }
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