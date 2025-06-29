<?php

namespace WP_Statistics\Service\Admin\Notification;

class NotificationFactory
{
    /**
     * Retrieves the raw notification data from WordPress options.
     *
     * @return array The raw notification data stored in the database.
     */
    public static function getRawNotificationsData()
    {
        return get_option('wp_statistics_notifications', []);
    }

    /**
     * Retrieves all notifications after processing and filtering.
     *
     * @return array Processed and decorated notifications.
     */
    public static function getAllNotifications()
    {
        $rawNotifications = self::getRawNotificationsData();
        $notifications    = NotificationProcessor::filterNotificationsByTags($rawNotifications['data'] ?? []);

        return NotificationProcessor::decorateNotifications($notifications);
    }

    /**
     * Checks if there are updated notifications.
     *
     * @return bool
     */
    public static function hasUpdatedNotifications()
    {
        $rawNotifications = self::getRawNotificationsData();
        $notifications    = NotificationProcessor::filterNotificationsByTags($rawNotifications['data'] ?? []);

        foreach ($notifications as $notification) {
            if (empty($notification['dismiss'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the count of new notifications, or false if no new notifications exist.
     *
     * @return int False if no new notifications exist, or the count of new notifications.
     */
    public static function getNewNotificationCount()
    {
        $rawNotifications = self::getRawNotificationsData();
        $notifications    = NotificationProcessor::filterNotificationsByTags($rawNotifications['data'] ?? []);

        $count = 0;

        foreach ($notifications as $notification) {
            if (empty($notification['dismiss'])) {
                $count++;
            }
        }

        return $count;
    }
}