<?php

namespace WP_STATISTICS;

use Exception;
use WP_Statistics;
use WP_Statistics\Async\BackgroundProcessFactory;
use WP_Statistics\Dependencies\GeoIp2\Database\Reader;

/**
 * @note This temporary GeoIP implementation will be replaced by a more efficient Geolocation structure in version 14.10
 * As a lesson learned: never let someone without an understanding of software architecture design the code.
 */
class GeoIP
{
    /**
     * Array containing URLs and filenames for GeoIP database sources.
     *
     * @var array
     */
    public static $library = array(
        'source'     => 'https://cdn.jsdelivr.net/npm/geolite2-city/GeoLite2-City.mmdb.gz',
        'userSource' => 'https://download.maxmind.com/app/geoip_download?edition_id=GeoLite2-City&license_key=&suffix=tar.gz',
        'file'       => 'GeoLite2-City',
    );

    /**
     * The file extension for GeoIP database files.
     *
     * @var string
     */
    public static $file_extension = 'mmdb';

    /**
     * Default country code for private IP addresses.
     *
     * @var string
     */
    public static $private_country = '000';

    /**
     * Cached GeoIP Reader instance.
     *
     * @var Reader|null
     */
    private static $readerCache = null;

    /**
     * Cache for geolocation results.
     *
     * @var array
     */
    private static $locationCache = [];

    /**
     * Retrieves the path to the GeoIP database file.
     *
     * @return string The normalized path to the GeoIP database file.
     */
    public static function get_geo_ip_path()
    {
        return wp_normalize_path(path_join(Helper::get_uploads_dir(WP_STATISTICS_UPLOADS_DIR), self::$library['file'] . '.' . self::$file_extension));
    }

    /**
     * Determine if the Geo-IP is active.
     *
     * This method checks if the Geo-IP functionality is active by verifying
     * the existence of the required Geo-IP file. Although deprecated and
     * removed from all add-ons, it remains for backward compatibility.
     *
     * @return bool  Returns true if the Geo-IP file exists, indicating that the Geo-IP is active; otherwise, false.
     *
     * @deprecated  This method is deprecated and should not be used in new development. It remains for backward compatibility.
     */
    public static function active()
    {
        if (self::isExist()) {
            return true;
        }

        return false;
    }

    /**
     * Is exist in the GeoIP database.
     *
     * @return bool
     */
    public static function isExist()
    {
        return file_exists(self::get_geo_ip_path());
    }

    /**
     * Retrieves the last update date for the GeoIP database.
     *
     * @return false|string|void
     */
    public static function getLastUpdate()
    {
        if (self::isExist()) {
            return date('Y-m-d H:i:s', filemtime(self::get_geo_ip_path()));
        }
    }

    /**
     * Retrieves the database size for the GeoIP database.
     *
     * @param bool $format Whether to format the size for readability.
     */
    public static function getDatabaseSize($format = true)
    {
        if (self::isExist()) {
            if ($format) {
                return size_format(filesize(self::get_geo_ip_path()));
            } else {
                return filesize(self::get_geo_ip_path());
            }
        }
    }

    /**
     * Retrieves the database type for the GeoIP database.
     *
     * @return string|bool The database type or false on failure.
     */
    public static function getDatabaseType()
    {
        $reader = self::Loader();

        if ($reader === false) {
            return false;
        }

        return $reader->metadata()->databaseType;
    }

    /**
     * Loads the GeoIP database reader and caches it.
     *
     * @return bool|Reader Instance of GeoIP Reader if successful, false on failure.
     */
    public static function Loader()
    {
        // Check if the reader is already cached.
        if (self::$readerCache !== null) {
            return self::$readerCache;
        }

        // Get the path to the GeoIP database file.
        $file = self::get_geo_ip_path();

        try {
            if (!file_exists($file)) {
                // Download it again if the GeoIP database is removed manually and not exist.
                BackgroundProcessFactory::downloadGeoIPDatabase();

                throw new Exception("GeoIP database library not found in {$file}, trying to download it.");
            }

            // Load the GeoIP Reader and cache it.
            self::$readerCache = new Reader($file);
            return self::$readerCache;

        } catch (Exception $e) {
            // Log the exception message.
            WP_Statistics::log($e->getMessage(), 'error');

            // Return false if there is an error loading the reader.
            return false;
        }
    }

