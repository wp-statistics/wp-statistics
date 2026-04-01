<?php
/**
 * Tracker Modes & Rate Limiter Comparison Benchmark
 *
 * Tests tracking performance across:
 * - All 3 transport modes: AJAX, REST API, Hybrid (mu-plugin)
 * - Rate limiter enabled vs disabled
 * - Hit endpoint vs Batch endpoint per mode
 *
 * This test uses actual HTTP requests via curl to measure real-world
 * endpoint latency including PHP bootstrap overhead.
 *
 * Run: php tests/benchmark-tracker-modes.php
 *
 * @package WP_Statistics
 * @since 15.0.0
 */

$wp_load_path = dirname(__DIR__, 4) . '/wp-load.php';
if (!file_exists($wp_load_path)) {
    echo "ERROR: Cannot find wp-load.php at {$wp_load_path}\n";
    exit(1);
}
require_once $wp_load_path;

use WP_Statistics\Components\Option;
use WP_Statistics\Service\Database\DatabaseSchema;
use WP_Statistics\Service\Tracking\Core\Tracker;

// ─── Config ──────────────────────────────────────────────────────────────────

$siteUrl        = home_url();
$ajaxUrl        = admin_url('admin-ajax.php');
$restUrl        = rest_url('wp-statistics/v2');
$muPluginUrl    = content_url('mu-plugins/wp-statistics-tracker.php');
$samplesPerTest = 30;
$burstSize      = 50;

// Check for mu-plugin
$muPluginPath = WPMU_PLUGIN_DIR . '/wp-statistics-tracker.php';
$hybridAvailable = file_exists($muPluginPath);

echo "\n";
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║  Tracker Modes & Rate Limiter Comparison Benchmark        ║\n";
echo "╠════════════════════════════════════════════════════════════╣\n";
echo "║  Site: {$siteUrl}\n";
echo "║  PHP " . PHP_VERSION . " | MySQL " . ($GLOBALS['wpdb']->db_version() ?? '?') . "\n";
echo "║  Samples per test: {$samplesPerTest}\n";
echo "║  Hybrid mu-plugin: " . ($hybridAvailable ? 'INSTALLED' : 'NOT INSTALLED') . "\n";
echo "╚════════════════════════════════════════════════════════════╝\n";

// ─── Helpers ─────────────────────────────────────────────────────────────────

function banner(string $title): void
{
    $line = str_repeat('─', 60);
    echo "\n{$line}\n  {$title}\n{$line}\n\n";
}

function result(string $metric, $value, string $unit = '', string $status = ''): void
{
    $icon = match ($status) {
        'pass' => "\033[32m✓\033[0m",
        'fail' => "\033[31m✗\033[0m",
        'warn' => "\033[33m⚠\033[0m",
        'info' => "\033[36mℹ\033[0m",
        default => ' ',
    };
    printf("  %s %-40s %s %s\n", $icon, $metric, $value, $unit);
}

function percentile(array $samples, float $p): float
{
    sort($samples);
    $idx = max(0, (int) ceil(count($samples) * $p) - 1);
    return $samples[$idx];
}

function generateSignature(int $resourceId, string $resourceType = 'post', int $userId = 0): string
{
    return md5(AUTH_KEY . AUTH_SALT . json_encode([$resourceType, $resourceId, $userId]));
}

/**
 * Send an HTTP hit request via curl and return [httpCode, latencyMs, body].
 */
function httpHit(string $url, int $resourceId, string $ip, array $extraFields = []): array
{
    $referrer    = base64_encode('https://google.com');
    $resourceUri = base64_encode('/bench-page-' . $resourceId);
    $signature   = generateSignature($resourceId);

    $fields = array_merge([
        'resource_uri_id' => '0',
        'resource_type'   => 'post',
        'resource_id'     => (string) $resourceId,
        'user_id'         => '0',
        'referrer'        => $referrer,
        'resource_uri'    => $resourceUri,
        'tracking_level'  => 'full',
        'timezone'        => 'UTC',
        'language_code'   => 'en-US',
        'language_name'   => 'English',
        'screen_width'    => '1920',
        'screen_height'   => '1080',
        'signature'       => $signature,
    ], $extraFields);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query($fields),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_HTTPHEADER     => [
            "X-Forwarded-For: {$ip}",
            'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/120.0.0.0',
        ],
    ]);

    $start    = hrtime(true);
    $body     = curl_exec($ch);
    $latency  = (hrtime(true) - $start) / 1e6;
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    // curl_close is a no-op since PHP 8.0, deprecated in 8.5

    return [$httpCode, $latency, $body];
}

