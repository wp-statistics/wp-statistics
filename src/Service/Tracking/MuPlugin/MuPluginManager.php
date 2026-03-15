<?php

namespace WP_Statistics\Service\Tracking\MuPlugin;

use WP_Statistics\Components\Option;

/**
 * Manages the mu-plugin proxy for high-performance tracking.
 *
 * Installs/uninstalls a mu-plugin that:
 * 1. Filters active plugins on tracking requests (optimization-code.php)
 * 2. Provides a SHORTINIT endpoint for minimal-bootstrap hit recording (endpoint.php)
 *
 * @since 15.0.0
 */
class MuPluginManager
{
    /**
     * Option key storing the installed mu-plugin version.
     */
    private const VERSION_OPTION = 'wp_statistics_mu_plugin_version';

    /**
     * Mu-plugin filename.
     */
    private const MU_PLUGIN_FILE = 'wp-statistics-optimizer.php';

    /**
     * Endpoint filename placed alongside the mu-plugin.
     */
    private const ENDPOINT_FILE = 'wp-statistics-endpoint.php';

    /**
     * Hook into WordPress to auto-install/update when the option is enabled.
     *
     * @return void
     */
    public function register()
    {
        if (!Option::getValue('mu_plugin_proxy', false)) {
            return;
        }

        if (!$this->isInstalled() || $this->needsUpdate()) {
            if (!$this->reinstall()) {
                error_log('WP Statistics: Failed to install mu-plugin. Check directory permissions for ' . ($this->getMuPluginsDir() ?: 'mu-plugins'));
            }
        }
    }

    /**
     * Install the mu-plugin files.
     *
     * @return bool True on success, false on failure.
     */
    public function install()
    {
        $muPluginsDir = $this->getMuPluginsDir();

        if (!$muPluginsDir) {
            return false;
        }

        // Ensure mu-plugins directory exists
        if (!is_dir($muPluginsDir)) {
            wp_mkdir_p($muPluginsDir);
        }

        // Copy optimization code
        $source = __DIR__ . '/optimization-code.php';
        $dest   = $muPluginsDir . '/' . self::MU_PLUGIN_FILE;

        if (!$this->copyFile($source, $dest)) {
            return false;
        }

        // Copy endpoint
        $endpointSource = __DIR__ . '/endpoint.php';
        $endpointDest   = $muPluginsDir . '/' . self::ENDPOINT_FILE;

        if (!$this->copyFile($endpointSource, $endpointDest)) {
            return false;
        }

        // Store installed version
        update_option(self::VERSION_OPTION, WP_STATISTICS_VERSION);

        return true;
    }

    /**
     * Uninstall the mu-plugin files.
     *
     * @return bool True on success, false on failure.
     */
    public function uninstall()
    {
        $muPluginsDir = $this->getMuPluginsDir();

        if (!$muPluginsDir) {
            return false;
        }

        $files = [
            $muPluginsDir . '/' . self::MU_PLUGIN_FILE,
            $muPluginsDir . '/' . self::ENDPOINT_FILE,
        ];

        foreach ($files as $file) {
            if (file_exists($file)) {
                wp_delete_file($file);
            }
        }

        delete_option(self::VERSION_OPTION);

        return true;
    }

    /**
     * Check if the mu-plugin needs an update.
     *
     * @return bool
     */
    public function needsUpdate()
    {
        $installedVersion = get_option(self::VERSION_OPTION, '');
        return $installedVersion !== WP_STATISTICS_VERSION;
    }

    /**
     * Check if the mu-plugin is installed.
     *
     * @return bool
     */
    public function isInstalled()
    {
        $muPluginsDir = $this->getMuPluginsDir();
        if (!$muPluginsDir) {
            return false;
        }

        return file_exists($muPluginsDir . '/' . self::MU_PLUGIN_FILE)
            && file_exists($muPluginsDir . '/' . self::ENDPOINT_FILE);
    }

    /**
     * Delete old and install new mu-plugin files.
     *
     * @return bool
     */
    public function reinstall()
    {
        $this->uninstall();
        return $this->install();
    }

    /**
     * Get the mu-plugins directory path.
     *
     * @return string|false
     */
    private function getMuPluginsDir()
    {
        if (defined('WPMU_PLUGIN_DIR')) {
            return WPMU_PLUGIN_DIR;
        }

        $dir = WP_CONTENT_DIR . '/mu-plugins';
        return is_writable(WP_CONTENT_DIR) ? $dir : false;
    }

    /**
     * Copy a file using WP_Filesystem.
     *
     * @param string $source Source file path.
     * @param string $dest   Destination file path.
     * @return bool
     */
    private function copyFile($source, $dest)
    {
        if (!file_exists($source)) {
            return false;
        }

        global $wp_filesystem;

        if (empty($wp_filesystem)) {
            require_once ABSPATH . '/wp-admin/includes/file.php';
            if (!WP_Filesystem() || empty($wp_filesystem)) {
                return false;
            }
        }

        $content = $wp_filesystem->get_contents($source);
        if ($content === false) {
            return false;
        }

        return $wp_filesystem->put_contents($dest, $content, FS_CHMOD_FILE);
    }

    /**
     * Get the public URL for the endpoint file.
     *
     * @return string
     */
    public function getEndpointUrl()
    {
        return content_url('mu-plugins/' . self::ENDPOINT_FILE);
    }
}
