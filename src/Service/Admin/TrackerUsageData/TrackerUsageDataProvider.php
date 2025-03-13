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
            $versionParts = explode('.', phpversion());
            return "$versionParts[0].$versionParts[1]";
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

        if (empty($wpdb->is_mysql) || empty($wpdb->use_mysqli) || empty($wpdb->dbh)) {
            return null;
        }

        $serverInfo = mysqli_get_server_info($wpdb->dbh);
        if (!$serverInfo) {
            return null;
        }

        $databaseType = str_contains($serverInfo, 'MariaDB') ? 'MariaDB' : (str_contains($serverInfo, 'MySQL') ? 'MySQL' : 'Unknown');

        // Extract the major and minor version (e.g., 10.5 from 10.5.9-MariaDB)
        $version = preg_match('/\d+\.\d+/', $serverInfo, $matches) ? $matches[0] : '';

        return trim("$databaseType $version");
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

        $serverInfo = mysqli_get_server_info($wpdb->dbh);

        if (!$serverInfo) {
            return null;
        }

        return str_contains($serverInfo, 'MariaDB') ? 'MariaDB' : (str_contains($serverInfo, 'MySQL') ? 'MySQL' : 'Unknown');
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
        ];
    }
}