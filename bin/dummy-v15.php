#!/usr/bin/env php
<?php
/**
 * WP Statistics v15 Dummy Data Generator
 *
 * Standalone development tool for generating realistic test data.
 * NOT included in plugin distribution.
 *
 * Usage: php bin/dummy-v15.php [options]
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

use WP_Statistics\Records\RecordFactory;
use WP_Statistics\Service\Database\DB;

// Parse command line arguments
$options = getopt('', [
    'days:',
    'from:',
    'to:',
    'visitors-per-day:',
    'clean',
    'help'
]);

if (isset($options['help'])) {
    showHelp();
    exit(0);
}

// Configuration
$days = $options['days'] ?? 365;
$visitorsPerDay = $options['visitors-per-day'] ?? 200;
$to = $options['to'] ?? date('Y-m-d');
$from = $options['from'] ?? date('Y-m-d', strtotime("-{$days} days"));
$clean = isset($options['clean']);

// Load data configurations
$countries = json_decode(file_get_contents(__DIR__ . '/data/countries.json'), true);
$devices = json_decode(file_get_contents(__DIR__ . '/data/devices.json'), true);
$referrers = json_decode(file_get_contents(__DIR__ . '/data/referrers.json'), true);

echo "=== WP Statistics Dummy Data Generator ===\n\n";
echo "Date Range: {$from} to {$to}\n";
echo "Visitors/Day: {$visitorsPerDay}\n\n";

if ($clean) {
    echo "Cleaning existing data...\n";
    cleanData();
    echo "✓ Data cleaned\n\n";
}

// Populate dimensions first
echo "Populating dimensions...\n";
$dimensionIds = populateDimensions($countries, $devices, $referrers);
echo "✓ Dimensions populated\n\n";

// Get WordPress posts for resources
$posts = getWordPressPosts();
echo "Found {$posts['count']} WordPress posts\n\n";

// Generate data day by day
echo "Generating data...\n";
$stats = generateData($from, $to, $visitorsPerDay, $dimensionIds, $posts, $countries, $devices, $referrers);

echo "\n✓ Complete!\n\n";
echo "Summary:\n";
echo "  Visitors: " . number_format($stats['visitors']) . "\n";
echo "  Sessions: " . number_format($stats['sessions']) . "\n";
echo "  Views: " . number_format($stats['views']) . "\n";
echo "  Time: {$stats['time']}\n";

// ============================================================================
// FUNCTIONS
// ============================================================================

/**
 * Populate dimension tables (countries, devices, browsers, OS, referrers)
 */
