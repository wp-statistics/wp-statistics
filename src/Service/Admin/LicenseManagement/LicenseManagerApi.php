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
    private $license = null;

    /**
     * @param string $licenseKey
     * @param string $domain
     */
    public function __construct($licenseKey, $domain = '')
    {
        try {
            $this->initLicenseObject($licenseKey, $domain);
        } catch (\Exception $e) {
        }
    }

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
     * Returns `license/status` request's result.
     *
     * @param string $licenseKey
     * @param string $domain
     *
     * @return object|null
     *
     * @throws \Exception
     */
    public function initLicenseObject($licenseKey, $domain = '')
    {
        if (!empty($this->license)) {
            return $this->license;
        }

        if (empty($licenseKey)) {
            return null;
        }

        $result = $this->call(self::LICENSE_STATUS, 'GET', [
            'license_key' => $licenseKey,
            'domain'      => !empty($domain) ? esc_url($domain) : Helper::get_domain_name(home_url()),
        ]);

        if (empty($result) || !isset($result->license_details)) {
            // translators: %s: License status request's result.
            throw new \Exception(sprintf(esc_html__('Invalid license status result: %s', 'wp-statistics'), esc_html(var_export($result, true))));
        }

        $this->license = $result;

        return $this->license;
    }

    /**
     * Returns license status request's result as an object.
     *
     * @return object|null
     */
    public function getLicenseObject()
    {
        return $this->license;
    }

    /**
     * Returns license status.
     *
     * @return string Possible returned values: `active`, `expired` or `invalid`.
     */
    public function getStatus()
    {
        if (empty($this->license) || empty($this->license->license_details)) {
            return 'invalid';
        }

        if (empty($this->license->license_details->valid_until) || $this->license->license_details->valid_until < wp_date('Y-m-d')) {
            return 'expired';
        }

        return 'active';
    }

    /**
     * Returns products lists for the given license.
     *
     * @return array
     */
    public function getProducts()
    {
        if (empty($this->license) || empty($this->license->products) || !is_array($this->license->products)) {
            return [];
        }

        return $this->licenseStatus->products;
    }
}
