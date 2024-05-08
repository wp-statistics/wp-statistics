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
    public static $ip_methods_server = array('HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR', 'HTTP_CLIENT_IP', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_X_REAL_IP', 'HTTP_INCAP_CLIENT_IP');

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
     * Returns available IP configuration options.
     *
     * @return array
     */
    public static function getIpOptions()
    {
        return array_merge(self::$ip_methods_server, ['sequential']);
    }

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

        // Check IP detection method
        if ($ip_method === 'sequential') {
            foreach (self::$ip_methods_server as $method) {
                if (isset($_SERVER[$method])) {
                    $ip = $_SERVER[$method];
                    break;
                }
            }
        } else {
            $ip = isset($_SERVER[$ip_method]) ? $_SERVER[$ip_method] : false;
        }

        /**
         * This Filter Used For Custom $_SERVER String
         * @see https://wp-statistics.com/sanitize-user-ip/
         */
        $ip = apply_filters('wp_statistics_sanitize_user_ip', sanitize_text_field($ip));

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
     * Generates a hashed version of an IP address using a daily salt, provided the hashing option is enabled.
     *
     * @example 192.168.1.1 -> #hash#e7b398f96b14993b571215e36b41850c65f39b1a
     * @param string|false $ip Optional. The IP address to be hashed. If false, the current user's IP is used.
     * @return string|false The hashed IP address if hashing is enabled and successful, false otherwise.
     */
    public static function hashUserIp($ip = false)
    {
        $date           = date('Y-m-d'); // Capture the current date to use in salt generation.
        $saltOptionName = 'wp_statistics_daily_salt'; // Define the option name for storing the daily salt.

        // Retrieve the currently stored daily salt from the WordPress options.
        $dailySalt = get_option($saltOptionName);

        // If today's date is different from the stored salt's date, generate and save a new daily salt.
        if (isset($dailySalt['date']) && $dailySalt['date'] != $date) {
            $dailySalt = [
                'date' => $date, // Update the salt's date to today.
                'salt' => sha1(wp_generate_password()) // Generate a new salt based on a new password and today's date.
            ];

            // Save the new daily salt in the WordPress options for future use.
            update_option($saltOptionName, $dailySalt);
        }

        // If there is no existing daily salt, generate and save it.
        if (!$dailySalt || !is_array($dailySalt)) {
            $dailySalt = [
                'date' => $date, // Set the salt's date to today.
                'salt' => sha1(wp_generate_password()) // Generate a new salt.
            ];

            // Save the new daily salt in the WordPress options.
            update_option($saltOptionName, $dailySalt);
        }

        // Determine the IP address to hash; use the provided IP or the current user's IP if none is provided.
        if (!$ip) {
            $ip = self::getIP();
        }

        // Retrieve the current user agent, defaulting to 'Unknown' if unavailable or empty.
        $userAgent = (UserAgent::getHttpUserAgent() == '' ? 'Unknown' : UserAgent::getHttpUserAgent());

        // Hash the combination of daily salt, IP, and user agent to create a unique identifier.
        // This hash is then prefixed and filtered for potential modification before being returned.
        return apply_filters('wp_statistics_hash_ip', self::$hash_ip_prefix . sha1($dailySalt['salt'] . $ip . $userAgent));
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
         * Check if the option to hash IP addresses is enabled in the settings.
         */
        if (Option::get('hash_ips') == true) {
            $user_ip = self::hashUserIp($user_ip);
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
        $visitorTable = DB::table('visitor');
        $result       = $wpdb->get_results("SELECT DISTINCT ip FROM {$visitorTable} WHERE ip NOT LIKE '#hash#%'");
        $resultUpdate = [];

        foreach ($result as $row) {
            if (!self::IsHashIP($row->ip)) {
                $resultUpdate[] = $wpdb->update(
                    $visitorTable,
                    array('ip' => self::hashUserIp($row->ip)),
                    array('ip' => $row->ip)
                );
            }
        }

        return count($resultUpdate);
    }

}