function populateDimensions($countries, $devices, $referrers) {
    $dimensionIds = [];

    // Countries
    foreach ($countries['distribution'] as $code => $weight) {
        $name = $countries['country_names'][$code];
        $continentCode = $countries['continent_codes'][$code];
        $continentName = $countries['continent_names'][$continentCode];

        // Check if country already exists
        $existing = RecordFactory::country()->getId(['code' => $code]);
        if ($existing) {
            $id = $existing;
        } else {
            $id = RecordFactory::country()->insert([
                'code' => $code,
                'name' => $name,
                'continent_code' => $continentCode,
                'continent' => $continentName,
            ]);
        }
        $dimensionIds['countries'][$code] = $id;
    }

    // Cities
    foreach ($countries['cities'] as $countryCode => $cityList) {
        $countryId = $dimensionIds['countries'][$countryCode];
        foreach ($cityList as $cityName) {
            // Check if city already exists
            $existing = RecordFactory::city()->getId([
                'country_id' => $countryId,
                'city_name' => $cityName
            ]);
            if ($existing) {
                $id = $existing;
            } else {
                $id = RecordFactory::city()->insert([
                    'country_id' => $countryId,
                    'city_name' => $cityName,
                    'region_name' => $cityName . ' Region',
                ]);
            }
            $dimensionIds['cities'][$countryCode][] = $id;
        }
    }

    // Device types
    foreach ($devices['types'] as $type => $weight) {
        $existing = RecordFactory::deviceType()->getId(['name' => $type]);
        if ($existing) {
            $id = $existing;
        } else {
            $id = RecordFactory::deviceType()->insert(['name' => $type]);
        }
        $dimensionIds['device_types'][$type] = $id;
    }

    // Browsers
    foreach ($devices['browsers'] as $browser => $weight) {
        $existing = RecordFactory::deviceBrowser()->getId(['name' => $browser]);
        if ($existing) {
            $id = $existing;
        } else {
            $id = RecordFactory::deviceBrowser()->insert(['name' => $browser]);
        }
        $dimensionIds['browsers'][$browser] = $id;
    }

    // Browser versions
    foreach ($devices['browser_versions'] as $browser => $versions) {
        $browserId = $dimensionIds['browsers'][$browser];
        foreach ($versions as $version) {
            // Check if browser version already exists
            $existing = RecordFactory::deviceBrowserVersion()->getId([
                'browser_id' => $browserId,
                'version' => $version
            ]);
            if ($existing) {
                $id = $existing;
            } else {
                $id = RecordFactory::deviceBrowserVersion()->insert([
                    'browser_id' => $browserId,
                    'version' => $version,
                ]);
            }
            $dimensionIds['browser_versions'][$browser][] = $id;
        }
    }

    // Operating systems
    $allOs = [];
    foreach ($devices['os'] as $deviceType => $osList) {
        foreach ($osList as $os => $weight) {
            if (!in_array($os, $allOs)) {
                $existing = RecordFactory::deviceOs()->getId(['name' => $os]);
                if ($existing) {
                    $id = $existing;
                } else {
                    $id = RecordFactory::deviceOs()->insert(['name' => $os]);
                }
                $dimensionIds['os'][$os] = $id;
                $allOs[] = $os;
            }
        }
    }

    // Referrers
    foreach ($referrers as $channel => $items) {
        if ($channel === 'channels') continue;

        if ($channel === 'search') {
            foreach ($items as $item) {
                // Check if referrer already exists
                $existing = RecordFactory::referrer()->getId([
                    'channel' => 'search',
                    'domain' => $item['domain']
                ]);
                if ($existing) {
                    $id = $existing;
                } else {
                    $id = RecordFactory::referrer()->insert([
                        'channel' => 'search',
                        'name' => $item['name'],
                        'domain' => $item['domain'],
                    ]);
                }
                $dimensionIds['referrers']['search'][] = ['id' => $id, 'weight' => $item['weight']];
            }
        } elseif ($channel === 'social') {
            foreach ($items as $item) {
                $existing = RecordFactory::referrer()->getId([
                    'channel' => 'social',
                    'domain' => $item['domain']
                ]);
                if ($existing) {
                    $id = $existing;
                } else {
                    $id = RecordFactory::referrer()->insert([
                        'channel' => 'social',
                        'name' => $item['name'],
                        'domain' => $item['domain'],
                    ]);
                }
                $dimensionIds['referrers']['social'][] = ['id' => $id, 'weight' => $item['weight']];
            }
        } elseif ($channel === 'referral') {
            foreach ($items as $item) {
                $existing = RecordFactory::referrer()->getId([
                    'channel' => 'referral',
                    'domain' => $item['domain']
                ]);
                if ($existing) {
                    $id = $existing;
                } else {
                    $id = RecordFactory::referrer()->insert([
                        'channel' => 'referral',
                        'name' => $item['name'],
                        'domain' => $item['domain'],
                    ]);
                }
                $dimensionIds['referrers']['referral'][] = ['id' => $id, 'weight' => $item['weight']];
            }
        } elseif ($channel === 'email') {
            foreach ($items as $item) {
                $existing = RecordFactory::referrer()->getId([
                    'channel' => 'email',
                    'domain' => $item['domain']
                ]);
                if ($existing) {
                    $id = $existing;
                } else {
                    $id = RecordFactory::referrer()->insert([
                        'channel' => 'email',
                        'name' => $item['name'],
                        'domain' => $item['domain'],
                    ]);
                }
                $dimensionIds['referrers']['email'][] = ['id' => $id, 'weight' => $item['weight']];
            }
        } elseif ($channel === 'paid') {
            foreach ($items as $item) {
                $existing = RecordFactory::referrer()->getId([
                    'channel' => 'paid',
                    'domain' => $item['domain']
                ]);
                if ($existing) {
                    $id = $existing;
                } else {
                    $id = RecordFactory::referrer()->insert([
                        'channel' => 'paid',
                        'name' => $item['name'],
                        'domain' => $item['domain'],
                    ]);
                }
                $dimensionIds['referrers']['paid'][] = ['id' => $id, 'weight' => $item['weight']];
            }
        }
    }

    return $dimensionIds;
}

