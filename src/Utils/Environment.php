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

    /**
     * Check if the current environment is local development.
     *
     * @return bool True if running locally, false otherwise
     */
    public static function isLocal()
    {
        // Check common local development domains
        $localDomains = array(
            'localhost',
            '127.0.0.1',
            '::1',
            'local.dev',
            'local.wp',
            '*.loc',
            '*.test',
            '*.local',
        );

        // Get current site URL and parse the host
        $siteUrl = Url::getDomain(home_url());

        // Check against local domains
        foreach ($localDomains as $domain) {
            // Handle wildcard domains
            if (strpos($domain, '*') !== false) {
                $pattern = '/^' . str_replace('\*', '.*', preg_quote($domain, '/')) . '$/';
                if (preg_match($pattern, $siteUrl)) {
                    return true;
                }
            } // Exact match
            elseif (strtolower($siteUrl) === strtolower($domain)) {
                return true;
            }
        }

        // Additional checks for common local environment indicators
        if (defined('WP_ENVIRONMENT_TYPE') && WP_ENVIRONMENT_TYPE === 'local') {
            return true;
        }

        if (isset($_SERVER['REMOTE_ADDR']) && in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1'])) {
            return true;
        }

        // Check for common local server software
        if (isset($_SERVER['SERVER_SOFTWARE']) && stripos($_SERVER['SERVER_SOFTWARE'], 'localhost') !== false) {
            return true;
        }

        return false;
    }
}
