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
    private $licensesOption = 'wp_statistics_licenses'; // Option key to store licenses

    /**
     * Get the list of products (add-ons) from the API and cache it for 1 week.
     *
     * @return array
     * @throws Exception if there is an error with the API call
     */
    public function getRemotePlugins()
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
     * Returns a decorated list of plugins (add-ons)
     *
     * @return PluginDecorator[] List of plugins
     */
    public function getPlugins()
    {
        $result  = [];
        $plugins = $this->getRemotePlugins();

        foreach ($plugins as $plugin) {
            if ($plugin->sku === 'premium') continue;

            $result[] = new PluginDecorator($plugin);
        }

        return $result;
    }

    /**
     * Retrieve plugin info by slug.
     *
     * @param string $slug Plugin slug.
     *
     * @return PluginDecorator|null Plugin object if found, null otherwise.
     */
    public function getPluginBySlug($slug)
    {
        $plugins = $this->getPlugins();

        foreach ($plugins as $plugin) {
            if ($plugin->getSlug() === $slug) return $plugin;
        }

        return null;
    }

    /**
     * Get all purchased plugins for a given license key or all stored licenses.
     *
     * @param string $licenseKey Optional license key to get purchased plugins for.
     *
     * @return PluginDecorator[] List of purchased plugins.
     */
    public function getPurchasedPlugins($licenseKey = false)
    {
        $result  = [];
        $plugins = [];

        if ($licenseKey) {
            $licenseStatus  = $this->validateLicense($licenseKey);
            $plugins        = $licenseStatus->products;
        } else {
            foreach ($this->getStoredLicenses() as $license => $data) {
                $licenseStatus  = $this->validateLicense($license);
                $plugins        = array_merge($plugins, $licenseStatus->products);
            }
        }

        if (empty($purchasedPlugins)) return [];

        foreach ($purchasedPlugins as $plugin) {
            $result[] = self::getPluginBySlug($plugin);
        }

        return $result;
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

            /**
             * @todo and important: Ensure that we throw an exception to prevent usage of licenses that are not related to the requested add-on.
             * @note And we need to get it from front-end, also not sure this is **the correct place** since this method is uses in different places.
             * Then uncomment the Exception
             */
            $requestedAddOn = Request::get('slug'); // e.g. wp-statistics-data-plus
            $productSlugs   = array_column($licenseData->products, 'slug');

            if (!in_array($requestedAddOn, $productSlugs, true)) {
                //throw new Exception(sprintf(__('The license is not related to the requested add-on <b>%s</b>.', 'wp-statistics'), $requestedAddOn));
            }

        } catch (Exception $e) {
            throw new Exception(
            // translators: %s: Error message.
                sprintf(__('Error: %s', 'wp-statistics'), $e->getMessage())
            );
        }

        // Store the license in the database
        $this->storeLicense($licenseKey, $licenseData);

        return $licenseData;
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
    public function getValidLicenseForPlugin($slug)
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

    /**
     * Checks whether the given license is premium or not.
     *
     * @param string $licenseKey
     *
     * @return bool
     */
    public function isLicensePremium($licenseKey)
    {
        try {
            $licenseData = $this->validateLicense($licenseKey);

            if (!empty($licenseData->license_details->sku) && $licenseData->license_details->sku === 'premium') {
                return true;
            }
        } catch (Exception $e) {
        }

        return false;
    }

    /**
     * Checks all stored licenses to see if any of them is premium.
     *
     * @return string|null License key. `null` if no premium licenses were found.
     */
    public function userHasPremiumLicense()
    {
        foreach ($this->getStoredLicenses() as $key => $license) {
            if (empty($license) || empty($license['license'])) {
                continue;
            }

            if (!empty($license['license']->sku) && $license['license']->sku === 'premium') {
                return $key;
            }
        }

        return null;
    }

    /**
     * Checks if user has any license or not.
     *
     * @return bool
     */
    public function userHasLicense()
    {
        return !empty($this->getStoredLicenses());
    }
}