/**
 * Get WordPress posts for resources
 */
function getWordPressPosts() {
    $posts = get_posts([
        'posts_per_page' => 50,
        'post_type' => ['post', 'page'],
        'post_status' => 'publish',
    ]);

    $items = [];
    foreach ($posts as $post) {
        // Create or get resource
        $resourceId = RecordFactory::resource()->insert([
            'resource_type' => $post->post_type,
            'resource_id' => $post->ID,
            'cached_title' => $post->post_title,
            'cached_author_id' => $post->post_author,
            'cached_date' => $post->post_date,
        ]);

        // Create or get resource URI
        $uri = str_replace(home_url(), '', get_permalink($post->ID));
        $resourceUriId = RecordFactory::resourceUri()->insert([
            'resource_id' => $resourceId,
            'uri' => $uri,
        ]);

        $items[] = [
            'resource_id' => $resourceId,
            'resource_uri_id' => $resourceUriId,
            'post_id' => $post->ID,
            'title' => $post->post_title,
        ];
    }

    return ['count' => count($items), 'items' => $items];
}

/**
 * Main data generation function
 */
function generateData($from, $to, $visitorsPerDay, $dimensionIds, $posts, $countries, $devices, $referrers) {
    $stats = ['visitors' => 0, 'sessions' => 0, 'views' => 0];
    $startTime = microtime(true);

    $currentDate = new DateTime($from);
    $endDate = new DateTime($to);
    $dayNum = 0;
    $totalDays = $currentDate->diff($endDate)->days + 1;

    while ($currentDate <= $endDate) {
        $date = $currentDate->format('Y-m-d');
        $dayNum++;

        // Calculate realistic visitor count
        $dayOfWeek = (int)$currentDate->format('N');
        $month = (int)$currentDate->format('n');

        $weekdayMultiplier = getWeekdayMultiplier($dayOfWeek);
        $growthMultiplier = 0.7 + (0.6 * ($dayNum / $totalDays));
        $seasonalMultiplier = getSeasonalMultiplier($month);

        $visitorsToday = (int)round($visitorsPerDay * $weekdayMultiplier * $growthMultiplier * $seasonalMultiplier);

        // Generate visitors for this day
        for ($i = 0; $i < $visitorsToday; $i++) {
            // Create visitor
            $visitorHash = md5($date . $i . rand());
            $visitorId = RecordFactory::visitor()->insert([
                'hash' => $visitorHash,
                'created_at' => $date . ' ' . sprintf('%02d:%02d:%02d', rand(0, 23), rand(0, 59), rand(0, 59)),
            ]);

            // Create session
            $sessionData = generateSessionData($date, $dimensionIds, $countries, $devices, $referrers);
            $sessionData['visitor_id'] = $visitorId;
            $sessionId = RecordFactory::session()->insert($sessionData);

            // Create views
            $viewCount = getRandomViewCount();
            $sessionDuration = 0;

            for ($v = 0; $v < $viewCount; $v++) {
                $post = $posts['items'][array_rand($posts['items'])];
                $viewDuration = ($v < $viewCount - 1) ? rand(30, 180) : null;

                $viewData = [
                    'session_id' => $sessionId,
                    'resource_uri_id' => $post['resource_uri_id'],
                    'resource_id' => $post['resource_id'],
                    'viewed_at' => $sessionData['started_at'],
                    'duration' => $viewDuration,
                ];
                RecordFactory::view()->insert($viewData);

                if ($viewDuration) {
                    $sessionDuration += $viewDuration;
                }

                $stats['views']++;
            }

            // Update session duration
            $endTime = strtotime($sessionData['started_at']) + $sessionDuration;
            RecordFactory::session()->update([
                'ID' => $sessionId,
                'ended_at' => date('Y-m-d H:i:s', $endTime),
                'duration' => $sessionDuration,
                'total_views' => $viewCount,
            ]);

            $stats['visitors']++;
            $stats['sessions']++;
        }

        // Progress
        if ($dayNum % 10 === 0 || $dayNum === $totalDays) {
            $pct = round(($dayNum / $totalDays) * 100, 1);
            echo "Day {$dayNum}/{$totalDays} ({$pct}%) - {$visitorsToday} visitors - " . number_format($stats['views']) . " views\n";
        }

        $currentDate->modify('+1 day');
    }

    $stats['time'] = round(microtime(true) - $startTime, 2) . 's';
    return $stats;
}

