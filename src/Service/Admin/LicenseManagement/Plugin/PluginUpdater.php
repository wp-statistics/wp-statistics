<?php

namespace WP_Statistics\Service\Admin\LicenseManagement\Plugin;

use Exception;
use WP_Statistics;
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
    private $pluginVersion;
    private $licenseKey;

    /**
     * PluginUpdater constructor.
     * Initializes the class properties and sets up necessary WordPress hooks.
     *
     * @param string $pluginSlug
     * @param string $pluginVersion
     * @param string $licenseKey
     */
    public function __construct($pluginSlug, $pluginVersion, $licenseKey)
    {
        $this->pluginSlug    = $pluginSlug;
        $this->pluginVersion = $pluginVersion;
        $this->licenseKey    = $licenseKey;

        // Hooks to check for updates
        add_filter('plugins_api', [$this, 'pluginsApiInfo'], 20, 3);
        add_filter('site_transient_update_plugins', [$this, 'checkForUpdate']);
        add_action('upgrader_process_complete', [$this, 'clearCache'], 10, 2);
    }

    /**
     * Fetch the version information from the API using ApiCommunicator and handle exceptions.
     *
     * @return object|false
     */
    private function requestUpdateInfo()
    {
        try {

            $result = new ApiCommunicator();
            $remote = $result->getDownload($this->licenseKey, $this->pluginSlug);

        } catch (Exception $e) {
            WP_Statistics::log($e->getMessage());
            return false;
        }

        return $remote;
    }

    /**
     * Handle the plugins_api call, returning version information when requested.
     *
     * @param mixed $res
     * @param string $action
     * @param object $args
     * @return mixed
     */
    public function pluginsApiInfo($res, $action, $args)
    {
        // Return if the current action is not related to plugin info or if it's not our plugin
        if ($action !== 'plugin_information' || $this->pluginSlug !== $args->slug) {
            return $res;
        }

        $remote = $this->requestUpdateInfo();

        if (!$remote) {
            return $res;
        }

        $res                 = new \stdClass();
        $res->name           = $remote->name;
        $res->slug           = $remote->slug;
        $res->version        = $remote->version;
        $res->tested         = $remote->tested;
        $res->requires       = $remote->requires;
        $res->author         = $remote->author;
        $res->author_profile = $remote->author_profile;
        $res->download_link  = $remote->download_url;
        $res->requires_php   = $remote->requires_php;
        $res->last_updated   = $remote->last_updated;

        // Sections such as description, installation, changelog
        $res->sections = [
            'description'  => $remote->sections->description,
            'installation' => $remote->sections->installation,
            'changelog'    => $remote->sections->changelog,
        ];

        // Banners (if provided)
        if (!empty($remote->banners)) {
            $res->banners = [
                'low'  => $remote->banners->low,
                'high' => $remote->banners->high,
            ];
        }

        return $res;
    }

    /**
     * Check if an update is available by comparing versions.
     *
     * @param object $transient
     * @return object
     */
    public function checkForUpdate($transient)
    {
        if (empty($transient->checked)) {
            return $transient;
        }

        $remote = $this->requestUpdateInfo();

        if ($remote && version_compare($this->pluginVersion, $remote->version, '<')) {
            $res              = new \stdClass();
            $res->slug        = $this->pluginSlug;
            $res->plugin      = plugin_basename(__FILE__);
            $res->new_version = $remote->version;
            $res->tested      = $remote->tested;
            $res->package     = $remote->download_url;

            $transient->response[$res->plugin] = $res;
        }

        return $transient;
    }

    /**
     * Clear cache after the plugin upgrade process completes.
     *
     * @param \WP_Upgrader $upgrader
     * @param array $options
     */
    public function clearCache($upgrader, $options)
    {
        if ('update' === $options['action'] && 'plugin' === $options['type']) {
            //delete_transient(); // todo
        }
    }
}
