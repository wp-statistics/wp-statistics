<?php

namespace WP_Statistics\Service\Admin\Notification;

use WP_Statistics\Utils\Request;
use WP_Statistics\Components\Ajax;

class NotificationActions
{
    /**
     * Registers AJAX actions for notifications.
     *
     * @return void
     */
    public function register()
    {
        Ajax::register('dismiss_notification', [$this, 'dismissNotification']);
        Ajax::register('update_notifications_status', [$this, 'updateNotificationsStatus']);
    }

    /**
     * Handles AJAX request to dismiss notifications.
     *
     * This function processes the dismissal of a specific notification or all notifications
     * based on the provided `notification_id` parameter. It verifies the AJAX nonce before
     * proceeding with the operation.
     *
     * @return void Outputs JSON response and exits execution.
     */
    public function dismissNotification()
    {
        check_ajax_referer('wp_rest', 'wps_nonce');

        $notificationId = Request::get('notification_id');

        if ($notificationId === 'all') {
            NotificationProcessor::dismissAllNotifications();
            $message = __('All notifications have been dismissed.', 'wp-statistics');
        } else {
            NotificationProcessor::dismissNotification($notificationId);
            $message = __('Notification has been dismissed.', 'wp-statistics');
        }

        wp_send_json_success(['message' => $message]);
        exit();
    }

    /**
     *
     */
    public function updateNotificationsStatus()
    {
        check_ajax_referer('wp_rest', 'wps_nonce');

        $hasUpdatedNotifications = NotificationProcessor::updateNotificationsStatus();

        if ($hasUpdatedNotifications) {
            $message = __('Notifications status has been updated.', 'wp-statistics');
        } else {
            $message = __('Notifications status has not been updated.', 'wp-statistics');
        }

        wp_send_json_success(['message' => $message]);
        exit();
    }
}