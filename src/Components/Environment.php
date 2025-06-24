<?php

namespace WPStatistics\Components;

use WP_Statistics\Utils\Format;

/**
 * Provides environment-related information and system checks.
 *
 * @since 15.0.0
 */
final class Environment
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

        if (memory_get_peak_usage(true) > Format::SizeToBytes($memoryLimit)) {
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
        $site_list = array();
        $sites     = get_sites();
        foreach ($sites as $site) {
            $site_list[] = $site->blog_id;
        }
        return $site_list;
    }
}
