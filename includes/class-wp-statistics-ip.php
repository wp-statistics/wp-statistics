<?php

namespace WP_STATISTICS;

use IPTools\Range;

class IP
{
    /**
     * Default User IP
     *
     * @var string
     */
    public static $default_ip = '127.0.0.1';

    /**
     * Default Private SubNets
     *
     * @var array
     */
    public static $private_SubNets = array('10.0.0.0/8', '172.16.0.0/12', '192.168.0.0/16', '127.0.0.1/24', 'fc00::/7');

    /**
     * List Of Common $_SERVER for get Users IP
     *
     * @var array
     */
    public static $ip_methods_server = array('REMOTE_ADDR', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'HTTP_X_REAL_IP', 'HTTP_X_CLUSTER_CLIENT_IP');

    /**
     * Default $_SERVER for Get User Real IP
     *
     * @var string
     */
    public static $default_ip_method = 'REMOTE_ADDR';

    /**
     * Hash IP Prefix
     *
     * @var string
     */
    public static $hash_ip_prefix = '#hash#';

    /**
     * Returns the current IP address of the remote client.
     *
     * @return bool|string
     */
    public static function getIP()
    {

        // Set Default
        $ip = false;

        // Get User IP Methods
        $ip_method = self::getIPMethod();

        // Check isset $_SERVER
        if (isset($_SERVER[$ip_method])) {
            $ip = sanitize_text_field($_SERVER[$ip_method]);
        }

        /**
         * This Filter Used For Custom $_SERVER String
         * @see https://wp-statistics.com/sanitize-user-ip/
         */
        $ip = apply_filters('wp_statistics_sanitize_user_ip', $ip);

        // Sanitize For HTTP_X_FORWARDED
        foreach (explode(',', $ip) as $user_ip) {
            $user_ip = trim($user_ip);
            if (self::isIP($user_ip) != false) {
                $ip = $user_ip;
            }
        }

        // If no valid ip address has been found, use default ip.
        if (false === $ip) {
            $ip = self::$default_ip;
        }

        return apply_filters('wp_statistics_user_ip', sanitize_text_field($ip));
    }

    /**
     * Generate hash string
     *
     * @param bool $ip
     * @return bool
     */
    public static function getHashIP($ip = false)
    {
        // Check if the option to hash IP addresses is enabled in the settings.
        if (Option::get('hash_ips') == true) {
            $date           = date('Y-m-d'); // Get the current date to ensure the salt is unique for each day.
            $saltOptionName = 'wp_statistics_daily_salt'; // The option name where the daily salt is stored.

            // Retrieve the currently stored daily salt from the WordPress options.
            $dailySalt = get_option($saltOptionName);
            // Generate a new daily salt using a combination of a WordPress salt and the current date.
            $newDailySalt = sha1(wp_salt() . $date);

            // Check if the stored salt is different from the newly generated salt.
            if ($dailySalt != $newDailySalt) {
                $dailySalt = $newDailySalt; // Update the variable to use the new salt.

                // Save the new daily salt in the WordPress options for future use.
                update_option($saltOptionName, $newDailySalt);
            }

            // Determine the IP address to be hashed; use the provided IP or fetch the current user's IP if not provided.
            $ip = ($ip === false ? self::getIP() : $ip);
            // Retrieve the current user agent, defaulting to 'Unknown' if it's not available or empty.
            $userAgent = (UserAgent::getHttpUserAgent() == '' ? 'Unknown' : UserAgent::getHttpUserAgent());

            // Create a hash of the daily salt combined with the IP and user agent, providing a unique identifier.
            // This hash is then prefixed and passed through a filter for potential modification before being returned.
            return apply_filters('wp_statistics_hash_ip', self::$hash_ip_prefix . sha1($dailySalt . $ip . $userAgent));
        }

        // Return false if hashing IP addresses is not enabled, indicating no action is taken.
        return false;
    }

    /**
     * Check IP is Hashed
     *
     * @param $ip
     * @return bool
     */
    public static function IsHashIP($ip)
    {
        return (substr($ip, 0, strlen(self::$hash_ip_prefix)) == self::$hash_ip_prefix);
    }

    /**
     * Store User IP To Database
     */
    public static function getStoreIP()
    {

        //Get User ip
        $user_ip = self::getIP();

        // use 127.0.0.1 If no valid ip address has been found.
        if (false === $user_ip) {
            return self::$default_ip;
        }

        /**
         * If the anonymize IP is enabled because of the data privacy & GDPR.
         *
         * @example 192.168.1.1 -> 192.168.1.0
         * @example 0897:D836:7A7C:803F:344B:5348:71EE:1130 -> 897:d836:7a7c:803f::
         */
        if (Option::get('anonymize_ips') == true) {
            $user_ip = wp_privacy_anonymize_ip($user_ip);
        }

        /**
         * If the hash IP is enabled because of the data privacy & GDPR.
         * @example 192.168.1.1 -> #hash#e7b398f96b14993b571215e36b41850c65f39b1a
         */
        if (self::getHashIP()) {
            $user_ip = self::getHashIP($user_ip);
        }

        return sanitize_text_field($user_ip);
    }

    /**
     * Check IP Has The Custom IP Range List
     *
     * @param $ip
     * @param array $range
     * @return bool
     * @throws \Exception
     */
    public static function CheckIPRange($range = array(), $ip = false)
    {

        // Get User IP
        $ip = ($ip === false ? IP::getIP() : $ip);

        // Get Range OF This IP
        try {
            $ip = new \IPTools\IP($ip);
        } catch (\Exception $e) {
            \WP_Statistics::log($e->getMessage());
            $ip = new \IPTools\IP(self::$default_ip);
        }

        // Check List
        foreach ($range as $list) {
            try {
                $contains_ip = Range::parse($list)->contains($ip);
            } catch (\Exception $e) {
                \WP_Statistics::log($e->getMessage());
                $contains_ip = false;
            }

            if ($contains_ip) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check Validation IP
     *
     * @param $ip
     * @return bool
     */
    public static function isIP($ip)
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
    }

    /**
     * what is Method $_SERVER for get User Real IP
     */
    public static function getIPMethod()
    {
        $ip_method = Option::get('ip_method');
        return ($ip_method != false ? $ip_method : self::$default_ip_method);
    }

    /**
     * Check IP contain Special Character
     *
     * @param $ip
     * @return bool
     */
    public static function check_sanitize_ip($ip)
    {
        $preg = preg_replace('/[^0-9- .:]/', '', $ip);
        return $preg == $ip;
    }

    /**
     * Update All Hash String For Hash IP
     */
    public static function Update_HashIP_Visitor()
    {
        global $wpdb;

        // Get the rows from the Visitors table.
        $result = $wpdb->get_results("SELECT DISTINCT ip FROM " . DB::table('visitor'));
        foreach ($result as $row) {
            if (IP::IsHashIP($row->ip)) {
                $wpdb->update(
                    DB::table('visitor'),
                    array('ip' => IP::$hash_ip_prefix . sha1($row->ip . Helper::random_string()),),
                    array('ip' => $row->ip)
                );
            }
        }
    }

}