<?php

namespace WP_Statistics\Utils;

use WP_Statistics\Utils\Format;

/**
 * Utility class for retrieving WordPress environment information.
 *
 * Provides static methods to access details such as the WordPress version,
 * site name, URL, admin email, memory usage, and multisite configuration.
 * Intended to centralize access to common environment data points.
 *
 * @package WP_Statistics\Utils
 * @since 15.0.0
 */
class Environment
{
    /**
     * Retrieve the current WordPress version.
     *
     * @return string The version number of the WordPress installation.
     */
    public static function getWordPressVersion()
    {
        return get_bloginfo('version');
    }

    /**
     * Get the site name.
     *
     * @return string The site name.
     */
    public static function getSiteName()
    {
        return get_bloginfo('name');
    }

    /**
     * Get the site URL.
     *
     * @return string The site URL.
     */
    public static function getSiteUrl()
    {
        return get_bloginfo('url');
    }

    /**
     * Get the site admin email.
     *
     * @return string The admin email address.
     */
    public static function getAdminEmail()
    {
        return get_bloginfo('admin_email');
    }

    /**
     * Check whether the current memory usage exceeds the configured PHP memory limit.
     *
     * @return bool True if the memory limit is exceeded, false otherwise.
     */
    public static function checkMemoryLimit()
    {
        if (!function_exists('memory_get_peak_usage') or !function_exists('ini_get')) {
            return false;
        }

        $memoryLimit = ini_get('memory_limit');

        if (memory_get_peak_usage(true) > Format::sizeToBytes($memoryLimit)) {
            return true;
        }

        return false;
    }

    /**
     * Retrieve a list of site IDs from a WordPress multisite network.
     *
     * @return array An array of blog IDs.
     */
    public static function getWordpressSitesList()
    {
        $site_list = [];
        $sites     = \get_sites();
        foreach ($sites as $site) {
            $site_list[] = $site->blog_id;
        }
        return $site_list;
    }
}
