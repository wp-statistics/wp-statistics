<?php

namespace WP_Statistics\Service\Admin\LicenseManagement;

use WP_Statistics\Traits\TransientCacheTrait;
use WP_Statistics\Components\RemoteRequest;
use Exception;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;

class ApiCommunicator
{
    use TransientCacheTrait;

    private $apiUrl         = 'https://staging.wp-statistics.veronalabs.com/wp-json/wp-license-manager/v1';
    private $licensesOption = 'wp_statistics_licenses'; // Option key to store licenses
    private $productDecorator;

    public function __construct()
    {
        $this->productDecorator = new ProductDecorator();
    }

    /**
     * Get the list of products (add-ons) from the API and cache it for 1 week.
     *
     * @param bool $skipPremium Skip "WP Statistics Premium" from displaying in the list.
     *
     * @return ProductDecorator[] List of products
     * @throws Exception if there is an error with the API call
     */
    public function getProductList($skipPremium = true)
    {
        try {
            $remoteRequest = new RemoteRequest("{$this->apiUrl}/product/list", 'GET');
            $products      = $remoteRequest->execute(false, true, WEEK_IN_SECONDS);

            if (empty($products) || !is_array($products)) {
                throw new Exception(__('Product list is empty!', 'wp-statistics'));
            }
        } catch (Exception $e) {
            throw new Exception(
                // translators: %s: Error message.
                sprintf(__('Error fetching product list: %s', 'wp-statistics'), $e->getMessage())
            );
        }

        return $this->productDecorator->decorateProducts($products, $skipPremium);
    }

    /**
     * Get the download link for the specified plugin using the license key.
     *
     * @param string $licenseKey
     * @param string $pluginSlug
     *
     * @return string|null The download URL if found, null otherwise
     * @throws Exception if the API call fails
     */
    public function getDownload($licenseKey, $pluginSlug)
    {
        $remoteRequest = new RemoteRequest("{$this->apiUrl}/product/download", 'GET', [
            'license_key' => $licenseKey,
            'domain'      => home_url(),
            'plugin_slug' => $pluginSlug,
        ]);

        return $remoteRequest->execute(true, true, DAY_IN_SECONDS);
    }

    /**
     * Validate the license and get the status of licensed products.
     *
     * @param string $licenseKey
     *
     * @return object License status
     * @throws Exception if the API call fails
     */
    public function validateLicense($licenseKey)
    {
        try {
            $remoteRequest = new RemoteRequest("{$this->apiUrl}/license/status", 'GET', [
                'license_key' => $licenseKey,
                'domain'      => home_url(),
            ]);

            $licenseData = $remoteRequest->execute(false);

            if (empty($licenseData)) {
                throw new Exception(__('Invalid license response!', 'wp-statistics'));
            }

            if (empty($licenseData->license_details)) {
                throw new Exception(!empty($licenseData->message) ? $licenseData->message : __('Unknown error!', 'wp-statistics'));
            }
        } catch (Exception $e) {
            throw new Exception(
                // translators: %s: Error message.
                sprintf(__('Error validating license: %s', 'wp-statistics'), $e->getMessage())
            );
        }

        if (empty($licenseData->license_details->valid_until) || $licenseData->license_details->valid_until < wp_date('Y-m-d')) {
            throw new Exception(__('License is expired!', 'wp-statistics'));
        }

        // Store the license in the database
        $this->storeLicense($licenseKey, $licenseData);

        return $licenseData;
    }

    /**
     * Validates all stored licenses.
     *
     * @return void
     *
     * @todo Remove later if it was not used by other classes.
     */
    public function validateAllLicenses()
    {
        foreach ($this->getStoredLicenses() as $licenseKey => $license) {
            try {
                $this->validateLicense($licenseKey);
            } catch (\Exception $e) {
                $this->removeLicense($licenseKey);

                if (stripos($e->getMessage(), 'domain') !== false) {
                    Notice::addNotice(sprintf(
                        // translators: %s: License key.
                        __('License %s was removed because of an invalid domain!', 'wp-statistics'),
                        $licenseKey
                    ), 'license_invalid_domain');
                } else if (stripos($e->getMessage(), 'expired') !== false) {
                    Notice::addNotice(sprintf(
                        // translators: %s: License key.
                        __('License %s was removed because it was expired!', 'wp-statistics'),
                        $licenseKey
                    ), 'license_expired');
                } else {
                    Notice::addNotice(sprintf(
                        // translators: 1: License key - 2: Error message.
                        __('License %s was removed because of an error: %s', 'wp-statistics'),
                        $licenseKey,
                        $e->getMessage()
                    ), 'license_error');
                }
            }
        }
    }

