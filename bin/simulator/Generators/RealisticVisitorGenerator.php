<?php

namespace WP_Statistics\Testing\Simulator\Generators;

use WP_Statistics\Testing\Simulator\ResourceProvisioner;
use WP_Statistics\Testing\Simulator\SimulatorConfig;
use WP_Statistics\Utils\Signature;

/**
 * RealisticVisitorGenerator - Generates realistic visitor profiles with user_id handling
 *
 * Creates visitor profiles with:
 * - Logged-in vs guest distribution (user_id)
 * - Device/browser correlation (iOS→Safari, Android→Chrome)
 * - Geographic realism (timezone-aligned visits)
 * - Returning visitor behavior
 * - Realistic referrer patterns
 *
 * @package WP_Statistics\Testing\Simulator\Generators
 * @since 15.0.0
 */
class RealisticVisitorGenerator extends AbstractDataGenerator
{
    /**
     * Configuration
     */
    private SimulatorConfig $config;

    /**
     * Resource provisioner for users/posts
     */
    private ResourceProvisioner $resourceProvisioner;

    /**
     * Loaded data
     */
    private array $countries = [];
    private array $devices = [];
    private array $referrers = [];
    private array $userAgents = [];
    private array $timezones = [];
    private array $languages = [];
    private array $realisticPatterns = [];

    /**
     * Visitor tracking for returning visitors
     * @var array<string, array>
     */
    private array $visitorHistory = [];

    /**
     * Constructor
     *
     * @param string $dataDir Path to data directory
     * @param SimulatorConfig $config Simulator configuration
     * @param ResourceProvisioner $resourceProvisioner Resource provisioner
     */
    public function __construct(
        string $dataDir,
        SimulatorConfig $config,
        ResourceProvisioner $resourceProvisioner
    ) {
        parent::__construct($dataDir);
        $this->config = $config;
        $this->resourceProvisioner = $resourceProvisioner;
        $this->loadAllData();
    }

    /**
     * Load all data files
     */
    private function loadAllData(): void
    {
        $this->countries = $this->loadDataFile('countries.json');
        $this->devices = $this->loadDataFile('devices.json');
        $this->referrers = $this->loadDataFile('referrers.json');
        $this->userAgents = $this->loadDataFile('user-agents.json');
        $this->timezones = $this->loadDataFile('timezones.json');
        $this->languages = $this->loadDataFile('languages.json');

        // Load realistic patterns if available
        try {
            $this->realisticPatterns = $this->loadDataFile('realistic-patterns.json');
        } catch (\RuntimeException $e) {
            $this->realisticPatterns = $this->getDefaultRealisticPatterns();
        }
    }

    /**
     * Generate a complete visitor profile with request data
     *
     * @return array Complete request data
     */
    public function generate(): array
    {
        $profile = $this->generateVisitorProfile();
        $resource = $this->resourceProvisioner->getRandomResource();

        if (!$resource) {
            throw new \RuntimeException('No resources available for tracking');
        }

        return $this->buildRequestData($profile, $resource);
    }

    /**
     * Set resources directly (mainly for testing)
     *
     * @param array $resources Array of resource data
     */
    public function setResources(array $resources): void
    {
        $this->resourceProvisioner->setResources($resources);
    }

    /**
     * Set users directly (mainly for testing)
     *
     * @param array $users Array of user data with 'ID' and 'role' keys
     */
    public function setUsers(array $users): void
    {
        // Convert to format expected by ResourceProvisioner (lowercase 'id')
        $normalizedUsers = array_map(function ($user) {
            return [
                'id'   => $user['ID'] ?? $user['id'],
                'role' => $user['role'] ?? 'subscriber',
            ];
        }, $users);
        $this->resourceProvisioner->setUsers($normalizedUsers);
    }

