<?php

namespace WP_Statistics\Service\Admin\LicenseManagement;

use WP_Statistics\Components\RemoteRequest;

/**
 * This class handles all the license management related API calls.
 */
class LicenseManagerApi
{
    public static $apiRootUrl = WP_STATISTICS_SITE . 'wp-json/wp-license-manager/v1/';

    // Endpoints
    public const LICENSE_STATUS   = 'license/status';
    public const PRODUCT_DOWNLOAD = 'product/download';

    /**
     * Calls an API endpoint.
     *
     * @param string $endpoint
     * @param string $method
     * @param array $params Key-value parameters to be passed to the endpoint.
     * @param array $args Key-value arguments to be passed to the request.
     * @param bool $throwFailedHttpCodeResponse Whether or not to throw an exception if the request returns a failed HTTP code.
     *
     * @return object The response body of the remote request.
     *
     * @throws \Exception If the request fails and `$throwFailedHttpCodeResponse` is true.
     */
    public static function call($endpoint, $method = 'GET', $params = [], $args = [], $throwFailedHttpCodeResponse = true)
    {
        $url     = self::$apiRootUrl . trim($endpoint, ' \\/');
        $request = new RemoteRequest($url, $method, $params, $args);

        return $request->execute($throwFailedHttpCodeResponse);
    }
}