    /**
     * Returns the licenses stored in the WordPress database.
     *
     * @return array
     */
    public function getStoredLicenses()
    {
        return get_option($this->licensesOption, []);
    }

    /**
     * Store license details in the WordPress database.
     *
     * @param string $licenseKey
     * @param object $license
     */
    public function storeLicense($licenseKey, $license)
    {
        // Get current licenses
        $currentLicenses = $this->getStoredLicenses();

        // Store the new license with its details and product slugs
        $currentLicenses[$licenseKey] = [
            'license'  => $license->license_details,
            'products' => wp_list_pluck($license->products, 'slug'),
        ];

        update_option($this->licensesOption, $currentLicenses);
    }

    /**
     * Removes a license from WordPress database.
     *
     * @param string $licenseKey
     *
     * @return void
     */
    public function removeLicense($licenseKey)
    {
        $currentLicenses = $this->getStoredLicenses();

        if (isset($currentLicenses[$licenseKey])) {
            unset($currentLicenses[$licenseKey]);
        }

        update_option($this->licensesOption, $currentLicenses);
    }

    /**
     * Returns the first validated license key that contains the add-on with the given slug.
     *
     * @param string $slug
     *
     * @return string|null License key. `null` if no valid licenses was found for this slug.
     */
    public function getValidLicenseForProduct($slug)
    {
        foreach ($this->getStoredLicenses() as $key => $license) {
            if (empty($license) || empty($license['products']) || !is_array($license['products'])) {
                continue;
            }

            if (in_array($slug, $license['products'])) {
                return $key;
            }
        }

        return null;
    }

    /**
     * Merge the product list with the status from the license.
     *
     * @param string $licenseKey
     * @param bool $skipPremium Skip "WP Statistics Premium" from displaying in the list.
     *
     * @return array Merged product list with status
     *
     * @throws Exception
     */
    public function mergeProductStatusWithLicense($licenseKey, $skipPremium = true)
    {
        // Get the list of products
        $productList = $this->getProductList($skipPremium);

        // Get the license status
        $licenseStatus = $this->validateLicense($licenseKey);

        // Merge product list with license status and return the result
        return $this->productDecorator->decorateProductsWithLicense($productList, $licenseStatus->products);
    }

    /**
     * Merges the product list with the status from all validated license.
     *
     * @param bool $skipPremium Skip "WP Statistics Premium" from displaying in the list.
     *
     * @return ProductDecorator[]
     *
     * @throws Exception
     */
    public function mergeProductsListWithAllValidLicenses($skipPremium = true)
    {
        // Get the list of all products
        $productList = $this->getProductList($skipPremium);

        // Make a list of licensed products (retrieved from license status calls)
        $licensedProducts = [];

        // Loop through the array keys (the actual license keys) and merge the validated products
        foreach (array_keys($this->getStoredLicenses()) as $license) {
            // Get current license status
            $licenseStatus    = $this->validateLicense($license);
            $licensedProducts = array_merge($licensedProducts, $licenseStatus->products);
        }

        // Merge the new list with all products and return the result
        return $this->productDecorator->decorateProductsWithLicense($productList, $licensedProducts);
    }

    /**
     * Get the download URL for a specific plugin slug from the license status.
     *
     * @param string $licenseKey
     * @param string $pluginSlug
     *
     * @return string|null The download URL if found, null otherwise
     * @throws Exception
     */
    public function getPluginDownloadUrl($licenseKey, $pluginSlug)
    {
        // Validate the license and get the licensed products
        $licenseStatus = $this->validateLicense($licenseKey);

        // Search for the download URL in the licensed products
        foreach ($licenseStatus->products as $product) {
            if ($product->slug === $pluginSlug) {
                return $product->download_url ?? null;
            }
        }

        return null;
    }
}
