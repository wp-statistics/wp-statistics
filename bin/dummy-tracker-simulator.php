#!/usr/bin/env php
<?php
/**
 * WP Statistics Tracker.js Simulator
 *
 * Generates realistic test data by simulating Tracker.js requests to admin-ajax.php.
 * This exercises the full tracking pipeline (validation, signature, recording).
 *
 * Usage: php bin/dummy-tracker-simulator.php [options]
 *
 * Prerequisites:
 * - 'use_cache_plugin' option must be enabled (Client-side tracking)
 * - 'bypass_ad_blockers' option must be enabled (AJAX tracking)
 *
 * @package WP_Statistics
 * @since 15.0.0
 */

// Load WordPress
define('WP_USE_THEMES', false);
require_once(__DIR__ . '/../../../../wp-load.php');

// Ensure WP Statistics is loaded
if (!class_exists('WP_Statistics')) {
    die("Error: WP Statistics plugin not found.\n");
}

use WP_Statistics\Globals\Option;
use WP_Statistics\Records\RecordFactory;
use WP_Statistics\Utils\Signature;

/**
 * TrackerSimulator - Simulates Tracker.js requests to admin-ajax.php
 */
class TrackerSimulator
{
    /** @var array Configuration options */
    private $config = [
        'days' => 7,
        'visitors_per_day' => 50,
        'from' => null,
        'to' => null,
        'delay_ms' => 50,
        'verbose' => false,
        'dry_run' => false,
        'url' => null,
    ];

    /** @var string WordPress admin-ajax.php URL */
    private $ajaxUrl;

    /** @var string Site URL */
    private $siteUrl;

    /** @var array Loaded data files */
    private $countries;
    private $devices;
    private $referrers;
    private $userAgents;
    private $timezones;
    private $languages;

    /** @var array Prepared resources (posts with resource_uri_id) */
    private $resources = [];

    /** @var array Statistics */
    private $stats = [
        'requests_sent' => 0,
        'requests_successful' => 0,
        'requests_failed' => 0,
        'errors' => [],
    ];

    /**
     * Constructor - Initialize the simulator
     */
    public function __construct(array $options = [])
    {
        $this->config = array_merge($this->config, $options);

        // Set date range
        $this->config['to'] = $this->config['to'] ?? date('Y-m-d');
        $this->config['from'] = $this->config['from'] ?? date('Y-m-d', strtotime("-{$this->config['days']} days"));

        // Get URLs (use custom URL if provided)
        if (!empty($this->config['url'])) {
            $this->siteUrl = $this->config['url'];
            $this->ajaxUrl = $this->config['url'] . '/wp-admin/admin-ajax.php';
        } else {
            $this->ajaxUrl = admin_url('admin-ajax.php');
            $this->siteUrl = home_url();
        }

        // Verify requirements
        $this->verifyRequirements();

        // Load data files
        $this->loadDataFiles();

        // Prepare resources
        $this->prepareResources();
    }

    /**
     * Verify WP Statistics settings are correct for tracker simulation
     */
    private function verifyRequirements()
    {
        if (!Option::getValue('use_cache_plugin')) {
            echo "Warning: Client-side tracking (use_cache_plugin) is not enabled.\n";
            echo "The AJAX endpoint may not be registered. Attempting anyway...\n\n";
        }

        if (!Option::getValue('bypass_ad_blockers')) {
            echo "Warning: Bypass ad blockers option is not enabled.\n";
            echo "This simulator uses admin-ajax.php which requires this setting.\n";
            echo "Attempting anyway...\n\n";
        }

        // Check if the site is reachable (skip in dry-run mode)
        if (!$this->config['dry_run']) {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $this->ajaxUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 5,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 3,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
            ]);
            curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
            curl_close($ch);

            if ($httpCode === 0 || $httpCode >= 500) {
                echo "Error: Cannot connect to {$this->ajaxUrl} (HTTP {$httpCode})\n";
                echo "Please ensure your local web server is running.\n";
                echo "Use --dry-run to test without making HTTP requests.\n\n";
                exit(1);
            }

