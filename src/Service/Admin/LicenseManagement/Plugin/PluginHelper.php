<?php

namespace WP_Statistics\Service\Admin\LicenseManagement\Plugin;

use Exception;
use WP_Statistics\Service\Admin\LicenseManagement\ApiCommunicator;
use WP_Statistics\Service\Admin\LicenseManagement\LicenseHelper;

class PluginHelper
{
    /**
     * Returns a decorated list of plugins (add-ons), excluding bundled plugins.
     *
     * @return PluginDecorator[] List of plugins
     */
    public static function getPlugins()
    {
        $apiCommunicator = new ApiCommunicator();

        $result     = [];
        $products   = $apiCommunicator->getProducts();

        foreach ($products as $product) {
            if (isset($product->sku) && $product->sku === 'premium') continue;

            $result[] = new PluginDecorator($product);
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
    public static function getPluginBySlug($slug)
    {
        $plugins = self::getPlugins();

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
    public static function getPurchasedPlugins($licenseKey = false)
    {
        $apiCommunicator    = new ApiCommunicator();

        $result  = [];
        $plugins = [];

        try {
            if ($licenseKey) {
                $licenseStatus  = $apiCommunicator->validateLicense($licenseKey);
                $plugins        = $licenseStatus->products;
            } else {
                $licenses = LicenseHelper::getLicenses();

                foreach ($licenses as $license => $data) {
                    $licenseStatus  = $apiCommunicator->validateLicense($license);
                    $plugins        = array_merge($plugins, $licenseStatus->products);
                }
            }
        } catch (Exception $e) {
            WP_Statistics()->log($e->getMessage(), 'error');
            $plugins = [];
        }

        if (empty($plugins)) return [];

        foreach ($plugins as $plugin) {
            $result[$plugin->slug] = self::getPluginBySlug($plugin->slug);
        }

        return $result;
    }

    public static function isPluginPurchased($slug)
    {
        $purchasedPlugins = self::getPurchasedPlugins();

        foreach ($purchasedPlugins as $plugin) {
            if ($plugin->getSlug() === $slug) return true;
        }

        return false;
    }
}