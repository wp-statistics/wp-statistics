<?php

namespace WP_Statistics\Service\Admin\Notification;

use WP_Statistics\Components\Ajax;
use WP_Statistics\Components\Option;

/**
 * Notification Manager.
 *
 * Handles remote notification display, dismissal, and view tracking.
 * Per-user state stored in user meta.
 *
 * @since 15.0.0
 */
class NotificationManager
{
    /**
     * Whether the manager has been initialized.
     *
     * @var bool
     */
    private static $initialized = false;

    /**
     * Initialize the notification manager.
     *
     * @return void
     */
    public static function init()
    {
        if (self::$initialized) {
            return;
        }

        self::$initialized = true;

        if (!Option::getValue('display_notifications', true)) {
            return;
        }

        Ajax::register('dismiss_notification', [__CLASS__, 'handleDismissAjax'], false);
        Ajax::register('dismiss_all_notifications', [__CLASS__, 'handleDismissAllAjax'], false);
        Ajax::register('mark_notifications_viewed', [__CLASS__, 'handleMarkViewedAjax'], false);
    }

    /**
     * Dismiss a single notification for the current user.
     *
     * @return void
     */
    public static function handleDismissAjax()
    {
        check_ajax_referer('wp_statistics_notification_nonce', '_wpnonce');

        $notificationId = isset($_POST['notification_id']) ? intval($_POST['notification_id']) : 0;

        if (empty($notificationId)) {
            wp_send_json_error(['message' => __('Notification ID is required.', 'wp-statistics')]);
        }

        $userId      = get_current_user_id();
        $dismissedIds = NotificationFactory::getDismissedIds($userId);

        if (!in_array($notificationId, $dismissedIds, true)) {
            $dismissedIds[] = $notificationId;
            update_user_meta($userId, 'wp_statistics_dismissed_notifications', $dismissedIds);
        }

        wp_send_json_success();
    }

    /**
     * Dismiss all visible notifications for the current user.
     *
     * @return void
     */
    public static function handleDismissAllAjax()
    {
        check_ajax_referer('wp_statistics_notification_nonce', '_wpnonce');

        $ids = isset($_POST['ids']) ? array_map('intval', (array) $_POST['ids']) : [];

        if (empty($ids)) {
            wp_send_json_error(['message' => __('No notification IDs provided.', 'wp-statistics')]);
        }

        $userId       = get_current_user_id();
        $dismissedIds = NotificationFactory::getDismissedIds($userId);
        $dismissedIds = array_unique(array_merge($dismissedIds, $ids));

        update_user_meta($userId, 'wp_statistics_dismissed_notifications', $dismissedIds);

        wp_send_json_success();
    }

    /**
     * Mark notifications as viewed for the current user (clears badge count).
     *
     * @return void
     */
    public static function handleMarkViewedAjax()
    {
        check_ajax_referer('wp_statistics_notification_nonce', '_wpnonce');

        $ids = isset($_POST['ids']) ? array_map('intval', (array) $_POST['ids']) : [];

        if (empty($ids)) {
            wp_send_json_success();
            return;
        }

        $userId    = get_current_user_id();
        $viewedIds = NotificationFactory::getViewedIds($userId);
        $viewedIds = array_unique(array_merge($viewedIds, $ids));

        update_user_meta($userId, 'wp_statistics_viewed_notifications', $viewedIds);

        wp_send_json_success();
    }
}
