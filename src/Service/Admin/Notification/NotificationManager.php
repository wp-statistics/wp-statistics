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
        }
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