/**
 * Generate session data with realistic distributions
 */
function generateSessionData($date, $dimensionIds, $countries, $devices, $referrers) {
    // Select country
    $countryCode = weightedRandom($countries['distribution']);
    $countryId = $dimensionIds['countries'][$countryCode];

    // Select city
    $cityId = null;
    if (isset($dimensionIds['cities'][$countryCode]) && count($dimensionIds['cities'][$countryCode]) > 0) {
        $cityId = $dimensionIds['cities'][$countryCode][array_rand($dimensionIds['cities'][$countryCode])];
    }

    // Select device type
    $deviceType = weightedRandom($devices['types']);
    $deviceTypeId = $dimensionIds['device_types'][$deviceType];

    // Select browser
    $browser = weightedRandom($devices['browsers']);
    $browserId = $dimensionIds['browsers'][$browser];

    // Select browser version
    $browserVersionId = null;
    if (isset($dimensionIds['browser_versions'][$browser]) && count($dimensionIds['browser_versions'][$browser]) > 0) {
        $browserVersionId = $dimensionIds['browser_versions'][$browser][array_rand($dimensionIds['browser_versions'][$browser])];
    }

    // Select OS based on device type
    $osOptions = $devices['os'][$deviceType];
    $osName = weightedRandom($osOptions);
    $osId = $dimensionIds['os'][$osName];

    // Select referrer channel
    $referrerChannel = weightedRandom($referrers['channels']);
    $referrerId = null;

    if ($referrerChannel !== 'direct' && isset($dimensionIds['referrers'][$referrerChannel])) {
        $referrerOptions = $dimensionIds['referrers'][$referrerChannel];
        $weights = array_column($referrerOptions, 'weight');
        $keys = array_keys($weights);
        $selected = weightedRandom(array_combine($keys, $weights));
        $referrerId = $referrerOptions[$selected]['id'];
    }

    // Generate IP address (simplified)
    $ip = rand(1, 255) . '.' . rand(1, 255) . '.' . rand(1, 255) . '.' . rand(1, 255);

    // Random time during the day (weighted toward business hours)
    $hour = getRandomHour();
    $minute = rand(0, 59);
    $second = rand(0, 59);
    $startedAt = $date . ' ' . sprintf('%02d:%02d:%02d', $hour, $minute, $second);

    return [
        'visitor_id' => null, // Will be set by caller
        'ip' => $ip,
        'country_id' => $countryId,
        'city_id' => $cityId,
        'device_type_id' => $deviceTypeId,
        'device_browser_id' => $browserId,
        'device_browser_version_id' => $browserVersionId,
        'device_os_id' => $osId,
        'referrer_id' => $referrerId,
        'started_at' => $startedAt,
    ];
}

