<?php

namespace WP_Statistics\Service\Admin\LicenseManagement;

use WP_Statistics\Components\RemoteRequest;
use WP_STATISTICS\Helper;
use WP_STATISTICS\TimeZone;

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
     * Checks if `license_details` object is not empty.
     *
     * @return bool
     */
    public function isLicenseDetailsValid()
    {
        return !empty($this->license) && !empty($this->license->license_details);
    }

    /**
     * Returns license status.
     *
     * @return string Possible returned values: `active`, `expired` or `invalid`.
     */
    public function getStatus()
    {
        if (!$this->isLicenseDetailsValid()) {
            return 'invalid';
        }

        if (empty($this->license->license_details->valid_until) || $this->license->license_details->valid_until < wp_date('Y-m-d')) {
            return 'expired';
        }

        return 'active';
    }

    /**
     * Returns `valid_until` date for the given license.
     *
     * @return string Date with 'Y-m-d' format.
     */
    public function getValidUntil()
    {
        if (
            !$this->isLicenseDetailsValid() ||
            !isset($this->license->license_details->valid_until) || !TimeZone::isValidDate($this->license->license_details->valid_until)
        ) {
            return '';
        }

        return $this->license->license_details->valid_until;
    }

    /**
     * Returns `type` of the given license.
     *
     * @return string
     */
    public function getType()
    {
        if (!$this->isLicenseDetailsValid() || !isset($this->license->license_details->type)) {
            return '';
        }

        return $this->license->license_details->type;
    }

    /**
     * Returns `max_domains` of the given license.
     *
     * @return int
     */
    public function getMaxDomains()
    {
        if (!$this->isLicenseDetailsValid() || !isset($this->license->license_details->max_domains)) {
            return 0;
        }

        return intval($this->license->license_details->max_domains);
    }

    /**
     * Returns full user information of the given product.
     *
     * @return object|null
     */
    public function getUser()
    {
        if (!$this->isLicenseDetailsValid() || empty($this->license->license_details->user)) {
            return null;
        }

        return $this->license->license_details->user;
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

        return $this->license->products;
    }

    /**
     * Returns full information of the given product.
     *
     * @param string $slug
     *
     * @return object|null
     */
    public function getProduct($slug)
    {
        if (empty($slug)) {
            return null;
        }

        $products = $this->getProducts();
        if (empty($products) || !is_array($products)) {
            return null;
        }

        foreach ($products as $product) {
            if (empty($product->slug)) {
                continue;
            }

            if (trim($product->slug) === sanitize_text_field($slug)) {
                return $product;
            }
        }

        return null;
    }

    /**
     * Returns download URL for the given product.
     *
     * @param string $slug
     *
     * @return string
     *
     * @todo Add more methods for `products` object.
     */
    public function getDownloadUrl($slug)
    {
        $product = $this->getProduct($slug);
        return !empty($product->download_url) ? esc_url($product->download_url) : '';
    }
}