/**
 * Send an HTTP batch request via curl.
 */
function httpBatch(string $url, string $ip, int $engagementMs = 5000, array $extraFields = []): array
{
    $batchData = json_encode([
        'engagement_time' => $engagementMs,
        'events' => [],
    ]);

    $fields = array_merge([
        'batch_data' => $batchData,
    ], $extraFields);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query($fields),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_HTTPHEADER     => [
            "X-Forwarded-For: {$ip}",
            'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/120.0.0.0',
        ],
    ]);

    $start    = hrtime(true);
    $body     = curl_exec($ch);
    $latency  = (hrtime(true) - $start) / 1e6;
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    // curl_close is a no-op since PHP 8.0, deprecated in 8.5

    return [$httpCode, $latency, $body];
}

/**
 * Run latency test against an endpoint and return stats.
 */
function runLatencyTest(string $endpointUrl, int $samples, string $mode, array $extraHitFields = []): array
{
    $latencies = [];
    $errors    = 0;
    $statuses  = [];

    for ($i = 0; $i < $samples; $i++) {
        $ip = '10.20.' . ($i % 254 + 1) . '.' . (intdiv($i, 254) + 1);

        [$code, $latency, $body] = httpHit($endpointUrl, $i + 1, $ip, $extraHitFields);

        $statuses[$code] = ($statuses[$code] ?? 0) + 1;

        if ($code === 200) {
            $latencies[] = $latency;
        } else {
            $errors++;
        }
    }

    if (empty($latencies)) {
        return ['ok' => false, 'errors' => $errors, 'statuses' => $statuses];
    }

    return [
        'ok'      => true,
        'count'   => count($latencies),
        'errors'  => $errors,
        'avg'     => round(array_sum($latencies) / count($latencies), 2),
        'p50'     => round(percentile($latencies, 0.50), 2),
        'p95'     => round(percentile($latencies, 0.95), 2),
        'p99'     => round(percentile($latencies, 0.99), 2),
        'min'     => round(min($latencies), 2),
        'max'     => round(max($latencies), 2),
        'statuses' => $statuses,
    ];
}

function printLatencyResults(array $r, string $label): void
{
    if (!$r['ok']) {
        result($label, 'FAILED', json_encode($r['statuses']), 'fail');
        return;
    }

    result("{$label} — avg", "{$r['avg']} ms", '', 'info');
    result("{$label} — p50", "{$r['p50']} ms", '', $r['p50'] < 500 ? 'pass' : 'warn');
    result("{$label} — p95", "{$r['p95']} ms", '', $r['p95'] < 1000 ? 'pass' : 'warn');
    result("{$label} — min/max", "{$r['min']} / {$r['max']} ms", '', 'info');
    result("{$label} — errors", "{$r['errors']}", '', $r['errors'] === 0 ? 'pass' : 'warn');
}

// ─── Endpoint definitions ────────────────────────────────────────────────────

$endpoints = [
    'ajax' => [
        'label'     => 'AJAX (admin-ajax.php)',
        'hitUrl'    => $ajaxUrl . '?action=wp_statistics_collect',
        'batchUrl'  => $ajaxUrl . '?action=wp_statistics_batch',
        'hitExtra'  => ['action' => 'wp_statistics_collect'],
        'batchExtra'=> ['action' => 'wp_statistics_batch'],
    ],
    'rest' => [
        'label'     => 'REST API (/wp-json/)',
        'hitUrl'    => $restUrl . '/hit',
        'batchUrl'  => $restUrl . '/batch',
        'hitExtra'  => [],
        'batchExtra'=> [],
    ],
];

if ($hybridAvailable) {
    $endpoints['hybrid'] = [
        'label'     => 'Hybrid Mode (mu-plugin)',
        'hitUrl'    => $muPluginUrl,
        'batchUrl'  => $restUrl . '/batch', // hybrid uses REST for batch
        'hitExtra'  => [],
        'batchExtra'=> [],
    ];
}

// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
// TEST 1: Hit Latency Per Mode (rate limit OFF)
// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

banner('Test 1: Hit Latency Per Mode (rate limit OFF)');

