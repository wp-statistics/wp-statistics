<?php

namespace WP_STATISTICS;

use Exception;
use WP_Statistics;
use WP_Statistics\Dependencies\IPTools\Range;

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
    public static $default_ip_method = 'sequential';

    /**
     * Hash IP Prefix
     *
     * @var string
     */
    public static $hash_ip_prefix = '#hash#';

    /**
     * Returns all IP method options
     *
     * @return array
     */
    public static function getIpOptions()
    {
        $ipOptions = self::$ip_methods_server;

        if (isset($_SERVER[Option::get('ip_method')])) {
            $ipOptions[] = Option::get('ip_method');
        }

        return array_unique($ipOptions);
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
        $ip_method = self::getIpMethod();

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

            // Ensure backward compatibility for IP handling.
            if ($ip == '') {
                // If the IP address is not available, set the IP method to the default value for the next visitor to ensure consistent behavior.
                Option::update('ip_method', self::$default_ip_method);
            }
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

    public static function getIpVersion()
    {
        try {
            $ipTools = new \WP_Statistics\Dependencies\IPTools\IP(self::getIP());
            return $ipTools->getVersion();
        } catch (Exception $e) {
            return '';
        }
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
        if (Option::get('anonymize_ips') == true || Helper::shouldTrackAnonymously()) {
            $user_ip = wp_privacy_anonymize_ip($user_ip);
        }

        /**
         * Check if the option to hash IP addresses is enabled in the settings.
         */
        if (Option::get('hash_ips') == true || Helper::shouldTrackAnonymously()) {
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
     * @throws Exception
     */
    public static function CheckIPRange($range = array(), $ip = false)
    {

        // Get User IP
        $ip = ($ip === false ? IP::getIP() : $ip);

        // Get Range OF This IP
        try {
            $ip = new WP_Statistics\Dependencies\IPTools\IP($ip);
        } catch (Exception $e) {
            WP_Statistics::log($e->getMessage(), 'warning');
            $ip = new WP_Statistics\Dependencies\IPTools\IP(self::$default_ip);
        }

        // Check List
        foreach ($range as $list) {
            try {
                $parsedRange = Range::parse($list);
                $contains_ip = false;

                if ($parsedRange->contains($ip)) {
                    $contains_ip = true;
                }
            } catch (Exception $e) {
                WP_Statistics::log($e->getMessage(), 'warning');
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
     * Retrieves the method used to obtain the user's real IP address.
     *
     * This method checks the configured IP method from the options and ensures
     * backward compatibility by setting the option to a default value if an invalid
     * method is found.
     *
     * @return string The method used to get the user's real IP address.
     */
    public static function getIpMethod()
    {
        // Retrieve the IP method from options
        $ipMethod = Option::get('ip_method');

        // If no method is set, return the default IP method
        if (empty($ipMethod)) {
            return self::$default_ip_method;
        }

        // Check for backward compatibility
        if (!in_array($ipMethod, self::getIpOptions())) {
            // Set the option to the default method for backward compatibility
            Option::update('ip_method', self::$default_ip_method);

            return self::$default_ip_method;
        }

        // Return the valid IP method
        return $ipMethod;
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