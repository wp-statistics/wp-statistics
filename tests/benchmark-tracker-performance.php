<?php
/**
 * Tracker Performance & Stress Test Suite
 *
 * Tests the full WP Statistics v15 tracking pipeline for:
 * - Single hit latency and throughput
 * - Batch/engagement endpoint performance
 * - Database query counts per hit
 * - Entity resolution under load (lookup-or-create contention)
 * - Session management performance
 * - Rate limiter correctness under burst
 * - Memory usage under sustained load
 * - Concurrent IP simulation
 *
 * Run: php tests/benchmark-tracker-performance.php
 * Run specific test: php tests/benchmark-tracker-performance.php --test=hit_throughput
 *
 * @package WP_Statistics
 * @since 15.0.0
 */

// Bootstrap WordPress
$wp_load_path = dirname(__DIR__, 4) . '/wp-load.php';
if (!file_exists($wp_load_path)) {
    echo "ERROR: Cannot find wp-load.php at {$wp_load_path}\n";
    echo "Run this script from the plugin's tests/ directory.\n";
    exit(1);
}
require_once $wp_load_path;

use WP_Statistics\Service\Tracking\Core\Tracker;
use WP_Statistics\Service\Tracking\Core\RateLimiter;
use WP_Statistics\Service\Tracking\Core\Payload;
use WP_Statistics\Service\Tracking\Core\Visitor;
use WP_Statistics\Service\Database\DatabaseSchema;
use WP_Statistics\Components\Option;

// ─── Config ──────────────────────────────────────────────────────────────────

$config = [
    'hit_samples'         => 50,      // Number of hits for latency test
    'throughput_duration'  => 10,      // Seconds to run throughput test
    'concurrent_ips'      => 20,      // Different IPs to simulate
    'burst_size'          => 100,     // Hits in burst test
    'memory_iterations'   => 200,     // Iterations for memory leak test
    'session_visitors'    => 50,      // Visitors for session test
    'entity_iterations'   => 100,     // Entity resolution iterations
];

// Parse CLI args
$selectedTest = null;
$verbose = false;
foreach ($argv ?? [] as $arg) {
    if (strpos($arg, '--test=') === 0) {
        $selectedTest = substr($arg, 7);
    }
    if ($arg === '-v' || $arg === '--verbose') {
        $verbose = true;
    }
}

// ─── Helpers ─────────────────────────────────────────────────────────────────

function banner(string $title): void
{
    $line = str_repeat('─', 60);
    echo "\n{$line}\n  {$title}\n{$line}\n\n";
}

function result(string $metric, $value, string $unit = '', string $status = ''): void
{
    $statusIcon = match ($status) {
        'pass'    => "\033[32m✓\033[0m",
        'fail'    => "\033[31m✗\033[0m",
        'warn'    => "\033[33m⚠\033[0m",
        'info'    => "\033[36mℹ\033[0m",
        default   => ' ',
    };
    printf("  %s %-40s %s %s\n", $statusIcon, $metric, $value, $unit);
}

function percentile(array $samples, float $p): float
{
    sort($samples);
    $index = (int) ceil(count($samples) * $p) - 1;
    return $samples[max(0, $index)];
}

function simulateHitRequest(string $ip = '203.0.113.100', int $resourceId = 1, string $resourceType = 'post'): void
{
    // Build params
    $params = [
        'resource_uri_id' => '0',
        'resource_type'   => $resourceType,
        'resource_id'     => (string) $resourceId,
        'user_id'         => '0',
        'referrer'        => base64_encode('https://google.com'),
        'resource_uri'    => base64_encode('/sample-page-' . $resourceId),
        'tracking_level'  => 'full',
        'timezone'        => 'UTC',
        'language_code'   => 'en-US',
        'language_name'   => 'English',
        'screen_width'    => '1920',
        'screen_height'   => '1080',
    ];

    // Generate valid signature
    $signatureData = json_encode([$resourceType, $resourceId, 0]);
    $params['signature'] = md5(AUTH_KEY . AUTH_SALT . $signatureData);

    // Populate $_POST, $_GET, and $_REQUEST (CLI doesn't auto-populate $_REQUEST)
    $_POST    = $params;
    $_GET     = [];
    $_REQUEST = $params;

    // Clear ALL proxy headers to prevent IP detection leaking between iterations.
    // Ip::isValid() uses FILTER_FLAG_NO_PRIV_RANGE, so we must use public IPs
    // (e.g. 203.0.113.x TEST-NET-3 range) to avoid fallback to 127.0.0.1.
    foreach (\WP_Statistics\Components\Ip::$ipMethodsServer as $header) {
        unset($_SERVER[$header]);
    }

    // Set server vars
    $_SERVER['REMOTE_ADDR']     = $ip;
    $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
    $_SERVER['HTTP_REFERER']    = 'https://google.com/search?q=test';
    $_SERVER['REQUEST_METHOD']  = 'POST';
}

/**
 * Generate a public IP from an index (avoids private ranges that Ip::isValid rejects).
 * Uses 203.0.113.0/24 (TEST-NET-3) and 198.51.100.0/24 (TEST-NET-2) ranges.
 */
function publicIp(int $index): string
{
    $octet4 = ($index % 254) + 1;
    $octet3 = intdiv($index, 254) % 254;
    // Rotate across two test-net ranges
    if ($octet3 % 2 === 0) {
        return "203.0.113.{$octet4}";
    }
    return "198.51.100.{$octet4}";
}

function countDbQueries(): array
{
    global $wpdb;
    $before = $wpdb->num_queries;
    return ['start' => $before, 'queries' => &$wpdb->queries];
}

function getQueryCount(array $counter): int
{
    global $wpdb;
    return $wpdb->num_queries - $counter['start'];
}

function clearTrackerState(): void
{
    // Reset any cached state between tests
    wp_cache_flush();
}

function getTableRowCount(string $table): int
{
    global $wpdb;
    $fullName = DatabaseSchema::table($table);
    return (int) $wpdb->get_var("SELECT COUNT(*) FROM {$fullName}");
}

