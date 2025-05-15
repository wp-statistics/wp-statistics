<?php

namespace WP_Statistics\Service\Admin\Notification;

use WP_Statistics\Decorators\NotificationDecorator;
use WP_Statistics\Service\Admin\ConditionTagEvaluator;

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
                        if (!ConditionTagEvaluator::checkConditions($tag)) {
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

    /**
     * Sync new notifications with old notifications.
     *
     * @param array $newNotifications
     *
     * @return array
     */
    public static function syncNotifications($newNotifications)
    {
        $oldNotifications = NotificationFactory::getRawNotificationsData();

        $dismissedNotifications = [];

        if (!empty($oldNotifications['data']) && is_array($oldNotifications['data'])) {
            foreach ($oldNotifications['data'] as $oldNotification) {
                if (!empty($oldNotification['dismiss']) && !empty($oldNotification['id'])) {
                    $dismissedNotifications[$oldNotification['id']] = true;
                }
            }
        }

        if (!empty($newNotifications['data']) && is_array($newNotifications['data'])) {
            foreach ($newNotifications['data'] as &$newNotification) {
                if (isset($dismissedNotifications[$newNotification['id']])) {
                    $newNotification['dismiss'] = true;
                }
            }
        }

        return $newNotifications;
    }

    /**
     * Checks for updated notifications by comparing new notifications with previously stored ones.
     *
     * @param array $newNotifications
     *
     * @return array
     */
    public static function checkUpdatedNotifications($rawNewNotifications)
    {
        $rawOldNotifications = NotificationFactory::getRawNotificationsData();
        $oldNotifications    = self::filterNotificationsByTags($rawOldNotifications['data'] ?? []);
        $oldNotificationIds  = [];

        foreach ($oldNotifications as $oldNotification) {
            if (!empty($oldNotification['id'])) {
                $oldNotificationIds[$oldNotification['id']] = true;
            }
        }

        $newNotifications               = self::filterNotificationsByTags($rawNewNotifications['data'] ?? []);
        $rawNewNotifications['updated'] = $rawOldNotifications['updated'] ?? false;

        if (!$rawNewNotifications['updated']) {
            foreach ($newNotifications as $newNotification) {
                if (!empty($newNotification['id']) && !isset($oldNotificationIds[$newNotification['id']])) {
                    $rawNewNotifications['updated'] = true;
                    break;
                }
            }
        }

        return $rawNewNotifications;
    }

    /**
     * Returns the new notifications array with an added count of unseen (new) notifications.
     *
     * @param array $rawNewNotifications
     * @return array Modified array including a 'count' key indicating new notifications.
     */
    public static function annotateNewNotificationCount($rawNewNotifications)
    {
        $rawOldNotifications = NotificationFactory::getRawNotificationsData();
        $oldNotifications    = self::filterNotificationsByTags($rawOldNotifications['data'] ?? []);
        $oldNotificationIds  = [];

        foreach ($oldNotifications as $oldNotification) {
            if (!empty($oldNotification['id'])) {
                $oldNotificationIds[$oldNotification['id']] = true;
            }
        }

        $newNotifications             = self::filterNotificationsByTags($rawNewNotifications['data'] ?? []);
        $updated                      = $rawNewNotifications['updated'] ?? false;
        $rawNewNotifications['count'] = $rawOldNotifications['count'] ?? 0;

        if ($updated) {
            $newCount = 0;

            foreach ($newNotifications as $newNotification) {
                if (!empty($newNotification['id']) && !isset($oldNotificationIds[$newNotification['id']])) {
                    $newCount++;
                }
            }

            $rawNewNotifications['count'] += $newCount;
        } else {
            $rawNewNotifications['count'] = 0;
        }

        return $rawNewNotifications;
    }

    /**
     * Updates the status of notifications.
     *
     * @return bool
     */
    public static function updateNotificationsStatus()
    {
        $notifications = NotificationFactory::getRawNotificationsData();

        if (!$notifications) {
            return false;
        }

        if (isset($notifications['updated']) && !empty($notifications['updated'])) {
            $notifications['updated'] = false;

            update_option('wp_statistics_notifications', $notifications);

            return true;
        }

        return false;
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
        if (!empty($notifications['data']) && is_array($notifications['data'])) {
            usort($notifications['data'], function ($a, $b) {
                return strtotime($b['activated_at']) - strtotime($a['activated_at']);
            });
        }

        return $notifications;
    }
}