    /**
     * Generate visitor profile
     *
     * @return array Visitor profile
     */
    public function generateVisitorProfile(): array
    {
        // Determine if this is a logged-in visitor
        $isLoggedIn = $this->randomBool($this->config->loggedInRatio);
        $userId = $isLoggedIn ? $this->selectUserId() : null;

        // Select country
        $countryCode = $this->weightedRandom($this->countries['distribution']);

        // Select device type with realistic distribution
        $deviceType = $this->selectDeviceType();

        // Select browser with device correlation
        $browser = $this->selectBrowserForDevice($deviceType);

        // Select OS with device/browser correlation
        $os = $this->selectOsForDevice($deviceType);

        // Get browser version (prefer recent versions)
        $browserVersion = $this->selectBrowserVersion($browser);

        // Get User-Agent string
        $userAgent = $this->buildUserAgentString($deviceType, $os, $browser, $browserVersion);

        // Get screen resolution for device type
        $resolution = $this->selectResolution($deviceType);
        list($width, $height) = explode('x', $resolution);

        // Get timezone for country
        $timezone = $this->selectTimezoneForCountry($countryCode);

        // Get language for country
        $langData = $this->selectLanguageForCountry($countryCode);

        // Generate referrer
        $referrer = $this->generateReferrer();

        // Generate IP address for country
        $ip = $this->generateIpForCountry($countryCode);

        // Determine session behavior based on login status
        $sessionBehavior = $this->getSessionBehavior($isLoggedIn);

        return [
            // User identification
            'user_id'        => $userId,
            'is_logged_in'   => $isLoggedIn,
            'ip'             => $ip,

            // Geographic data
            'country_code'   => $countryCode,
            'city'           => $this->selectCityForCountry($countryCode),
            'timezone'       => $timezone,

            // Device/browser data
            'device_type'    => $deviceType,
            'browser'        => $browser,
            'browser_version' => $browserVersion,
            'os'             => $os,
            'user_agent'     => $userAgent,
            'screen_width'   => (int) $width,
            'screen_height'  => (int) $height,

            // Locale data
            'language_code'  => $langData['code'],
            'language_name'  => $langData['name'],

            // Referrer data
            'referrer'       => $referrer,

            // Session behavior (for multi-page simulation)
            'pages_per_session'   => $sessionBehavior['pages_per_session'],
            'bounce_probability'  => $sessionBehavior['bounce_probability'],
            'return_probability'  => $sessionBehavior['return_probability'],
            'session_duration'    => $sessionBehavior['session_duration'],
        ];
    }

    /**
     * Select user ID for logged-in visitor
     *
     * @return int User ID
     */
    private function selectUserId(): int
    {
        $users = $this->resourceProvisioner->getUsers();

        if (empty($users)) {
            return 1; // Default to admin
        }

        // Apply role-based weights
        $roleWeights = $this->realisticPatterns['user_role_weights'] ?? [
            'administrator' => 5,
            'editor'        => 10,
            'author'        => 15,
            'subscriber'    => 50,
        ];

        return $this->resourceProvisioner->getRandomUserId($roleWeights) ?? 1;
    }

    /**
     * Select device type with realistic distribution
     *
     * @return string Device type
     */
    private function selectDeviceType(): string
    {
        $types = $this->realisticPatterns['device_distribution'] ?? $this->devices['types'];
        return $this->weightedRandom($types);
    }

    /**
     * Select browser with device correlation
     *
     * @param string $deviceType Device type
     * @return string Browser name
     */
    private function selectBrowserForDevice(string $deviceType): string
    {
        $correlations = $this->realisticPatterns['device_browser_correlation'] ?? [];

        if (isset($correlations[$deviceType])) {
            return $this->weightedRandom($correlations[$deviceType]);
        }

        return $this->weightedRandom($this->devices['browsers']);
    }

    /**
     * Select OS for device type
     *
     * @param string $deviceType Device type
     * @return string OS name
     */
    private function selectOsForDevice(string $deviceType): string
    {
        $osOptions = $this->devices['os'][$deviceType] ?? ['Windows' => 100];
        return $this->weightedRandom($osOptions);
    }

    /**
     * Select browser version (prefer recent)
     *
     * @param string $browser Browser name
     * @return string Version string
     */
    private function selectBrowserVersion(string $browser): string
    {
        $versions = $this->devices['browser_versions'][$browser] ?? ['100.0'];

        // Weight towards newer versions
        $weights = [];
        foreach ($versions as $i => $version) {
            $weights[$version] = max(1, count($versions) - $i) * 10;
        }

        return $this->weightedRandom($weights);
    }

    /**
     * Build User-Agent string
     *
     * @param string $deviceType Device type
     * @param string $os Operating system
     * @param string $browser Browser name
     * @param string $version Browser version
     * @return string User-Agent string
     */
    private function buildUserAgentString(string $deviceType, string $os, string $browser, string $version): string
    {
        // Try to get template from user-agents.json
        $templates = $this->userAgents[$deviceType][$os][$browser] ?? [];

        if (!empty($templates)) {
            $template = $this->randomFrom($templates);
            return str_replace('{version}', $version, $template);
        }

        // Fallback to generated User-Agent
        return $this->generateFallbackUserAgent($deviceType, $os, $browser, $version);
    }

