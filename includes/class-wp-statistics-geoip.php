<?php

namespace WP_STATISTICS;

class GeoIP
{
    /**
     * List Geo ip Library
     *
     * @var array
     */
    public static $library = array(
        'country' => array(
            'source'     => 'https://cdn.jsdelivr.net/npm/geolite2-country/GeoLite2-Country.mmdb.gz',
            'userSource' => 'https://download.maxmind.com/app/geoip_download?edition_id=GeoLite2-Country&license_key=&suffix=tar.gz',
            'file'       => 'GeoLite2-Country',
            'opt'        => 'geoip',
            'cache'      => 31536000 //1 Year
        ),
        'city'    => array(
            'source'     => 'https://cdn.jsdelivr.net/npm/geolite2-city/GeoLite2-City.mmdb.gz',
            'userSource' => 'https://download.maxmind.com/app/geoip_download?edition_id=GeoLite2-City&license_key=&suffix=tar.gz',
            'file'       => 'GeoLite2-City',
            'opt'        => 'geoip_city',
            'cache'      => 6998000 //3 Month
        )
    );

    /**
     * Geo IP file Extension
     *
     * @var string
     */
    public static $file_extension = 'mmdb';

    /**
     * Default Private Country
     *
     * @var string
     */
    public static $private_country = '000';

    /**
     * Get Geo IP Path
     *
     * @param $pack
     * @return mixed
     */
    public static function get_geo_ip_path($pack)
    {
        return wp_normalize_path(path_join(Helper::get_uploads_dir(WP_STATISTICS_UPLOADS_DIR), self::$library[strtolower($pack)]['file'] . '.' . self::$file_extension));
    }

    /**
     * Check Is Active Geo-ip
     *
     * @param mixed $which
     * @param bool $CheckDBFile
     * @return boolean
     */
    public static function active($which = false, $CheckDBFile = true)
    {

        //Default Geo-Ip Option name
        $which = ($which === false ? 'country' : $which);
        $opt   = ($which == "city" ? 'geoip_city' : 'geoip');
        $value = Option::get($opt);

        //Check Exist GEO-IP file
        $file = self::get_geo_ip_path($which);
        if ($CheckDBFile and !file_exists($file)) {
            if ($value) {
                Option::update($opt, false);
            }
            return false;
        }

        // Return
        return $value;
    }