            // If we were redirected, update the AJAX URL
            if ($finalUrl !== $this->ajaxUrl) {
                echo "Note: Redirected to {$finalUrl}\n";
                $this->ajaxUrl = $finalUrl;
                // Also update site URL based on the redirect
                $this->siteUrl = preg_replace('#/wp-admin/admin-ajax\.php$#', '', $finalUrl);
            }
        }
    }

    /**
     * Load JSON data files
     */
    private function loadDataFiles()
    {
        $dataDir = __DIR__ . '/data/';

        $this->countries = json_decode(file_get_contents($dataDir . 'countries.json'), true);
        $this->devices = json_decode(file_get_contents($dataDir . 'devices.json'), true);
        $this->referrers = json_decode(file_get_contents($dataDir . 'referrers.json'), true);
        $this->userAgents = json_decode(file_get_contents($dataDir . 'user-agents.json'), true);
        $this->timezones = json_decode(file_get_contents($dataDir . 'timezones.json'), true);
        $this->languages = json_decode(file_get_contents($dataDir . 'languages.json'), true);

        if (!$this->countries || !$this->devices || !$this->referrers) {
            die("Error: Failed to load data files from {$dataDir}\n");
        }
    }

    /**
     * Prepare WordPress posts as trackable resources
     */
    private function prepareResources()
    {
        $posts = get_posts([
            'posts_per_page' => 50,
            'post_type' => ['post', 'page'],
            'post_status' => 'publish',
        ]);

        if (empty($posts)) {
            die("Error: No published posts found. Create some posts first.\n");
        }

        foreach ($posts as $post) {
            // Create or get resource record
            $resourceId = RecordFactory::resource()->getId([
                'resource_type' => $post->post_type,
                'resource_id' => $post->ID,
            ]);

            if (!$resourceId) {
                $resourceId = RecordFactory::resource()->insert([
                    'resource_type' => $post->post_type,
                    'resource_id' => $post->ID,
                    'cached_title' => $post->post_title,
                    'cached_author_id' => $post->post_author,
                    'cached_date' => $post->post_date,
                ]);
            }

            // Get permalink path (without domain)
            $uri = str_replace(home_url(), '', get_permalink($post->ID));

            // Create or get resource_uri record
            $resourceUriId = RecordFactory::resourceUri()->getId([
                'resource_id' => $resourceId,
                'uri' => $uri,
            ]);

            if (!$resourceUriId) {
                $resourceUriId = RecordFactory::resourceUri()->insert([
                    'resource_id' => $resourceId,
                    'uri' => $uri,
                ]);
            }

            $this->resources[] = [
                'resource_id' => $resourceId,
                'resource_uri_id' => $resourceUriId,
                'post_id' => $post->ID,
                'post_type' => $post->post_type,
                'uri' => $uri,
                'title' => $post->post_title,
            ];
        }
    }

    /**
     * Generate a valid signature for the request
     */
    private function generateSignature($resourceType, $resourceId)
    {
        return Signature::generate([
            $resourceType,
            $resourceId,
        ]);
    }

    /**
     * Generate visitor profile (device, browser, location, etc.)
     */
    private function generateVisitorProfile()
    {
        // Select country
        $countryCode = $this->weightedRandom($this->countries['distribution']);

        // Select device type
        $deviceType = $this->weightedRandom($this->devices['types']);

        // Select browser
        $browser = $this->weightedRandom($this->devices['browsers']);

        // Select OS based on device type
        $osOptions = $this->devices['os'][$deviceType];
        $os = $this->weightedRandom($osOptions);

        // Select browser version
        $browserVersions = $this->devices['browser_versions'][$browser] ?? ['100.0'];
        $browserVersion = $browserVersions[array_rand($browserVersions)];

        // Get User-Agent string
        $userAgent = $this->getUserAgentString($deviceType, $os, $browser, $browserVersion);

        // Get screen resolution
        $resolutions = $this->devices['resolutions'][$deviceType] ?? ['1920x1080'];
        $resolution = $resolutions[array_rand($resolutions)];
        list($width, $height) = explode('x', $resolution);

        // Get timezone for country
        $timezones = $this->timezones[$countryCode] ?? ['UTC'];
        $timezone = $timezones[array_rand($timezones)];

        // Get language for country
        $langData = $this->languages[$countryCode] ?? ['code' => 'en-US', 'name' => 'English'];

        // Generate referrer
        $referrer = $this->generateReferrer();

        // Generate IP address for country
        $ip = $this->generateIpForCountry($countryCode);

        return [
            'country_code' => $countryCode,
            'device_type' => $deviceType,
            'browser' => $browser,
            'browser_version' => $browserVersion,
            'os' => $os,
            'user_agent' => $userAgent,
            'screen_width' => $width,
            'screen_height' => $height,
            'timezone' => $timezone,
            'language_code' => $langData['code'],
            'language_name' => $langData['name'],
            'referrer' => $referrer,
            'ip' => $ip,
        ];
    }

    /**
     * Get User-Agent string for device/browser/OS combination
     */
    private function getUserAgentString($deviceType, $os, $browser, $version)
    {
        // Map OS to user-agent key
        $osKey = $os;
        if ($deviceType === 'Mobile' && in_array($os, ['iOS'])) {
            $osKey = 'iOS';
        } elseif ($deviceType === 'Mobile' && in_array($os, ['Android'])) {
            $osKey = 'Android';
        } elseif ($deviceType === 'Tablet' && in_array($os, ['iOS'])) {
            $osKey = 'iOS';
        } elseif ($deviceType === 'Tablet' && in_array($os, ['Android'])) {
            $osKey = 'Android';
        }

        // Try to get template from user-agents.json
        $templates = $this->userAgents[$deviceType][$osKey][$browser] ?? [];

        if (!empty($templates)) {
            $template = $templates[array_rand($templates)];
            return str_replace('{version}', $version, $template);
        }

        // Fallback to a generic User-Agent
        return $this->buildFallbackUserAgent($deviceType, $browser, $version, $os);
    }

    /**
     * Build fallback User-Agent string
     */
    private function buildFallbackUserAgent($deviceType, $browser, $version, $os)
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
     * Generate referrer URL based on channel distribution
     */
    private function generateReferrer()
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

        // Build referrer URL
        switch ($channel) {
            case 'search':
                return "https://www.{$domain}/search?q=" . urlencode('sample search');
            case 'social':
                return "https://www.{$domain}/";
            case 'referral':
                return "https://{$domain}/article/sample";
            case 'email':
                return "https://{$domain}/";
            case 'paid':
                return "https://www.{$domain}/aclk?sa=l";
            default:
                return "https://{$domain}/";
        }
    }

    /**
     * Generate IP address for a country (using realistic ranges)
     */
    private function generateIpForCountry($countryCode)
    {
        // Simplified IP ranges per country
        $countryIpRanges = [
            'US' => ['24', '63', '65', '66', '67', '68', '69', '70', '71', '72', '73', '74', '75', '76', '96', '97', '98', '99', '100'],
            'GB' => ['2', '5', '31', '46', '81', '82', '86', '87', '90', '91', '92', '109'],
            'CA' => ['24', '64', '65', '66', '67', '68', '69', '70', '71', '72', '74', '75', '76', '99', '142', '184', '192', '198', '199', '204', '205', '206', '207'],
            'DE' => ['46', '77', '78', '79', '80', '84', '85', '87', '88', '89', '91', '93', '94', '95', '130', '134', '138', '139'],
            'FR' => ['37', '46', '78', '80', '81', '82', '83', '84', '85', '86', '88', '90', '91', '92', '93', '109', '176', '178', '185', '193'],
            'AU' => ['1', '14', '27', '43', '49', '58', '59', '60', '101', '103', '110', '112', '113', '114', '115', '116', '117', '118', '119', '120', '121', '122', '123', '124', '125'],
            'IN' => ['14', '27', '36', '43', '49', '59', '61', '101', '103', '106', '110', '112', '115', '116', '117', '119', '120', '121', '122', '123', '124', '125'],
            'BR' => ['131', '138', '139', '143', '146', '152', '168', '177', '179', '186', '187', '189', '191', '200', '201'],
            'JP' => ['14', '27', '36', '42', '43', '49', '58', '59', '60', '61', '101', '106', '110', '111', '112', '113', '114', '115', '116', '117', '118', '119', '120', '121', '122', '123', '124', '125', '126'],
            'NL' => ['2', '5', '31', '37', '46', '77', '78', '80', '81', '82', '83', '84', '85', '86', '87', '88', '89', '91', '92', '93', '94', '95', '109', '145', '146', '176', '178', '185', '188', '193', '194', '195'],
            'ES' => ['2', '5', '31', '37', '46', '77', '78', '79', '80', '81', '82', '83', '84', '85', '86', '87', '88', '89', '90', '91', '92', '93', '94', '95', '109', '176', '178', '185', '188', '193', '194', '195', '212', '213'],
            'IT' => ['2', '5', '31', '37', '46', '77', '78', '79', '80', '81', '82', '83', '84', '85', '86', '87', '88', '89', '90', '91', '92', '93', '94', '95', '109', '151', '176', '178', '185', '188', '193', '194', '195', '212', '213'],
            'MX' => ['131', '138', '143', '148', '152', '177', '187', '189', '200', '201'],
            'SE' => ['2', '5', '31', '37', '46', '77', '78', '79', '80', '81', '82', '83', '84', '85', '86', '87', '88', '89', '90', '91', '92', '93', '94', '95', '109', '176', '178', '185', '188', '193', '194', '195', '212', '213'],
            'CH' => ['31', '46', '77', '78', '79', '80', '81', '82', '83', '84', '85', '86', '87', '88', '89', '91', '92', '93', '94', '95', '109', '176', '178', '185', '188', '193', '194', '195', '212', '213'],
            'PL' => ['31', '37', '46', '77', '78', '79', '80', '81', '82', '83', '84', '85', '86', '87', '88', '89', '91', '92', '93', '94', '95', '109', '176', '178', '185', '188', '193', '194', '195', '212', '213'],
            'BE' => ['2', '5', '31', '37', '46', '77', '78', '79', '80', '81', '82', '83', '84', '85', '86', '87', '88', '89', '91', '92', '93', '94', '95', '109', '176', '178', '185', '188', '193', '194', '195', '212', '213'],
            'NO' => ['2', '5', '31', '37', '46', '77', '78', '79', '80', '81', '82', '83', '84', '85', '86', '87', '88', '89', '91', '92', '93', '94', '95', '109', '176', '178', '185', '188', '193', '194', '195', '212', '213'],
            'DK' => ['2', '5', '31', '37', '46', '77', '78', '79', '80', '81', '82', '83', '84', '85', '86', '87', '88', '89', '91', '92', '93', '94', '95', '109', '176', '178', '185', '188', '193', '194', '195', '212', '213'],
            'FI' => ['2', '5', '31', '37', '46', '77', '78', '79', '80', '81', '82', '83', '84', '85', '86', '87', '88', '89', '91', '92', '93', '94', '95', '109', '176', '178', '185', '188', '193', '194', '195', '212', '213'],
        ];

        $firstOctet = $countryIpRanges[$countryCode] ?? ['100', '101', '102'];
        $first = $firstOctet[array_rand($firstOctet)];

        return $first . '.' . rand(1, 254) . '.' . rand(1, 254) . '.' . rand(1, 254);
    }

    /**
     * Generate request parameters (matching Tracker.js format)
     */
    private function generateRequestData($profile)
    {
        // Select random resource
        $resource = $this->resources[array_rand($this->resources)];

        return [
            // AJAX action
            'action' => 'wp_statistics_hit_record',

            // Required tracking params
            'resourceUriId' => $resource['resource_uri_id'],
            'resourceUri' => base64_encode($resource['uri']),
            'resource_type' => $resource['post_type'],
            'resource_id' => $resource['post_id'],
            'signature' => $this->generateSignature($resource['post_type'], $resource['post_id']),

            // Locale info (from browser)
            'timezone' => $profile['timezone'],
            'language' => $profile['language_code'],
            'languageFullName' => $profile['language_name'],
            'screenWidth' => $profile['screen_width'],
            'screenHeight' => $profile['screen_height'],

            // Referrer (base64 encoded)
            'referred' => base64_encode($profile['referrer']),

            // Additional (for compatibility)
            'page_uri' => base64_encode($resource['uri']),
        ];
    }

    /**
     * Send HTTP request to admin-ajax.php
     */
    private function sendTrackingRequest($params, $profile)
    {
        if ($this->config['dry_run']) {
            $this->stats['requests_sent']++;
            $this->stats['requests_successful']++;
            return true;
        }

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $this->ajaxUrl,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($params),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_POSTREDIR => CURL_REDIR_POST_ALL, // Preserve POST data through redirects
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded',
                'User-Agent: ' . $profile['user_agent'],
                'X-Forwarded-For: ' . $profile['ip'],
                'Referer: ' . ($profile['referrer'] ?: $this->siteUrl . '/'),
            ],
            // For local development
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        $this->stats['requests_sent']++;

        if ($error) {
            $this->stats['requests_failed']++;
            $this->stats['errors'][] = "CURL Error: $error";
            return false;
        }

        $result = json_decode($response, true);

        if ($httpCode === 200 && isset($result['status']) && $result['status'] === true) {
            $this->stats['requests_successful']++;
            return true;
        }

        $this->stats['requests_failed']++;
        $errorMsg = $result['data'] ?? $response;
        if (!in_array($errorMsg, $this->stats['errors'])) {
            $this->stats['errors'][] = "HTTP $httpCode: $errorMsg";
        }
        return false;
    }

    /**
     * Weighted random selection
     */
    private function weightedRandom($weights)
    {
        $total = array_sum($weights);
        $rand = mt_rand(1, (int)($total * 100)) / 100;

        $cumulative = 0;
        foreach ($weights as $key => $weight) {
            $cumulative += $weight;
            if ($rand <= $cumulative) {
                return $key;
            }
        }

        return array_key_last($weights);
    }

    /**
     * Get weekday multiplier
     */
    private function getWeekdayMultiplier($dayOfWeek)
    {
        $multipliers = [
            1 => 1.0,   // Monday
            2 => 1.1,   // Tuesday
            3 => 1.2,   // Wednesday (peak)
            4 => 1.1,   // Thursday
            5 => 0.9,   // Friday
            6 => 0.6,   // Saturday
            7 => 0.5,   // Sunday
        ];
        return $multipliers[$dayOfWeek] ?? 1.0;
    }

    /**
     * Get hour distribution weights
     */
    private function getHourDistribution()
    {
        return [
            0 => 1, 1 => 1, 2 => 1, 3 => 1, 4 => 1, 5 => 1,
            6 => 2, 7 => 3, 8 => 4,
            9 => 6, 10 => 7, 11 => 8, 12 => 7, 13 => 7, 14 => 8, 15 => 7, 16 => 6, 17 => 5,
            18 => 4, 19 => 3, 20 => 3, 21 => 2, 22 => 2, 23 => 1
        ];
    }

    /**
     * Main execution loop
     */
    public function run()
    {
        $this->printHeader();

        $currentDate = new DateTime($this->config['from']);
        $endDate = new DateTime($this->config['to']);
        $totalDays = $currentDate->diff($endDate)->days + 1;
        $dayNum = 0;

        $hourDistribution = $this->getHourDistribution();
        $totalHourWeight = array_sum($hourDistribution);

        while ($currentDate <= $endDate) {
            $date = $currentDate->format('Y-m-d');
            $dayNum++;

            // Calculate visitors for this day
            $dayOfWeek = (int)$currentDate->format('N');
            $weekdayMultiplier = $this->getWeekdayMultiplier($dayOfWeek);
            $visitorsToday = (int)round($this->config['visitors_per_day'] * $weekdayMultiplier);

            echo "Day {$dayNum}/{$totalDays} ({$date}): {$visitorsToday} visitors\n";

            // Distribute visitors across hours
            for ($i = 0; $i < $visitorsToday; $i++) {
                // Select hour based on distribution
                $hour = $this->weightedRandom($hourDistribution);

                // Generate visitor profile
                $profile = $this->generateVisitorProfile();

                // Generate request data
                $params = $this->generateRequestData($profile);

                // Send request
                $success = $this->sendTrackingRequest($params, $profile);

                if ($this->config['verbose']) {
                    $status = $success ? 'OK' : 'FAIL';
                    $resource = array_filter($this->resources, fn($r) => $r['resource_uri_id'] == $params['resourceUriId']);
                    $resource = reset($resource);
                    echo "  [{$status}] {$profile['ip']} - {$profile['browser']}/{$profile['os']} - {$resource['uri']}\n";
                }

                // Delay between requests
                if ($this->config['delay_ms'] > 0 && !$this->config['dry_run']) {
                    usleep($this->config['delay_ms'] * 1000);
                }
            }

            $currentDate->modify('+1 day');
        }

        $this->printSummary();
    }

    /**
     * Print header
     */
    private function printHeader()
    {
        echo "=== WP Statistics Tracker.js Simulator ===\n\n";
        echo "Date Range: {$this->config['from']} to {$this->config['to']}\n";
        echo "Visitors/Day: {$this->config['visitors_per_day']}\n";
        echo "Resources: " . count($this->resources) . " posts\n";
        echo "AJAX URL: {$this->ajaxUrl}\n";
        if ($this->config['dry_run']) {
            echo "Mode: DRY RUN (no actual requests)\n";
        }
        echo "\n";
    }

    /**
     * Print summary
     */
    private function printSummary()
    {
        echo "\n=== Summary ===\n";
        echo "Requests sent: " . number_format($this->stats['requests_sent']) . "\n";
        echo "Successful: " . number_format($this->stats['requests_successful']) . "\n";
        echo "Failed: " . number_format($this->stats['requests_failed']) . "\n";

        if (!empty($this->stats['errors'])) {
            echo "\nErrors:\n";
            $uniqueErrors = array_unique($this->stats['errors']);
            foreach (array_slice($uniqueErrors, 0, 10) as $error) {
                echo "  - {$error}\n";
            }
            if (count($uniqueErrors) > 10) {
                echo "  ... and " . (count($uniqueErrors) - 10) . " more\n";
            }
        }
    }
}

