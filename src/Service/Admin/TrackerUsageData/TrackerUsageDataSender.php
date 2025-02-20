<?php

namespace WP_Statistics\Service\Admin\TrackerUsageData;

use Exception;
use WP_Statistics\Components\RemoteRequest;

class TrackerUsageDataSender
{
    /**
     * API base URL for send tracker usage data.
     *
     * @var string
     */
    private $apiUrl = 'https://connect.wp-statistics.com';

    /**
     * Sends tracker usage data to the remote API.
     *
     * @param array $data
     *
     * @return bool
     */
    public function sendTrackerUsageData($data)
    {
        try {
            $url    = $this->apiUrl . '/api/v1/data';
            $method = 'POST';
            $args   = [
                'timeout'     => 45,
                'redirection' => 5,
                'headers'     => array(
                    'Accept'       => 'application/json',
                    'Content-Type' => 'application/json; charset=utf-8',
                    'user-agent'   => $data['plugin_slug'],
                ),
                'body'        => json_encode($data),
                'cookies'     => array(),
            ];

            $remoteRequest = new RemoteRequest($url, $method, [], $args);

            $remoteRequest->execute(false, false);

            $response     = $remoteRequest->getResponseBody();
            $responseCode = $remoteRequest->getResponseCode();

            if ($responseCode !== 200) {
                return false;
            }

            return true;
        } catch (Exception $e) {
            WP_Statistics()->log($e->getMessage(), 'error');
            return false;
        }
    }
}