<?php

namespace WP_Statistics\Service\Admin\LicenseManagement\Plugin;

use Exception;
use WP_Statistics\Service\Admin\LicenseManagement\ApiCommunicator;
use WP_Statistics\Service\Admin\LicenseManagement\LicenseHelper;

class PluginHelper
{
    /**
     * Returns a decorated list of plugins (add-ons) from API, excluding bundled plugins.
     *
     * @return PluginDecorator[] List of plugins
     */
    public static function getPlugins()
    {
        $result = [];

        try {
            $apiCommunicator    = new ApiCommunicator();
            $products           = $apiCommunicator->getProducts();
        } catch (Exception $e) {
            WP_Statistics()->log($e->getMessage(), 'error');
            $products = [];
        }

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
     * Get all plugins for a given license key or all stored licenses.
     *
     * @param string $licenseKey Optional license key to get purchased plugins for.
     *
     * @return PluginDecorator[] List of purchased plugins.
     */
    public static function getLicensedPlugins($licenseKey = false)
    {
        $result  = [];
        $plugins = [];

        if ($licenseKey) {
            $license = LicenseHelper::getLicenseInfo($licenseKey);
            $plugins = $license && $license['status'] === 'valid' ? $license['products'] : [];
        } else {
            $licenses = LicenseHelper::getLicenses();

            foreach ($licenses as $license => $data) {
                if (empty($data['products'])) continue;

                $plugins = array_merge($plugins, $data['products']);
            }
        }

        if (empty($plugins)) return [];

        foreach ($plugins as $plugin) {
            $result[$plugin] = self::getPluginBySlug($plugin);
        }

        return $result;
    }
}