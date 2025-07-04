<?php

namespace WP_Statistics\Context;

use WP_Statistics\Service\Analytics\DeviceDetection\UserAgent;
use WP_Statistics\Service\Tracking\TrackerHelper;
use WP_Statistics\Context\Option;
use WP_Statistics;
use ErrorException;
use Exception;

/**
 * Context helper for IP address management and utilities.
 *
 * Provides static methods for retrieving, validating, and processing IP addresses.
 * Handles IP hashing, anonymization, and range checking functionality.
 *
 * @package WP_Statistics\Context
 * @since   15.0.0
 */
final class Ip
{
    /**
     * Default User IP.
     *
     * @var string
     */
    public static string $defaultIp = '127.0.0.1';

    /**
     * Default Private SubNets.
     *
     * @var array
     */
    public static array $privateSubNets = [
        '10.0.0.0/8',
        '172.16.0.0/12',
        '192.168.0.0/16',
        '127.0.0.1/24',
        'fc00::/7',
        '::1'
    ];

    /**
     * List of common $_SERVER variables for getting user IP.
     *
     * @var array
     */
    public static array $ipMethodsServer = [
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED',
        'REMOTE_ADDR',
        'HTTP_CLIENT_IP',
        'HTTP_X_CLUSTER_CLIENT_IP',
        'HTTP_X_REAL_IP',
        'HTTP_INCAP_CLIENT_IP'
    ];

    /**
     * Default $_SERVER method for getting user real IP.
     *
     * @var string
     */
    public static string $defaultIpMethod = 'sequential';

    /**
     * Hash IP prefix.
     *
     * @var string
     */
    public static string $hashIpPrefix = '#hash#';

    /**
     * Returns all available detection headers.
     *
     * @return array
     */
    public static function getDetectionHeaders()
    {
        $headers = self::$ipMethodsServer;

        if (isset($_SERVER[self::getMethod()])) {
            $headers[] = self::getMethod();
        }

        return array_unique($headers);
    }

    /**
     * Returns the current IP address of the remote client.
     *
     * @return string The client's IP address.
     */
    public static function getCurrent()
    {
        $ip = false;

        $ipMethod = self::getMethod();

        if ($ipMethod === 'sequential') {
            foreach (self::$ipMethodsServer as $method) {
                if (isset($_SERVER[$method])) {
                    $ip = $_SERVER[$method];
                    break;
                }
            }
        } else {
            $ip = $_SERVER[$ipMethod] ?? false;

            // Ensure backward compatibility for IP handling
            if (empty($ip)) {
                // If the IP address is not available, set the IP method to the default value for the next visitor
                Option::updateValue('ip_method', self::$defaultIpMethod);
            }
        }

        /**
         * Filters the user IP address before sanitization.
         *
         * @param string $ip The raw IP address.
         */
        $ip = apply_filters('wp_statistics_sanitize_user_ip', sanitize_text_field($ip));

        // Sanitize for HTTP_X_FORWARDED (handle comma-separated IPs)
        if (!empty($ip)) {
            foreach (explode(',', $ip) as $userIp) {
                $userIp = trim($userIp);
                if (self::isValid($userIp)) {
                    $ip = $userIp;
                    break;
                }
            }
        }

        // If no valid IP address has been found, use default IP
        if (empty($ip) || !self::isValid($ip)) {
            $ip = self::$defaultIp;
        }

        /**
         * Filters the final user IP address.
         *
         * @param string $ip The sanitized IP address.
         */
        return apply_filters('wp_statistics_user_ip', sanitize_text_field($ip));
    }

    /**
     * Get the IP version (IPv4 or IPv6).
     *
     * @param string|null $ip Optional. IP address to check. If null, uses current user IP.
     * @return string 'IPv4', 'IPv6', or empty string if invalid.
     */
    public static function getVersion($ip = null)
    {
        if ($ip === null) {
            $ip = self::getCurrent();
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return 'IPv4';
        } elseif (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return 'IPv6';
        }

        return '';
    }

    /**
     * Gets or creates the daily salt for IP hashing.
     *
     * @return string The daily salt string.
     */
    public static function getSalt()
    {
        $date      = date('Y-m-d');
        $dailySalt = Option::getValue('daily_salt', []);

        if (isset($dailySalt['date']) && $dailySalt['date'] !== $date) {
            $dailySalt = [
                'date' => $date,
                'salt' => hash('sha256', wp_generate_password())
            ];
            Option::updateValue('daily_salt', $dailySalt);
        }

        if (!$dailySalt || !is_array($dailySalt)) {
            $dailySalt = [
                'date' => $date,
                'salt' => hash('sha256', wp_generate_password())
            ];
            Option::updateValue('daily_salt', $dailySalt);
        }

        return $dailySalt['salt'];
    }

    /**
     * Generates a hashed version of an IP address using a daily salt.
     *
     * This method creates a secure hash of the IP address combined with user agent
     * and a daily rotating salt for privacy protection.
     *
     * @param string|null $ip Optional. The IP address to hash. If null, uses current user IP.
     * @return string The hashed IP address with prefix.
     */
    public static function hash($ip = null)
    {
        if ($ip === null) {
            $ip = self::getCurrent();
        }

        $salt      = self::getSalt();
        $userAgent = UserAgent::getHttpUserAgent();

        $hash          = hash('sha256', $salt . $ip . $userAgent);
        $truncatedHash = substr(self::$hashIpPrefix . $hash, 0, 46);

        /**
         * Filters the hashed IP address.
         *
         * @param string $truncatedHash The hashed IP address.
         */
        return apply_filters('wp_statistics_hash_ip', $truncatedHash);
    }

