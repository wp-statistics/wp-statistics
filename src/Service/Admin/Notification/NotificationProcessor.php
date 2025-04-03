<?php

namespace WP_Statistics\Service\Admin\Notification;

use WP_Statistics\Decorators\NotificationDecorator;

class NotificationProcessor
{
    /**
     * Filters notifications based on their associated tags.
     *
     * @param array $notifications List of notifications to be filtered.
     * @return array Filtered notifications.
     */
    public static function filterNotificationsByTags($notifications)
    {
        if (!empty($notifications) && is_array($notifications)) {
            foreach ($notifications as $key => $notification) {
                if (!empty($notification['tags']) && is_array($notification['tags'])) {
                    $condition = true;
                    foreach ($notification['tags'] as $tag) {
                        if (!NotificationConditionTags::checkConditions($tag)) {
                            $condition = false;
                            break;
                        }
                    }
                } else {
                    $condition = true;
                }

                if (!$condition) {
                    unset($notifications[$key]);
                }
            }

            $notifications = array_values($notifications);
        }

        return $notifications;
    }

    /**
     * Decorates notifications using the NotificationDecorator.
     *
     * @param array $notifications List of notifications to be decorated.
     * @return array Decorated notifications.
     */
    public static function decorateNotifications($notifications)
    {
        if (empty($notifications) || !is_array($notifications)) {
            return [];
        }

        return array_map(function ($notification) {
            return new NotificationDecorator((object)$notification);
        }, $notifications);
    }

    /**
     * Dismisses a specific notification by ID.
     *
     * @param int $notificationId The ID of the notification to dismiss.
     * @return bool Returns true on success.
     */
    public static function dismissNotification($notificationId)
    {
        $notifications = NotificationFactory::getRawNotificationsData();
        $userId        = get_current_user_id();
        $dismissed     = get_user_meta($userId, 'wp_statistics_dismissed_notifications', true);

        if (!is_array($dismissed)) {
            $dismissed = [];
        }

        $notificationId = intval($notificationId);

        if (!empty($notifications['data']) && is_array($notifications['data'])) {
            $notificationIds = array_column($notifications['data'], 'id');

            // Check if notification exists and is not already dismissed
            if (in_array($notificationId, $notificationIds, true) && !in_array($notificationId, $dismissed, true)) {
                $dismissed[] = $notificationId;
                update_user_meta($userId, 'wp_statistics_dismissed_notifications', $dismissed);
            }
        }

        return true;
    }

    /**
     * Dismisses all notifications.
     *
     * @return bool Returns true on success.
     */
    public static function dismissAllNotifications()
    {
        $notifications = NotificationFactory::getRawNotificationsData();
        $userId        = get_current_user_id();
        $dismissed     = get_user_meta($userId, 'wp_statistics_dismissed_notifications', true);

        if (!is_array($dismissed)) {
            $dismissed = [];
        }

        if (!empty($notifications['data']) && is_array($notifications['data'])) {
            $newDismissed     = array_column($notifications['data'], 'id');
            $updatedDismissed = array_unique(array_merge($dismissed, $newDismissed));

            if ($updatedDismissed !== $dismissed) {
                update_user_meta($userId, 'wp_statistics_dismissed_notifications', $updatedDismissed);
            }
        }

        return true;
    }

    /**
     * Sync new notifications with old notifications.
     *
     * @param array $notifications
     *
     * @return array
     */
    public static function syncNotifications($notifications)
    {
        $userId    = get_current_user_id();
        $dismissed = get_user_meta($userId, 'wp_statistics_dismissed_notifications', true);

        // Ensure $dismissed is an array
        $originalDismissed = is_array($dismissed) ? $dismissed : [];
        $dismissed         = is_array($dismissed) ? array_flip($dismissed) : [];

        // Get all valid notification IDs
        $validNotificationIds = [];

        if (!empty($notifications) && is_array($notifications)) {
            foreach ($notifications as &$notification) {
                if (!empty($notification['id'])) {
                    $validNotificationIds[$notification['id']] = true;

                    // Restore dismissed state
                    if (isset($dismissed[$notification['id']])) {
                        $notification['dismiss'] = true;
                    }
                }
            }
        }

        // Remove old dismissed IDs that no longer exist
        $updatedDismissed = array_values(array_filter(array_keys($dismissed), function ($id) use ($validNotificationIds) {
            return isset($validNotificationIds[$id]);
        }));

        // Only update if the dismissed list has changed
        if ($originalDismissed !== $updatedDismissed) {
            update_user_meta($userId, 'wp_statistics_dismissed_notifications', $updatedDismissed);
        }

        return $notifications;
    }

    /**
     * Updates the status of notifications.
     *
     * @return bool
     */
    public static function updateNotificationsStatus(): bool
    {
        if (!is_user_logged_in()) {
            return false;
        }

        return update_user_meta(get_current_user_id(), 'wp_statistics_last_seen_notification', current_time('mysql'));
    }

    /**
     * Sorts the notifications array by the 'activated_at' field in descending order.
     *
     * @param array $notifications
     *
     * @return array
     */
    public static function sortNotificationsByActivatedAt($notifications)
    {
        if (!empty($notifications) && is_array($notifications)) {
            usort($notifications, function ($a, $b) {
                $timeA = strtotime($a['activated_at'] ?? '1970-01-01');
                $timeB = strtotime($b['activated_at'] ?? '1970-01-01');
                return $timeB <=> $timeA;
            });
        }

        return $notifications;
    }
}