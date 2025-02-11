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

        return array_map(fn($notification) => new NotificationDecorator((object)$notification), $notifications);
    }

    /**
     * Dismisses a specific notification by ID.
     *
     * @param int $notificationId The ID of the notification to dismiss.
     * @return bool Returns true on success.
     */
    public static function dismissNotification($notificationId)
    {
        $notifications  = NotificationFactory::getRawNotificationsData();
        $notificationId = intval($notificationId);

        if (!empty($notifications['data']) && is_array($notifications['data'])) {
            foreach ($notifications['data'] as &$notification) {
                if ($notificationId === $notification['id']) {
                    $notification['dismiss'] = true;
                    break;
                }
            }

            update_option('wp_statistics_notifications', $notifications);
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

        if (!empty($notifications['data']) && is_array($notifications['data'])) {
            foreach ($notifications['data'] as &$notification) {
                $notification['dismiss'] = true;
            }

            update_option('wp_statistics_notifications', $notifications);
        }

        return true;
    }
}