/**
 * Get weighted random hour (business hours weighted higher)
 */
function getRandomHour() {
    $weights = [
        0=>1, 1=>1, 2=>1, 3=>1, 4=>1, 5=>1, 6=>2, 7=>3, 8=>4,
        9=>6, 10=>7, 11=>8, 12=>7, 13=>7, 14=>8, 15=>7, 16=>6, 17=>5,
        18=>4, 19=>3, 20=>3, 21=>2, 22=>2, 23=>1
    ];
    return weightedRandom($weights);
}

/**
 * Get random view count (weighted toward lower counts)
 */
function getRandomViewCount() {
    $weights = [
        1 => 40,  // 40% bounce
        2 => 25,
        3 => 15,
        4 => 10,
        5 => 5,
        6 => 3,
        7 => 1,
        8 => 0.5,
        9 => 0.3,
        10 => 0.2,
    ];
    return weightedRandom($weights);
}

/**
 * Get weekday multiplier (weekdays higher than weekends)
 */
function getWeekdayMultiplier($dayOfWeek) {
    $multipliers = [
        1 => 1.0,   // Monday
        2 => 1.1,   // Tuesday
        3 => 1.2,   // Wednesday (peak)
        4 => 1.1,   // Thursday
        5 => 0.9,   // Friday
        6 => 0.6,   // Saturday
        7 => 0.5,   // Sunday
    ];
    return $multipliers[$dayOfWeek];
}

/**
 * Get seasonal multiplier
 */
function getSeasonalMultiplier($month) {
    $multipliers = [
        1 => 0.9,    // January
        2 => 0.95,   // February
        3 => 1.0,    // March
        4 => 1.05,   // April
        5 => 1.1,    // May
        6 => 1.0,    // June
        7 => 0.85,   // July (summer dip)
        8 => 0.85,   // August
        9 => 1.15,   // September
        10 => 1.2,   // October (peak)
        11 => 1.15,  // November
        12 => 0.95,  // December
    ];
    return $multipliers[$month];
}

/**
 * Weighted random selection
 */
function weightedRandom($weights) {
    $total = array_sum($weights);
    $rand = mt_rand(1, $total * 100) / 100;

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
 * Clean existing data
 */
function cleanData() {
    global $wpdb;

    // Tables to clean (order matters due to foreign keys)
    $tables = [
        'views',
        'sessions',
        'visitors',
        'summary',
        'summary_totals',
        'parameters',
        'cities',                      // Clean before countries (FK constraint)
        'countries',
        'device_browser_versions',     // Clean before browsers (FK constraint)
        'device_browsers',
        'device_oss',
        'device_types',
        'referrers'
    ];

    foreach ($tables as $table) {
        $tableName = $wpdb->prefix . 'statistics_' . $table;
        $wpdb->query("TRUNCATE TABLE {$tableName}");
    }
}

/**
 * Show help message
 */
function showHelp() {
    echo "Usage: php bin/dummy-v15.php [options]\n\n";
    echo "Options:\n";
    echo "  --days=<number>           Days to generate (default: 365)\n";
    echo "  --from=<YYYY-MM-DD>       Start date\n";
    echo "  --to=<YYYY-MM-DD>         End date\n";
    echo "  --visitors-per-day=<num>  Average visitors per day (default: 200)\n";
    echo "  --clean                   Remove existing data first\n";
    echo "  --help                    Show this help\n\n";
    echo "Examples:\n";
    echo "  php bin/dummy-v15.php --days=30\n";
    echo "  php bin/dummy-v15.php --from=2024-01-01 --to=2024-12-31\n";
    echo "  php bin/dummy-v15.php --days=365 --clean\n";
    echo "  php bin/dummy-v15.php --days=90 --visitors-per-day=500\n";
}
