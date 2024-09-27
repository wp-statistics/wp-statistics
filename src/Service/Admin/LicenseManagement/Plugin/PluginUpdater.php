<?php

namespace WP_Statistics\Service\Admin\LicenseManagement\Plugin;

use Exception;
use WP_Statistics\Service\Admin\LicenseManagement\ApiCommunicator;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Class PluginUpdater
 *
 * Handles updating WP Statistics add-ons by fetching the latest version information from a remote API
 * and integrating it with the WordPress plugin update system.
 */
class PluginUpdater
{
    private $pluginSlug;
    private $pluginName;
    private $pluginVersion;
    private $licenseKey;
    private $apiUrl;
    private $domain;
    private $transientCacheKey;

    /**
     * PluginUpdater constructor.
     * Initializes the class properties and sets up necessary WordPress hooks.
     *
     * @param string $pluginSlug
     * @param string $pluginName
     * @param string $pluginVersion
     * @param string $licenseKey
     * @param string $apiUrl
     * @param string $domain
     */
    public function __construct($pluginSlug, $pluginName, $pluginVersion, $licenseKey, $apiUrl, $domain)
    {
        $this->pluginSlug        = $pluginSlug;
        $this->pluginName        = $pluginName;
        $this->pluginVersion     = $pluginVersion;
        $this->licenseKey        = $licenseKey;
        $this->apiUrl            = $apiUrl;
        $this->domain            = $domain;
        $this->transientCacheKey = 'plugin_updater_' . $pluginSlug;

        // Hooks to check for updates
        add_filter('pre_set_site_transient_update_plugins', [$this, 'checkForUpdate']);
        add_filter('plugins_api', [$this, 'pluginsApiHandler'], 10, 3);
        add_filter('plugin_row_meta', [$this, 'pluginRowNotice'], 10, 2);
        add_action("after_plugin_row_{$pluginSlug}/{$pluginSlug}.php", [$this, 'afterPluginRowNotice'], 10, 2); // Plugin row notice
    }

    /**
     * Check if an update is available by calling the API.
     * Adds the new version information to the plugin update transient if an update is available.
     *
     * @param object $transient
     * @return object
     */
    public function checkForUpdate($transient)
    {
        if (empty($transient->checked)) {
            return $transient;
        }

        $remoteInfo = $this->getCachedVersionInfo();

        if ($remoteInfo && version_compare($this->pluginVersion, $remoteInfo->version, '<')) {
            $transient->response[$this->pluginSlug] = (object)[
                'slug'        => $this->pluginSlug,
                'plugin'      => $this->pluginName,
                'new_version' => $remoteInfo->version,
                'package'     => $remoteInfo->download_url,
                'tested'      => $remoteInfo->tested,
                'requires'    => $remoteInfo->requires,
            ];
        } else {
            $transient->no_update[$this->pluginSlug] = (object)[
                'slug'        => $this->pluginSlug,
                'plugin'      => $this->pluginName,
                'new_version' => $this->pluginVersion,
            ];
        }

        return $transient;
    }

    /**
     * Fetch the version information from the API using the license management service.
     *
     * @return object|false
     * @throws Exception
     */
    private function getVersionInfo()
    {
        $result = new ApiCommunicator();
        return $result->getDownload($this->licenseKey, $this->pluginSlug);
    }

    /**
     * Cache the version information using transients.
     * Fetches fresh data from the API if the cache is expired or not available.
     *
     * @return object|false
     */
    private function getCachedVersionInfo()
    {
        $cachedInfo = get_transient($this->transientCacheKey);

        if (false === $cachedInfo) {
            $remoteInfo = $this->getVersionInfo();
            if (!$remoteInfo) {
                return false;
            }
            set_transient($this->transientCacheKey, $remoteInfo, 12 * HOUR_IN_SECONDS);
            return $remoteInfo;
        }

        return $cachedInfo;
    }

    /**
     * Handle the plugins_api call, returning version information when requested.
     *
     * @param mixed $result
     * @param string $action
     * @param object $args
     * @return mixed
     */
    public function pluginsApiHandler($result, $action, $args)
    {
        if ('plugin_information' !== $action) {
            return $result;
        }

        if ($args->slug !== $this->pluginSlug) {
            return $result;
        }

        $remoteInfo = $this->getCachedVersionInfo();

        if (!$remoteInfo) {
            return $result;
        }

        $result = (object)[
            'name'          => $remoteInfo->name,
            'slug'          => $this->pluginSlug,
            'version'       => $remoteInfo->version,
            'author'        => $remoteInfo->author,
            'homepage'      => $remoteInfo->homepage,
            'requires'      => $remoteInfo->requires,
            'tested'        => $remoteInfo->tested,
            'download_link' => $remoteInfo->download_url,
            'sections'      => [
                'description' => $remoteInfo->description,
            ],
            'banners'       => $remoteInfo->banners,
            'icons'         => $remoteInfo->icons,
        ];

        return $result;
    }

    /**
     * Display a notice in the plugin row when a new version is available.
     * This method is hooked into the plugin row meta filter.
     *
     * @param array $pluginMeta
     * @param string $pluginFile
     * @return array
     */
    public function pluginRowNotice($pluginMeta, $pluginFile)
    {
        $slug = $this->pluginSlug . '/' . $this->pluginSlug . '.php';

        if ($pluginFile !== $slug) {
            return $pluginMeta;
        }

        $remoteInfo = $this->getCachedVersionInfo();

        if ($remoteInfo && version_compare($this->pluginVersion, $remoteInfo->version, '<')) {
            $pluginMeta[] = sprintf(
                '<strong><span style="color: #ff0000;">%s</span></strong>',
                esc_html__('A new version is available!', 'wp-statistics')
            );
        }

        return $pluginMeta;
    }

    /**
     * Display a custom message after the plugin row, indicating that a new version is available.
     * This method is hooked into the after_plugin_row_{$plugin_file} action.
     *
     * @param string $pluginFile
     * @param array $pluginData
     */
    public function afterPluginRowNotice($pluginFile, $pluginData)
    {
        $slug = $this->pluginSlug . '/' . $this->pluginSlug . '.php';

        if ($pluginFile !== $slug) {
            return;
        }

        $remoteInfo = $this->getCachedVersionInfo();

        if ($remoteInfo && version_compare($this->pluginVersion, $remoteInfo->version, '<')) {
            echo '<tr class="plugin-update-tr">
                <td colspan="3" class="plugin-update colspanchange">
                    <div class="update-message notice notice-warning">
                        <p>' . sprintf(
                    esc_html__('A new version of %s is available! Please update to version %s.', 'wp-statistics'),
                    esc_html($this->pluginName),
                    esc_html($remoteInfo->version)
                ) . '</p>
                    </div>
                </td>
            </tr>';
        }
    }
}
