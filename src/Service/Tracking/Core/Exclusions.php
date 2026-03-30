<?php

namespace WP_Statistics\Service\Tracking\Core;

use WP_Statistics\Components\DateTime;
use WP_Statistics\Components\Ip;
use WP_Statistics\Components\Singleton;
use WP_Statistics\Components\Option;
use WP_Statistics\Records\RecordFactory;
use WP_Statistics\Utils\User;


/**
 * Class Exclusions
 *
 * Handles visitor exclusion logic for JS tracker hit requests.
 *
 * All checks use client-provided request parameters (validated by signature)
 * rather than server-side WordPress conditional tags, since hits arrive
 * via REST/AJAX and the WordPress query loop is not set up.
 */
class Exclusions extends Singleton
{
    /**
     * Cached exclusion map to avoid re-building on each call.
     *
     * @var array<string, array<string, string>>|null
     */
    private static $exclusionMap = null;

    /**
     * Stores plugin options used for exclusion logic.
     *
     * @var array
     */
    private static $options = [];

    /**
     * Cached compiled URL regex patterns for excluded URLs.
     *
     * @var array|null
     */
    private static $excludedUrlPatterns = null;

    /**
     * Cached result of the last exclusion check to prevent redundant processing.
     *
     * Note: This is cached per-request. If record() is called multiple times
     * in a single PHP request, the second call reuses the first result.
     *
     * @var array{exclusion_match: bool, exclusion_reason: string}|null
     */
    private static $exclusionResult = null;

    /**
     * Returns the default exclusion map with reasons and corresponding check methods.
     *
     * @return array<string, array<string, string>> Associative array of exclusion keys to message and method.
     */
    private static function getExclusionMap()
    {
        return [
            'robot'           => [
                'message' => 'Robot',
                'method'  => 'exclusionRobot',
            ],
            'broken_file'     => [
                'message' => 'Broken Link',
                'method'  => 'exclusionBrokenFile',
            ],
            'ip_match'        => [
                'message' => 'IP Match',
                'method'  => 'exclusionIpMatch',
            ],
            'feed'            => [
                'message' => 'Feed',
                'method'  => 'exclusionFeed',
            ],
            '404'             => [
                'message' => '404',
                'method'  => 'exclusion404',
            ],
            'excluded_url'    => [
                'message' => 'Excluded URL',
                'method'  => 'exclusionExcludedUrl',
            ],
            'user_role'       => [
                'message' => 'User Role',
                'method'  => 'exclusionUserRole',
            ],
            'geoip'           => [
                'message' => 'Geolocation',
                'method'  => 'exclusionGeoIp',
            ],
            'robot_threshold' => [
                'message' => 'Robot Threshold',
                'method'  => 'exclusionRobotThreshold',
            ],
        ];
    }

    /**
     * Get human-readable labels for exclusion reason keys.
     *
     * @return array<string, string> Map of reason key to translated display label.
     */
    public static function getReasonLabels(): array
    {
        return array_map(
            fn($entry) => __($entry['message'], 'wp-statistics'),
            self::getExclusionMap()
        );
    }

    /**
     * Returns the list of exclusion keys, filtered by developers.
     *
     * @return string[] List of exclusion reason keys.
     */
    public static function getExclusionList()
    {
        if (self::$exclusionMap === null) {
            self::$exclusionMap = self::getExclusionMap();
        }

        $keys = array_keys(self::$exclusionMap);
        return (array)apply_filters('wp_statistics_exclusion_list', $keys);
    }

    /**
     * Checks if exclusion recording is enabled in settings.
     *
     * @return bool True if recording exclusions is active, false otherwise.
     */
    public static function isRecordActive()
    {
        return !empty(self::$options['record_exclusions']);
    }

