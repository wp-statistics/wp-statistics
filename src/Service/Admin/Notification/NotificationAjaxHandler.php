<?php

namespace WP_Statistics\Service\Admin\Notification;

use WP_Statistics\Utils\Request;

class NotificationAjaxHandler
{
    /**
     * Handles AJAX request to dismiss notifications.
     *
     * This function processes the dismissal of a specific notification or all notifications
     * based on the provided `notification_id` parameter. It verifies the AJAX nonce before
     * proceeding with the operation.
     *
     * @return void Outputs JSON response and exits execution.
     */
    public function dismissNotificationActionCallback()
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
}