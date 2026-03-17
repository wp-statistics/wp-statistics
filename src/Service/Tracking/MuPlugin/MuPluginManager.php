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
     * Polyfills filename placed alongside the mu-plugin.
     */
    private const POLYFILLS_FILE = 'wp-statistics-polyfills.php';

    /**
     * Hook into WordPress to auto-install/update when the option is enabled.
     *
     * @return void
     */
    public function register()
    {
        // Always listen for settings changes so the toggle can enable/disable the feature
        add_action('wp_statistics_settings_saved', [$this, 'onSettingsSaved'], 10, 2);

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
     * Handle mu-plugin install/uninstall when the setting changes.
     *
     * @param string $tab      Settings tab key.
     * @param array  $settings Submitted settings.
     * @return void
     */
    public function onSettingsSaved($tab, $settings)
    {
        if (!array_key_exists('mu_plugin_proxy', $settings)) {
            return;
        }

        if (!empty($settings['mu_plugin_proxy'])) {
            $this->reinstall();
        } else {
            $this->uninstall();
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

        // Copy polyfills
        $polyfillsSource = __DIR__ . '/polyfills.php';
        $polyfillsDest   = $muPluginsDir . '/' . self::POLYFILLS_FILE;

        if (!$this->copyFile($polyfillsSource, $polyfillsDest)) {
            $this->uninstall();
            return false;
        }

        // Bake endpoint template with actual paths
        if (!$this->installEndpoint($muPluginsDir)) {
            $this->uninstall();
            return false;
        }

        // Store installed version
        update_option(self::VERSION_OPTION, WP_STATISTICS_VERSION);

        return true;
    }

    /**
     * Read the endpoint template, replace placeholders, and write to mu-plugins.
     *
     * @param string $muPluginsDir Target mu-plugins directory.
     * @return bool True on success, false on failure.
     */
    private function installEndpoint($muPluginsDir)
    {
        if (!$this->ensureFilesystem()) {
            return false;
        }

        global $wp_filesystem;

        $templatePath = __DIR__ . '/endpoint.php';
        $content      = $wp_filesystem->get_contents($templatePath);

        if ($content === false) {
            return false;
        }

        $pluginDir = WP_STATISTICS_DIR;

        $content = str_replace(
            ['{{ABSPATH}}', '{{PLUGIN_DIR}}', '{{VERSION}}'],
            [ABSPATH, $pluginDir, WP_STATISTICS_VERSION],
            $content
        );

        $dest = $muPluginsDir . '/' . self::ENDPOINT_FILE;

        return $wp_filesystem->put_contents($dest, $content, FS_CHMOD_FILE);
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
            $muPluginsDir . '/' . self::POLYFILLS_FILE,
        ];

        $allDeleted = true;
        foreach ($files as $file) {
            if (file_exists($file)) {
                wp_delete_file($file);
                if (file_exists($file)) {
                    error_log('WP Statistics: Failed to delete mu-plugin file: ' . $file);
                    $allDeleted = false;
                }
            }
        }

        delete_option(self::VERSION_OPTION);

        return $allDeleted;
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
            && file_exists($muPluginsDir . '/' . self::ENDPOINT_FILE)
            && file_exists($muPluginsDir . '/' . self::POLYFILLS_FILE);
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
    /**
     * Ensure WP_Filesystem is initialized.
     *
     * @return bool
     */
    private function ensureFilesystem()
    {
        global $wp_filesystem;

        if (!empty($wp_filesystem)) {
            return true;
        }

        require_once ABSPATH . '/wp-admin/includes/file.php';

        return WP_Filesystem() && !empty($wp_filesystem);
    }

    private function copyFile($source, $dest)
    {
        if (!file_exists($source) || !$this->ensureFilesystem()) {
            return false;
        }

        global $wp_filesystem;

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