    /**
     * Determines whether the given visitor should be excluded based on configured rules.
     *
     * @param Visitor $visitor
     * @return array{exclusion_match: bool, exclusion_reason: string}
     */
    public static function check(Visitor $visitor)
    {
        if (! empty(self::$exclusionResult)) {
            return self::$exclusionResult;
        }

        self::$exclusionResult = [
            'exclusion_match'  => false,
            'exclusion_reason' => ''
        ];

        if (empty(self::$options)) {
            self::$options = Option::get();
        }

        $exclusionList = self::getExclusionList();

        foreach ($exclusionList as $reason) {
            if (!isset(self::$exclusionMap[$reason])) {
                continue;
            }

            $method = self::$exclusionMap[$reason]['method'];

            if (!$method || !method_exists(__CLASS__, $method)) {
                continue;
            }

            if (self::$method($visitor)) {
                self::$exclusionResult = apply_filters(
                    'wp_statistics_exclusion',
                    [
                        'exclusion_match'  => true,
                        'exclusion_reason' => $reason
                    ],
                    $visitor
                );

                return self::$exclusionResult;
            }
        }

        self::$exclusionResult = apply_filters('wp_statistics_exclusion', self::$exclusionResult, $visitor);

        return self::$exclusionResult;
    }

    /**
     * Records the exclusion occurrence in the database.
     *
     * @param array{exclusion_match: bool, exclusion_reason: string} $exclusion Exclusion details.
     * @return void
     */
    public static function record($exclusion = [])
    {
        if (!self::isRecordActive()) {
            return;
        }

        $date   = DateTime::get();
        $reason = $exclusion['exclusion_reason'] ?? '';

        $record = RecordFactory::exclusion()->get([
            'date'   => $date,
            'reason' => $reason
        ]);

        if ($record) {
            RecordFactory::exclusion($record)->update([
                'count' => $record->count + 1
            ]);

            return;
        }

        $id = RecordFactory::exclusion()->insert([
            'date'   => $date,
            'reason' => $reason,
            'count'  => 1
        ]);

        if ($id) {
            do_action('wp_statistics_save_exclusion', $exclusion, $id);
        }
    }

    /**
     * Exclude feed requests when configured.
     *
     * Uses the client-provided resource_type parameter from the JS tracker
     * instead of is_feed() which doesn't work during REST/AJAX requests.
     *
     * @param Visitor $visitor
     * @return bool True when resource is a feed and feeds are excluded.
     */
    public static function exclusionFeed(Visitor $visitor)
    {
        if (empty(self::$options['exclude_feeds'])) {
            return false;
        }

        return $visitor->getRequest()->getResourceType() === 'feed';
    }

    /**
     * Exclude 404 responses when configured.
     *
     * Uses the client-provided resource_type parameter from the JS tracker
     * instead of is_404() which doesn't work during REST/AJAX requests.
     *
     * @param Visitor $visitor
     * @return bool True on 404 when exclusion enabled.
     */
    public static function exclusion404(Visitor $visitor)
    {
        if (empty(self::$options['exclude_404s'])) {
            return false;
        }

        return $visitor->getRequest()->getResourceType() === '404';
    }

    /**
     * Exclude visitors exceeding a hit threshold.
     *
     * @param Visitor $visitor
     * @return bool True when hits exceed threshold.
     */
    public static function exclusionRobotThreshold(Visitor $visitor)
    {
        $threshold = intval(self::$options['robot_threshold'] ?? 0);

        if ($threshold <= 0) {
            return false;
        }

        $visitorRecord = RecordFactory::visitor()->get([
            'hash'             => $visitor->getHashedIp(),
            'DATE(created_at)' => DateTime::get(),
        ]);

        if (!$visitorRecord) {
            return false;
        }

        return ($visitorRecord->hits + 1) > $threshold;
    }

    /**
     * Exclude users with specific roles or anonymous if configured.
     *
     * Uses the user_id provided by the JS tracker (embedded in the page by PHP
     * and included in the signature to prevent spoofing) since the plugin is cookieless.
     *
     * @param Visitor $visitor
     * @return bool True when user or anonymous matches exclusion.
     */
    public static function exclusionUserRole(Visitor $visitor)
    {
        $userId = absint($visitor->getRequest()->getUserId());

        if ($userId > 0) {
            $roles = User::getRolesById($userId);

            foreach ($roles as $role) {
                if (!empty(self::$options['exclude_' . $role])) {
                    return true;
                }
            }

            return false;
        }

        return !empty(self::$options['exclude_anonymous_users']);
    }

