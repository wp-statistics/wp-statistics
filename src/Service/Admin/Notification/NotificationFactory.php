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
        $notifications    = NotificationProcessor::syncNotifications($notifications);
        $notifications    = NotificationProcessor::sortNotificationsByActivatedAt($notifications);

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

    /**
     * Checks if there are updated notifications.
     *
     * @return bool
     */
    public static function hasUpdatedNotifications($userId)
    {
        $lastSeen = strtotime(get_user_meta($userId, 'wp_statistics_last_seen_notification', true));

        if (!$lastSeen) {
            return true;
        }

        $rawNotifications = self::getRawNotificationsData();
        $notifications    = NotificationProcessor::filterNotificationsByTags($rawNotifications['data'] ?? []);

        if (empty($notifications)) {
            return false;
        }

        $timezoneOffset = get_option('gmt_offset') * 3600;

        $latestNotification = strtotime(max(array_column($notifications, 'activated_at')));
        $latestNotification += $timezoneOffset;

        return ($latestNotification > $lastSeen);
    }
}