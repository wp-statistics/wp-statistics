<?php

namespace WP_STATISTICS;

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

                throw new \Exception("GeoIP database library not found in {$file}");
            }

            // Load the GeoIP Reader and cache it.
            self::$readerCache = new Reader($file);
            return self::$readerCache;

        } catch (\Exception $e) {
            // Log the exception message.
            \WP_Statistics::log($e->getMessage());

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
            'city'      => __('Unknown', 'wp-statistics'),
            'continent' => __('Unknown', 'wp-statistics'),
            'region'    => __('Unknown', 'wp-statistics'),
        ];

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

        } catch (\Exception $e) {
            // Log the exception message.
            \WP_Statistics::log($e->getMessage());
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
     * @throws \Exception If there is an issue during GeoIP lookup.
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
     */
    public static function download($type = 'enable')
    {
        try {
            if (!function_exists('WP_Filesystem')) {
                include_once ABSPATH . 'wp-admin/includes/file.php';
            }

            WP_Filesystem();
            global $wp_filesystem;

            // Initialize result array with a failure status.
            $result['status'] = false;

            // Load required functions if not already available.
            if (!function_exists('download_url')) {
                include(ABSPATH . 'wp-admin/includes/file.php');
            }
            if (!function_exists('wp_generate_password')) {
                include(ABSPATH . 'wp-includes/pluggable.php');
            }

            // Retrieve the WordPress upload directory path.
            $upload_dir = wp_upload_dir();

            // Check for the existence of gzopen function.
            if (false === function_exists('gzopen')) {
                return array_merge($result, array("notice" => __('Error: <code>gzopen()</code> Function Not Found!', 'wp-statistics')));
            }

            $isMaxmind = false;

            // Determine the download URL for the GeoIP database.
            if (Option::get('geoip_license_type') == "user-license" && Option::get('geoip_license_key')) {
                $download_url = add_query_arg(array(
                    'license_key' => Option::get('geoip_license_key')
                ), GeoIP::$library['userSource']);
                $isMaxmind    = true;
            } else {
                $download_url = GeoIP::$library['source'];
            }

            // Allow third-party plugins to modify the download URL.
            $download_url = apply_filters('wp_statistics_geo_ip_download_url', $download_url, GeoIP::$library['source']);

            ini_set('max_execution_time', '300');

            // Attempt to download the GeoIP database file.
            $response = wp_remote_get($download_url, array(
                'timeout'   => 300,
                'sslverify' => false
            ));

            if (is_wp_error($response)) {
                \WP_Statistics::log(array('code' => 'download_geoip', 'message' => $response->get_error_message()));
                return array_merge($result, array("notice" => $response->get_error_message()));
            }

            // Check the response code from the download request.
            if (wp_remote_retrieve_response_code($response) != '200') {
                return array_merge($result, array("notice" => sprintf(__('Error: %1$s, Request URL: %2$s', 'wp-statistics'), wp_remote_retrieve_body($response), $download_url)));
            }

            // Define the path for the GeoIP database file.
            $DBFile = self::get_geo_ip_path();

            // Check if the upload subdirectory exists, and create it if not.
            if (!file_exists($upload_dir['basedir'] . '/' . WP_STATISTICS_UPLOADS_DIR)) {
                if (!$wp_filesystem->mkdir($upload_dir['basedir'] . '/' . WP_STATISTICS_UPLOADS_DIR, 0755)) {
                    return array_merge($result, array("notice" => sprintf(__('Error Creating GeoIP Database Directory. Ensure Web Server Has Directory Creation Permissions in: %s', 'wp-statistics'), $upload_dir['basedir'])));
                }
            }

            // Check write permissions for the upload directory.
            if (!$wp_filesystem->is_writable($upload_dir['basedir'] . '/' . WP_STATISTICS_UPLOADS_DIR)) {
                return array_merge($result, array("notice" => sprintf(__('Error Setting Permissions for GeoIP Database Directory. Check Write Permissions for Directories in: %s', 'wp-statistics'), $upload_dir['basedir'])));
            }

            // Download the GeoIP database file to a temporary location.
            $TempFile = download_url($download_url);

            // Check for download errors and handle accordingly.
            if (is_wp_error($TempFile)) {
                return array_merge($result, array("notice" => sprintf(__('Error Downloading GeoIP Database from: %1$s - %2$s', 'wp-statistics'), $download_url, $TempFile->get_error_message())));
            } else {
                if ($isMaxmind) {
                    // Handle MaxMind-specific database extraction.
                    $phar          = new \PharData($TempFile);
                    $database      = self::$library['file'] . '.' . self::$file_extension;
                    $fileInArchive = trailingslashit($phar->current()->getFileName()) . $database;
                    $phar->extractTo(Helper::get_uploads_dir(WP_STATISTICS_UPLOADS_DIR), $fileInArchive, true);

                    @rename(trailingslashit(Helper::get_uploads_dir(WP_STATISTICS_UPLOADS_DIR)) . $fileInArchive, $DBFile);
                    @rmdir(trailingslashit(Helper::get_uploads_dir(WP_STATISTICS_UPLOADS_DIR)) . $phar->current()->getFileName());

                    if (!is_file($DBFile)) {
                        // Handle extraction errors.
                        @rmdir($DBFile);
                        wp_delete_file($TempFile);
                        return array_merge($result, array("notice" => __('There was an error creating the GeoIP database file.', 'wp-statistics')));
                    }
                } else {
                    // Handle extraction from gzipped file.
                    $ZipHandle = gzopen($TempFile, 'rb');

                    // Create the new file to unzip to.
                    $DBfh = fopen($DBFile, 'wb'); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen

                    // Check for errors during file opening.
                    if (!$ZipHandle) {
                        wp_delete_file($TempFile);

                        return array_merge($result, array("notice" => sprintf(__('Error Opening Downloaded GeoIP Database for Reading: %s', 'wp-statistics'), $TempFile)));
                    } else {
                        if (!$DBfh) {
                            wp_delete_file($TempFile);

                            return array_merge($result, array("notice" => sprintf(__('Error Opening Destination GeoIP Database for Writing: %s', 'wp-statistics'), $DBFile)));
                        } else {
                            // Write the extracted data to the database file.
                            while (($data = gzread($ZipHandle, 4096)) !== false) {
                                fwrite($DBfh, $data); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite
                            }

                            // Close the files.
                            gzclose($ZipHandle);
                            fclose($DBfh); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose

                            // Delete the temporary file.
                            wp_delete_file($TempFile);
                        }
                    }
                }

                // Update the result array with success status and message.
                $result['status'] = true;
                $result['notice'] = __('GeoIP Database Successfully Updated!', 'wp-statistics');

                // Update the option with the timestamp of the last download.
                if ($type == "update") {
                    Option::update('last_geoip_dl', time());
                }

                // Update incomplete GeoIP information if the option is enabled.
                if (Option::get('auto_pop')) {
                    BackgroundProcessFactory::batchUpdateIncompleteGeoIpForVisitors();
                }
            }

            // Send notification email if the option is enabled.
            if (Option::get('geoip_report') == true) {

                Helper::send_mail(
                    Option::getEmailNotification(),
                    __('GeoIP update on', 'wp-statistics') . ' ' . get_bloginfo('name'),
                    $result['notice'],
                    true,
                    array("email_title" => __('GeoIP update on', 'wp-statistics') . ' <a href="' . get_bloginfo('url') . '" target="_blank" style="text-decoration: none; color: #303032; font-family: Roboto,Arial,Helvetica,sans-serif; font-size: 16px; font-weight: 600; line-height: 18.75px;font-style: italic">' . get_bloginfo('name') . '</a>')
                );
            }

        } catch (\Exception $e) {
            // Log any exceptions that occur during the download process.
            $result['notice'] = sprintf(__('Error: %1$s', 'wp-statistics'), $e->getMessage());
        }

        return $result;
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