    /**
     * Exclude URLs matching configured patterns.
     *
     * @param Visitor $visitor
     * @return bool True when request URI matches an excluded pattern.
     */
    public static function exclusionExcludedUrl(Visitor $visitor)
    {
        if (self::$excludedUrlPatterns === null) {
            self::$excludedUrlPatterns = self::compileExcludedUrls(self::$options['excluded_urls'] ?? '');
        }

        $requestUri = urldecode(trim(explode('?', $visitor->getRequest()->getResourceUri())[0], '/\\'));

        foreach (self::$excludedUrlPatterns as $pattern) {
            if (preg_match($pattern, $requestUri)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Compiles newline-separated URL patterns into regex array.
     *
     * @param string $urls Multiline string of URL patterns.
     * @return array<int, string> Array of regex patterns.
     */
    private static function compileExcludedUrls($urls)
    {
        $patterns = [];

        foreach (explode("\n", $urls) as $url) {
            $url = trim(urldecode(trim($url, '/\\')));

            if (strpos($url, '*') !== false) {
                if (trim($url, '* ') === '') {
                    continue;
                }
                $patterns[] = '/^' . str_replace('\\*', '.*', preg_quote($url, '/'))
                    . '$/i';
            } elseif (strlen($url) > 2) {
                $patterns[] = '/^' . preg_quote($url, '/') . '$/i';
            }
        }

        return $patterns;
    }

    /**
     * Excludes IPs matching configured ranges.
     *
     * @param Visitor $visitor
     * @return bool True when client IP is in excluded range.
     */
    public static function exclusionIpMatch(Visitor $visitor)
    {
        if (empty(self::$options['exclude_ip'])) {
            return false;
        }

        $ipRanges = explode("\n", self::$options['exclude_ip']);

        foreach ($ipRanges as $subnet) {
            $subnet = trim($subnet);

            if (strlen($subnet) > 2 && Ip::isInRange([$subnet])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Excludes broken file requests (404 errors for static files like images, CSS, JS).
     *
     * Uses the client-provided resource_type parameter from the JS tracker
     * instead of is_404() which doesn't work during REST/AJAX requests.
     *
     * @param Visitor $visitor
     * @return bool True when 404 and file extension present.
     */
    public static function exclusionBrokenFile(Visitor $visitor)
    {
        if ($visitor->getRequest()->getResourceType() !== '404') {
            return false;
        }

        $requestUri = $visitor->getRequest()->getResourceUri();

        if (empty($requestUri)) {
            return false;
        }

        $path      = wp_parse_url($requestUri, PHP_URL_PATH);
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        if (empty($extension) || strtolower($extension) === 'php') {
            return false;
        }

        return true;
    }

    /**
     * Excludes bots based on UA or detection library.
     *
     * @param Visitor $visitor
     * @return bool True when UA or IP is identified as a bot.
     */
    public static function exclusionRobot(Visitor $visitor)
    {
        $rawUserAgent = $visitor->getHttpUserAgent();

        if (empty($rawUserAgent) || empty($visitor->getIp())) {
            return true;
        }

        $userAgent = $visitor->getUserAgent();

        if ($userAgent->isBot()) {
            return true;
        }

        if (!$userAgent->isBrowserDetected() && !$userAgent->isPlatformDetected()) {
            return true;
        }

        $robots = explode("\n", self::$options['robotlist'] ?? '');
        $robots = apply_filters('wp_statistics_exclusion_robots', $robots);

        foreach ($robots as $robot) {
            $robot = trim($robot);

            if (strlen($robot) > 3) {
                if (stripos($rawUserAgent, $robot) !== false) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Excludes by geographic location based on GeoIP settings.
     *
     * @param Visitor $visitor
     * @return bool True when country is excluded or not in included list.
     */
    public static function exclusionGeoIp(Visitor $visitor)
    {
        static $excludedCountries = null;
        static $includedCountries = null;

        if ($excludedCountries === null) {
            $excludedCountries = array_flip(array_filter(array_map('trim',
                explode("\n", strtoupper(str_replace("\r\n", "\n", self::$options['excluded_countries'] ?? '')))
            )));
        }

        if ($includedCountries === null) {
            $countriesString   = strtoupper(str_replace("\r\n", "\n", self::$options['included_countries'] ?? ''));
            $includedCountries = $countriesString === '' ? [] : array_flip(array_filter(array_map('trim', explode("\n", $countriesString))));
        }

        if (empty($excludedCountries) && empty($includedCountries)) {
            return false;
        }

        $countryCode = strtoupper($visitor->getCountry() ?? '');

        if ($countryCode === '') {
            return false;
        }

        if (isset($excludedCountries[$countryCode])) {
            return true;
        }

        return !empty($includedCountries) && !isset($includedCountries[$countryCode]);
    }
}