    /**
     * Retrieves the default country code for private IPs.
     *
     * @return string The country code used for private IPs.
     */
    public static function getDefaultCountryCode()
    {
        $opt = Option::get('private_country_code');

        if (isset($opt) && !empty($opt)) {
            return trim($opt);
        }

        return self::$private_country;
    }

    /**
     * Retrieves the geolocation information for a given IP address.
     *
     * Caches the location information to avoid redundant lookups.
     *
     * @param bool|string $ip The IP address to lookup. Defaults to the user's IP.
     * @return array|null[] The location.
     */
    public static function getLocation($ip)
    {
        // Check if the location is already cached.
        if (isset(self::$locationCache[$ip])) {
            return self::$locationCache[$ip];
        }

        $defaultLocation = [
            'country'   => self::getDefaultCountryCode(),
            'city'      => 'Unknown',
            'continent' => 'Unknown',
            'region'    => 'Unknown',
        ];

        // Add compatibility for hash IP addresses.
        if (strpos($ip, IP::$hash_ip_prefix) !== false) {
            return $defaultLocation;
        }

        try {
            // Load the GeoIP reader.
            $reader = self::Loader();

            // Check if the reader is loaded.
            if ($reader === false) {
                return $defaultLocation;
            }

            // Search for location information in GeoIP database.
            $record = $reader->city($ip);

            $location = [
                'country'   => $record->country->isoCode,
                'city'      => $record->city->name,
                'continent' => $record->continent->name,
                'region'    => $record->mostSpecificSubdivision->name,
            ];

            // Cache the location result.
            self::$locationCache[$ip] = $location;

            return $location;

        } catch (Exception $e) {
            // No need to log since the error is already logged in Loader method.
            // Log the exception message.
            //WP_Statistics::log($e->getMessage(), 'error');
        }

        // Cache and return the default location if an error occurs.
        self::$locationCache[$ip] = $defaultLocation;
        return $defaultLocation;
    }

    /**
     * Retrieves the country information for a given IP address.
     *
     * @param bool|string $ip The IP address to lookup. Defaults to the user's IP.
     * @return string|null The country code or detail requested, or null on failure.
     * @throws Exception If there is an issue during GeoIP lookup.
     */
    public static function getCountry($ip = false)
    {
        // Use default country code as fallback.
        $default_country = self::getDefaultCountryCode();

        // Get the user's IP if not provided.
        $ip = ($ip === false ? IP::getIP() : $ip);

        // Check if IP is in a private range.
        if ($default_country != self::$private_country) {
            if (IP::CheckIPRange(IP::$private_SubNets)) {
                return $default_country;
            }
        }

        // Validate the IP address.
        if (IP::isIP($ip) === false) {
            return $default_country;
        }

        $location = self::getLocation($ip);

        return $location['country'];
    }

    /**
     * Downloads the GeoIP database from MaxMind.
     *
     * @param string $type The type of download operation ('enable' or 'update').
     *
     * @return mixed Array containing status and notice messages.
     * @throws array if an error occurs during the download or extraction process.
     */
    public static function download($type = 'enable')
    {
        $result     = ['status' => false];
        $gzFilePath = self::getGzPath();

        try {
            $download_url = self::getDownloadUrl();

            $response = wp_remote_get($download_url, [
                'stream'   => true,
                'filename' => $gzFilePath,
                'timeout'  => 120,
            ]);

            // Check the HTTP status code
            $status_code = wp_remote_retrieve_response_code($response);
            if ($status_code !== 200) {
                throw new Exception(sprintf(__('Unexpected HTTP status code %1$d while downloading GeoIP database from: %2$s', 'wp-statistics'), $status_code, $download_url));
            }

            if (is_wp_error($response)) {
                throw new Exception(sprintf(__('Error downloading GeoIP database from: %1$s - %2$s', 'wp-statistics'), $download_url, $response->get_error_message()));
            }

            $DBFile = self::get_geo_ip_path();
            self::extractGzFile($gzFilePath, $DBFile);

            wp_delete_file($gzFilePath); // Clean up the temporary file

            $result['status'] = true;
            $result['notice'] = __('GeoIP Database successfully updated.', 'wp-statistics');

            if ($type === 'update') {
                Option::update('last_geoip_dl', time());
            }

            if (Option::get('auto_pop')) {
                BackgroundProcessFactory::batchUpdateIncompleteGeoIpForVisitors();
            }

        } catch (Exception $e) {
            wp_delete_file($gzFilePath); // Ensure temporary file is deleted in case of an error

            $result['notice'] = sprintf(__('Error: %1$s', 'wp-statistics'), $e->getMessage());
            WP_Statistics::log($result['notice'], 'error'); // Log the error for debugging
        }

        return $result;
    }

