<?php

namespace WP_Statistics\Service\Admin\Notification;

class NotificationFactory
{
    /**
     * Cached result of getAll().
     *
     * @var array|null
     */
    private static $allCache = null;

    /**
     * Retrieve raw notification data from WordPress options.
     *
     * @return array
     */
    public static function getRawData()
    {
        return get_option('wp_statistics_notifications', []);
    }

    /**
     * Get all notifications filtered by condition tags.
     *
     * @return array
     */
    public static function getAll()
    {
        if (self::$allCache === null) {
            $raw            = self::getRawData();
            self::$allCache = NotificationProcessor::filterByTags($raw['data'] ?? []);
        }

        return self::$allCache;
    }

    /**
     * Get notifications for a specific user, excluding dismissed ones.
     *
     * @param int $userId User ID. Defaults to current user.
     * @return array
     */
    public static function getForUser($userId = 0)
    {
        if (!$userId) {
            $userId = get_current_user_id();
        }

        $notifications = self::getAll();
        $dismissedIds  = self::getDismissedIds($userId);

        if (empty($dismissedIds)) {
            return $notifications;
        }

        return array_values(array_filter($notifications, function ($notification) use ($dismissedIds) {
            return !in_array($notification['id'], $dismissedIds, true);
        }));
    }

    /**
     * Get count of unread (not yet viewed) notifications for a user.
     *
     * @param int $userId User ID. Defaults to current user.
     * @return int
     */
    public static function getUnreadCount($userId = 0)
    {
        if (!$userId) {
            $userId = get_current_user_id();
        }

        $notifications = self::getForUser($userId);
        $viewedIds     = self::getViewedIds($userId);

        if (empty($viewedIds)) {
            return count($notifications);
        }

        $unread = 0;
        foreach ($notifications as $notification) {
            if (!in_array($notification['id'], $viewedIds, true)) {
                $unread++;
            }
        }

        return $unread;
    }

    /**
     * Get dismissed notification IDs for a user.
     *
     * @param int $userId
     * @return array
     */
    public static function getDismissedIds($userId = 0)
    {
        if (!$userId) {
            $userId = get_current_user_id();
        }

        $dismissed = get_user_meta($userId, 'wp_statistics_dismissed_notifications', true);

        return is_array($dismissed) ? $dismissed : [];
    }

    /**
     * Get viewed notification IDs for a user.
     *
     * @param int $userId
     * @return array
     */
    public static function getViewedIds($userId = 0)
    {
        if (!$userId) {
            $userId = get_current_user_id();
        }

        $viewed = get_user_meta($userId, 'wp_statistics_viewed_notifications', true);

        return is_array($viewed) ? $viewed : [];
    }
}