Option::updateValue('tracker_rate_limit', false);
wp_cache_flush();

$modeResults = [];

foreach ($endpoints as $key => $ep) {
    echo "  Testing {$ep['label']}...\n";
    $r = runLatencyTest($ep['hitUrl'], $samplesPerTest, $key, $ep['hitExtra']);
    $modeResults[$key] = $r;
    printLatencyResults($r, $ep['label']);
    echo "\n";
}

// Comparison summary
banner('Comparison: Hit Latency (rate limit OFF)');

echo sprintf("  %-25s %8s %8s %8s %8s\n", 'Mode', 'Avg', 'P50', 'P95', 'Errors');
echo "  " . str_repeat('─', 58) . "\n";

foreach ($modeResults as $key => $r) {
    if ($r['ok']) {
        echo sprintf(
            "  %-25s %7.1f %7.1f %7.1f %7d\n",
            $endpoints[$key]['label'],
            $r['avg'],
            $r['p50'],
            $r['p95'],
            $r['errors']
        );
    } else {
        echo sprintf("  %-25s %s\n", $endpoints[$key]['label'], 'FAILED');
    }
}

// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
// TEST 2: Hit Latency Per Mode (rate limit ON)
// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

banner('Test 2: Hit Latency Per Mode (rate limit ON, threshold=30)');

Option::updateValue('tracker_rate_limit', true);
Option::updateValue('tracker_rate_limit_threshold', 30);
wp_cache_flush();

$rateLimitResults = [];

foreach ($endpoints as $key => $ep) {
    echo "  Testing {$ep['label']}...\n";
    $r = runLatencyTest($ep['hitUrl'], $samplesPerTest, $key, $ep['hitExtra']);
    $rateLimitResults[$key] = $r;
    printLatencyResults($r, $ep['label']);
    echo "\n";
}

// Rate limit overhead comparison
banner('Comparison: Rate Limit Overhead');

echo sprintf("  %-25s %10s %10s %10s\n", 'Mode', 'RL OFF', 'RL ON', 'Overhead');
echo "  " . str_repeat('─', 58) . "\n";

foreach ($modeResults as $key => $r) {
    if ($r['ok'] && ($rateLimitResults[$key]['ok'] ?? false)) {
        $offAvg = $r['avg'];
        $onAvg  = $rateLimitResults[$key]['avg'];
        $overhead = round($onAvg - $offAvg, 1);
        $pct = $offAvg > 0 ? round(($overhead / $offAvg) * 100, 1) : 0;

        echo sprintf(
            "  %-25s %8.1f ms %8.1f ms %+7.1f ms (%+.1f%%)\n",
            $endpoints[$key]['label'],
            $offAvg, $onAvg, $overhead, $pct
        );
    }
}

// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
// TEST 3: Batch Endpoint Per Mode
// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

banner('Test 3: Batch/Engagement Endpoint Per Mode');

Option::updateValue('tracker_rate_limit', false);
wp_cache_flush();

// First create sessions via hits on each mode
foreach ($endpoints as $key => $ep) {
    for ($i = 0; $i < 3; $i++) {
        httpHit($ep['hitUrl'], $i + 1, '10.30.' . ($i + 1) . '.1', $ep['hitExtra']);
    }
}

foreach ($endpoints as $key => $ep) {
    $latencies = [];
    $errors    = 0;

    for ($i = 0; $i < $samplesPerTest; $i++) {
        $ip = '10.30.' . ($i % 3 + 1) . '.1';
        [$code, $latency, $body] = httpBatch($ep['batchUrl'], $ip, rand(1000, 30000), $ep['batchExtra']);

        if ($code === 200) {
            $latencies[] = $latency;
        } else {
            $errors++;
        }
    }

    if (!empty($latencies)) {
        $avg = round(array_sum($latencies) / count($latencies), 2);
        $p50 = round(percentile($latencies, 0.50), 2);
        result("{$ep['label']} batch avg", "{$avg} ms", '', 'info');
        result("{$ep['label']} batch p50", "{$p50} ms", '', $p50 < 500 ? 'pass' : 'warn');
        result("{$ep['label']} batch errors", "{$errors}", '', $errors === 0 ? 'pass' : 'warn');
    } else {
        result("{$ep['label']} batch", 'ALL FAILED', "({$errors} errors)", 'fail');
    }
    echo "\n";
}

// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
// TEST 4: Rate Limiter Enforcement Per Mode
// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

banner('Test 4: Rate Limiter Burst Test Per Mode');

Option::updateValue('tracker_rate_limit', true);
Option::updateValue('tracker_rate_limit_threshold', 30);

foreach ($endpoints as $key => $ep) {
    wp_cache_flush();

    $accepted = 0;
    $rejected = 0;
    $other    = 0;
    $sameIp   = '192.168.77.' . (array_search($key, array_keys($endpoints)) + 1);

    for ($i = 0; $i < $burstSize; $i++) {
        [$code,,] = httpHit($ep['hitUrl'], $i + 1, $sameIp, $ep['hitExtra']);

        if ($code === 200) {
            $accepted++;
        } elseif ($code === 429) {
            $rejected++;
        } else {
            $other++;
        }
    }

    $threshold = 30;
    $enforced  = $accepted <= $threshold;

    result("{$ep['label']} accepted", $accepted, "/ {$burstSize}", 'info');
    result("{$ep['label']} rejected (429)", $rejected, '', $rejected > 0 ? 'pass' : 'warn');
    result("{$ep['label']} other errors", $other, '', $other === 0 ? 'pass' : 'warn');
    result("{$ep['label']} enforced", $enforced ? 'YES' : 'NO', '', $enforced ? 'pass' : 'fail');
    echo "\n";
}

// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
// TEST 5: Throughput Per Mode (10-second sustained)
// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

banner('Test 5: Throughput Per Mode (10 seconds, sequential)');

Option::updateValue('tracker_rate_limit', false);
wp_cache_flush();

$throughputResults = [];

foreach ($endpoints as $key => $ep) {
    $start     = time();
    $duration  = 10;
    $hits      = 0;
    $errors    = 0;
    $latencies = [];

    while ((time() - $start) < $duration) {
        $ip = '10.40.' . ($hits % 254 + 1) . '.' . (intdiv($hits, 254) % 254 + 1);
        [$code, $latency,] = httpHit($ep['hitUrl'], ($hits % 50) + 1, $ip, $ep['hitExtra']);

        if ($code === 200) {
            $latencies[] = $latency;
        } else {
            $errors++;
        }
        $hits++;
    }

    $elapsed    = time() - $start;
    $throughput = count($latencies) / max($elapsed, 1);

    $throughputResults[$key] = [
        'throughput' => $throughput,
        'hits'       => $hits,
        'errors'     => $errors,
        'avgLatency' => !empty($latencies) ? array_sum($latencies) / count($latencies) : 0,
    ];

    result("{$ep['label']} throughput", round($throughput, 1), 'hits/sec', 'info');
    result("{$ep['label']} total hits", $hits, "({$errors} errors)", $errors === 0 ? 'pass' : 'warn');
    result("{$ep['label']} avg latency", round($throughputResults[$key]['avgLatency'], 1), 'ms', 'info');
    echo "\n";
}

// Final comparison
banner('Final Summary: Mode Comparison');

echo sprintf("  %-25s %10s %10s %10s %10s\n", 'Mode', 'Throughput', 'Avg ms', 'P50 ms', 'P95 ms');
echo "  " . str_repeat('─', 70) . "\n";

foreach ($modeResults as $key => $r) {
    if ($r['ok']) {
        $tp = $throughputResults[$key]['throughput'] ?? 0;
        echo sprintf(
            "  %-25s %8.1f/s %8.1f %8.1f %8.1f\n",
            $endpoints[$key]['label'],
            $tp, $r['avg'], $r['p50'], $r['p95']
        );
    }
}

echo "\n";

// Fastest mode
if (!empty($modeResults)) {
    $fastest = null;
    $fastestAvg = PHP_FLOAT_MAX;
    foreach ($modeResults as $key => $r) {
        if ($r['ok'] && $r['avg'] < $fastestAvg) {
            $fastestAvg = $r['avg'];
            $fastest = $key;
        }
    }
    if ($fastest) {
        result("Fastest mode", $endpoints[$fastest]['label'], "(avg {$fastestAvg} ms)", 'pass');
    }
}

// Restore defaults
Option::updateValue('tracker_rate_limit', false);

echo "\n" . str_repeat('═', 60) . "\n";
echo "  Benchmark complete.\n";
echo str_repeat('═', 60) . "\n\n";
