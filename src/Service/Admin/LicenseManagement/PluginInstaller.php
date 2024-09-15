<?php

namespace WP_Statistics\Service\Admin\LicenseManagement;

/**
 * Helper class that handles plugin download, install, etc.
 */
class PluginInstaller
{
    private $pluginSlug;

    /**
     * @param string $pluginSlug
     */
    public function __construct($pluginSlug)
    {
        if (empty($pluginSlug)) {
            throw new \Exception(__('Plugin slug is empty!', 'wp-statistics'));
        }

        $this->pluginSlug = $pluginSlug;
    }

    /**
     * Downloads and installs the plugin.
     *
     * @param string $pluginUrl
     *
     * @return void
     *
     * @throws \Exception
     */
    public function downloadAndInstallPlugin($pluginUrl)
    {
        if (empty($pluginUrl)) {
            throw new \Exception(__('Download URL is empty!', 'wp-statistics'));
        }

        if (!current_user_can('install_plugins')) {
            throw new \Exception(__('You do not have permission to install plugins.', 'wp-statistics'));
        }

        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';

        // Download the plugin zip file
        $downloadFile = download_url($pluginUrl);
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
     * Returns plugin file path.
     *
     * @return string
     *
     * @throws \Exception
     */
    private function getPluginFile()
    {
        $pluginDir = path_join(WP_PLUGIN_DIR, $this->pluginSlug);
        if (is_dir($pluginDir)) {
            $pluginFiles = scandir($pluginDir);
            foreach ($pluginFiles as $file) {
                if (strpos($file, '.php') !== false) {
                    return path_join($this->pluginSlug, $file);
                }
            }
        }

        throw new \Exception(__('Plugin directory not found.', 'wp-statistics'));
    }

    /**
     * Check if the plugin is active?
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function isPluginActive()
    {
        $pluginFile = $this->getPluginFile($this->pluginSlug);

        return $pluginFile && is_plugin_active($pluginFile);
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
        if ($this->isPluginActive()) {
            throw new \Exception(__('Plugin already active.', 'wp-statistics'));
        }

        $pluginFile = $this->getPluginFile($this->pluginSlug);
        if (!$pluginFile) {
            throw new \Exception(__('Plugin not found.', 'wp-statistics'));
        }

        $activateResult = activate_plugin($pluginFile);
        if (is_wp_error($activateResult)) {
            throw new \Exception(__('Failed to activate the plugin.', 'wp-statistics'));
        }

        return true;
    }

    /**
     * Returns plugin's full metadata.
     *
     * @return array
     *
     * @throws \Exception
     */
    public function getPluginData()
    {
        $pluginFile = $this->getPluginFile($this->pluginSlug);
        if (!$pluginFile) {
            throw new \Exception(__('Plugin not found.', 'wp-statistics'));
        }

        if (!function_exists('get_plugin_data')) {
            require_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }

        return get_plugin_data($pluginFile);
    }
}
