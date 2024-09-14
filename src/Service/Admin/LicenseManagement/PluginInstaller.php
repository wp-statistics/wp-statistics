<?php

namespace WP_Statistics\Service\Admin\LicenseManagement;

use Exception;
use Plugin_Upgrader;

class PluginInstaller
{
    private $pluginUrl;
    private $pluginSlug;

    // Constructor to initialize the plugin URL and slug
    public function __construct($pluginUrl, $pluginSlug)
    {
        $this->pluginUrl  = $pluginUrl;
        $this->pluginSlug = $pluginSlug;
    }

    // Main function to download and install the plugin
    public function downloadAndInstallPlugin()
    {
        if (!current_user_can('install_plugins')) {
            throw new Exception(__('You do not have permission to install plugins.'));
        }

        include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
        include_once ABSPATH . 'wp-admin/includes/file.php';

        // Download the plugin zip file
        $downloadFile = download_url($this->pluginUrl);
        if (is_wp_error($downloadFile)) {
            throw new Exception(__('Failed to download the plugin.'));
        }

        // Prepare for unpacking the plugin
        $pluginUpgrader = new Plugin_Upgrader();
        $installResult  = $pluginUpgrader->install($downloadFile);

        // Cleanup downloaded file
        @unlink($downloadFile);

        if (is_wp_error($installResult)) {
            throw new Exception(__('Failed to install the plugin.'));
        }

        return $installResult;
    }

    // Activate the plugin after installation
    public function activatePlugin()
    {
        $pluginFile = $this->getPluginFile($this->pluginSlug);

        if ($pluginFile && !is_plugin_active($pluginFile)) {
            $activateResult = activate_plugin($pluginFile);
            if (is_wp_error($activateResult)) {
                throw new Exception(__('Failed to activate the plugin.'));
            }
        } else {
            throw new Exception(__('Plugin not found or already active.'));
        }

        return true;
    }

    // Get the plugin file path based on plugin slug
    private function getPluginFile($pluginSlug)
    {
        $pluginDir = WP_PLUGIN_DIR . '/' . $pluginSlug;
        if (is_dir($pluginDir)) {
            $pluginFiles = scandir($pluginDir);
            foreach ($pluginFiles as $file) {
                if (strpos($file, '.php') !== false) {
                    return $pluginSlug . '/' . $file;
                }
            }
        }
        throw new Exception(__('Plugin directory not found.'));
    }
}
