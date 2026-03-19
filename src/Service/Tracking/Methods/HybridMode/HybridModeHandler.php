<?php

namespace WP_Statistics\Service\Tracking\Methods\HybridMode;

/**
 * Manages the mu-plugin proxy for Hybrid Mode tracking.
 *
 * Installs/uninstalls a SHORTINIT endpoint for minimal-bootstrap hit recording.
 *
 * @since 15.0.0
 */
class HybridModeHandler
{
    /**
     * Option key storing the installed mu-plugin version.
     */
    private const VERSION_OPTION = 'wp_statistics_mu_plugin_version';

    /**
     * Tracker filename installed into mu-plugins.
     */
    public const ENDPOINT_FILE = 'wp-statistics-tracker.php';


    /**
     * Auto-install or update the mu-plugin if needed.
     *
     * @return void
     */
    public function ensureInstalled(): void
    {
        if (!$this->isInstalled() || $this->needsUpdate()) {
            if (!$this->reinstall()) {
                error_log('WP Statistics: Failed to install mu-plugin. Check directory permissions for ' . ($this->getMuPluginsDir() ?: 'mu-plugins'));
            }
        }
    }

    /**
     * Install the mu-plugin file.
     *
     * @return bool True on success, false on failure.
     */
    public function install()
    {
        $muPluginsDir = $this->getMuPluginsDir();

        if (!$muPluginsDir) {
            return false;
        }

        if (!is_dir($muPluginsDir)) {
            wp_mkdir_p($muPluginsDir);
        }

        if (!$this->installEndpoint($muPluginsDir)) {
            $this->uninstall();
            return false;
        }

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

        $templatePath = __DIR__ . '/tracker.php';
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
     * Uninstall the mu-plugin file.
     *
     * @return bool True on success, false on failure.
     */
    public function uninstall()
    {
        $muPluginsDir = $this->getMuPluginsDir();

        if (!$muPluginsDir) {
            return false;
        }

        $file = $muPluginsDir . '/' . self::ENDPOINT_FILE;

        if (file_exists($file)) {
            wp_delete_file($file);
            if (file_exists($file)) {
                error_log('WP Statistics: Failed to delete mu-plugin file: ' . $file);
                delete_option(self::VERSION_OPTION);
                return false;
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

        return file_exists($muPluginsDir . '/' . self::ENDPOINT_FILE);
    }

    /**
     * Delete old and install new mu-plugin file.
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
}