    /**
     * Generate fallback User-Agent string
     */
    private function generateFallbackUserAgent(string $deviceType, string $os, string $browser, string $version): string
    {
        if ($deviceType === 'Desktop') {
            switch ($os) {
                case 'Windows':
                    return "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/{$version} Safari/537.36";
                case 'macOS':
                    return "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/{$version} Safari/537.36";
                case 'Linux':
                    return "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/{$version} Safari/537.36";
            }
        } elseif ($deviceType === 'Mobile') {
            if ($os === 'iOS') {
                return "Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/{$version} Mobile/15E148 Safari/604.1";
            } else {
                return "Mozilla/5.0 (Linux; Android 14; SM-G998B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/{$version} Mobile Safari/537.36";
            }
        } elseif ($deviceType === 'Tablet') {
            if ($os === 'iOS') {
                return "Mozilla/5.0 (iPad; CPU OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/{$version} Mobile/15E148 Safari/604.1";
            } else {
                return "Mozilla/5.0 (Linux; Android 14; SM-X910) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/{$version} Safari/537.36";
            }
        }

        return "Mozilla/5.0 (compatible; WPStatisticsSimulator/1.0)";
    }

    /**
     * Select screen resolution for device type
     *
     * @param string $deviceType Device type
     * @return string Resolution (WxH)
     */
    private function selectResolution(string $deviceType): string
    {
        $resolutions = $this->devices['resolutions'][$deviceType] ?? ['1920x1080'];
        return $this->randomFrom($resolutions);
    }

    /**
     * Select timezone for country
     *
     * @param string $countryCode Country code
     * @return string Timezone name
     */
    private function selectTimezoneForCountry(string $countryCode): string
    {
        $timezones = $this->timezones[$countryCode] ?? ['UTC'];
        return $this->randomFrom($timezones);
    }

    /**
     * Select language for country
     *
     * @param string $countryCode Country code
     * @return array Language data with 'code' and 'name'
     */
    private function selectLanguageForCountry(string $countryCode): array
    {
        return $this->languages[$countryCode] ?? ['code' => 'en-US', 'name' => 'English'];
    }

    /**
     * Select city for country
     *
     * @param string $countryCode Country code
     * @return string City name
     */
    private function selectCityForCountry(string $countryCode): string
    {
        $cities = $this->countries['cities'][$countryCode] ?? ['Unknown'];
        return $this->randomFrom($cities);
    }

    /**
     * Generate referrer URL
     *
     * @return string Referrer URL or empty string for direct
     */
    private function generateReferrer(): string
    {
        $channel = $this->weightedRandom($this->referrers['channels']);

        if ($channel === 'direct') {
            return '';
        }

        if (!isset($this->referrers[$channel])) {
            return '';
        }

        // Get weighted referrer from channel
        $channelReferrers = $this->referrers[$channel];
        $weights = array_column($channelReferrers, 'weight', 'domain');
        $domain = $this->weightedRandom($weights);

        // Build referrer URL with realistic paths
        switch ($channel) {
            case 'search':
                $queries = ['sample search', 'best practices', 'how to guide', 'tutorial', 'review'];
                return "https://www.{$domain}/search?q=" . urlencode($this->randomFrom($queries));

            case 'social':
                $paths = ['/posts/123', '/p/abc123', '/status/123456', ''];
                return "https://www.{$domain}" . $this->randomFrom($paths);

            case 'referral':
                $paths = ['/article/sample', '/blog/post', '/news/123', '/'];
                return "https://{$domain}" . $this->randomFrom($paths);

            case 'email':
                return "https://{$domain}/";

            case 'paid':
                return "https://www.{$domain}/aclk?sa=l&utm_source=google&utm_medium=cpc";

            default:
                return "https://{$domain}/";
        }
    }

