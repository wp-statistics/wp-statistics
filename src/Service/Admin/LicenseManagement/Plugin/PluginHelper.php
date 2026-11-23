<?php

namespace WP_Statistics\Service\Admin\LicenseManagement\Plugin;

use Exception;
use WP_Statistics\Service\Admin\LicenseManagement\ApiCommunicator;
use WP_Statistics\Service\Admin\LicenseManagement\LicenseHelper;

class PluginHelper
{
    /**
     * Hard-coded list of all plugins, useful when we don't want to request the API.
     */
    public static $plugins = [
        'wp-statistics-data-plus'          => 'Data Plus',
        'wp-statistics-marketing'          => 'Marketing',
        'wp-statistics-mini-chart'         => 'Mini Chart',
        'wp-statistics-advanced-reporting' => 'Advanced Reporting',
        'wp-statistics-realtime-stats'     => 'Real-Time Stats',
        'wp-statistics-widgets'            => 'Widgets',
        'wp-statistics-customization'      => 'Customization',
        'wp-statistics-rest-api'           => 'REST API'
    ];

    /**
     * Returns a decorated list of plugins (add-ons) from API, excluding bundled plugins.
     *
     * @return PluginDecorator[] List of plugins
     */
    public static function getRemotePlugins()
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
     * The 8 add-ons as saved in the plugin, for when the remote list can't be loaded (no connection,
     * blocked outbound requests, API down). Entries have no price, version, download or changelog,
     * so views show "Learn more" only. See {@see PluginDecorator::isBundled()}.
     *
     * @return PluginDecorator[]
     */
    public static function getBundledPlugins()
    {
        $result = [];

        foreach (AddonCatalog::all() as $slug => $copy) {
            $result[] = new PluginDecorator((object)[
                'id'                => 0,
                'slug'              => $slug,
                'name'              => $copy['name'],
                'description'       => $copy['short'],
                'short_description' => $copy['short'],
                'thumbnail'         => '',
                'price'             => '',
                'label'             => '',
                'version'           => '',
                'changelog_url'     => '',
                'changelog'         => [],
                'product_url'       => WP_STATISTICS_SITE_URL . '/add-ons/' . $slug . '/',
                'documentation_url' => '',
                'bundled'           => true,
            ]);
        }

        return $result;
    }

    /**
     * Remote add-on list, or the saved list when the remote one is empty.
     *
     * @param bool $isFallback Set to true when the saved list is returned.
     *
     * @return PluginDecorator[]
     */
    public static function getPluginsWithFallback(&$isFallback = false)
    {
        $plugins    = self::getRemotePlugins();
        $isFallback = empty($plugins);

        return $isFallback ? self::getBundledPlugins() : $plugins;
    }

    /**
     * Retrieve plugin info by slug.
     *
     * @param string $slug Plugin slug.
     *
     * @return PluginDecorator|null Plugin object if found, null otherwise.
     */
    public static function getRemotePluginBySlug($slug)
    {
        $plugins = self::getRemotePlugins();

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
            $result[] = $plugin;
        }

        return $result;
    }
}