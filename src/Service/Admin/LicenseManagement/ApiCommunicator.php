<?php

namespace WP_Statistics\Service\Admin\LicenseManagement;

use Exception;
use WP_Statistics\Utils\Request;
use WP_Statistics\Components\RemoteRequest;
use WP_Statistics\Traits\TransientCacheTrait;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;
use WP_Statistics\Service\Admin\LicenseManagement\Plugin\PluginDecorator;

class ApiCommunicator
{
    use TransientCacheTrait;

    private $apiUrl = 'https://staging.wp-statistics.veronalabs.com/wp-json/wp-license-manager/v1';

    /**
     * Get the list of products (add-ons) from the API and cache it for 1 week.
     *
     * @return array
     * @throws Exception if there is an error with the API call
     */
    public function getProducts()
    {
        try {
            $remoteRequest  = new RemoteRequest("{$this->apiUrl}/product/list", 'GET');
            $plugins        = $remoteRequest->execute(false, true, WEEK_IN_SECONDS);

            if (empty($plugins) || !is_array($plugins)) {
                throw new Exception(__('Product list is empty!', 'wp-statistics'));
            }

        } catch (Exception $e) {
            throw new Exception(
            // translators: %s: Error message.
                sprintf(__('Error fetching product list: %s', 'wp-statistics'), $e->getMessage())
            );
        }

        return $plugins;
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
    public function getDownloadUrl($licenseKey, $pluginSlug)
    {
        $remoteRequest = new RemoteRequest("{$this->apiUrl}/product/download", 'GET', [
            'license_key' => $licenseKey,
            'domain'      => home_url(),
            'plugin_slug' => $pluginSlug,
        ]);

        return $remoteRequest->execute(true, true, DAY_IN_SECONDS);
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
    public function getDownloadUrlFromLicense($licenseKey, $pluginSlug)
    {
        // Validate the license and get the licensed products
        $licenseStatus = $this->validateLicense($licenseKey, $pluginSlug);

        // Search for the download URL in the licensed products
        foreach ($licenseStatus->products as $product) {
            if ($product->slug === $pluginSlug) {
                return $product->download_url ?? null;
            }
        }

        return null;
    }

    /**
     * Validate the license and get the status of licensed products.
     *
     * @param string $licenseKey
     * @param string $product Optional param to check whether the license is valid for a particular product, or not
     *
     * @return object License status
     * @throws Exception if the API call fails
     */
    public function validateLicense($licenseKey, $product = false)
    {
        try {
            $remoteRequest = new RemoteRequest("{$this->apiUrl}/license/status", 'GET', [
                'license_key' => $licenseKey,
                'domain'      => home_url(),
            ]);

            $licenseData = $remoteRequest->execute(false, false);

            if (empty($licenseData)) {
                throw new Exception(__('Invalid license response!', 'wp-statistics'));
            }

            if (empty($licenseData->license_details)) {
                throw new Exception(!empty($licenseData->message) ? $licenseData->message : __('Unknown error!', 'wp-statistics'));
            }


            if (!empty($product)) {
                $productSlugs = array_column($licenseData->products, 'slug');

                if (!in_array($product, $productSlugs, true)) {
                    throw new Exception(sprintf(__('The license is not related to the requested add-on <b>%s</b>.', 'wp-statistics'), $product));
                }
            }

        } catch (Exception $e) {
            throw new Exception(
            // translators: %s: Error message.
                sprintf(__('Error: %s', 'wp-statistics'), $e->getMessage())
            );
        }

        LicenseHelper::storeLicense($licenseKey, $licenseData);

        return $licenseData;
    }
}
