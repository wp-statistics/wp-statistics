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
     * Set when the remote add-on list could not be loaded; no new fetch is tried while it exists.
     */
    const FETCH_FAILED_TRANSIENT = 'wp_statistics_addons_list_failed';

    /**
     * How long a failed fetch of the add-on list is remembered.
     */
    const FETCH_FAILED_DURATION = HOUR_IN_SECONDS;

    /**
     * Forget a failed fetch, so the next call asks the server again ("Try again", Downloads tab).
     */
    public static function clearFetchFailure()
    {
        delete_transient(self::FETCH_FAILED_TRANSIENT);
    }

    /**
     * Returns a decorated list of plugins (add-ons) from API, excluding bundled plugins.
     *
     * @return PluginDecorator[] List of plugins
     */
    public static function getRemotePlugins()
    {
        $result   = [];
        $products = [];

        // A failed fetch is remembered for an hour, so blocked outbound requests cost one try
        // and one log line per hour instead of several per admin page. The saved list fills in meanwhile.
        if (!get_transient(self::FETCH_FAILED_TRANSIENT)) {
            try {
                $apiCommunicator = new ApiCommunicator();
                $products        = $apiCommunicator->getProducts();
            } catch (Exception $e) {
                set_transient(self::FETCH_FAILED_TRANSIENT, time(), self::FETCH_FAILED_DURATION);
                WP_Statistics()->log($e->getMessage(), 'error');
                $products = [];
            }
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

    /**
     * Is any add-on covered by a saved valid license still not installed?
     * When every licensed add-on is already on the site there is nothing for the Download step to do.
     *
     * @return bool
     */
    public static function hasAddonsToDownload()
    {
        $handler = new PluginHandler();

        foreach (array_unique(self::getLicensedPlugins()) as $slug) {
            if (!is_string($slug) || !isset(self::$plugins[$slug])) continue;

            if (!$handler->isPluginInstalled($slug)) return true;
        }

        return false;
    }
}
