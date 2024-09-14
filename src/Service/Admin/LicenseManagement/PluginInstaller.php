<?php

namespace WP_Statistics\Service\Admin\LicenseManagement;

class PluginInstaller
{
    private $pluginUrl;
    private $pluginSlug;

    public function __construct($pluginUrl, $pluginSlug)
    {
        $this->pluginUrl  = $pluginUrl;
        $this->pluginSlug = $pluginSlug;
    }

    /**
     * Downloads and installs the plugin.
     *
     * @return void
     *
     * @throws \Exception
     */
    public function downloadAndInstallPlugin()
    {
        if (!current_user_can('install_plugins')) {
            throw new \Exception(__('You do not have permission to install plugins.', 'wp-statistics'));
        }

        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';

        // Download the plugin zip file
        $downloadFile = download_url($this->pluginUrl);
        if (is_wp_error($downloadFile)) {
            throw new \Exception(__('Failed to download the plugin.', 'wp-statistics'));
        }

        // Prepare for unpacking the plugin
        $skin           = new \Automatic_Upgrader_Skin();
        $pluginUpgrader = new \Plugin_Upgrader($skin);
        $installResult  = $pluginUpgrader->install($downloadFile);

        // Cleanup downloaded file
        @unlink($downloadFile);

        if (is_wp_error($installResult)) {
            throw new \Exception(__('Failed to install the plugin.', 'wp-statistics'));
        }

        return $installResult;
    }

    /**
     * Returns plugin file path based on the slug.
     *
     * @param string $pluginSlug
     *
     * @return string
     *
     * @throws \Exception
     */
    private function getPluginFile($pluginSlug)
    {
        $pluginDir = path_join(WP_PLUGIN_DIR, $pluginSlug);
        if (is_dir($pluginDir)) {
            $pluginFiles = scandir($pluginDir);
            foreach ($pluginFiles as $file) {
                if (strpos($file, '.php') !== false) {
                    return path_join($pluginSlug, $file);
                }
            }
        }

        throw new \Exception(__('Plugin directory not found.', 'wp-statistics'));
    }

    /**
     * Activate the plugin.
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function activatePlugin()
    {
        $pluginFile = $this->getPluginFile($this->pluginSlug);

        if ($pluginFile && !is_plugin_active($pluginFile)) {
            $activateResult = activate_plugin($pluginFile);
            if (is_wp_error($activateResult)) {
                throw new \Exception(__('Failed to activate the plugin.', 'wp-statistics'));
            }
        } else {
            throw new \Exception(__('Plugin not found or already active.', 'wp-statistics'));
        }

        return true;
    }
}
