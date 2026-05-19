<?php

namespace WP_Statistics\Service\Geolocation;

use WP_Statistics\Helper;
use WP_STATISTICS\Option;
use WP_Statistics\Service\Database\Migrations\BackgroundProcess\BackgroundProcessFactory;

abstract class AbstractGeoIPProvider implements GeoServiceProviderInterface
{
    /**
     * @var string
     */
    protected $databaseFileName = '';

    /**
     * Default country code for private IP addresses.
     *
     * @var string
     */
    protected $defaultPrivateCountry = '000';

    /**
     * Get the default location data.
     *
     * @return null[]
     */
    public function getDefaultLocation()
    {
        return [
            'country'      => null,
            'country_code' => null,
            'continent'    => null,
            'region'       => null,
            'city'         => null,
            'latitude'     => null,
            'longitude'    => null,
        ];
    }

    /**
     * Retrieves the default country code for private IPs.
     *
     * @return string The country code used for private IPs.
     */
    public function getPrivateCountryCode()
    {
        $privateCountryCode = Option::get('private_country_code');

        if ($privateCountryCode) {
            return trim($privateCountryCode);
        }

        return $this->defaultPrivateCountry;
    }

    /**
     * Retrieves the private country code.
     *
     * @return string
     */
    public function getDefaultPrivateCountryCode()
    {
        return $this->defaultPrivateCountry;
    }

    /**
     * Construct the full path for a given file name in the uploads directory.
     *
     * @param string $fileName
     * @return string
     */
    protected function getFilePath(string $fileName)
    {
        $uploadDir = wp_upload_dir();
        return $uploadDir['basedir'] . '/' . WP_STATISTICS_UPLOADS_DIR . '/' . $fileName;
    }

    /**
     * Get the path to the GeoIP database file.
     *
     * @return string
     */
    protected function getDatabasePath()
    {
        return $this->getFilePath($this->databaseFileName);
    }

    /**
     * Delete the given file.
     *
     * @param string $file
     * @return bool
     */
    protected function deleteFile($file)
    {
        if (file_exists($file)) {
            return wp_delete_file($file);
        }
        return true;
    }

    /**
     * Delete the existing GeoIP database.
     *
     * @return bool
     */
    public function deleteDatabase()
    {
        if ($this->isDatabaseExist()) {
            $databasePath = $this->getDatabasePath();
            return $this->deleteFile($databasePath);
        }
        return true; // If the file does not exist, treat it as already deleted
    }

    /**
     * Determine if the Geo-IP database is active.
     *
     * @return bool
     */
    public function isDatabaseExist()
    {
        return file_exists($this->getDatabasePath());
    }

    /**
     * Get the last updated timestamp for the Geolocation database file.
     *
     * @return false|string
     */
    public function getLastDatabaseFileUpdated()
    {
        if ($this->isDatabaseExist()) {
            return gmdate('Y-m-d H:i:s', filemtime($this->getDatabasePath()));
        }
    }

    /**
     * Get the last download timestamp for the GeoIP database.
     *
     * @return false|int
     */
    public function getLastDownloadTimestamp()
    {
        return Option::get('last_geoip_dl');
    }

    /**
     * Retrieves the database size for the GeoIP database.
     *
     * @param bool $format Whether to format the size for readability.
     */
    public function getDatabaseSize($format = true)
    {
        if ($this->isDatabaseExist()) {
            if ($format) {
                return size_format(filesize($this->getDatabasePath()));
            } else {
                return filesize($this->getDatabasePath());
            }
        }
    }

    /**
     * Get the remote URL for downloading the GeoIP database.
     *
     * This URL can be filtered via WordPress filters.
     *
     * @param string $defaultUrl The default URL for downloading the database.
     * @doc https://wp-statistics.com/resources/how-to-host-the-geolocation-database-and-ensure-compatibility-with-a-private-network/
     * @return string The filtered URL.
     */
    protected function getFilteredDownloadUrl(string $defaultUrl)
    {
        /**
         * Filter: wp_statistics_geolocation_download_url
         *
         * Allows customization of the GeoIP database download URL.
         *
         * @param string $defaultUrl The default download URL.
         */
        $url = apply_filters('wp_statistics_geolocation_download_url', $defaultUrl);

        return self::sanitizeDownloadUrl((string) $url, $defaultUrl);
    }

    /**
     * Whitelist a filtered GeoIP download URL before it reaches wp_remote_get().
     * Returns $defaultUrl on any failure so SSRF attempts silently no-op.
     */
    private static function sanitizeDownloadUrl($url, $defaultUrl)
    {
        if ($url === '' || $url === $defaultUrl) {
            return $defaultUrl;
        }

        $parts = wp_parse_url($url);

        if (!is_array($parts) || empty($parts['scheme']) || empty($parts['host'])) {
            return $defaultUrl;
        }

        if (!in_array(strtolower($parts['scheme']), ['http', 'https'], true)) {
            return $defaultUrl;
        }

        if (!empty($parts['user']) || !empty($parts['pass'])) {
            return $defaultUrl;
        }

        if (isset($parts['port']) && !in_array((int) $parts['port'], [80, 443, 8080, 8443], true)) {
            return $defaultUrl;
        }

        $host = strtolower($parts['host']);

        $blockedHosts = [
            '169.254.169.254',
            'metadata.google.internal',
            'metadata.goog',
            'localhost',
            '0.0.0.0',
        ];

        if (in_array($host, $blockedHosts, true)) {
            return $defaultUrl;
        }

        if (strpos($host, '[fe80') === 0 || strpos($host, '[fd00:ec2') === 0 || $host === '[::1]') {
            return $defaultUrl;
        }

        // RFC 1918 hosts are intentionally allowed because mirroring the GeoIP
        // database on a private network is a documented setup. The 127.0.0.0/8
        // loopback range is rejected explicitly so attacker-controlled filter
        // values cannot point at local-machine services.
        if (filter_var($host, FILTER_VALIDATE_IP) && strpos($host, '127.') === 0) {
            return $defaultUrl;
        }

        return $url;
    }

    /**
     * Update the last download timestamp.
     */
    protected function updateLastDownloadTimestamp()
    {
        Option::update('last_geoip_dl', time());
    }

    /**
     * Batch update incomplete GeoIP info for visitors.
     */
    protected function batchUpdateIncompleteGeoIp()
    {
        if (Option::get('auto_pop')) {
            BackgroundProcessFactory::getBackgroundProcess('update_unknown_visitor_geoip')->process();
        }
    }

    /**
     * Send email notification about the GeoIP update.
     *
     * @param string $notice
     */
    protected function sendGeoIpUpdateEmail(string $notice)
    {
        if (Option::get('geoip_report')) {
            Helper::send_mail(
                Option::getEmailNotification(),
                __('GeoIP update on', 'wp-statistics') . ' ' . get_bloginfo('name'),
                $notice,
                true,
                [
                    "email_title" => __('GeoIP update on', 'wp-statistics') . ' <a href="' . get_bloginfo('url') . '" target="_blank" style="text-decoration: none; color: #303032; font-family: Roboto,Arial,Helvetica,sans-serif; font-size: 16px; font-weight: 600; line-height: 18.75px;font-style: italic">' . get_bloginfo('name') . '</a>'
                ]
            );
        }
    }
}
