<?php

namespace WP_Statistics\Service\Admin\LicenseManagement;

use WP_Statistics\Components\RemoteRequest;
use WP_STATISTICS\Helper;

/**
 * This class handles all the license management related API calls.
 */
class LicenseManagerApi
{
    public $apiRootUrl = WP_STATISTICS_SITE . 'wp-json/wp-license-manager/v1/';

    // Endpoints
    public const LICENSE_STATUS   = 'license/status';
    public const PRODUCT_DOWNLOAD = 'product/download';

    /** @var object License status request's result. */
    private $licenseStatus = null;

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
    public function call($endpoint, $method = 'GET', $params = [], $args = [], $throwFailedHttpCodeResponse = true)
    {
        $url     = $this->apiRootUrl . trim($endpoint, ' \\/');
        $request = new RemoteRequest($url, $method, $params, $args);

        return $request->execute($throwFailedHttpCodeResponse);
    }

    /**
     * Returns license status.
     *
     * @param string $licenseKey
     * @param string $domain
     *
     * @return object|null
     *
     * @throws \Exception
     */
    public function getStatus($licenseKey = '', $domain = '')
    {
        if (!empty($this->licenseStatus)) {
            return $this->licenseStatus;
        }

        if (empty($licenseKey)) {
            return null;
        }

        $licenseStatus = $this->call(self::LICENSE_STATUS, 'GET', [
            'license_key' => $licenseKey,
            'domain'      => !empty($domain) ? esc_url($domain) : Helper::get_domain_name(home_url()),
        ]);

        if (empty($licenseStatus) || !isset($licenseStatus->license_details)) {
            // translators: %s: License status request's response.
            throw new \Exception(sprintf(esc_html__('Invalid status response: %s', 'wp-statistics'), esc_html(var_export($licenseStatus, true))));
        }

        $this->licenseStatus = $licenseStatus;

        return $this->licenseStatus;
    }
}
