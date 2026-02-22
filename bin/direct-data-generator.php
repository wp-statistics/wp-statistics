#!/usr/bin/env php
<?php
/**
 * WP Statistics Direct Data Generator
 *
 * Generates test data by directly inserting into the database using RecordFactory.
 * This doesn't require a running web server.
 *
 * Usage: php bin/direct-data-generator.php [options]
 *
 * @package WP_Statistics
 * @since 15.0.0
 */

// Load WordPress
define('WP_USE_THEMES', false);
require_once(__DIR__ . '/../../../../wp-load.php');

// Ensure WP Statistics is loaded
if (!class_exists('WP_Statistics\Bootstrap')) {
    die("Error: WP Statistics plugin not found.\n");
}

use WP_Statistics\Records\RecordFactory;

/**
 * DirectDataGenerator - Inserts data directly into database
 */
class DirectDataGenerator
{
    /** @var array Configuration */
    private $config = [
        'days' => 14,
        'visitors_per_day' => 50,
        'from' => null,
        'to' => null,
        'verbose' => false,
    ];

    /** @var array Loaded data files */
    private $countries;
    private $devices;
    private $referrers;
    private $languages;
    private $timezones;

    /** @var array Resources (posts) */
    private $resources = [];

    /** @var array Search term resource URIs */
    private $searchTermResources = [];

    /** @var array Sample search terms */
    private $searchTerms = [
        'wordpress tutorial',
        'how to create plugin',
        'best practices',
        'seo tips',
        'performance optimization',
        'security guide',
        'theme development',
        'rest api',
        'gutenberg blocks',
        'woocommerce setup',
        'contact form',
        'custom post type',
        'user registration',
        'database optimization',
        'caching solutions',
        'backup strategies',
        'migration guide',
        'multisite setup',
        'analytics dashboard',
        'email marketing',
    ];

    /** @var array Cached IDs */
    private $cache = [
        'countries' => [],
        'cities' => [],
        'device_types' => [],
        'browsers' => [],
        'browser_versions' => [],
        'oss' => [],
        'resolutions' => [],
        'languages' => [],
        'timezones' => [],
        'referrers' => [],
    ];

    /** @var array Stats */
    private $stats = [
        'visitors' => 0,
        'views' => 0,
        'sessions' => 0,
    ];

    public function __construct(array $options = [])
    {
        $this->config = array_merge($this->config, $options);

        // Set date range
        $this->config['to'] = $this->config['to'] ?? date('Y-m-d');
        $this->config['from'] = $this->config['from'] ?? date('Y-m-d', strtotime("-{$this->config['days']} days"));

        $this->loadDataFiles();
        $this->prepareResources();
    }

    private function loadDataFiles()
    {
        $dataDir = __DIR__ . '/data/';

        $this->countries = json_decode(file_get_contents($dataDir . 'countries.json'), true);
        $this->devices = json_decode(file_get_contents($dataDir . 'devices.json'), true);
        $this->referrers = json_decode(file_get_contents($dataDir . 'referrers.json'), true);
        $this->languages = json_decode(file_get_contents($dataDir . 'languages.json'), true);
        $this->timezones = json_decode(file_get_contents($dataDir . 'timezones.json'), true);

        if (!$this->countries || !$this->devices || !$this->referrers) {
            die("Error: Failed to load data files.\n");
        }
    }

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

        echo "Prepared " . count($this->resources) . " resources.\n";

