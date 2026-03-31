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
     * Fetch notifications from the remote API and store in the WordPress database.
     *
     * @return bool True on success, false on failure.
     */
    public function fetchNotifications()
    {
        try {
            $pluginSlug = basename(dirname(WP_STATISTICS_MAIN_FILE));
            $url        = $this->apiUrl . '/api/v1/notifications';
            $method     = 'GET';
            $params     = [
                'plugin_slug' => $pluginSlug,
                'per_page'    => 100,
                'sortby'      => 'activated_at-desc',
            ];
            $args = [
                'timeout'     => 45,
                'redirection' => 5,
                'headers'     => [
                    'Accept'       => 'application/json',
                    'Content-Type' => 'application/json; charset=utf-8',
                    'user-agent'   => $pluginSlug,
                ],
                'cookies' => [],
            ];

            $remoteRequest = new RemoteRequest($url, $method, $params, $args);

            $remoteRequest->execute(false, false);

            $response     = $remoteRequest->getResponseBody();
            $responseCode = $remoteRequest->getResponseCode();

            if ($responseCode === 404) {
                update_option('wp_statistics_notifications', []);
                return false;
            }

            if ($responseCode !== 200) {
                return false;
            }

            $notifications = json_decode($response, true);

            if (empty($notifications) || !is_array($notifications)) {
                return false;
            }

            $notifications = NotificationProcessor::sortByActivatedAt($notifications);

            update_option('wp_statistics_notifications', $notifications, false);

            return true;
        } catch (Exception $e) {
            WP_Statistics()->log($e->getMessage(), 'error');
            return false;
        }
    }
}