    /**
     * Check if IP is hashed.
     *
     * @param string $ip The IP address to check.
     * @return bool True if IP is hashed, false otherwise.
     */
    public static function isHashed(string $ip)
    {
        return substr($ip, 0, strlen(self::$hashIpPrefix)) === self::$hashIpPrefix;
    }

    /**
     * Get IP address for storage in database.
     *
     * This method processes the IP address according to privacy settings,
     * applying anonymization and/or hashing as configured.
     *
     * @return string
     */
    public static function getAnonymized()
    {
        $userIp = self::getCurrent();

        // Use default IP if no valid IP address found
        if (empty($userIp)) {
            return self::$defaultIp;
        }

        /**
         * Anonymize IP if enabled for privacy & GDPR compliance.
         *
         * @example 192.168.1.1 -> 192.168.1.0
         * @example 2001:db8::1 -> 2001:db8::
         */
        if (Option::getValue('anonymize_ips') || TrackerHelper::shouldTrackAnonymously()) {
            $userIp = wp_privacy_anonymize_ip($userIp);
        }

        /**
         * Hash IP if enabled in settings.
         */
        if (Option::getValue('hash_ips') || TrackerHelper::shouldTrackAnonymously()) {
            $userIp = self::hash($userIp);
        }

        return sanitize_text_field($userIp);
    }

    /**
     * Check if the given IP is within any of the specified IP ranges.
     *
     * @param array $ranges Array of IP ranges to check against.
     * @param string|null $ip Optional. IP address to check. If null, uses current user IP.
     * @return bool True if IP is within any range, false otherwise.
     * @throws Exception When IP address or range is invalid.
     */
    public static function isInRange($ranges = [], $ip = null)
    {
        $isWithinRange = false;

        // Get user IP if not provided
        if ($ip === null) {
            $ip = self::getCurrent();
        }

        // Check each range
        foreach ($ranges as $range) {
            try {
                // Handle direct IP comparison (not CIDR)
                if (strpos($range, '/') === false) {
                    if ($ip === $range) {
                        $isWithinRange = true;
                        break;
                    }
                    continue;
                }

                // Parse CIDR notation
                [$rangeIp, $netmask] = explode('/', $range, 2);

                // Validate netmask for IPv4
                if (self::isV4($rangeIp) && ($netmask < 0 || $netmask > 32)) {
                    continue;
                }

                // Validate netmask for IPv6
                if (self::isV6($rangeIp) && ($netmask < 0 || $netmask > 128)) {
                    continue;
                }

                // Skip if IP versions don't match
                if ((self::isV4($ip) && self::isV6($rangeIp)) ||
                    (self::isV6($ip) && self::isV4($rangeIp))) {
                    continue;
                }

                // Convert IP and range to binary
                $binIp    = inet_pton($ip);
                $binRange = inet_pton($rangeIp);

                if ($binIp === false || $binRange === false) {
                    throw new ErrorException(esc_html__('Invalid IP address or Range.', 'wp-statistics'));
                }

                // Calculate network comparison
                $bytes        = strlen($binIp);
                $bits         = absint($netmask);
                $netmaskBytes = ceil($bits / 8);
                $netmaskBin   = str_repeat("\xff", $netmaskBytes);

                // Handle partial bytes
                if ($bits % 8 !== 0) {
                    $remainingBits = 8 - ($bits % 8);
                    $netmaskBin    = substr($netmaskBin, 0, -1) . chr(256 - pow(2, $remainingBits));
                }

                // Pad netmask
                $netmaskBin = str_pad($netmaskBin, $bytes, "\x00");

                if (($binIp & $netmaskBin) === ($binRange & $netmaskBin)) {
                    $isWithinRange = true;
                    break;
                }

            } catch (Exception $e) {
                WP_Statistics::log($e->getMessage(), 'warning');
                continue;
            }
        }

        return $isWithinRange;
    }

    /**
     * Check if IP is valid (not private or reserved).
     *
     * @param string $ip The IP address to validate.
     * @return bool True if IP is valid, false otherwise.
     */
    public static function isValid($ip)
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;
    }

    /**
     * Check if IP is a valid IPv6 address.
     *
     * @param string $ip The IP address to validate.
     * @return bool True if IP is IPv6, false otherwise.
     */
    public static function isV6($ip)
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;
    }

    /**
     * Check if IP is a valid IPv4 address.
     *
     * @param string $ip The IP address to validate.
     * @return bool True if IP is IPv4, false otherwise.
     */
    public static function isV4($ip)
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false;
    }

    /**
     * Get the IP detection method from options.
     *
     * This method ensures backward compatibility by checking if the configured
     * method is valid and falling back to defaults if needed.
     *
     * @return string The IP detection method to use.
     */
    public static function getMethod()
    {
        $ipMethod = Option::getValue('ip_method', self::$defaultIpMethod);

        if (!in_array($ipMethod, self::getDetectionHeaders(), true)) {
            Option::updateValue('ip_method', self::$defaultIpMethod);
            return self::$defaultIpMethod;
        }

        return $ipMethod;
    }

    /**
     * Check if IP address is properly sanitized.
     *
     * @param string $ip The IP address to check.
     * @return bool True if IP is valid, false otherwise.
     */
    public static function isSanitized($ip)
    {
        return filter_var($ip, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * Get visitor's address from Cloudflare header.
     *
     * @return string Sanitized IP address or empty string if not found.
     */
    public static function getFromCloudflare()
    {
        $ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? '';

        return self::isSanitized($ip) ? $ip : '';
    }
}