        // Prepare search term resources
        $this->prepareSearchTermResources();
    }

    private function prepareSearchTermResources()
    {
        foreach ($this->searchTerms as $term) {
            // URI format: /?s=search+term
            $uri = '/?s=' . str_replace(' ', '+', $term);

            // Create or get resource record for search
            $resourceId = RecordFactory::resource()->getId([
                'resource_type' => 'search',
                'resource_id' => 0,
            ]);

            if (!$resourceId) {
                $resourceId = RecordFactory::resource()->insert([
                    'resource_type' => 'search',
                    'resource_id' => 0,
                    'cached_title' => 'Search Results',
                    'cached_author_id' => 0,
                    'cached_date' => date('Y-m-d H:i:s'),
                ]);
            }

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

            $this->searchTermResources[] = [
                'resource_id' => $resourceId,
                'resource_uri_id' => $resourceUriId,
                'uri' => $uri,
                'term' => $term,
            ];
        }

        echo "Prepared " . count($this->searchTermResources) . " search term resources.\n";
    }

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

    private function getWeekdayMultiplier($dayOfWeek)
    {
        $multipliers = [
            1 => 1.0, 2 => 1.1, 3 => 1.2, 4 => 1.1,
            5 => 0.9, 6 => 0.6, 7 => 0.5,
        ];
        return $multipliers[$dayOfWeek] ?? 1.0;
    }

    private function getHourDistribution()
    {
        return [
            0 => 1, 1 => 1, 2 => 1, 3 => 1, 4 => 1, 5 => 1,
            6 => 2, 7 => 3, 8 => 4,
            9 => 6, 10 => 7, 11 => 8, 12 => 7, 13 => 7, 14 => 8, 15 => 7, 16 => 6, 17 => 5,
            18 => 4, 19 => 3, 20 => 3, 21 => 2, 22 => 2, 23 => 1
        ];
    }

    private function generateIpForCountry($countryCode)
    {
        $countryIpRanges = [
            'US' => ['24', '63', '65', '66', '67', '68', '69', '70', '71', '72'],
            'GB' => ['2', '5', '31', '46', '81', '82', '86', '87', '90', '91'],
            'CA' => ['24', '64', '65', '66', '67', '68', '99', '142', '184', '192'],
            'DE' => ['46', '77', '78', '79', '80', '84', '85', '87', '88', '89'],
            'FR' => ['37', '46', '78', '80', '81', '82', '83', '84', '176', '178'],
            'AU' => ['1', '14', '27', '43', '49', '58', '59', '101', '103', '110'],
            'IN' => ['14', '27', '36', '43', '49', '59', '61', '101', '103', '106'],
            'BR' => ['131', '138', '139', '143', '146', '152', '168', '177', '179', '186'],
            'JP' => ['14', '27', '36', '42', '43', '49', '58', '59', '60', '61'],
            'NL' => ['2', '5', '31', '37', '46', '77', '78', '80', '145', '146'],
        ];

        $firstOctet = $countryIpRanges[$countryCode] ?? ['100', '101', '102'];
        $first = $firstOctet[array_rand($firstOctet)];

        return $first . '.' . rand(1, 254) . '.' . rand(1, 254) . '.' . rand(1, 254);
    }

    private function getOrCreateCountry($code)
    {
        if (isset($this->cache['countries'][$code])) {
            return $this->cache['countries'][$code];
        }

        $id = RecordFactory::country()->getId(['code' => $code]);
        if (!$id) {
            $countryName = $this->countries['country_names'][$code] ?? $code;
            $continentCode = $this->countries['continent_codes'][$code] ?? 'NA';
            $continentName = $this->countries['continent_names'][$continentCode] ?? 'Unknown';

            $id = RecordFactory::country()->insert([
                'code' => $code,
                'name' => $countryName,
                'continent_code' => $continentCode,
                'continent' => $continentName,
            ]);
        }

        $this->cache['countries'][$code] = $id;
        return $id;
    }

    private function getOrCreateCity($cityName, $countryId)
    {
        $key = $cityName . '_' . $countryId;
        if (isset($this->cache['cities'][$key])) {
            return $this->cache['cities'][$key];
        }

        $id = RecordFactory::city()->getId(['city_name' => $cityName, 'country_id' => $countryId]);
        if (!$id) {
            $id = RecordFactory::city()->insert([
                'city_name' => $cityName,
                'country_id' => $countryId,
                'region_code' => '',
                'region_name' => '',
            ]);
        }

        $this->cache['cities'][$key] = $id;
        return $id;
    }

    private function getOrCreateDeviceType($type)
    {
        if (isset($this->cache['device_types'][$type])) {
            return $this->cache['device_types'][$type];
        }

        $id = RecordFactory::deviceType()->getId(['name' => $type]);
        if (!$id) {
            $id = RecordFactory::deviceType()->insert(['name' => $type]);
        }

        $this->cache['device_types'][$type] = $id;
        return $id;
    }

    private function getOrCreateBrowser($name)
    {
        if (isset($this->cache['browsers'][$name])) {
            return $this->cache['browsers'][$name];
        }

        $id = RecordFactory::deviceBrowser()->getId(['name' => $name]);
        if (!$id) {
            $id = RecordFactory::deviceBrowser()->insert(['name' => $name]);
        }

        $this->cache['browsers'][$name] = $id;
        return $id;
    }

    private function getOrCreateBrowserVersion($browserId, $version)
    {
        $key = $browserId . '_' . $version;
        if (isset($this->cache['browser_versions'][$key])) {
            return $this->cache['browser_versions'][$key];
        }

        $id = RecordFactory::deviceBrowserVersion()->getId(['browser_id' => $browserId, 'version' => $version]);
        if (!$id) {
            $id = RecordFactory::deviceBrowserVersion()->insert([
                'browser_id' => $browserId,
                'version' => $version,
            ]);
        }

        $this->cache['browser_versions'][$key] = $id;
        return $id;
    }

    private function getOrCreateOs($name)
    {
        if (isset($this->cache['oss'][$name])) {
            return $this->cache['oss'][$name];
        }

        $id = RecordFactory::deviceOs()->getId(['name' => $name]);
        if (!$id) {
            $id = RecordFactory::deviceOs()->insert(['name' => $name]);
        }

        $this->cache['oss'][$name] = $id;
        return $id;
    }

    private function getOrCreateResolution($width, $height)
    {
        $key = $width . 'x' . $height;
        if (isset($this->cache['resolutions'][$key])) {
            return $this->cache['resolutions'][$key];
        }

        $id = RecordFactory::resolution()->getId(['width' => $width, 'height' => $height]);
        if (!$id) {
            $id = RecordFactory::resolution()->insert([
                'width' => $width,
                'height' => $height,
            ]);
        }

        $this->cache['resolutions'][$key] = $id;
        return $id;
    }

    private function getOrCreateLanguage($code)
    {
        if (isset($this->cache['languages'][$code])) {
            return $this->cache['languages'][$code];
        }

        $id = RecordFactory::language()->getId(['code' => $code]);
        if (!$id) {
            $parts = explode('-', $code);
            $id = RecordFactory::language()->insert([
                'code' => $parts[0] ?? $code,
                'name' => $code,
                'region' => $parts[1] ?? '',
            ]);
        }

        $this->cache['languages'][$code] = $id;
        return $id;
    }

    private function getOrCreateTimezone($name)
    {
        if (isset($this->cache['timezones'][$name])) {
            return $this->cache['timezones'][$name];
        }

        $id = RecordFactory::timezone()->getId(['name' => $name]);
        if (!$id) {
            $id = RecordFactory::timezone()->insert([
                'name' => $name,
                'offset' => '+00:00',
                'is_dst' => 0,
            ]);
        }

        $this->cache['timezones'][$name] = $id;
        return $id;
    }

    private function getOrCreateReferrer($domain, $channel)
    {
        if (empty($domain)) return null;

        $key = $domain . '_' . $channel;
        if (isset($this->cache['referrers'][$key])) {
            return $this->cache['referrers'][$key];
        }

        $id = RecordFactory::referrer()->getId(['domain' => $domain]);
        if (!$id) {
            $id = RecordFactory::referrer()->insert([
                'domain' => $domain,
                'name' => $domain,
                'channel' => $channel,
            ]);
        }

        $this->cache['referrers'][$key] = $id;
        return $id;
    }

    private function generateVisitor($date, $hour)
    {
        // Select attributes
        $countryCode = $this->weightedRandom($this->countries['distribution']);
        $deviceType = $this->weightedRandom($this->devices['types']);
        $browser = $this->weightedRandom($this->devices['browsers']);
        $osOptions = $this->devices['os'][$deviceType] ?? ['Unknown' => 100];
        $os = $this->weightedRandom($osOptions);

        // Browser version
        $browserVersions = $this->devices['browser_versions'][$browser] ?? ['100.0'];
        $browserVersion = $browserVersions[array_rand($browserVersions)];

        // Resolution
        $resolutions = $this->devices['resolutions'][$deviceType] ?? ['1920x1080'];
        $resolution = $resolutions[array_rand($resolutions)];
        list($width, $height) = explode('x', $resolution);

        // City
        $cities = $this->countries['cities'][$countryCode] ?? ['Unknown'];
        $city = $cities[array_rand($cities)];

        // Timezone
        $timezones = $this->timezones[$countryCode] ?? ['UTC'];
        $timezone = $timezones[array_rand($timezones)];

        // Language
        $langData = $this->languages[$countryCode] ?? ['code' => 'en-US', 'name' => 'English'];

        // Referrer
        $channel = $this->weightedRandom($this->referrers['channels']);
        $referrerDomain = '';

        if ($channel !== 'direct' && isset($this->referrers[$channel])) {
            $sources = $this->referrers[$channel];
            $weights = array_column($sources, 'weight', 'domain');
            $referrerDomain = $this->weightedRandom($weights);
        }

        // IP
        $ip = $this->generateIpForCountry($countryCode);
        $hashedIp = hash('sha256', $ip . wp_salt());

        // Generate timestamp
        $timestamp = strtotime("$date $hour:00:00") + rand(0, 3599);

        // Is logged in (12% chance)
        $isLoggedIn = rand(1, 100) <= 12;
        $userId = $isLoggedIn ? rand(1, 10) : 0;

        return [
            'ip' => $ip,
            'hashed_ip' => $hashedIp,
            'country_code' => $countryCode,
            'city' => $city,
            'device_type' => strtolower($deviceType),
            'browser' => $browser,
            'browser_version' => $browserVersion,
            'os' => $os,
            'screen_width' => (int)$width,
            'screen_height' => (int)$height,
            'timezone' => $timezone,
            'language_code' => $langData['code'],
            'referrer_domain' => $referrerDomain,
            'referrer_channel' => $channel,
            'timestamp' => $timestamp,
            'date' => $date,
            'user_id' => $userId,
        ];
    }

    private function insertVisitorData($visitor)
    {
        // Get or create all reference IDs
        $countryId = $this->getOrCreateCountry($visitor['country_code']);
        $cityId = $this->getOrCreateCity($visitor['city'], $countryId);
        $deviceTypeId = $this->getOrCreateDeviceType($visitor['device_type']);
        $browserId = $this->getOrCreateBrowser($visitor['browser']);
        $browserVersionId = $this->getOrCreateBrowserVersion($browserId, $visitor['browser_version']);
        $osId = $this->getOrCreateOs($visitor['os']);
        $resolutionId = $this->getOrCreateResolution($visitor['screen_width'], $visitor['screen_height']);
        $languageId = $this->getOrCreateLanguage($visitor['language_code']);
        $timezoneId = $this->getOrCreateTimezone($visitor['timezone']);
        $referrerId = $this->getOrCreateReferrer($visitor['referrer_domain'], $visitor['referrer_channel']);

        // Create visitor record
        $visitorId = RecordFactory::visitor()->insert([
            'hash' => $visitor['hashed_ip'],
            'ip' => $visitor['hashed_ip'],
            'created_at' => date('Y-m-d H:i:s', $visitor['timestamp']),
        ]);

        if (!$visitorId) {
            return null;
        }

        $this->stats['visitors']++;

        // Create session with realistic bounce behavior
        $resource = $this->resources[array_rand($this->resources)];

        // Realistic bounce rate: ~45% single-page visits
        $isBounce = rand(1, 100) <= 45;
        $viewCount = $isBounce ? 1 : rand(2, 6);

        // Session duration: bounces have short duration (5-30s), engaged users longer (60-600s)
        $duration = $isBounce ? rand(5, 30) : rand(60, 600);

        $sessionId = RecordFactory::session()->insert([
            'visitor_id' => $visitorId,
            'referrer_id' => $referrerId,
            'country_id' => $countryId,
            'city_id' => $cityId,
            'total_views' => $viewCount,
            'device_type_id' => $deviceTypeId,
            'device_os_id' => $osId,
            'device_browser_id' => $browserId,
            'device_browser_version_id' => $browserVersionId,
            'started_at' => date('Y-m-d H:i:s', $visitor['timestamp']),
            'ended_at' => date('Y-m-d H:i:s', $visitor['timestamp'] + $duration),
            'duration' => $duration,
            'user_id' => $visitor['user_id'],
            'timezone_id' => $timezoneId,
            'language_id' => $languageId,
            'resolution_id' => $resolutionId,
        ]);

        if (!$sessionId) {
            return $visitorId;
        }

        $this->stats['sessions']++;

        // Create views
        $prevViewId = null;
        $viewIds = [];

        for ($i = 0; $i < $viewCount; $i++) {
            // 10% chance of being a search term view
            $isSearchView = !empty($this->searchTermResources) && rand(1, 100) <= 10;

            if ($isSearchView) {
                $pageResource = $this->searchTermResources[array_rand($this->searchTermResources)];
            } else {
                $pageResource = $this->resources[array_rand($this->resources)];
            }

            $viewTime = $visitor['timestamp'] + ($i * rand(10, 60));

            // View duration in MILLISECONDS (5-120 seconds = 5000-120000 ms)
            $viewDurationMs = rand(5000, 120000);

            $viewId = RecordFactory::view()->insert([
                'session_id' => $sessionId,
                'resource_uri_id' => $pageResource['resource_uri_id'],
                'resource_id' => $pageResource['resource_id'],
                'viewed_at' => date('Y-m-d H:i:s', $viewTime),
                'duration' => $viewDurationMs,
            ]);

            if ($viewId) {
                $viewIds[] = $viewId;
                $this->stats['views']++;

                // Update previous view's next_view_id
                if ($prevViewId) {
                    RecordFactory::view()->update(
                        ['next_view_id' => $viewId],
                        ['ID' => $prevViewId]
                    );
                }

                $prevViewId = $viewId;
            }
        }

        // Update session with initial and last view IDs
        if (!empty($viewIds)) {
            RecordFactory::session()->update(
                [
                    'initial_view_id' => $viewIds[0],
                    'last_view_id' => end($viewIds),
                ],
                ['ID' => $sessionId]
            );
        }

        return $visitorId;
    }

    public function run()
    {
        echo "=== WP Statistics Direct Data Generator ===\n\n";
        echo "Date Range: {$this->config['from']} to {$this->config['to']}\n";
        echo "Visitors/Day: {$this->config['visitors_per_day']}\n\n";

        $currentDate = new DateTime($this->config['from']);
        $endDate = new DateTime($this->config['to']);
        $totalDays = $currentDate->diff($endDate)->days + 1;
        $dayNum = 0;

        $hourDistribution = $this->getHourDistribution();

        while ($currentDate <= $endDate) {
            $date = $currentDate->format('Y-m-d');
            $dayNum++;

            $dayOfWeek = (int)$currentDate->format('N');
            $weekdayMultiplier = $this->getWeekdayMultiplier($dayOfWeek);
            $visitorsToday = (int)round($this->config['visitors_per_day'] * $weekdayMultiplier);

            echo "Day {$dayNum}/{$totalDays} ({$date}): {$visitorsToday} visitors... ";

            for ($i = 0; $i < $visitorsToday; $i++) {
                $hour = $this->weightedRandom($hourDistribution);
                $visitor = $this->generateVisitor($date, $hour);

                try {
                    $this->insertVisitorData($visitor);
                } catch (Exception $e) {
                    if ($this->config['verbose']) {
                        echo "\nError: " . $e->getMessage() . "\n";
                    }
                }
            }

            echo "Done\n";
            $currentDate->modify('+1 day');
        }

        echo "\n=== Summary ===\n";
        echo "Visitors: " . number_format($this->stats['visitors']) . "\n";
        echo "Sessions: " . number_format($this->stats['sessions']) . "\n";
        echo "Views: " . number_format($this->stats['views']) . "\n";
    }
}

// Parse CLI args
$options = getopt('', [
    'days:',
    'from:',
    'to:',
    'visitors-per-day:',
    'verbose',
    'help'
]);

if (isset($options['help'])) {
    echo "Usage: php bin/direct-data-generator.php [options]\n\n";
    echo "Options:\n";
    echo "  --days=<number>           Days to generate (default: 14)\n";
    echo "  --from=<YYYY-MM-DD>       Start date\n";
    echo "  --to=<YYYY-MM-DD>         End date (default: today)\n";
    echo "  --visitors-per-day=<num>  Visitors per day (default: 50)\n";
    echo "  --verbose                 Show errors\n";
    exit(0);
}

$config = [
    'days' => (int)($options['days'] ?? 14),
    'visitors_per_day' => (int)($options['visitors-per-day'] ?? 50),
    'verbose' => isset($options['verbose']),
];

if (isset($options['from'])) $config['from'] = $options['from'];
if (isset($options['to'])) $config['to'] = $options['to'];

$generator = new DirectDataGenerator($config);
$generator->run();
