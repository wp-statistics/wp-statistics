<?php

namespace WP_Statistics\Service\Admin\Notification;

use WP_STATISTICS\User;
use WP_Statistics\Utils\Request;
use WP_Statistics\Components\Ajax;

/**
 * @todo Send responses in a standard format using Ajax::success() or Ajax::error()
 */
class NotificationActions
{
    /**
     * Registers AJAX actions for notifications.
     *
     * @return void
     */
    public function register()
    {
        Ajax::register('dismiss_notification', [$this, 'dismissNotification'], false);
        Ajax::register('update_notifications_status', [$this, 'updateNotificationsStatus'], false);
    }

    /**
     * Ensures the current user is allowed to change the notification state.
     *
     * @return void Sends a 403 JSON response and exits when the user has no access.
     */
    private function checkAccess()
    {
        if (!User::Access('manage')) {
            wp_send_json_error([
                'message' => esc_html__('Unauthorized.', 'wp-statistics')
            ], 403);
        }
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

        $this->checkAccess();

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

        $this->checkAccess();

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