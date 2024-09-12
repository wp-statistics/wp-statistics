<?php

namespace WP_Statistics\Service\Admin\LicenseManagement\ApiHandler;

use WP_STATISTICS\TimeZone;

/**
 * This class handles all the license management related API calls.
 */
class LicenseStatusResponseDecorator
{
    /** @var object License status request's result. */
    private $license = null;

    /**
     * @param object $license
     *
     * @throws \Exception
     */
    public function __construct($license)
    {
        if (empty($license) || !isset($license->license_details)) {
            // translators: %s: License status request's result.
            throw new \Exception(sprintf(esc_html__('Invalid license status result: %s', 'wp-statistics'), esc_html(var_export($license, true))));
        }

        $this->license = $license;
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
