<?php

namespace WP_Statistics\Service\Admin\AnonymizedUsageData;

use Exception;
use WP_Statistics\Components\RemoteRequest;

class AnonymizedUsageDataSender
{
    /**
     * API base URL for send anonymized usage data.
     *
     * @var string
     */
    private $apiUrl = 'https://connect.wp-statistics.com';

    /**
     * Sends anonymized usage data to the remote API.
     *
     * @param array $data
     *
     * @return bool
     */
    public function sendAnonymizedUsageData($data)
    {
        try {
            $pluginSlug = basename(dirname(WP_STATISTICS_MAIN_FILE));
            $url        = $this->apiUrl . '/api/v1/data';
            $method     = 'POST';
            $params     = ['plugin_slug' => $pluginSlug];
            $args       = [
                'timeout'     => 45,
                'redirection' => 5,
                'headers'     => array(
                    'Accept'       => 'application/json',
                    'Content-Type' => 'application/json; charset=utf-8',
                    'user-agent'   => $pluginSlug,
                ),
                'body'        => json_encode($data),
                'cookies'     => array(),
            ];

            $remoteRequest = new RemoteRequest($url, $method, $params, $args);

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