<?php

namespace WP_Statistics\Service\Admin\Notification;

class NotificationFactory
{
    /**
     * Retrieves all notifications after processing and filtering.
     *
     * @return array Processed and decorated notifications.
     */
    public static function getAllNotifications()
    {
        $rawNotifications = get_option('wp_statistics_notifications', []);
        $notifications    = NotificationProcessor::filterNotificationsByTags($rawNotifications['data'] ?? []);

        return NotificationProcessor::decorateNotifications($notifications);
    }

    /**
     * Retrieves the raw notification data from WordPress options.
     *
     * @return array The raw notification data stored in the database.
     */
    public static function getRawNotificationsData()
    {
        return get_option('wp_statistics_notifications', []);
    }
}