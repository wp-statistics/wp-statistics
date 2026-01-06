<?php

namespace WP_Statistics\Utils;

/**
 * Utility class for interacting with the WordPress file system.
 *
 * Provides methods to retrieve upload directory paths and URLs,
 * as well as convert specific content URLs into file system paths.
 * Also handles WP Statistics specific directory management with protection.
 *
 * @package WP_Statistics\Utils
 * @since 15.0.0
 */
class FileSystem
{
    /**
     * Absolute path to the uploads directory.
     *
     * @return string Trailing‑slash‑stripped absolute path.
     */
    public static function getUploadsDir()
    {
        $uploads = wp_upload_dir(null, false);
        return wp_normalize_path($uploads['basedir']);
    }

    /**
     * Base URL of the uploads directory.
     *
     * @return string Uploads base URL (no trailing slash).
     */
    public static function getUploadUrl()
    {
        $uploadDir = wp_get_upload_dir();
        $baseurl   = $uploadDir['baseurl'];

        if (strpos($baseurl, 'http://') === 0) {
            $isSsl = function_exists('is_ssl') ? is_ssl() :
                (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
                (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);

            if ($isSsl) {
                $baseurl = 'https://' . substr($baseurl, 7);
            }
        }

        return $baseurl;
    }

    /**
     * Convert a URL under wp‑content to its filesystem path.
     *
     * @param string $url
     * @return string Filesystem path or the untouched input URL.
     */
    public static function urlToDir(string $url)
    {
        if (stripos($url, home_url()) === false) {
            return '';
        }

        $pluginName = basename($url);

        $pluginDir = untrailingslashit(WP_PLUGIN_DIR);

        return wp_normalize_path($pluginDir . '/' . $pluginName);
    }

    /**
     * Get the WP Statistics uploads directory path.
     *
     * @return string Full path to wp-content/uploads/wp-statistics/
     */
    public static function getPluginUploadsDir(): string
    {
        return trailingslashit(self::getUploadsDir()) . WP_STATISTICS_UPLOADS_DIR;
    }

    /**
     * Get a subdirectory path within the WP Statistics uploads directory.
     *
     * @param string $subdir Subdirectory name (e.g., 'backups', 'imports')
     * @return string Full path to the subdirectory
     */
    public static function getPluginSubDir(string $subdir): string
    {
        return trailingslashit(self::getPluginUploadsDir()) . $subdir;
    }

    /**
     * Get the backups directory path.
     *
     * @return string Full path to wp-content/uploads/wp-statistics/backups/
     */
    public static function getBackupsDir(): string
    {
        return self::getPluginSubDir('backups');
    }

    /**
     * Get the imports (temp) directory path.
     *
     * @return string Full path to wp-content/uploads/wp-statistics/imports/
     */
    public static function getImportsDir(): string
    {
        return self::getPluginSubDir('imports');
    }

    /**
     * Ensure a directory exists and is protected.
     *
     * Creates the directory if it doesn't exist and adds protection files
     * (.htaccess and index.php) to prevent direct access.
     *
     * @param string $dir Full path to the directory
     * @return bool True if directory exists and is protected
     */
    public static function ensureDirectory(string $dir): bool
    {
        // Create directory if it doesn't exist
        if (!file_exists($dir)) {
            if (!wp_mkdir_p($dir)) {
                return false;
            }
        }

        // Add protection files
        self::protectDirectory($dir);

        return true;
    }

    /**
     * Protect a directory with .htaccess and index.php files.
     *
     * @param string $dir Full path to the directory
     * @return void
     */
    public static function protectDirectory(string $dir): void
    {
        $dir = trailingslashit($dir);

        // Create .htaccess to deny direct access
        $htaccessFile = $dir . '.htaccess';
        if (!file_exists($htaccessFile)) {
            $htaccessContent = "# Deny access to this directory\n";
            $htaccessContent .= "<IfModule mod_authz_core.c>\n";
            $htaccessContent .= "    Require all denied\n";
            $htaccessContent .= "</IfModule>\n";
            $htaccessContent .= "<IfModule !mod_authz_core.c>\n";
            $htaccessContent .= "    Order deny,allow\n";
            $htaccessContent .= "    Deny from all\n";
            $htaccessContent .= "</IfModule>\n";

            @file_put_contents($htaccessFile, $htaccessContent);
        }

        // Create index.php to prevent directory listing
        $indexFile = $dir . 'index.php';
        if (!file_exists($indexFile)) {
            @file_put_contents($indexFile, '<?php // Silence is golden');
        }
    }

    /**
     * Ensure the main WP Statistics uploads directory exists and is protected.
     *
     * @return bool True if directory exists and is protected
     */
    public static function ensurePluginUploadsDir(): bool
    {
        return self::ensureDirectory(self::getPluginUploadsDir());
    }

    /**
     * Ensure the backups directory exists and is protected.
     *
     * @return bool True if directory exists and is protected
     */
    public static function ensureBackupsDir(): bool
    {
        // Ensure parent directory first
        self::ensurePluginUploadsDir();

        return self::ensureDirectory(self::getBackupsDir());
    }

    /**
     * Ensure the imports directory exists and is protected.
     *
     * @return bool True if directory exists and is protected
     */
    public static function ensureImportsDir(): bool
    {
        // Ensure parent directory first
        self::ensurePluginUploadsDir();

        return self::ensureDirectory(self::getImportsDir());
    }

    /**
     * Get the full path to a file within a subdirectory.
     *
     * @param string $subdir Subdirectory name
     * @param string $filename File name
     * @return string Full path to the file
     */
    public static function getFilePath(string $subdir, string $filename): string
    {
        return trailingslashit(self::getPluginSubDir($subdir)) . $filename;
    }

    /**
     * List files in a subdirectory matching a pattern.
     *
     * @param string $subdir Subdirectory name
     * @param string $pattern Glob pattern (default: *.json)
     * @return array List of file paths
     */
    public static function listFiles(string $subdir, string $pattern = '*.json'): array
    {
        $dir = self::getPluginSubDir($subdir);

        if (!file_exists($dir)) {
            return [];
        }

        $files = glob(trailingslashit($dir) . $pattern);

        return $files ?: [];
    }
}
