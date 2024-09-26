<?php

namespace WP_Statistics\Service\Admin\LicenseManagement\Plugin;

/**
 * Helper class that handles plugin download, install, etc.
 */
class PluginHandler
{
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
            // translators: %s: Error message.
            throw new \Exception(sprintf(__('Failed to download the plugin: %s', 'wp-statistics'), $downloadFile->get_error_message()));
        }

        // Prepare for unpacking the plugin
        $pluginUpgrader = new \Plugin_Upgrader(new \Plugin_Installer_Skin());
        $installResult  = $pluginUpgrader->install($downloadFile);

        // Cleanup downloaded file
        @unlink($downloadFile);

        if (is_wp_error($installResult)) {
            // translators: %s: Error message.
            throw new \Exception(sprintf(__('Failed to install the plugin: %s', 'wp-statistics'), $installResult->get_error_message()));
        }

        return $installResult;
    }

    /**
     * Returns plugin file path.
     *
     * @param string $pluginSlug
     *
     * @return string
     *
     * @throws \Exception
     */
    public function getPluginFile($pluginSlug)
    {
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        foreach (get_plugins() as $pluginFile => $pluginInfo) {
            // Return this plugin if the folder name matches the input slug
            if (explode('/', $pluginFile)[0] == $pluginSlug) {
                return trailingslashit(WP_PLUGIN_DIR) . $pluginFile;
            }
        }

        throw new \Exception(__('Plugin not found.', 'wp-statistics'));
    }

    /**
     * Checks if the plugin is active?
     *
     * @param string $pluginSlug
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function isPluginActive($pluginSlug)
    {
        $pluginFile = $this->getPluginFile($pluginSlug);

        return $pluginFile && is_plugin_active(plugin_basename($pluginFile));
    }

    /**
     * Activates the plugin.
     *
     * @param string $pluginSlug
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function activatePlugin($pluginSlug)
    {
        if ($this->isPluginActive($pluginSlug)) {
            throw new \Exception(__('Plugin already active.', 'wp-statistics'));
        }

        $pluginFile = $this->getPluginFile($pluginSlug);
        if (!$pluginFile) {
            throw new \Exception(__('Plugin not found.', 'wp-statistics'));
        }

        $activateResult = activate_plugin($pluginFile);
        if (is_wp_error($activateResult)) {
            // translators: %s: Error message.
            throw new \Exception(sprintf(__('Failed to activate the plugin: %s', 'wp-statistics'), $activateResult->get_error_message()));
        }

        return true;
    }

    /**
     * Deactivates the plugin.
     *
     * @param string $pluginSlug
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function deactivatePlugin($pluginSlug)
    {
        $pluginFile = $this->getPluginFile($pluginSlug);
        if (!$pluginFile) {
            throw new \Exception(__('Plugin not found.', 'wp-statistics'));
        }

        deactivate_plugins($pluginFile);

        return true;
    }

    /**
     * Returns plugin's full metadata.
     *
     * @param string $pluginSlug
     *
     * @return array
     *
     * @throws \Exception
     */
    public function getPluginData($pluginSlug)
    {
        $pluginFile = $this->getPluginFile($pluginSlug);
        if (!$pluginFile) {
            throw new \Exception(__('Plugin not found.', 'wp-statistics'));
        }

        if (!function_exists('get_plugin_data')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        return get_plugin_data($pluginFile);
    }
}
