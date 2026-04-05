<?php

namespace WP_Statistics\Service\Admin\Notification;

use WP_Statistics\Service\Admin\ConditionTagEvaluator;

class NotificationProcessor
{
    /**
     * Filter notifications based on their associated condition tags.
     *
     * @param array $notifications List of notifications to filter.
     * @return array Filtered notifications.
     */
    public static function filterByTags($notifications)
    {
        if (empty($notifications) || !is_array($notifications)) {
            return [];
        }

        foreach ($notifications as $key => $notification) {
            if (!empty($notification['tags']) && is_array($notification['tags'])) {
                foreach ($notification['tags'] as $tag) {
                    if (!ConditionTagEvaluator::checkConditions($tag)) {
                        unset($notifications[$key]);
                        break;
                    }
                }
            }
        }

        return array_values($notifications);
    }

    /**
     * Sort notifications by activated_at in descending order (newest first).
     *
     * @param array $notifications Raw notification data with 'data' key.
     * @return array Sorted notification data.
     */
    public static function sortByActivatedAt($notifications)
    {
        if (!empty($notifications['data']) && is_array($notifications['data'])) {
            usort($notifications['data'], function ($a, $b) {
                return strtotime($b['activated_at']) - strtotime($a['activated_at']);
            });
        }

        return $notifications;
    }
}
