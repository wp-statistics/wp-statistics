<?php

namespace WP_Statistics\Service\Admin\Notification;

use Exception;
use WP_Statistics\Components\RemoteRequest;

class NotificationFetcher
{
    /**
     * API base URL for fetching notifications.
     *
     * @var string
     */
    private $apiUrl = 'https://connect.wp-statistics.com';

    /**
     * Fetches notifications from the remote API and stores them in the WordPress database.
     *
     * @return bool True on success, false on failure.
     * @throws Exception If the API response is invalid or an error occurs.
     */
    public function fetchNotification()
    {
        try {
            $pluginSlug = basename(dirname(WP_STATISTICS_MAIN_FILE));
            $url        = $this->apiUrl . '/api/v1/notifications';
            $method     = 'GET';
            $params     = ['plugin_slug' => $pluginSlug, 'per_page' => 20, 'sortby' => 'activated_at-desc'];
            $args       = [
                'timeout'     => 45,
                'redirection' => 5,
                'headers'     => array(
                    'Accept'       => 'application/json',
                    'Content-Type' => 'application/json; charset=utf-8',
                    'user-agent'   => $pluginSlug,
                ),
                'cookies'     => array(),
            ];

            $remoteRequest = new RemoteRequest($url, $method, $params, $args);

            $remoteRequest->execute(false, false);

            $response     = $remoteRequest->getResponseBody();
            $responseCode = $remoteRequest->getResponseCode();

            if ($responseCode !== 200) {
                return false;
            }

            $notifications = json_decode($response, true);

            if (empty($notifications) || !is_array($notifications)) {
                throw new Exception(
                    sprintf(__('No notifications were found. The API returned an empty response from the following URL: %s', 'wp-statistics'), "{$this->apiUrl}/api/v1/notifications?plugin_slug={$pluginSlug}")
                );
            }

            $notifications = NotificationProcessor::syncNotifications($notifications);
            $notifications = NotificationProcessor::sortNotificationsByActivatedAt($notifications);

            $prevRawNotificationsData = NotificationFactory::getRawNotificationsData();

            if (!update_option('wp_statistics_notifications', $notifications)) {
                if ($prevRawNotificationsData !== $notifications) {
                    WP_Statistics()->log('Failed to update wp_statistics_notifications option.', 'error');
                }
            }

            return true;

        } catch (Exception $e) {
            WP_Statistics()->log($e->getMessage(), 'error');
            return false;
        }
    }
}