    /**
     * Generate IP address for country
     *
     * @param string $countryCode Country code
     * @return string IP address
     */
    private function generateIpForCountry(string $countryCode): string
    {
        $countryIpRanges = [
            'US' => ['24', '63', '65', '66', '67', '68', '69', '70', '71', '72', '73', '74', '75', '76', '96', '97', '98', '99', '100'],
            'GB' => ['2', '5', '31', '46', '81', '82', '86', '87', '90', '91', '92', '109'],
            'CA' => ['24', '64', '65', '66', '67', '68', '69', '70', '71', '72', '74', '75', '76', '99', '142', '184'],
            'DE' => ['46', '77', '78', '79', '80', '84', '85', '87', '88', '89', '91', '93', '94', '95', '130', '134'],
            'FR' => ['37', '46', '78', '80', '81', '82', '83', '84', '85', '86', '88', '90', '91', '92', '93', '109'],
            'AU' => ['1', '14', '27', '43', '49', '58', '59', '60', '101', '103', '110', '112', '113', '114', '115'],
            'IN' => ['14', '27', '36', '43', '49', '59', '61', '101', '103', '106', '110', '112', '115', '116', '117'],
            'BR' => ['131', '138', '139', '143', '146', '152', '168', '177', '179', '186', '187', '189', '191', '200', '201'],
            'JP' => ['14', '27', '36', '42', '43', '49', '58', '59', '60', '61', '101', '106', '110', '111', '112', '113'],
        ];

        $firstOctet = $countryIpRanges[$countryCode] ?? ['100', '101', '102'];
        $first = $this->randomFrom($firstOctet);

        return $first . '.' . mt_rand(1, 254) . '.' . mt_rand(1, 254) . '.' . mt_rand(1, 254);
    }

    /**
     * Get session behavior based on login status
     *
     * @param bool $isLoggedIn Whether visitor is logged in
     * @return array Session behavior parameters
     */
    private function getSessionBehavior(bool $isLoggedIn): array
    {
        if ($isLoggedIn) {
            return [
                'pages_per_session'   => $this->randomInt(5, 12),
                'bounce_probability'  => 0.25,
                'return_probability'  => 0.60,
                'session_duration'    => $this->randomInt(300, 1200), // 5-20 minutes
            ];
        }

        return [
            'pages_per_session'   => $this->randomInt(2, 4),
            'bounce_probability'  => 0.50,
            'return_probability'  => 0.25,
            'session_duration'    => $this->randomInt(30, 300), // 30sec-5min
        ];
    }

    /**
     * Build request data from profile and resource
     *
     * @param array $profile Visitor profile
     * @param array $resource Resource data
     * @return array Request data
     */
    private function buildRequestData(array $profile, array $resource): array
    {
        return [
            // AJAX action
            'action' => 'wp_statistics_hit_record',

            // Required tracking params
            'resourceUriId' => $resource['resource_uri_id'],
            'resourceUri'   => base64_encode($resource['uri']),
            'resource_type' => $resource['post_type'],
            'resource_id'   => $resource['post_id'],
            'signature'     => Signature::generate([$resource['post_type'], $resource['post_id']]),

            // Locale info
            'timezone'         => $profile['timezone'],
            'language'         => $profile['language_code'],
            'languageFullName' => $profile['language_name'],
            'screenWidth'      => $profile['screen_width'],
            'screenHeight'     => $profile['screen_height'],

            // Referrer
            'referred' => base64_encode($profile['referrer']),

            // Page URI
            'page_uri' => base64_encode($resource['uri']),

            // Profile for HTTP headers
            '_profile' => $profile,
        ];
    }

    /**
     * Get default realistic patterns
     *
     * @return array Default patterns
     */
    private function getDefaultRealisticPatterns(): array
    {
        return [
            'device_distribution' => [
                'Desktop' => 55,
                'Mobile'  => 40,
                'Tablet'  => 5,
            ],
            'device_browser_correlation' => [
                'Desktop' => [
                    'Chrome'  => 60,
                    'Safari'  => 15,
                    'Firefox' => 12,
                    'Edge'    => 10,
                    'Opera'   => 3,
                ],
                'Mobile' => [
                    'Chrome'  => 50,
                    'Safari'  => 45,
                    'Firefox' => 3,
                    'Opera'   => 2,
                ],
                'Tablet' => [
                    'Safari'  => 55,
                    'Chrome'  => 40,
                    'Firefox' => 3,
                    'Edge'    => 2,
                ],
            ],
            'user_role_weights' => [
                'administrator' => 5,
                'editor'        => 10,
                'author'        => 15,
                'subscriber'    => 50,
            ],
        ];
    }
}
