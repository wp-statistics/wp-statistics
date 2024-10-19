<?php

namespace WP_Statistics\Service\Admin\LicenseManagement\Plugin;

use Exception;

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
     * @return mixed
     *
     * @throws Exception
     */
    public function downloadAndInstallPlugin($pluginUrl)
    {
        if (empty($pluginUrl)) {
            throw new Exception(__('Download URL is empty!', 'wp-statistics'));
        }

        if (!current_user_can('install_plugins')) {
            throw new Exception(__('You do not have permission to install plugins.', 'wp-statistics'));
        }

        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';

        // Download the plugin zip file
        $downloadFile = download_url($pluginUrl);
        if (is_wp_error($downloadFile)) {
            // translators: %s: Error message.
            throw new Exception(sprintf(__('Failed to download the plugin: %s', 'wp-statistics'), $downloadFile->get_error_message()));
        }

        // Prepare for unpacking the plugin
        $pluginUpgrader = new \Plugin_Upgrader(new \Automatic_Upgrader_Skin());
        $installResult  = $pluginUpgrader->install($downloadFile, ['overwrite_package' => true]);

        // Cleanup downloaded file
        @unlink($downloadFile);

        if (is_wp_error($installResult)) {
            // translators: %s: Error message.
            throw new Exception(sprintf(__('Failed to install the plugin: %s', 'wp-statistics'), $installResult->get_error_message()));
        }

        return $installResult;
    }

    /**
     * Returns plugin file path.
     *
     * @param string $pluginSlug
     *
     * @return string
     */
    public function getPluginFile($pluginSlug)
    {
        return "$pluginSlug/$pluginSlug.php";
    }

    /**
     * Checks if the plugin is installed?
     *
     * @param string $pluginSlug
     *
     * @return bool
     */
    public function isPluginInstalled($pluginSlug)
    {
        return file_exists(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $this->getPluginFile($pluginSlug));
    }

    /**
     * Checks if the plugin is active?
     *
     * @param string $pluginSlug
     *
     * @return bool
     */
    public function isPluginActive($pluginSlug)
    {
        return $this->isPluginInstalled($pluginSlug) && is_plugin_active(plugin_basename($this->getPluginFile($pluginSlug)));
    }

    /**
     * Activates the plugin.
     *
     * @param string $pluginSlug
     *
     * @return bool
     *
     * @throws Exception
     */
    public function activatePlugin($pluginSlug)
    {
        if (!$this->isPluginInstalled($pluginSlug)) {
            throw new Exception(__('Plugin is not installed!', 'wp-statistics'));
        }

        if ($this->isPluginActive($pluginSlug)) {
            throw new Exception(__('Plugin already active.', 'wp-statistics'));
        }

        $activateResult = activate_plugin($this->getPluginFile($pluginSlug));
        if (is_wp_error($activateResult)) {
            // translators: %s: Error message.
            throw new Exception(sprintf(__('Failed to activate the plugin: %s', 'wp-statistics'), $activateResult->get_error_message()));
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
     * @throws Exception
     */
    public function deactivatePlugin($pluginSlug)
    {
        if (!$this->isPluginInstalled($pluginSlug)) {
            throw new Exception(__('Plugin is not installed!', 'wp-statistics'));
        }

        deactivate_plugins($this->getPluginFile($pluginSlug));

        return true;
    }

    /**
     * Returns plugin's full metadata.
     *
     * @param string $pluginSlug
     *
     * @return array
     *
     * @throws Exception
     */
    public function getPluginData($pluginSlug)
    {
        if (!$this->isPluginInstalled($pluginSlug)) {
            throw new Exception(__('Plugin is not installed!', 'wp-statistics'));
        }

        return get_plugin_data(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $this->getPluginFile($pluginSlug));
    }

    /**
     * Retrieves a list of installed wp-statistics add-ons (plugins that starts with wp-statistics- prefix).
     *
     * @return array Array of plugin data, keyed by plugin file name. See get_plugin_data().
     */
    public function getInstalledPlugins()
    {
        $result     = [];
        $plugins    = get_plugins();

        foreach ($plugins as $pluginFile => $pluginData) {
            // If not wp-statistics add-on, skip
            if (strpos($pluginFile, 'wp-statistics-') !== 0) continue;

            $result[$pluginFile] = $pluginData;
        }

        return $result;
    }
}
