<?php

namespace WP_Statistics\Service\Admin\Notification;

class NotificationFactory
{
    /**
     * The visible notifications of this request, and the stored data they came from.
     * The header asks for the dot, the badge and the count on one page, so the filter runs once.
     * Comparing with the stored data keeps it right after a dismiss in the same request.
     */
    private static $visibleNotifications = [];
    private static $visibleSource        = null;

    /**
     * Stored notifications that are inside their dates and match the site's tags.
     *
     * @return array
     */
    private static function getVisibleNotifications()
    {
        $source = self::getRawNotificationsData()['data'] ?? [];

        if (self::$visibleSource !== $source) {
            self::$visibleNotifications = NotificationProcessor::filterNotificationsByTags($source);
            self::$visibleSource        = $source;
        }

        return self::$visibleNotifications;
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
     * Retrieves all notifications after processing and filtering.
     *
     * @return array Processed and decorated notifications.
     */
    public static function getAllNotifications()
    {
        return NotificationProcessor::decorateNotifications(self::getVisibleNotifications());
    }

    /**
     * Checks if there are updated notifications.
     *
     * @return bool
     */
    public static function hasUpdatedNotifications()
    {
        $notifications = self::getVisibleNotifications();

        foreach ($notifications as $notification) {
            if (empty($notification['dismiss'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the bell badge of the newest unread notification that has one, or null.
     *
     * @return array|null ['text' => string, 'urgent' => bool]
     */
    public static function getBellBadge()
    {
        $notifications = self::getVisibleNotifications();

        foreach ($notifications as $notification) {
            if (empty($notification['dismiss']) && !empty($notification['extra_data']['badge'])) {
                return [
                    'text'   => (string)$notification['extra_data']['badge'],
                    'urgent' => ($notification['background_color'] ?? '') === 'danger',
                ];
            }
        }

        return null;
    }

    /**
     * Returns the count of new notifications, or false if no new notifications exist.
     *
     * @return int False if no new notifications exist, or the count of new notifications.
     */
    public static function getNewNotificationCount()
    {
        $notifications = self::getVisibleNotifications();

        $count = 0;

        foreach ($notifications as $notification) {
            if (empty($notification['dismiss'])) {
                $count++;
            }
        }

        return $count;
    }
}