    /**
     * geo ip Loader
     *
     * @param $pack
     * @return bool|\GeoIp2\Database\Reader
     */
    public static function Loader($pack)
    {
        // Check file Exist
        $file = self::get_geo_ip_path($pack);

        if (file_exists($file)) {
            try {

                //Load GeoIP Reader
                return new \GeoIp2\Database\Reader($file);
            } catch (\Exception $e) {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Get Default Country Code
     *
     * @return string
     */
    public static function getDefaultCountryCode()
    {

        $opt = Option::get('private_country_code');
        if (isset($opt) and !empty($opt)) {
            return trim($opt);
        }

        return self::$private_country;
    }

    /**
     * Get Country Detail By User IP
     *
     * @param bool $ip
     * @param string $return
     * @return string|null
     * @throws \Exception
     * @see https://github.com/maxmind/GeoIP2-php
     */
    public static function getCountry($ip = false, $return = 'isoCode')
    {

        // Check in WordPress Cache
        $user_country = wp_cache_get('country-' . $ip, 'wp-statistics');
        if ($user_country != false) {
            return $user_country;
        }

        // Check in WordPress Database
        $user_country = self::getUserCountryFromDB($ip);
        if ($user_country != false) {
            wp_cache_set('country-' . $ip, $user_country, 'wp-statistics', DAY_IN_SECONDS);
            return $user_country;
        }

        // Default Country Name
        $default_country = self::getDefaultCountryCode();

        // Get User IP
        $ip = ($ip === false ? IP::getIP() : $ip);

        // Check Unknown IP
        if ($default_country != self::$private_country) {
            if (IP::CheckIPRange(IP::$private_SubNets)) {
                return $default_country;
            }
        }

        // Sanitize IP
        if (IP::isIP($ip) === false) {
            return $default_country;
        }

        if (Option::get('geoip')) {
            try {
                // Load GEO-IP
                $reader = self::Loader('country');

                if ($reader != false) {
                    //Search in Geo-IP
                    $record = $reader->country($ip);

                    //Get Country
                    if ($return == "all") {
                        $location = $record->country;
                    } else {
                        $location = $record->country->{$return};
                    }
                }

            } catch (\Exception $e) {
                \WP_Statistics::log($e->getMessage());
            }
        }

        # Check Has Location
        if (isset($location) and !empty($location)) {
            wp_cache_set('country-' . $ip, $location, 'wp-statistics', DAY_IN_SECONDS);
            return $location;
        }

        return $default_country;
    }

    /**
     * Get User Country From Database
     *
     * @param $ip
     * @return false|string
     */
    public static function getUserCountryFromDB($ip)
    {
        global $wpdb;

        $date = date('Y-m-d', current_time('timestamp') - self::$library['country']['cache']); // phpcs:ignore 	WordPress.DateTime.RestrictedFunctions.date_date
        $user = $wpdb->get_row(
            $wpdb->prepare("SELECT `location` FROM `" . DB::table('visitor') . "` WHERE `ip` = %s and `last_counter` >= %s ORDER BY `ID` DESC LIMIT 1", $ip, $date)
        );

        if (null !== $user) {
            return $user->location;
        }

        return false;
    }

    /**
     * This function downloads the GeoIP database from MaxMind.
     *
     * @param $pack
     * @param string $type
     *
     * @return mixed
     */
    public static function download($pack, $type = "enable")
    {
        try {
            WP_Filesystem();
            global $wp_filesystem;

            // Create Empty Return Function
            $result["status"] = false;

            // Sanitize Pack name
            $pack = strtolower($pack);

            // If GeoIP is disabled, bail out.
            if ($type == "update" and Option::get(GeoIP::$library[$pack]['opt']) == '') {
                return '';
            }

            // Load Require Function
            if (!function_exists('download_url')) {
                include(ABSPATH . 'wp-admin/includes/file.php');
            }
            if (!function_exists('wp_generate_password')) {
                include(ABSPATH . 'wp-includes/pluggable.php');
            }

            // Get the upload directory from WordPress.
            $upload_dir = wp_upload_dir();

            // We need the gzopen() function
            if (false === function_exists('gzopen')) {
                if ($type == "enable") {
                    Option::update(GeoIP::$library[$pack]['opt'], '');
                }

                return array_merge($result, array("notice" => __('Error: <code>gzopen()</code> Function Not Found!', 'wp-statistics')));
            }

            // This is the location of the file to download.
            if (Option::get('geoip_license_type') == "user-license" && Option::get('geoip_license_key')) {
                $download_url = add_query_arg(array(
                    'license_key' => Option::get('geoip_license_key')
                ), GeoIP::$library[$pack]['userSource']);
            } else {
                $download_url = GeoIP::$library[$pack]['source'];
            }

            // Apply filter to allow third-party plugins to modify the download url
            $download_url = apply_filters('wp_statistics_geo_ip_download_url', $download_url, GeoIP::$library[$pack]['source'], $pack);

            ini_set('max_execution_time', '60');

            $response = wp_remote_get($download_url, array(
                'timeout'   => 60,
                'sslverify' => false
            ));

            if (is_wp_error($response)) {
                \WP_Statistics::log(array('code' => 'download_geoip', 'type' => $pack, 'message' => $response->get_error_message()));
                return array_merge($result, array("notice" => $response->get_error_message()));
            }

            // Change download url if the maxmind.com doesn't response.
            if (wp_remote_retrieve_response_code($response) != '200') {
                return array_merge($result, array("notice" => sprintf(__('Error: %1$s, Request URL: %2$s', 'wp-statistics'), wp_remote_retrieve_body($response), $download_url)));
            }

            // Create a variable with the name of the database file to download.
            $DBFile = self::get_geo_ip_path($pack);

            // Check to see if the subdirectory we're going to upload to exists, if not create it.
            if (!file_exists($upload_dir['basedir'] . '/' . WP_STATISTICS_UPLOADS_DIR)) {
                if (!$wp_filesystem->mkdir($upload_dir['basedir'] . '/' . WP_STATISTICS_UPLOADS_DIR, 0755)) {
                    if ($type == "enable") {
                        Option::update(GeoIP::$library[$pack]['opt'], '');
                    }

                    return array_merge($result, array("notice" => sprintf(__('Error Creating GeoIP Database Directory. Ensure Web Server Has Directory Creation Permissions in: %s', 'wp-statistics'), $upload_dir['basedir'])));
                }
            }

            if (!$wp_filesystem->is_writable($upload_dir['basedir'] . '/' . WP_STATISTICS_UPLOADS_DIR)) {
                if ($type == "enable") {
                    Option::update(GeoIP::$library[$pack]['opt'], '');
                }

                return array_merge($result, array("notice" => sprintf(__('Error Setting Permissions for GeoIP Database Directory. Check Write Permissions for Directories in: %s', 'wp-statistics'), $upload_dir['basedir'])));
            }

            // Download the file from MaxMind, this places it in a temporary location.
            $TempFile = download_url($download_url);

            // If we failed, through a message, otherwise proceed.
            if (is_wp_error($TempFile)) {
                if ($type == "enable") {
                    Option::update(GeoIP::$library[$pack]['opt'], '');
                }

                return array_merge($result, array("notice" => sprintf(__('Error Downloading GeoIP Database from: %1$s - %2$s', 'wp-statistics'), $download_url, $TempFile->get_error_message())));
            } else {
                // Open the downloaded file to unzip it.
                $ZipHandle = gzopen($TempFile, 'rb');

                // Create th new file to unzip to.
                $DBfh = fopen($DBFile, 'wb'); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen

                // If we failed to open the downloaded file, through an error and remove the temporary file.  Otherwise do the actual unzip.
                if (!$ZipHandle) {
                    if ($type == "enable") {
                        Option::update(GeoIP::$library[$pack]['opt'], '');
                    }

                    wp_delete_file($TempFile);
                    return array_merge($result, array("notice" => sprintf(__('Error Opening Downloaded GeoIP Database for Reading: %s', 'wp-statistics'), $TempFile)));
                } else {
                    // If we failed to open the new file, throw and error and remove the temporary file.  Otherwise actually do the unzip.
                    if (!$DBfh) {
                        if ($type == "enable") {
                            Option::update(GeoIP::$library[$pack]['opt'], '');
                        }

                        wp_delete_file($TempFile);
                        return array_merge($result, array("notice" => sprintf(__('Error Opening Destination GeoIP Database for Writing: %s', 'wp-statistics'), $DBFile)));
                    } else {
                        while (($data = gzread($ZipHandle, 4096)) != false) {
                            fwrite($DBfh, $data); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite
                        }

                        // Close the files.
                        gzclose($ZipHandle);
                        fclose($DBfh); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose

                        // Delete the temporary file.
                        wp_delete_file($TempFile);

                        // Display the success message.
                        $result["status"] = true;
                        $result["notice"] = __('GeoIP Database Successfully Updated!', 'wp-statistics');

                        // Update the options to reflect the new download.
                        if ($type == "update") {
                            Option::update('last_geoip_dl', time());
                            Option::update('update_geoip', false);
                        }

                        // Populate any missing GeoIP information if the user has selected the option.
                        if ($pack == "country") {
                            if (Option::get('geoip') && GeoIP::IsSupport() && Option::get('auto_pop')) {
                                self::Update_GeoIP_Visitor();
                            }
                        }
                    }
                }
            }

            // Send Email
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
            $result['notice'] = sprintf(__('Error: %1$s', 'wp-statistics'), $e->getMessage());
        }

        return $result;
    }

    /**
     * Update All GEO-IP Visitors
     *
     * @return array
     */
    public static function Update_GeoIP_Visitor()
    {
        global $wpdb;

        // Find all rows in the table that currently don't have GeoIP info or have an unknown ('000') location.
        $result = $wpdb->get_results(
            $wpdb->prepare("SELECT id,ip FROM `" . DB::table('visitor') . "` WHERE location = '' or location = %s or location IS NULL", GeoIP::$private_country)
        );

        // Try create a new reader instance.
        $reader = false;
        if (Option::get('geoip')) {
            $reader = GeoIP::Loader('country');
        }

        if ($reader === false) {
            return array('status' => false, 'data' => __('Cannot Load GeoIP Database. Ensure It\'s Downloaded via Settings Page.', 'wp-statistics'));
        }

        $count = 0;

        // Loop through all the missing rows and update them if we find a location for them.
        foreach ($result as $item) {
            $count++;

            // If the IP address is only a hash, don't bother updating the record.
            if (IP::IsHashIP($item->ip) === false and $reader != false) {
                try {
                    $record   = $reader->country($item->ip);
                    $location = $record->country->isoCode;
                    if ($location == "") {
                        $location = GeoIP::$private_country;
                    }
                } catch (\Exception $e) {
                    \WP_Statistics::log($e->getMessage());
                    $location = GeoIP::$private_country;
                }

                // Update the row in the database.
                $wpdb->update(
                    DB::table('visitor'),
                    array('location' => $location),
                    array('id' => $item->id)
                );
            }
        }

        return array('status' => true, 'data' => sprintf(__('Updated %s GeoIP Records in Visitor Database.', 'wp-statistics'), $count));
    }

    /**
     * if PHP modules we need for GeoIP exists.
     *
     * @return bool
     */
    public static function IsSupport()
    {
        $enabled = true;

        // PHP cURL extension installed
        if (!function_exists('curl_init')) {
            $enabled = false;
        }

        // PHP NOT running in safe mode
        if (ini_get('safe_mode')) {
            // Double check php version, 5.4 and above don't support safe mode but the ini value may still be set after an upgrade.
            if (!version_compare(phpversion(), '5.4', '<')) {
                $enabled = false;
            }
        }

        return $enabled;
    }

    /**
     * Get City Detail By User IP
     *
     * @param string|bool $ip
     * @param bool $dataScope
     * @return array|string
     * @see https://github.com/maxmind/GeoIP2-php
     */
    public static function getCity($ip = false, $dataScope = false)
    {
        $default_location = [
            'city'      => __('Unknown', 'wp-statistics'),
            'region'    => __('Unknown', 'wp-statistics'),
            'continent' => __('Unknown', 'wp-statistics')
        ];

        // Get User IP
        $ip = ($ip === false ? IP::getIP() : $ip);

        // Load GEO-IP
        $reader = self::Loader('city');

        //Get City name
        if ($reader != false && IP::isIP($ip) != false) {
            try {
                //Search in Geo-IP
                $record = $reader->city($ip);

                $location = [];

                //Get City
                $city             = $record->city->name;
                $location['city'] = !empty($city) ? $city : $default_location['city'];

                //Get Region
                if ($dataScope) {
                    $region             = $record->mostSpecificSubdivision->name;
                    $location['region'] = !empty($region) ? $region : $default_location['region'];

                    // Get Continent
                    $continent             = $record->continent->name;
                    $location['continent'] = !empty($continent) ? $continent : $default_location['continent'];
                }

            } catch (\Exception $e) {
                /**
                 * For debugging, you can comment out the logger.
                 */
                //\WP_Statistics::log($e->getMessage());
            }
        }

        # Check Has Location
        if (isset($location)) {
            return $dataScope ? $location : $location['city'];
        }

        return $dataScope ? $default_location : $default_location['city'];
    }

    /**
     * Geo IP Tools Link
     *
     * @param $ip
     * @return string
     */
    public static function geoIPTools($ip)
    {
        //return "http://www.geoiptool.com/en/?IP={$ip}";
        return "https://redirect.li/map/?ip={$ip}";
    }
}