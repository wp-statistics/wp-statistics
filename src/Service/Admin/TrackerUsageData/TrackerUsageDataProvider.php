<?php

namespace WP_Statistics\Service\Admin\TrackerUsageData;

use WP_Statistics\Service\Admin\SiteHealthInfo;
use WP_Statistics\Service\Admin\LicenseManagement\LicenseHelper;
use WP_STATISTICS\DB;
use WP_STATISTICS\Option;

class TrackerUsageDataProvider
{
    /**
     * Retrieves the URL for the current site where the front end is accessible.
     *
     * @return string
     */
    public static function getHomeUrl()
    {
        $url = self::getCleanDomain(home_url());

        return self::hashDomain($url);
    }

    /**
     * Get the WordPress version.
     *
     * @return string
     */
    public static function getWordPressVersion()
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

        return preg_match('/\d+\.\d+/', $serverInfo, $matches) ? $matches[0] : '';
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
            'webserver'     => self::getServerSoftware(),
            'database_type' => self::getDatabaseType(),
        ];
    }

    /**
     * Get clean domain
     *
     * @param string $url
     *
     * @return string
     */
    public static function getCleanDomain(string $url): string
    {
        if (!preg_match('/^https?:\/\//', $url)) {
            $url = 'https://' . $url; // Default to HTTPS if no scheme
        }

        $host = parse_url($url, PHP_URL_HOST);

        $host = preg_replace('/^www\./', '', $host);

        return $host;
    }

    /**
     * Hashes a URL using the SHA-256 algorithm and returns the first 40 characters.
     *
     * @param string $domain
     *
     * @return string
     */
    private static function hashDomain($domain)
    {
        return substr(hash('sha256', $domain), 0, 40);
    }

    /**
     * Get the current theme info, theme name and version.
     *
     * @return array
     */
    public static function getThemeInfo()
    {
        $themeData = wp_get_theme();

        return array(
            'slug' => $themeData->get_stylesheet(),
        );
    }

    /**
     * Get all plugins grouped into activated or not.
     *
     * @return array
     */
    public static function getAllPlugins()
    {
        $activePluginsKeys = get_option('active_plugins', array());

        return array(
            'activated_plugins' => $activePluginsKeys,
        );
    }

    /**
     * Retrieves plugin settings
     *
     * @return array
     */
    public static function getPluginSettings()
    {
        $siteHealthInfo = new SiteHealthInfo();
        $rawSettings    = $siteHealthInfo->addStatisticsInfo([]);
        $settings       = [];
        $debugSlug      = SiteHealthInfo::DEBUG_INFO_SLUG;

        if (isset($rawSettings[$debugSlug]['fields'])) {
            foreach ($rawSettings[$debugSlug]['fields'] as $k => $f) {
                if ($k === 'version' || $k === 'geoIpDatabaseSize') {
                    continue;
                }

                if (isset($f['debug'])) {
                    $settings[$k] = $f['debug'];
                } else {
                    $settings[$k] = $f['value'];
                }
            }
        }

        return $settings;
    }

    /**
     * Retrieves the timezone string.
     *
     * @return string
     */
    public static function getTimezone()
    {
        $timezone = get_option('timezone_string');

        if (!empty($timezone)) {
            return $timezone;
        }

        $gmt_offset = get_option('gmt_offset');
        return 'UTC' . ($gmt_offset >= 0 ? '+' : '') . $gmt_offset;
    }

    /**
     * Retrieves the current locale.
     *
     * @return string
     */
    public static function getLocale()
    {
        return get_locale();
    }

    /**
     * Retrieves the status, type, and associated products of all licenses.
     *
     * This method fetches all licenses using the LicenseHelper and returns an array
     * containing the 'status', 'type', and 'products' for each license.
     *
     * @return array An array of license details, where each element contains:
     *          - 'status' (string): The status of the license.
     *          - 'type' (string): The type of the license.
     *          - 'products' (array): The products associated with the license.
     */
    public static function getLicensesInfo()
    {
        $rawLicenses = LicenseHelper::getLicenses('all');
        $licenses    = [];

        foreach ($rawLicenses as $k => $v) {
            $licenses[] = [
                'status'   => $v['status'],
                'type'     => $v['type'],
                'products' => $v['products'],
            ];
        }

        return $licenses;
    }

    /**
     * Get Number of Table Rows
     *
     * @return array
     */
    public static function getTablesStats()
    {
        $userOnlineTable = DB::table('useronline');
        $rawTableRows    = DB::getTableRows();
        $tableRows       = [];

        foreach ($rawTableRows as $k => $v) {
            if ($k === $userOnlineTable) {
                continue;
            }
            $tableRows[$k] = $v['rows'];
        }

        return $tableRows;
    }

    /**
     * Retrieves the payload data
     *
     * @return array
     */
    public static function getPayload()
    {
        return [
            'plugin_database_version_legacy' => get_option('wp_statistics_plugin_version'),
            'plugin_database_version'        => Option::getOptionGroup('db', 'version', '0.0.0'),
            'jobs'                           => Option::getOptionGroup('jobs'),
            'dismissed_notices'              => Option::getOptionGroup('dismissed_notices'),
        ];
    }
}