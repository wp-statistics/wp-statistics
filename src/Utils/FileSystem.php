<?php

namespace WP_Statistics\Utils;

/**
 * Utility class for interacting with the WordPress file system.
 *
 * Provides methods to retrieve upload directory paths and URLs,
 * as well as convert specific content URLs into file system paths.
 * Useful for resolving paths and links within WordPress content directories.
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
}