function truncateTrackingTables(): void
{
    global $wpdb;
    $tables = ['visitors', 'sessions', 'views', 'exclusions'];
    foreach ($tables as $table) {
        $fullName = DatabaseSchema::table($table);
        if (DatabaseSchema::tableExists($fullName)) {
            $wpdb->query("TRUNCATE TABLE {$fullName}");
        }
    }
}

// ─── Tests ───────────────────────────────────────────────────────────────────

$tests = [];

// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
// TEST 1: Single Hit Latency
// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

$tests['hit_latency'] = function () use ($config, $verbose) {
    banner('Test 1: Single Hit Latency');

    // Disable rate limiter for this test
    Option::updateValue('tracker_rate_limit', false);

    $samples = [];
    $errors  = 0;

    for ($i = 0; $i < $config['hit_samples']; $i++) {
        simulateHitRequest(publicIp($i), $i + 1);

        $start = hrtime(true);
        try {
            (new Tracker())->record();
            $elapsed = (hrtime(true) - $start) / 1e6; // ms
            $samples[] = $elapsed;
        } catch (\Exception $e) {
            if ($e->getCode() !== 200) { // 200 = exclusion, not error
                $errors++;
                if ($verbose) {
                    echo "    Error on hit {$i}: {$e->getMessage()} (code {$e->getCode()})\n";
                }
            }
        }
    }

    if (empty($samples)) {
        result('No successful hits recorded', '', '', 'fail');
        return;
    }

    $p50  = percentile($samples, 0.50);
    $p95  = percentile($samples, 0.95);
    $p99  = percentile($samples, 0.99);
    $avg  = array_sum($samples) / count($samples);
    $min  = min($samples);
    $max  = max($samples);

    result('Samples', count($samples), "hits ({$errors} errors)", 'info');
    result('Average latency', round($avg, 2), 'ms', $avg < 100 ? 'pass' : 'warn');
    result('P50 latency', round($p50, 2), 'ms', $p50 < 50 ? 'pass' : 'warn');
    result('P95 latency', round($p95, 2), 'ms', $p95 < 200 ? 'pass' : 'warn');
    result('P99 latency', round($p99, 2), 'ms', $p99 < 500 ? 'pass' : 'warn');
    result('Min latency', round($min, 2), 'ms', 'info');
    result('Max latency', round($max, 2), 'ms', $max < 500 ? 'pass' : 'warn');
};

// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
// TEST 2: DB Queries Per Hit
// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

$tests['db_queries'] = function () use ($verbose) {
    banner('Test 2: Database Queries Per Hit');

    Option::updateValue('tracker_rate_limit', false);

    // Enable query logging
    if (!defined('SAVEQUERIES')) {
        define('SAVEQUERIES', true);
    }

    global $wpdb;
    $wpdb->queries = [];

    // Test 1: First hit (new visitor, new session, new everything)
    simulateHitRequest('203.0.113.201', 999);
    $counter = countDbQueries();
    try {
        (new Tracker())->record();
    } catch (\Exception $e) {
        // exclusions are OK
    }
    $firstHitQueries = getQueryCount($counter);

    // Test 2: Repeat visitor (existing session, cached dimensions)
    simulateHitRequest('203.0.113.201', 998);
    $counter = countDbQueries();
    try {
        (new Tracker())->record();
    } catch (\Exception $e) {
        // exclusions are OK
    }
    $repeatHitQueries = getQueryCount($counter);

    // Test 3: New visitor, same page (different IP)
    simulateHitRequest('203.0.113.202', 999);
    $counter = countDbQueries();
    try {
        (new Tracker())->record();
    } catch (\Exception $e) {
        // exclusions are OK
    }
    $newVisitorQueries = getQueryCount($counter);

    result('First hit (cold)', $firstHitQueries, 'queries', $firstHitQueries <= 25 ? 'pass' : 'warn');
    result('Repeat visit (warm session)', $repeatHitQueries, 'queries', $repeatHitQueries <= 15 ? 'pass' : 'warn');
    result('New visitor, same page', $newVisitorQueries, 'queries', $newVisitorQueries <= 20 ? 'pass' : 'warn');

    if ($verbose && defined('SAVEQUERIES') && SAVEQUERIES) {
        echo "\n  Last hit query log:\n";
        $recentQueries = array_slice($wpdb->queries, -$newVisitorQueries);
        foreach ($recentQueries as $i => $q) {
            printf("    %2d. [%.1fms] %s\n", $i + 1, $q[1] * 1000, substr($q[0], 0, 120));
        }
    }
};

// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
// TEST 3: Throughput Under Sustained Load
// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

$tests['hit_throughput'] = function () use ($config) {
    banner('Test 3: Throughput Under Sustained Load');

    Option::updateValue('tracker_rate_limit', false);

    $duration = $config['throughput_duration'];
    $hits     = 0;
    $errors   = 0;
    $start    = time();
    $latencies = [];

    while ((time() - $start) < $duration) {
        $ip = publicIp($hits);
        simulateHitRequest($ip, ($hits % 100) + 1);

        $t0 = hrtime(true);
        try {
            (new Tracker())->record();
            $latencies[] = (hrtime(true) - $t0) / 1e6;
        } catch (\Exception $e) {
            if ($e->getCode() !== 200) {
                $errors++;
            }
        }

        $hits++;
    }

    $actualDuration = time() - $start;
    $hitsPerSecond  = $hits / max($actualDuration, 1);
    $avgLatency     = !empty($latencies) ? array_sum($latencies) / count($latencies) : 0;

    result('Duration', $actualDuration, 'seconds', 'info');
    result('Total hits processed', $hits, '', 'info');
    result('Throughput', round($hitsPerSecond, 1), 'hits/sec', $hitsPerSecond > 10 ? 'pass' : 'warn');
    result('Errors', $errors, '', $errors === 0 ? 'pass' : 'warn');
    result('Avg latency under load', round($avgLatency, 2), 'ms', 'info');
    result('P95 latency under load', round(percentile($latencies, 0.95), 2), 'ms', 'info');
};

// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
// TEST 4: Rate Limiter Under Burst
// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

$tests['rate_limiter'] = function () use ($config) {
    banner('Test 4: Rate Limiter Under Burst');

    // Enable rate limiter
    Option::updateValue('tracker_rate_limit', true);
    Option::updateValue('tracker_rate_limit_threshold', 30);
    clearTrackerState();

    $singleIp    = '203.0.113.50';
    $accepted    = 0;
    $rejected    = 0;
    $burstSize   = $config['burst_size'];

    for ($i = 0; $i < $burstSize; $i++) {
        simulateHitRequest($singleIp, $i + 1);

        try {
            (new Tracker())->record();
            $accepted++;
        } catch (\Exception $e) {
            if ($e->getCode() === 429) {
                $rejected++;
            }
        }
    }

    $threshold = (int) Option::getValue('tracker_rate_limit_threshold', 30);

    result('Burst size', $burstSize, 'requests', 'info');
    result('Threshold', $threshold, 'per window', 'info');
    result('Accepted', $accepted, 'hits', 'info');
    result('Rejected (429)', $rejected, 'hits', $rejected > 0 ? 'pass' : 'warn');
    result('Rate limit enforced', $accepted <= $threshold ? 'YES' : 'NO', '', $accepted <= $threshold ? 'pass' : 'fail');

    // Verify different IPs are independent
    $otherIpAccepted = 0;
    for ($i = 0; $i < 5; $i++) {
        simulateHitRequest('198.51.100.50', $i + 200);
        try {
            (new Tracker())->record();
            $otherIpAccepted++;
        } catch (\Exception $e) {
            // ignore
        }
    }

    result('Other IP still accepted', $otherIpAccepted > 0 ? 'YES' : 'NO', '', $otherIpAccepted > 0 ? 'pass' : 'fail');

    // Disable for subsequent tests
    Option::updateValue('tracker_rate_limit', false);
};

// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
// TEST 5: Engagement/Batch Endpoint Performance
// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

$tests['batch_performance'] = function () use ($config) {
    banner('Test 5: Engagement/Batch Endpoint Performance');

    Option::updateValue('tracker_rate_limit', false);

    // First create a visitor/session via a hit
    $testIp = '203.0.113.100';
    simulateHitRequest($testIp, 1);
    try {
        (new Tracker())->record();
    } catch (\Exception $e) {
        // fine
    }

    // Now test engagement updates
    $_SERVER['REMOTE_ADDR'] = $testIp;
    $samples  = [];
    $errors   = 0;

    for ($i = 0; $i < 50; $i++) {
        $engagementMs = rand(1000, 30000);

        $start = hrtime(true);
        try {
            $tracker = new Tracker();
            $result  = $tracker->recordEngagement($engagementMs);
            $elapsed = (hrtime(true) - $start) / 1e6;

            if ($result) {
                $samples[] = $elapsed;
            }
        } catch (\Exception $e) {
            $errors++;
        }
    }

    if (!empty($samples)) {
        result('Engagement updates', count($samples), "successful ({$errors} errors)", 'info');
        result('Avg engagement update', round(array_sum($samples) / count($samples), 2), 'ms', 'info');
        result('P95 engagement update', round(percentile($samples, 0.95), 2), 'ms', 'info');
    } else {
        result('No engagement updates succeeded', '', '', 'warn');
    }

    // Test batch JSON processing
    $batchPayloads = [];
    for ($i = 0; $i < 20; $i++) {
        $batchPayloads[] = json_encode([
            'engagement_time' => rand(5000, 60000),
            'events' => [
                ['type' => 'custom_event', 'data' => ['action' => 'click', 'target' => 'button_' . $i], 'timestamp' => time() * 1000, 'url' => 'https://example.com/page-' . $i],
            ],
        ]);
    }

    $batchSamples = [];
    foreach ($batchPayloads as $payload) {
        $start = hrtime(true);
        try {
            $data = json_decode($payload, true);
            $engagementTime = $data['engagement_time'] ?? 0;

            if ($engagementTime > 0) {
                (new Tracker())->recordEngagement($engagementTime);
            }

            if (!empty($data['events'])) {
                do_action('wp_statistics_batch_events', $data['events']);
            }

            $batchSamples[] = (hrtime(true) - $start) / 1e6;
        } catch (\Exception $e) {
            // fine
        }
    }

    if (!empty($batchSamples)) {
        result('Batch payloads processed', count($batchSamples), '', 'info');
        result('Avg batch processing', round(array_sum($batchSamples) / count($batchSamples), 2), 'ms', 'info');
    }
};

// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
// TEST 6: Memory Usage Under Sustained Load
// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

$tests['memory_usage'] = function () use ($config) {
    banner('Test 6: Memory Usage Under Sustained Load');

    Option::updateValue('tracker_rate_limit', false);

    $iterations  = $config['memory_iterations'];
    $memStart    = memory_get_usage(true);
    $peakStart   = memory_get_peak_usage(true);
    $snapshots   = [];

    for ($i = 0; $i < $iterations; $i++) {
        simulateHitRequest(publicIp($i + 5000), ($i % 50) + 1);

        try {
            (new Tracker())->record();
        } catch (\Exception $e) {
            // fine
        }

        if ($i % 50 === 0) {
            $snapshots[] = [
                'iteration' => $i,
                'memory'    => memory_get_usage(true),
                'peak'      => memory_get_peak_usage(true),
            ];
        }
    }

    $memEnd  = memory_get_usage(true);
    $peakEnd = memory_get_peak_usage(true);
    $growth  = $memEnd - $memStart;
    $perHit  = $growth / $iterations;

    result('Iterations', $iterations, '', 'info');
    result('Memory at start', round($memStart / 1024 / 1024, 2), 'MB', 'info');
    result('Memory at end', round($memEnd / 1024 / 1024, 2), 'MB', 'info');
    result('Peak memory', round($peakEnd / 1024 / 1024, 2), 'MB', $peakEnd < 128 * 1024 * 1024 ? 'pass' : 'warn');
    result('Total growth', round($growth / 1024, 2), 'KB', 'info');
    result('Growth per hit', round($perHit / 1024, 4), 'KB/hit', $perHit < 10240 ? 'pass' : 'warn');

    // Check for memory leak (linear growth pattern)
    if (count($snapshots) >= 3) {
        $first = $snapshots[0]['memory'];
        $mid   = $snapshots[intdiv(count($snapshots), 2)]['memory'];
        $last  = end($snapshots)['memory'];

        $growthRate1 = $mid - $first;
        $growthRate2 = $last - $mid;

        $isLeaking = $growthRate2 > $growthRate1 * 1.5;
        result('Memory leak detected', $isLeaking ? 'POSSIBLE' : 'NO', '', $isLeaking ? 'warn' : 'pass');
    }
};

// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
// TEST 7: Concurrent IP Simulation
// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

$tests['concurrent_ips'] = function () use ($config) {
    banner('Test 7: Concurrent IP Simulation');

    Option::updateValue('tracker_rate_limit', false);

    $numIps       = $config['concurrent_ips'];
    $hitsPerIp    = 10;
    $totalHits    = $numIps * $hitsPerIp;
    $successful   = 0;
    $errors       = 0;
    $latencies    = [];

    $userAgents = [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120.0.0.0',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 14_0) AppleWebKit/605.1.15 Safari/17.0',
        'Mozilla/5.0 (X11; Linux x86_64; rv:120.0) Gecko/20100101 Firefox/120.0',
        'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15',
        'Mozilla/5.0 (Linux; Android 14) AppleWebKit/537.36 Chrome/120.0.0.0 Mobile',
    ];

    // Interleave hits from different IPs (simulates concurrent users)
    for ($hit = 0; $hit < $hitsPerIp; $hit++) {
        for ($ip = 0; $ip < $numIps; $ip++) {
            $ipAddr = publicIp($ip + $hit * 100);
            simulateHitRequest($ipAddr, ($hit % 20) + 1);
            $_SERVER['HTTP_USER_AGENT'] = $userAgents[$ip % count($userAgents)];

            $start = hrtime(true);
            try {
                (new Tracker())->record();
                $successful++;
                $latencies[] = (hrtime(true) - $start) / 1e6;
            } catch (\Exception $e) {
                if ($e->getCode() !== 200) {
                    $errors++;
                }
            }
        }
    }

    $visitors = getTableRowCount('visitors');
    $sessions = getTableRowCount('sessions');
    $views    = getTableRowCount('views');

    result('Simulated IPs', $numIps, '', 'info');
    result('Total hits attempted', $totalHits, '', 'info');
    result('Successful hits', $successful, '', $successful > $totalHits * 0.9 ? 'pass' : 'warn');
    result('Errors', $errors, '', $errors === 0 ? 'pass' : 'warn');
    result('Visitors in DB', $visitors, '', 'info');
    result('Sessions in DB', $sessions, '', 'info');
    result('Views in DB', $views, '', 'info');

    if (!empty($latencies)) {
        result('Avg latency', round(array_sum($latencies) / count($latencies), 2), 'ms', 'info');
        result('P95 latency', round(percentile($latencies, 0.95), 2), 'ms', 'info');

        // Check for latency degradation over time
        $firstQuarter = array_slice($latencies, 0, intdiv(count($latencies), 4));
        $lastQuarter  = array_slice($latencies, -intdiv(count($latencies), 4));

        $avgFirst = array_sum($firstQuarter) / count($firstQuarter);
        $avgLast  = array_sum($lastQuarter) / count($lastQuarter);
        $degradation = ($avgLast - $avgFirst) / max($avgFirst, 0.01) * 100;

        result('Latency degradation', round($degradation, 1) . '%',
            $degradation > 50 ? '(significant slowdown!)' : '',
            $degradation < 50 ? 'pass' : 'warn'
        );
    }
};

// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
// TEST 8: Session Resolution Performance
// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

$tests['session_resolution'] = function () use ($config) {
    banner('Test 8: Session Resolution Performance');

    Option::updateValue('tracker_rate_limit', false);

    $numVisitors = $config['session_visitors'];

    // Phase 1: Create initial sessions
    echo "  Phase 1: Creating {$numVisitors} initial sessions...\n";
    $createLatencies = [];

    for ($i = 0; $i < $numVisitors; $i++) {
        $ip = publicIp($i + 3000);
        simulateHitRequest($ip, 1);

        $start = hrtime(true);
        try {
            (new Tracker())->record();
            $createLatencies[] = (hrtime(true) - $start) / 1e6;
        } catch (\Exception $e) {
            // fine
        }
    }

    // Phase 2: Return visits (should reuse sessions)
    echo "  Phase 2: Return visits (session reuse)...\n";
    $reuseLatencies = [];

    for ($i = 0; $i < $numVisitors; $i++) {
        $ip = publicIp($i + 3000);
        simulateHitRequest($ip, 2); // Different page, same visitor

        $start = hrtime(true);
        try {
            (new Tracker())->record();
            $reuseLatencies[] = (hrtime(true) - $start) / 1e6;
        } catch (\Exception $e) {
            // fine
        }
    }

    $sessionsBefore = getTableRowCount('sessions');

    // Phase 3: Third page view (should still reuse sessions)
    $thirdViewLatencies = [];
    for ($i = 0; $i < $numVisitors; $i++) {
        $ip = publicIp($i + 3000);
        simulateHitRequest($ip, 3);

        $start = hrtime(true);
        try {
            (new Tracker())->record();
            $thirdViewLatencies[] = (hrtime(true) - $start) / 1e6;
        } catch (\Exception $e) {
            // fine
        }
    }

    $sessionsAfter = getTableRowCount('sessions');

    result('Session creation avg', round(array_sum($createLatencies) / max(count($createLatencies), 1), 2), 'ms', 'info');
    result('Session reuse avg', round(array_sum($reuseLatencies) / max(count($reuseLatencies), 1), 2), 'ms', 'info');
    result('Third view avg', round(array_sum($thirdViewLatencies) / max(count($thirdViewLatencies), 1), 2), 'ms', 'info');

    $createdNewSessions = $sessionsAfter - $sessionsBefore;
    result('Sessions (phase 2→3)', "{$sessionsBefore} → {$sessionsAfter}", "(+{$createdNewSessions})", 'info');
    result('Session reuse working', $createdNewSessions === 0 ? 'YES' : 'NO (+' . $createdNewSessions . ')',
        '', $createdNewSessions === 0 ? 'pass' : 'warn'
    );

    // Compare latencies
    $createAvg = array_sum($createLatencies) / max(count($createLatencies), 1);
    $reuseAvg  = array_sum($reuseLatencies) / max(count($reuseLatencies), 1);

    if ($createAvg > 0) {
        $reuseSpeedup = (($createAvg - $reuseAvg) / $createAvg) * 100;
        result('Session reuse speedup', round($reuseSpeedup, 1) . '%', 'faster than cold start', $reuseSpeedup > 0 ? 'pass' : 'info');
    }
};

// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
// TEST 9: Data Integrity Under Load
// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

$tests['data_integrity'] = function () {
    banner('Test 9: Data Integrity Under Load');

    Option::updateValue('tracker_rate_limit', false);

    // Track initial state
    $viewsBefore    = getTableRowCount('views');
    $sessionsBefore = getTableRowCount('sessions');
    $visitorsBefore = getTableRowCount('visitors');

    // Send exactly 25 hits from 5 unique IPs (5 hits each)
    $expectedHits = 25;
    $uniqueIps    = 5;
    $hitsPerIp    = $expectedHits / $uniqueIps;
    $successCount = 0;

    for ($ip = 0; $ip < $uniqueIps; $ip++) {
        for ($hit = 0; $hit < $hitsPerIp; $hit++) {
            simulateHitRequest(publicIp($ip + 4000), ($hit + 1) * 10 + $ip);
            try {
                (new Tracker())->record();
                $successCount++;
            } catch (\Exception $e) {
                // fine
            }
        }
    }

    $viewsAfter    = getTableRowCount('views');
    $sessionsAfter = getTableRowCount('sessions');
    $visitorsAfter = getTableRowCount('visitors');

    $newViews    = $viewsAfter - $viewsBefore;
    $newSessions = $sessionsAfter - $sessionsBefore;
    $newVisitors = $visitorsAfter - $visitorsBefore;

    result('Hits sent', $successCount, "/ {$expectedHits}", 'info');
    result('Views created', $newViews, "(expected: {$successCount})", $newViews === $successCount ? 'pass' : 'fail');
    result('Sessions created', $newSessions, "(expected: {$uniqueIps})", $newSessions === $uniqueIps ? 'pass' : 'warn');
    result('Visitors created', $newVisitors, "(expected: {$uniqueIps})", $newVisitors === $uniqueIps ? 'pass' : 'warn');

    // Verify view→session relationships
    global $wpdb;
    $viewsTable    = DatabaseSchema::table('views');
    $sessionsTable = DatabaseSchema::table('sessions');

    $orphanedViews = (int) $wpdb->get_var("
        SELECT COUNT(*) FROM {$viewsTable} v
        LEFT JOIN {$sessionsTable} s ON v.session_id = s.ID
        WHERE s.ID IS NULL AND v.ID > {$viewsBefore}
    ");

    result('Orphaned views', $orphanedViews, '', $orphanedViews === 0 ? 'pass' : 'fail');

    // Check session view counts match actual views
    $sessionViewMismatch = (int) $wpdb->get_var("
        SELECT COUNT(*) FROM {$sessionsTable} s
        WHERE s.ID > {$sessionsBefore}
        AND s.total_views != (SELECT COUNT(*) FROM {$viewsTable} v WHERE v.session_id = s.ID)
    ");

    result('Session view count accuracy', $sessionViewMismatch === 0 ? 'MATCH' : "{$sessionViewMismatch} mismatches",
        '', $sessionViewMismatch === 0 ? 'pass' : 'fail'
    );
};

// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
// TEST 10: Payload Validation & Edge Cases
// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

$tests['payload_edge_cases'] = function () {
    banner('Test 10: Payload Validation & Edge Cases');

    Option::updateValue('tracker_rate_limit', false);

    $cases = [
        'Valid hit' => function () {
            simulateHitRequest('203.0.113.91', 1);
            (new Tracker())->record();
            return true;
        },
        'Empty resource_uri' => function () {
            simulateHitRequest('203.0.113.92', 1);
            $_POST['resource_uri'] = '';
            (new Tracker())->record();
            return true;
        },
        'Invalid signature' => function () {
            simulateHitRequest('203.0.113.93', 1);
            $_POST['signature'] = 'invalid_signature_value';
            (new Tracker())->record();
            return true; // Should throw 403
        },
        'XSS in referrer' => function () {
            simulateHitRequest('203.0.113.94', 1);
            $_POST['referrer'] = base64_encode('<script>alert("xss")</script>');
            (new Tracker())->record();
            return true;
        },
        'SQL injection in resource_uri' => function () {
            simulateHitRequest('203.0.113.95', 1);
            $_POST['resource_uri'] = base64_encode("'; DROP TABLE wp_posts; --");
            (new Tracker())->record();
            return true;
        },
        'Very long referrer (2KB)' => function () {
            simulateHitRequest('203.0.113.96', 1);
            $_POST['referrer'] = base64_encode('https://example.com/' . str_repeat('a', 2048));
            (new Tracker())->record();
            return true;
        },
        'Unicode resource_uri' => function () {
            simulateHitRequest('203.0.113.97', 1);
            $_POST['resource_uri'] = base64_encode('/日本語/ページ');
            (new Tracker())->record();
            return true;
        },
        'Zero resource_id' => function () {
            simulateHitRequest('203.0.113.98', 0);
            (new Tracker())->record();
            return true;
        },
    ];

    foreach ($cases as $name => $test) {
        try {
            $test();
            result($name, 'ACCEPTED', '', 'info');
        } catch (\Exception $e) {
            $code = $e->getCode();
            $status = match (true) {
                $name === 'Invalid signature' && $code === 403 => 'pass',
                $name === 'XSS in referrer' && $code >= 400    => 'pass',
                $name === 'SQL injection in resource_uri' && $code >= 400 => 'pass',
                $code === 200                                  => 'info', // exclusion
                default                                        => 'warn',
            };
            result($name, "REJECTED ({$code})", substr($e->getMessage(), 0, 50), $status);
        }
    }
};

// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
// TEST 11: Exclusion System Performance & Correctness
// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

$tests['exclusions'] = function () use ($verbose) {
    banner('Test 11: Per-Exclusion Rule Testing');

    Option::updateValue('tracker_rate_limit', false);

    // Reset all static caches in the Exclusions singleton
    $resetExclusions = function () {
        $ref = new \ReflectionClass(\WP_Statistics\Service\Tracking\Core\Exclusions::class);
        foreach (['exclusionResult', 'options', 'exclusionMap', 'excludedUrlPatterns', 'excludedCountries', 'includedCountries'] as $name) {
            $prop = $ref->getProperty($name);
            $prop->setValue(null, null);
        }
    };

    // Helper: run a single hit and return [excluded:bool, reason:string]
    $tryHit = function () use ($resetExclusions) {
        $resetExclusions();
        try {
            (new Tracker())->record();
            return [false, ''];
        } catch (\Exception $e) {
            return [$e->getCode() === 200, $e->getMessage()];
        }
    };

    // Save original options we'll modify
    $originals = [];
    foreach (['exclude_feeds', 'exclude_404s', 'exclude_ip', 'excluded_urls', 'excluded_countries', 'included_countries', 'robot_threshold', 'exclude_administrator', 'exclude_anonymous_users'] as $key) {
        $originals[$key] = Option::getValue($key, '');
    }

    // ── 1. Robot (bot user-agent) ────────────────────────────────────

    echo "  1. robot — Bot User-Agent Detection\n";

    $bots = [
        'Googlebot'    => 'Googlebot/2.1 (+http://www.google.com/bot.html)',
        'Bingbot'      => 'Mozilla/5.0 (compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm)',
        'YandexBot'    => 'Mozilla/5.0 (compatible; YandexBot/3.0; +http://yandex.com/bots)',
        'AhrefsBot'    => 'Mozilla/5.0 (compatible; AhrefsBot/7.0; +http://ahrefs.com/robot/)',
        'Empty UA'     => '',
        'curl'         => 'curl/7.88.1',
    ];

    foreach ($bots as $name => $ua) {
        simulateHitRequest('203.0.113.120', 1);
        $_SERVER['HTTP_USER_AGENT'] = $ua;
        [$excluded, $reason] = $tryHit();
        result("  {$name}", $excluded ? "EXCLUDED ({$reason})" : 'ACCEPTED', '', $excluded ? 'pass' : 'warn');
    }

    // Normal browser should NOT be excluded
    simulateHitRequest('203.0.113.121', 1);
    $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
    [$excluded,] = $tryHit();
    result('  Chrome browser', !$excluded ? 'ACCEPTED (correct)' : 'EXCLUDED (wrong!)', '', !$excluded ? 'pass' : 'fail');

    // ── 2. Broken File (404 + file extension) ────────────────────────

    echo "\n  2. broken_file — 404 + Static File Extension\n";

    $brokenFiles = [
        '/images/logo.png'        => true,
        '/assets/style.css'       => true,
        '/scripts/app.js'         => true,
        '/missing-page'           => false,  // No extension → not broken file
        '/page.php'               => false,  // .php excluded from check
    ];

    foreach ($brokenFiles as $uri => $shouldExclude) {
        simulateHitRequest('203.0.113.122', 1, '404');
        $_REQUEST['resource_uri'] = $_POST['resource_uri'] = base64_encode($uri);
        [$excluded, $reason] = $tryHit();
        $label = $excluded ? 'EXCLUDED' : 'ACCEPTED';
        $correct = $excluded === $shouldExclude;
        result("  404 {$uri}", $label, $correct ? '' : '(UNEXPECTED)', $correct ? 'pass' : 'fail');
    }

    // ── 3. IP Match ──────────────────────────────────────────────────

    echo "\n  3. ip_match — IP Exclusion List\n";

    Option::updateValue('exclude_ip', "203.0.113.200\n203.0.113.201\n198.51.100.0/24");

    $ipTests = [
        '203.0.113.200' => true,   // Exact match
        '203.0.113.201' => true,   // Exact match
        '198.51.100.50' => true,   // CIDR match
        '198.51.100.1'  => true,   // CIDR match
        '203.0.113.202' => false,  // Not in list
    ];

    foreach ($ipTests as $ip => $shouldExclude) {
        simulateHitRequest($ip, 1);
        [$excluded, $reason] = $tryHit();
        $label = $excluded ? 'EXCLUDED' : 'ACCEPTED';
        $correct = $excluded === $shouldExclude;
        result("  IP {$ip}", $label, $correct ? '' : '(UNEXPECTED)', $correct ? 'pass' : 'fail');
    }

    Option::updateValue('exclude_ip', $originals['exclude_ip']);

    // ── 4. Feed ──────────────────────────────────────────────────────

    echo "\n  4. feed — Feed Request Exclusion\n";

    // Disabled
    Option::updateValue('exclude_feeds', false);
    simulateHitRequest('203.0.113.123', 1, 'feed');
    [$excluded,] = $tryHit();
    result('  Feed (setting OFF)', !$excluded ? 'ACCEPTED (correct)' : 'EXCLUDED', '', !$excluded ? 'pass' : 'fail');

    // Enabled
    Option::updateValue('exclude_feeds', true);
    simulateHitRequest('203.0.113.124', 1, 'feed');
    [$excluded,] = $tryHit();
    result('  Feed (setting ON)', $excluded ? 'EXCLUDED (correct)' : 'ACCEPTED', '', $excluded ? 'pass' : 'fail');

    // Non-feed should not be affected
    simulateHitRequest('203.0.113.125', 1, 'post');
    [$excluded,] = $tryHit();
    result('  Post (setting ON)', !$excluded ? 'ACCEPTED (correct)' : 'EXCLUDED', '', !$excluded ? 'pass' : 'fail');

    Option::updateValue('exclude_feeds', $originals['exclude_feeds']);

    // ── 5. 404 ───────────────────────────────────────────────────────

    echo "\n  5. 404 — 404 Page Exclusion\n";

    Option::updateValue('exclude_404s', false);
    simulateHitRequest('203.0.113.126', 1, '404');
    $_REQUEST['resource_uri'] = $_POST['resource_uri'] = base64_encode('/not-found-page');
    [$excluded,] = $tryHit();
    result('  404 page (setting OFF)', !$excluded ? 'ACCEPTED (correct)' : 'EXCLUDED', '', !$excluded ? 'pass' : 'fail');

    Option::updateValue('exclude_404s', true);
    simulateHitRequest('203.0.113.127', 1, '404');
    $_REQUEST['resource_uri'] = $_POST['resource_uri'] = base64_encode('/not-found-page');
    [$excluded,] = $tryHit();
    result('  404 page (setting ON)', $excluded ? 'EXCLUDED (correct)' : 'ACCEPTED', '', $excluded ? 'pass' : 'fail');

    Option::updateValue('exclude_404s', $originals['exclude_404s']);

    // ── 6. Excluded URL ──────────────────────────────────────────────

    echo "\n  6. excluded_url — URL Pattern Exclusion\n";

    Option::updateValue('excluded_urls', "wp-admin*\nprivate/secret\napi/internal/*");

    $urlTests = [
        '/wp-admin/options.php'  => true,
        '/wp-admin'              => true,
        '/private/secret'        => true,
        '/api/internal/data'     => true,
        '/public/page'           => false,
        '/blog/post-1'           => false,
    ];

    foreach ($urlTests as $uri => $shouldExclude) {
        simulateHitRequest('203.0.113.128', 1);
        $_REQUEST['resource_uri'] = $_POST['resource_uri'] = base64_encode($uri);
        [$excluded, $reason] = $tryHit();
        $label = $excluded ? 'EXCLUDED' : 'ACCEPTED';
        $correct = ($excluded && $reason === 'excluded_url') === $shouldExclude;
        // If excluded for a different reason (robot etc), note it
        $note = ($excluded && $reason !== 'excluded_url' && $shouldExclude) ? "(reason: {$reason})" : '';
        result("  {$uri}", $label, $note ?: ($correct ? '' : '(UNEXPECTED)'), $correct ? 'pass' : 'warn');
    }

    Option::updateValue('excluded_urls', $originals['excluded_urls']);

    // ── 7. User Role ─────────────────────────────────────────────────

    echo "\n  7. user_role — Role-Based Exclusion\n";

    // Exclude administrators
    Option::updateValue('exclude_administrator', true);

    // Hit with admin user_id (user 1 is typically admin)
    simulateHitRequest('203.0.113.129', 1);
    $_REQUEST['user_id'] = $_POST['user_id'] = '1';
    // Regenerate signature with user_id=1
    $_REQUEST['signature'] = $_POST['signature'] = md5(AUTH_KEY . AUTH_SALT . json_encode(['post', 1, 1]));
    [$excluded, $reason] = $tryHit();
    result('  Admin user (exclude ON)', $excluded ? "EXCLUDED ({$reason})" : 'ACCEPTED', '', ($excluded && $reason === 'user_role') ? 'pass' : 'warn');

    // Non-admin user (user_id=0, anonymous)
    Option::updateValue('exclude_anonymous_users', false);
    simulateHitRequest('203.0.113.130', 1);
    [$excluded,] = $tryHit();
    result('  Anonymous user (exclude OFF)', !$excluded ? 'ACCEPTED (correct)' : 'EXCLUDED', '', !$excluded ? 'pass' : 'fail');

    // Exclude anonymous users
    Option::updateValue('exclude_anonymous_users', true);
    simulateHitRequest('203.0.113.131', 1);
    [$excluded, $reason] = $tryHit();
    result('  Anonymous user (exclude ON)', $excluded ? "EXCLUDED ({$reason})" : 'ACCEPTED', '', ($excluded && $reason === 'user_role') ? 'pass' : 'warn');

    Option::updateValue('exclude_administrator', $originals['exclude_administrator']);
    Option::updateValue('exclude_anonymous_users', $originals['exclude_anonymous_users']);

    // ── 8. GeoIP ─────────────────────────────────────────────────────

    echo "\n  8. geoip — Country Exclusion/Inclusion\n";

    // Use real public IPs with known countries from DB-IP:
    //   8.8.8.8      → US (Google DNS)
    //   1.1.1.1      → AU (Cloudflare)
    //   77.88.55.60  → RU (Yandex)
    //   114.114.114.114 → CN (China)
    //   81.2.69.144  → GB (UK)

    // First verify GeoIP is available
    $geoTestIps = [
        '8.8.8.8'         => 'US',
        '77.88.55.60'     => 'RU',
        '114.114.114.114' => 'CN',
        '81.2.69.144'     => 'GB',
    ];

    // Probe one IP to check if GeoIP DB is loaded
    simulateHitRequest('8.8.8.8', 1);
    $resetExclusions();
    $probePayload = Payload::parse();
    $probeVisitor = new Visitor($probePayload);
    $probeCountry = $probeVisitor->getCountry();

    if (empty($probeCountry)) {
        result('  GeoIP database', 'NOT AVAILABLE', '(skipping geo tests)', 'warn');
    } else {
        result('  GeoIP database', 'LOADED', "(8.8.8.8 → {$probeCountry})", 'pass');

        // --- Blacklist mode: exclude CN and RU ---
        Option::updateValue('excluded_countries', "CN\nRU");
        Option::updateValue('included_countries', '');

        foreach ($geoTestIps as $ip => $expectedCountry) {
            $shouldExclude = in_array($expectedCountry, ['CN', 'RU']);

            simulateHitRequest($ip, 1);
            [$excluded, $reason] = $tryHit();

            // Detect the actual country for this IP
            $resetExclusions();
            simulateHitRequest($ip, 1);
            $p = Payload::parse();
            $v = new Visitor($p);
            $actualCountry = $v->getCountry() ?: '??';

            $label = $excluded ? 'EXCLUDED' : 'ACCEPTED';
            $isGeoExclusion = $excluded && $reason === 'geoip';
            $correct = $shouldExclude ? $isGeoExclusion : !$excluded;

            result("  {$ip} ({$actualCountry}), blacklist CN+RU", $label,
                $correct ? '' : "(reason: {$reason}, UNEXPECTED)",
                $correct ? 'pass' : 'fail');
        }

        // --- Whitelist mode: only allow US ---
        Option::updateValue('excluded_countries', '');
        Option::updateValue('included_countries', 'US');

        foreach ($geoTestIps as $ip => $expectedCountry) {
            $shouldExclude = $expectedCountry !== 'US';

            simulateHitRequest($ip, 1);
            [$excluded, $reason] = $tryHit();

            $label = $excluded ? 'EXCLUDED' : 'ACCEPTED';
            $isGeoExclusion = $excluded && $reason === 'geoip';
            $correct = $shouldExclude ? $isGeoExclusion : !$excluded;

            result("  {$ip} ({$expectedCountry}), whitelist US only", $label,
                $correct ? '' : "(reason: {$reason}, UNEXPECTED)",
                $correct ? 'pass' : 'fail');
        }

        // --- No geo filtering: all should pass ---
        Option::updateValue('excluded_countries', '');
        Option::updateValue('included_countries', '');

        simulateHitRequest('77.88.55.60', 1);
        [$excluded,] = $tryHit();
        result('  RU IP, no geo filter', !$excluded ? 'ACCEPTED (correct)' : 'EXCLUDED', '', !$excluded ? 'pass' : 'fail');
    }

    Option::updateValue('excluded_countries', $originals['excluded_countries']);
    Option::updateValue('included_countries', $originals['included_countries']);

    // ── 9. Robot Threshold ───────────────────────────────────────────

    echo "\n  9. robot_threshold — Hit-Per-Day Threshold\n";

    Option::updateValue('robot_threshold', 5);

    $thresholdIp = '203.0.113.140';
    $accepted = 0;
    $excluded = 0;

    for ($i = 0; $i < 10; $i++) {
        simulateHitRequest($thresholdIp, $i + 1);
        [$wasExcluded, $reason] = $tryHit();

        if ($wasExcluded && $reason === 'robot_threshold') {
            $excluded++;
        } else {
            $accepted++;
        }
    }

    result('  Threshold=5, sent 10 hits', "accepted={$accepted} excluded={$excluded}", '',
        ($accepted <= 5 && $excluded >= 5) ? 'pass' : 'warn');

    // Disable threshold
    Option::updateValue('robot_threshold', 0);
    simulateHitRequest('203.0.113.141', 1);
    [$wasExcluded,] = $tryHit();
    result('  Threshold=0 (disabled)', !$wasExcluded ? 'ACCEPTED (correct)' : 'EXCLUDED', '', !$wasExcluded ? 'pass' : 'fail');

    Option::updateValue('robot_threshold', $originals['robot_threshold']);

    // ── Static Cache Bug ─────────────────────────────────────────────

    echo "\n  --- Static Cache Behavior ---\n";

    $resetExclusions();
    simulateHitRequest('203.0.113.150', 1);
    $_SERVER['HTTP_USER_AGENT'] = 'Googlebot/2.1';

    $firstBot = false;
    try { (new Tracker())->record(); } catch (\Exception $e) {
        if ($e->getCode() === 200) $firstBot = true;
    }

    // WITHOUT reset: different UA, same PHP process
    simulateHitRequest('203.0.113.151', 2);
    $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/120.0.0.0';

    $secondCached = false;
    try { (new Tracker())->record(); } catch (\Exception $e) {
        if ($e->getCode() === 200) $secondCached = true;
    }

    result('1st hit (bot): excluded', $firstBot ? 'YES' : 'NO', '', $firstBot ? 'pass' : 'fail');
    result('2nd hit (browser, no reset): excluded', $secondCached ? 'YES (STALE CACHE!)' : 'NO (correct)', '',
        $secondCached ? 'warn' : 'pass'
    );

    if ($secondCached) {
        result('BUG', 'Exclusions::$exclusionResult caches per-process',
            'A bot preceding a real browser in the same PHP process causes the browser to be silently excluded', 'warn');
    }
};

// ─── Runner ──────────────────────────────────────────────────────────────────

echo "\n";
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║   WP Statistics v15 — Tracker Performance & Stress Tests  ║\n";
echo "╠════════════════════════════════════════════════════════════╣\n";
echo "║  PHP " . PHP_VERSION . " | MySQL " . ($wpdb->db_version() ?? '?') . str_repeat(' ', max(0, 27 - strlen(PHP_VERSION) - strlen($wpdb->db_version() ?? '?'))) . "  ║\n";
echo "║  Memory limit: " . ini_get('memory_limit') . str_repeat(' ', max(0, 40 - strlen(ini_get('memory_limit')))) . "  ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n";

$totalStart = hrtime(true);

if ($selectedTest) {
    if (isset($tests[$selectedTest])) {
        $tests[$selectedTest]();
    } else {
        echo "\nUnknown test: {$selectedTest}\n";
        echo "Available tests: " . implode(', ', array_keys($tests)) . "\n";
        exit(1);
    }
} else {
    foreach ($tests as $name => $test) {
        $test();
    }
}

$totalElapsed = (hrtime(true) - $totalStart) / 1e9;

echo "\n";
echo str_repeat('═', 60) . "\n";
printf("  Total time: %.2f seconds\n", $totalElapsed);
echo str_repeat('═', 60) . "\n\n";
