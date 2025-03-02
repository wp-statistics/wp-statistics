<?php

namespace WP_Statistics\Service\Admin\TrackerUsageData;

class TrackerUsageDataProvider
{
    /**
     * Retrieves the URL for the current site where the front end is accessible.
     *
     * @return string
     */
    public static function getHomeUrl()
    {
        return home_url();
    }

    /**
     * Get the WordPress version.
     *
     * @return string
     */
    public static function getWordpressVersion()
    {
        return get_bloginfo('version');
    }

    /**
     * Get the PHP version.
     *
     * @return string|null
     */
    public static function getPhpVersion()
    {
        if (function_exists('phpversion')) {
            return phpversion();
        }

        return null;
    }

    /**
     * Get the plugin version.
     *
     * @return string
     */
    public static function getPluginVersion()
    {
        return WP_STATISTICS_VERSION;
    }

    /**
     * Get the database version.
     *
     * @return string|null
     */
    public static function getDatabaseVersion()
    {
        global $wpdb;

        if (empty($wpdb->is_mysql) || empty($wpdb->use_mysqli)) {
            return null;
        }

        $server_info = mysqli_get_server_info($wpdb->dbh);

        if (!$server_info) {
            return null;
        }

        return preg_replace('/^(\d+\.\d+).*/', '$1', $server_info);
    }

    /**
     * Get the database type.
     *
     * @return string|null
     */
    public static function getDatabaseType()
    {
        global $wpdb;

        if (empty($wpdb->is_mysql) || empty($wpdb->use_mysqli)) {
            return null;
        }

        $server_info = mysqli_get_server_info($wpdb->dbh);

        if (!$server_info) {
            return null;
        }

        if (strpos($server_info, 'MariaDB') !== false) {
            return 'MariaDB';
        }

        if (strpos($server_info, 'MySQL') !== false) {
            return 'MySQL';
        }

        return 'Unknown';
    }

    /**
     * Get the plugin slug.
     *
     * @return string
     */
    public static function getPluginSlug()
    {
        return basename(dirname(WP_STATISTICS_MAIN_FILE));
    }

    /**
     * Retrieves the software information of the web server.
     *
     * @return string
     */
    public static function getServerSoftware()
    {
        if (!empty($_SERVER['SERVER_SOFTWARE'])) {
            return $_SERVER['SERVER_SOFTWARE']; // @phpcs:ignore
        }

        return 'Unknown';
    }

    /**
     * Retrieves server information.
     *
     * @return array
     */
    public static function getServerInfo()
    {
        return [
            'webserver' => self::getServerSoftware(),
            'database'  => self::getDatabaseType(),
        ];
    }
}