    /**
     * Gets the download URL for the GeoIP database.
     *
     * @return string
     */
    private static function getDownloadUrl()
    {
        if (Option::get('geoip_license_type') === "user-license" && Option::get('geoip_license_key')) {
            return add_query_arg(['license_key' => Option::get('geoip_license_key')], GeoIP::$library['userSource']);
        }
        return GeoIP::$library['source'];
    }

    /**
     * Retrieves the path for the temporary GZ file.
     *
     * @return string The full path to the GZ file.
     */
    private static function getGzPath()
    {
        $upload_dir = wp_upload_dir();
        return trailingslashit($upload_dir['basedir']) . self::$library['file'] . '.mmdb.gz';
    }

    /**
     * Extracts the database file from the downloaded GZ archive.
     *
     * @param string $gzFilePath The path to the downloaded GZ file.
     * @param string $destination The destination path for the extracted database file.
     * @throws Exception if extraction fails.
     */
    private static function extractGzFile($gzFilePath, $destination)
    {
        $uploadPath = Helper::get_uploads_dir(WP_STATISTICS_UPLOADS_DIR);

        if (!file_exists($uploadPath)) {
            if (!mkdir($uploadPath, 0755, true) && !is_dir($uploadPath)) {
                throw new Exception(sprintf(__('Error creating directory: %s', 'wp-statistics'), $uploadPath));
            }
        }

        /**
         * Check if the server is using MaxMind's GeoIP database.
         * If so, extract the database file from the archive.
         */
        if (Option::get('geoip_license_type') === "user-license" && Option::get('geoip_license_key')) {
            // Check if the PharData class is available.
            if (!class_exists('PharData')) {
                throw new Exception(__('PharData class not found.', 'wp-statistics'));
            }

            $tarGz         = new \PharData($gzFilePath);
            $fileInArchive = trailingslashit($tarGz->current()->getFileName()) . self::$library['file'] . '.' . self::$file_extension;

            // Extract the database file from the archive.
            $tarGz->extractTo($uploadPath, $fileInArchive, true); // Extract all files

            // Rename and remove the extracted directory.
            rename($uploadPath . '/' . $fileInArchive, $destination);
            rmdir($uploadPath . '/' . trailingslashit($tarGz->current()->getFileName()));

            return;
        }

        $gzHandle = gzopen($gzFilePath, 'rb');
        if (!$gzHandle) {
            throw new Exception(__('Failed to open GZ archive.', 'wp-statistics'));
        }

        $dbFileHandle = fopen($destination, 'wb'); // Open the destination file for writing
        if (!$dbFileHandle) {
            gzclose($gzHandle);
            throw new Exception(__('Failed to open destination file for writing.', 'wp-statistics'));
        }

        while (!gzeof($gzHandle)) {
            fwrite($dbFileHandle, gzread($gzHandle, 4096)); // Read from GZ and write to the destination file
        }

        gzclose($gzHandle);
        fclose($dbFileHandle);

        if (!file_exists($destination)) {
            throw new Exception(__('Error extracting GeoIP database file.', 'wp-statistics'));
        }
    }

    /**
     * Retrieves city information based on a given IP address.
     *
     * @param string|bool $ip The IP address to lookup. Defaults to the user's IP.
     * @param bool $dataScope Whether to include region and continent information.
     * @return array|string The city name or an array of location details.
     * @see https://github.com/maxmind/GeoIP2-php
     */
    public static function getCity($ip = false, $dataScope = false)
    {
        // Get the user's IP if not provided.
        $ip = ($ip === false ? IP::getIP() : $ip);

        $location = self::getLocation($ip);

        // Retrieve region and continent if requested.
        if ($dataScope) {
            return [
                'city'      => $location['city'],
                'region'    => $location['region'],
                'continent' => $location['continent']
            ];
        }

        return $location['city'];
    }

    /**
     * Generates a link to an external GeoIP tool for IP information.
     *
     * @param string $ip The IP address to query.
     * @return string URL to the GeoIP tool with the IP parameter.
     */
    public static function geoIPTools($ip)
    {
        return "https://redirect.li/map/?ip={$ip}";
    }
}