// ============================================================================
// CLI HANDLING
// ============================================================================

// Parse command line arguments
$options = getopt('', [
    'days:',
    'from:',
    'to:',
    'visitors-per-day:',
    'delay:',
    'url:',
    'verbose',
    'dry-run',
    'help'
]);

if (isset($options['help'])) {
    echo "Usage: php bin/dummy-tracker-simulator.php [options]\n\n";
    echo "Simulates Tracker.js requests to admin-ajax.php for realistic test data.\n\n";
    echo "Options:\n";
    echo "  --days=<number>           Days to generate (default: 7)\n";
    echo "  --from=<YYYY-MM-DD>       Start date\n";
    echo "  --to=<YYYY-MM-DD>         End date (default: today)\n";
    echo "  --visitors-per-day=<num>  Average visitors per day (default: 50)\n";
    echo "  --delay=<ms>              Delay between requests in ms (default: 50)\n";
    echo "  --url=<url>               Custom site URL (e.g., https://wordpress.test)\n";
    echo "  --verbose                 Show detailed request output\n";
    echo "  --dry-run                 Generate data without sending requests\n";
    echo "  --help                    Show this help\n\n";
    echo "Examples:\n";
    echo "  php bin/dummy-tracker-simulator.php --days=7 --visitors-per-day=50\n";
    echo "  php bin/dummy-tracker-simulator.php --url=https://localhost:8080 --days=1\n";
    echo "  php bin/dummy-tracker-simulator.php --from=2024-12-01 --to=2024-12-31 --verbose\n";
    echo "  php bin/dummy-tracker-simulator.php --days=1 --visitors-per-day=10 --dry-run\n\n";
    echo "Prerequisites:\n";
    echo "  - 'use_cache_plugin' option should be enabled\n";
    echo "  - 'bypass_ad_blockers' option should be enabled\n";
    exit(0);
}

// Build configuration
$config = [
    'days' => (int)($options['days'] ?? 7),
    'visitors_per_day' => (int)($options['visitors-per-day'] ?? 50),
    'delay_ms' => (int)($options['delay'] ?? 50),
    'verbose' => isset($options['verbose']),
    'dry_run' => isset($options['dry-run']),
];

if (isset($options['from'])) {
    $config['from'] = $options['from'];
}

if (isset($options['to'])) {
    $config['to'] = $options['to'];
}

if (isset($options['url'])) {
    $config['url'] = rtrim($options['url'], '/');
}

// Run simulator
$simulator = new TrackerSimulator($config);
$simulator->run();
