<?php

namespace WP_Statistics\Components;

use WP_Statistics\Service\Analytics\DeviceDetection\UserAgent;
use WP_Statistics\Components\Option;
use ErrorException;
use Exception;
use WP_Statistics\Service\Integrations\IntegrationHelper;

/**
 * Handles IP address detection, validation, and anonymization for visitor tracking.
 *
 * Provides static methods for retrieving the client IP from server headers, validating
 * IP formats, anonymizing and hashing IPs for privacy compliance, and checking IPs
 * against private or custom-defined ranges. Supports both IPv4 and IPv6.
 *
 * Used throughout the plugin to ensure accurate and privacy-aware IP tracking.
 *
 * @package WP_Statistics\Components
 * @since 15.0.0
 */
class Ip
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
        'HTTP_CF_CONNECTING_IP',
        'HTTP_INCAP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED',
        'HTTP_CLIENT_IP',
        'HTTP_X_CLUSTER_CLIENT_IP',
        'HTTP_X_REAL_IP',
        'REMOTE_ADDR',
    ];

    /**
     * Default $_SERVER method for getting user real IP.
     *
     * @var string
     */
    public static string $defaultIpMethod = 'sequential';

    /**
     * Cached IP method for the current request.
     *
     * @var string|null
     */
    private static $cachedIpMethod = null;

    /**
     * Get IP from the first available header in the sequential list.
     *
     * @return string|false The IP address or false if none found.
     */
    private static function getIpFromHeaders()
    {
        foreach (self::$ipMethodsServer as $method) {
            if (isset($_SERVER[$method])) {
                return $_SERVER[$method];
            }
        }

        return false;
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
            $ip = self::getIpFromHeaders();
        } else {
            $ip = $_SERVER[$ipMethod] ?? false;

            if (empty($ip)) {
                // If the configured header is absent, fall back to sequential for this request
                $ip = self::getIpFromHeaders();
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
     * Gets or creates the salt for IP hashing, rotated based on the configured interval.
     *
     * @return string The salt string.
     */
    public static function getSalt()
    {
        $interval      = Option::getValue('hash_rotation_interval', 'daily');
        $currentPeriod = self::getCurrentPeriod($interval);
        $dailySalt     = Option::getValue('daily_salt', []);

        if (isset($dailySalt['date']) && $dailySalt['date'] !== $currentPeriod) {
            $dailySalt = [
                'date' => $currentPeriod,
                'salt' => hash('sha256', wp_generate_password())
            ];
            Option::updateValue('daily_salt', $dailySalt);
        }

        if (!$dailySalt || !is_array($dailySalt)) {
            $dailySalt = [
                'date' => $currentPeriod,
                'salt' => hash('sha256', wp_generate_password())
            ];
            Option::updateValue('daily_salt', $dailySalt);
        }

        return $dailySalt['salt'];
    }

    /**
     * Returns the current period identifier based on the rotation interval.
     *
     * @param string $interval One of 'daily', 'weekly', 'monthly', 'disabled'.
     * @return string Period identifier string.
     */
    private static function getCurrentPeriod($interval)
    {
        switch ($interval) {
            case 'weekly':
                return date('o-W');
            case 'monthly':
                return date('Y-m');
            case 'disabled':
                return 'permanent';
            case 'daily':
            default:
                return date('Y-m-d');
        }
    }

    /**
     * Generates a hashed version of an IP address using a daily salt.
     *
     * This method creates a secure hash of the IP address combined with user agent
     * and a daily rotating salt for privacy protection.
     *
     * @param string|null $ip Optional. The IP address to hash. If null, uses current user IP.
     * @return string The hashed IP address without prefix (20 characters).
     */
    public static function hash($ip = null)
    {
        if ($ip === null) {
            $ip = self::getCurrent();
        }

        $salt         = self::getSalt();
        $userAgent    = UserAgent::getHttpUserAgent();
        $anonymizedIp = wp_privacy_anonymize_ip($ip);

        $hash          = hash('sha256', $salt . $anonymizedIp . $userAgent);
        $truncatedHash = substr($hash, 0, 20);


        /**
         * Filters the hashed IP address.
         *
         * @param string $truncatedHash The hashed IP address.
         */
        return apply_filters('wp_statistics_hash_ip', $truncatedHash);
    }

    /**
     * Get IP address for storage in database.
     *
     * Returns the raw IP when store_ip is enabled, or null when disabled.
     * The hash column handles visitor dedup separately.
     *
     * @return string|null
     */
    public static function getStorableIp()
    {
        if (!Option::getValue('store_ip') || IntegrationHelper::shouldTrackAnonymously()) {
            return null;
        }

        return sanitize_text_field(self::getCurrent());
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

                    // Handle wildcard patterns (e.g., 192.168.*.*)
                    if (strpos($range, '*') !== false) {
                        $pattern = '/^' . str_replace('\\*', '\\d+', preg_quote($range, '/')) . '$/';
                        if (preg_match($pattern, $ip)) {
                            $isWithinRange = true;
                            break;
                        }
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
                \WP_Statistics()->log($e->getMessage(), 'warning');
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
        // Return cached value if available for this request
        if (self::$cachedIpMethod !== null) {
            return self::$cachedIpMethod;
        }

        $ipMethod = Option::getValue('ip_method', self::$defaultIpMethod);

        // Handle custom header: resolve to the actual header name
        if ($ipMethod === 'custom') {
            $customHeader = Option::getValue('user_custom_header_ip_method', '');
            $ipMethod     = !empty($customHeader) ? strtoupper(sanitize_text_field($customHeader)) : self::$defaultIpMethod;
        }

        /**
         * Filters the resolved IP detection method before validation.
         *
         * Allows overriding the $_SERVER key used for IP detection.
         * Return 'sequential' for automatic detection, or a specific
         * $_SERVER key like 'HTTP_X_CUSTOM_IP'.
         *
         * @param string $ipMethod The resolved IP detection method.
         */
        $ipMethod = apply_filters('wp_statistics_ip_detection_method', $ipMethod);

        // Validate: must be in known list, 'sequential', or a valid custom header (HTTP_ + alphanumeric/underscores)
        if ($ipMethod !== self::$defaultIpMethod
            && !in_array($ipMethod, self::$ipMethodsServer, true)
            && !preg_match('/^HTTP_[A-Z0-9_]+$/', $ipMethod)
        ) {
            Option::updateValue('ip_method', self::$defaultIpMethod);
            self::$cachedIpMethod = self::$defaultIpMethod;
            return self::$defaultIpMethod;
        }

        self::$cachedIpMethod = $ipMethod;
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