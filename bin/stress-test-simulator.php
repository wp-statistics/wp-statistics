#!/usr/bin/env php
<?php
/**
 * WP Statistics Stress Test Simulator CLI
 *
 * Generates realistic, invalid, and attack traffic data for testing WP Statistics v15.
 *
 * Usage:
 *   php bin/stress-test-simulator.php [options]
 *
 * Options:
 *   --target=N           Target number of records (supports K/M suffix: 100K, 1M)
 *   --workers=N          Number of parallel HTTP workers (default: 10)
 *   --scenario=NAME      Preset scenario: normal, stress, invalid, security, mixed
 *   --invalid-ratio=N    Ratio of invalid data (0.0-1.0, e.g., 0.05 for 5%)
 *   --attack-ratio=N     Ratio of attack payloads (0.0-1.0, e.g., 0.01 for 1%)
 *   --days=N             Number of days in date range (default: 30)
 *   --from=DATE          Start date (YYYY-MM-DD)
 *   --to=DATE            End date (YYYY-MM-DD)
 *   --logged-in-ratio=N  Ratio of logged-in visitors (default: 0.12)
 *   --resume             Resume from checkpoint
 *   --checkpoint-id=ID   Checkpoint identifier for resume
 *   --no-checkpoints     Disable checkpoint saving
 *   --url=URL            Target URL (default: auto-detect)
 *   --quick              Quick test mode (10 requests only)
 *   --dry-run            Show configuration without running
 *   --verbose            Verbose output
 *   --help               Show this help message
 *
 * Examples:
 *   # Generate 1000 records with defaults
 *   php bin/stress-test-simulator.php --target=1000
 *
 *   # Stress test with 100K records and 20 workers
 *   php bin/stress-test-simulator.php --target=100K --workers=20 --scenario=stress
 *
 *   # Security testing with attack payloads
 *   php bin/stress-test-simulator.php --target=5000 --scenario=security
 *
 *   # Mixed testing with invalid and attack data
 *   php bin/stress-test-simulator.php --target=10000 --invalid-ratio=0.1 --attack-ratio=0.05
 *
 *   # Resume from previous checkpoint
 *   php bin/stress-test-simulator.php --resume --checkpoint-id=my-test
 *
 * @package WP_Statistics
 * @since 15.0.0
 */

declare(strict_types=1);

// ============================================================================
// Bootstrap
// ============================================================================

// Find WordPress root
$wpRoot = findWordPressRoot(__DIR__);
if (!$wpRoot) {
    fwrite(STDERR, "Error: Could not find WordPress installation.\n");
    fwrite(STDERR, "Make sure this script is in the plugin's bin/ directory.\n");
    exit(1);
}

// Configure WP Statistics settings via WP-CLI (before WordPress loads)
configureSettingsViaCli($wpRoot);

// Load WordPress
require_once $wpRoot . '/wp-load.php';

// Load simulator classes from bin/simulator/
require_once __DIR__ . '/simulator/SimulatorConfig.php';
require_once __DIR__ . '/simulator/SettingsConfigurator.php';
require_once __DIR__ . '/simulator/ResourceProvisioner.php';
require_once __DIR__ . '/simulator/CheckpointManager.php';
require_once __DIR__ . '/simulator/Generators/AbstractDataGenerator.php';
require_once __DIR__ . '/simulator/Generators/RealisticVisitorGenerator.php';
require_once __DIR__ . '/simulator/Generators/InvalidDataGenerator.php';
require_once __DIR__ . '/simulator/Generators/AttackPayloadGenerator.php';
require_once __DIR__ . '/simulator/Http/CurlMultiSender.php';
require_once __DIR__ . '/simulator/SimulatorRunner.php';

use WP_Statistics\Testing\Simulator\SimulatorConfig;
use WP_Statistics\Testing\Simulator\SimulatorRunner;
use WP_Statistics\Testing\Simulator\CheckpointManager;

/**
 * Configure WP Statistics settings via WP-CLI before WordPress loads
 *
 * This ensures bypass_ad_blockers and use_cache_plugin are enabled
 */
function configureSettingsViaCli(string $wpRoot): void
{
    // Check if WP-CLI is available
    $wpCli = trim(shell_exec('which wp 2>/dev/null') ?? '');
    if (empty($wpCli)) {
        // WP-CLI not available, settings will be configured via PHP after WordPress loads
        return;
    }

    // Try to update settings via WP-CLI
    $command = sprintf(
        'cd %s && wp eval %s 2>/dev/null',
        escapeshellarg($wpRoot),
        escapeshellarg('
            $settings = get_option("wp_statistics_settings", []);
            $changed = false;
            if (!isset($settings["bypass_ad_blockers"]) || $settings["bypass_ad_blockers"] !== "1") {
                $settings["bypass_ad_blockers"] = "1";
                $changed = true;
            }
            if (!isset($settings["use_cache_plugin"]) || $settings["use_cache_plugin"] !== "1") {
                $settings["use_cache_plugin"] = "1";
                $changed = true;
            }
            if ($changed) {
                update_option("wp_statistics_settings", $settings);
                echo "Settings configured.\n";
            }
        ')
    );

    shell_exec($command);
}

// ============================================================================
// CLI Functions
// ============================================================================

/**
 * Find WordPress root directory
 */
function findWordPressRoot(string $startDir): ?string
{
    $dir = realpath($startDir);

    // Walk up directory tree looking for wp-load.php
    for ($i = 0; $i < 10; $i++) {
        if (file_exists($dir . '/wp-load.php')) {
            return $dir;
        }
        $parent = dirname($dir);
        if ($parent === $dir) {
            break;
        }
        $dir = $parent;
    }

    // Try common relative paths
    $paths = [
        dirname(__DIR__, 4), // plugins/wp-statistics/bin -> wp-content -> wp root
        dirname(__DIR__, 5), // Extra level if in subdirectory
    ];

    foreach ($paths as $path) {
        if (file_exists($path . '/wp-load.php')) {
            return $path;
        }
    }

    return null;
}

/**
 * Check if HTTPS is available for a given URL
 * Tests by making a HEAD request to the HTTPS version
 */
function checkHttpsAvailable(string $url): bool
{
    // Only test if URL is HTTP
    if (strpos($url, 'http://') !== 0) {
        return false;
    }

    $httpsUrl = str_replace('http://', 'https://', $url);

    // Try a quick HEAD request
    $ch = curl_init($httpsUrl);
    curl_setopt_array($ch, [
        CURLOPT_NOBODY         => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 2,
        CURLOPT_CONNECTTIMEOUT => 2,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_NOPROXY        => '*',
    ]);

    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // If we got a valid response, HTTPS is available
    return $httpCode >= 200 && $httpCode < 500;
}

/**
 * Parse command line arguments
 */
function parseArgs(array $argv): array
{
    $args = [
        'target'         => 1000,
        'workers'        => 10,
        'scenario'       => null,
        'invalid-ratio'  => 0.0,
        'attack-ratio'   => 0.0,
        'days'           => 30,
        'from'           => null,
        'to'             => null,
        'logged-in-ratio' => 0.12,
        'resume'         => false,
        'checkpoint-id'  => null,
        'no-checkpoints' => false,
        'url'            => null,
        'quick'          => false,
        'dry-run'        => false,
        'verbose'        => false,
        'help'           => false,
    ];

    foreach ($argv as $arg) {
        if (strpos($arg, '--') !== 0) {
            continue;
        }

        $arg = substr($arg, 2);
        $parts = explode('=', $arg, 2);
        $key = $parts[0];
        $value = $parts[1] ?? true;

        // Handle boolean flags
        if ($value === true || $value === 'true' || $value === '1') {
            $value = true;
        } elseif ($value === 'false' || $value === '0') {
            $value = false;
        }

        // Parse volume strings (e.g., 100K, 1M)
        if ($key === 'target' && is_string($value)) {
            $value = parseVolume($value);
        }

        // Parse numeric values
        if (in_array($key, ['workers', 'days']) && is_string($value)) {
            $value = (int)$value;
        }

        // Parse float values
        if (in_array($key, ['invalid-ratio', 'attack-ratio', 'logged-in-ratio']) && is_string($value)) {
            $value = (float)$value;
        }

        if (array_key_exists($key, $args)) {
            $args[$key] = $value;
        }
    }

    return $args;
}

/**
 * Parse volume string (e.g., 100K, 1M) to integer
 */
function parseVolume(string $value): int
{
    $value = strtoupper(trim($value));

    if (preg_match('/^(\d+(?:\.\d+)?)\s*([KMB])?$/', $value, $matches)) {
        $num = (float)$matches[1];
        $suffix = $matches[2] ?? '';

        switch ($suffix) {
            case 'K':
                return (int)($num * 1000);
            case 'M':
                return (int)($num * 1000000);
            case 'B':
                return (int)($num * 1000000000);
            default:
                return (int)$num;
        }
    }

    return (int)$value;
}

/**
 * Show help message
 */
function showHelp(): void
{
    $script = basename(__FILE__);
    echo <<<HELP
WP Statistics Stress Test Simulator

Usage: php bin/{$script} [options]

Options:
  --target=N           Target number of records (supports K/M suffix: 100K, 1M)
  --workers=N          Number of parallel HTTP workers (default: 10)
  --scenario=NAME      Preset scenario: normal, stress, invalid, security, mixed
  --invalid-ratio=N    Ratio of invalid data (0.0-1.0, e.g., 0.05 for 5%)
  --attack-ratio=N     Ratio of attack payloads (0.0-1.0, e.g., 0.01 for 1%)
  --days=N             Number of days in date range (default: 30)
  --from=DATE          Start date (YYYY-MM-DD)
  --to=DATE            End date (YYYY-MM-DD)
  --logged-in-ratio=N  Ratio of logged-in visitors (default: 0.12)
  --resume             Resume from checkpoint
  --checkpoint-id=ID   Checkpoint identifier for resume
  --no-checkpoints     Disable checkpoint saving
  --url=URL            Target URL (default: auto-detect from site_url())
  --quick              Quick test mode (10 requests only)
  --dry-run            Show configuration without running
  --verbose            Verbose output
  --help               Show this help message

Scenarios:
  normal      Standard realistic traffic (default)
  stress      High-volume stress testing (100K+ records)
  invalid     Focus on invalid/edge case data (50% invalid)
  security    Security testing with attack payloads (20% attacks)
  mixed       Combination of all data types

Examples:
  # Generate 1000 realistic records
  php bin/{$script} --target=1000

  # Stress test with 100K records
  php bin/{$script} --target=100K --scenario=stress --workers=20

  # Security testing
  php bin/{$script} --target=5000 --scenario=security

  # Custom mix
  php bin/{$script} --target=10K --invalid-ratio=0.1 --attack-ratio=0.05

  # Resume interrupted simulation
  php bin/{$script} --resume --checkpoint-id=my-test

  # Specify date range
  php bin/{$script} --target=5000 --from=2024-01-01 --to=2024-03-31


HELP;
}

/**
 * Output with color support
 */
function output(string $message, string $level = 'info'): void
{
    $colors = [
        'info'    => "\033[0m",      // Default
        'success' => "\033[32m",     // Green
        'warning' => "\033[33m",     // Yellow
        'error'   => "\033[31m",     // Red
    ];

    $reset = "\033[0m";
    $color = $colors[$level] ?? $colors['info'];

    // Detect if terminal supports colors
    $useColor = (function_exists('posix_isatty') && posix_isatty(STDOUT))
        || getenv('TERM') !== false;

    if ($useColor) {
        echo $color . $message . $reset . "\n";
    } else {
        echo $message . "\n";
    }
}

/**
 * Create configuration from CLI args
 */
function createConfig(array $args): SimulatorConfig
{
    // Detect target URL
    $targetUrl = $args['url'];
    if (!$targetUrl) {
        $targetUrl = admin_url('admin-ajax.php');
    }

    // Convert HTTP to HTTPS if the site uses SSL (avoid POST→GET redirect issues)
    // Check multiple indicators of SSL usage, or test if HTTPS is available
    $shouldUseHttps = is_ssl()
        || (defined('FORCE_SSL_ADMIN') && FORCE_SSL_ADMIN)
        || strpos(home_url(), 'https://') === 0
        || strpos(site_url(), 'https://') === 0
        || checkHttpsAvailable($targetUrl);

    if ($shouldUseHttps) {
        $targetUrl = str_replace('http://', 'https://', $targetUrl);
    }

    // Create base config and set defaults
    $config = new SimulatorConfig();
    $config->setDefaultDirectories();
    $config->targetUrl = $targetUrl;
    $config->targetRecords = $args['target'];

    // Apply scenario presets
    if ($args['scenario']) {
        switch ($args['scenario']) {
            case 'stress':
                $config->scenario = 'stress';
                $config->parallelWorkers = 20;
                $config->delayMs = 0;
                $config->batchSize = 10000;
                break;

            case 'security':
                $config->scenario = 'security';
                $config->attackPayloadRatio = 0.2;
                $config->validateResponses = true;
                $config->securityReport = true;
                break;

            case 'invalid':
                $config->scenario = 'invalid';
                $config->invalidDataRatio = 0.5;
                break;

            case 'mixed':
                $config->scenario = 'mixed';
                $config->invalidDataRatio = 0.1;
                $config->attackPayloadRatio = 0.05;
                break;
        }
    }

    // Override with specific CLI args
    $config->parallelWorkers = $args['workers'];

    if ($args['invalid-ratio'] > 0) {
        $config->invalidDataRatio = $args['invalid-ratio'];
    }

    if ($args['attack-ratio'] > 0) {
        $config->attackPayloadRatio = $args['attack-ratio'];
    }

    $config->loggedInRatio = $args['logged-in-ratio'];

    // Date range
    if ($args['from']) {
        $config->dateFrom = $args['from'];
    } elseif ($args['days']) {
        $config->dateFrom = date('Y-m-d', strtotime("-{$args['days']} days"));
    }

    if ($args['to']) {
        $config->dateTo = $args['to'];
    }

    // Checkpoints
    $config->enableCheckpoints = !$args['no-checkpoints'];
    if ($args['checkpoint-id']) {
        $config->checkpointId = $args['checkpoint-id'];
    }

    // Quick test mode
    if ($args['quick']) {
        $config->targetRecords = 10;
        $config->enableCheckpoints = false;
    }

    return $config;
}

/**
 * Show configuration summary
 */
function showConfig(SimulatorConfig $config, array $args): void
{
    output('=== Configuration ===', 'info');
    output(sprintf('Target URL:        %s', $config->targetUrl), 'info');
    output(sprintf('Target Records:    %s', number_format($config->targetRecords)), 'info');
    output(sprintf('Parallel Workers:  %d', $config->parallelWorkers), 'info');
    output(sprintf('Date Range:        %s to %s', $config->dateFrom, $config->dateTo), 'info');
    output(sprintf('Logged-in Ratio:   %.0f%%', $config->loggedInRatio * 100), 'info');

    if ($config->invalidDataRatio > 0) {
        output(sprintf('Invalid Data:      %.1f%%', $config->invalidDataRatio * 100), 'info');
    }

    if ($config->attackPayloadRatio > 0) {
        output(sprintf('Attack Payloads:   %.1f%%', $config->attackPayloadRatio * 100), 'info');
    }

    if ($args['scenario']) {
        output(sprintf('Scenario:          %s', $args['scenario']), 'info');
    }

    output(sprintf('Checkpoints:       %s', $config->enableCheckpoints ? 'enabled' : 'disabled'), 'info');

    if ($args['resume']) {
        output('Resume Mode:       enabled', 'info');
    }

    output('', 'info');
}

// ============================================================================
// Main Execution
// ============================================================================

// Parse arguments
$args = parseArgs($argv);

// Show help
if ($args['help']) {
    showHelp();
    exit(0);
}

// Header
output('');
output('╔═══════════════════════════════════════════════════════════╗', 'info');
output('║       WP Statistics Stress Test Simulator                 ║', 'info');
output('╚═══════════════════════════════════════════════════════════╝', 'info');
output('', 'info');

// Create configuration
$config = createConfig($args);

// Show configuration
showConfig($config, $args);

// Dry run mode
if ($args['dry-run']) {
    output('Dry run mode - no requests will be sent.', 'warning');
    exit(0);
}

// Validate configuration
$runner = new SimulatorRunner($config);
$errors = $runner->validateConfig();

if (!empty($errors)) {
    output('Configuration errors:', 'error');
    foreach ($errors as $error) {
        output('  - ' . $error, 'error');
    }
    exit(1);
}

// Set up output callback
$runner->setOutputCallback(function (string $message, string $level) use ($args) {
    // In verbose mode, show everything
    // In normal mode, skip some info messages
    if (!$args['verbose'] && $level === 'info' && strpos($message, '[') !== 0) {
        return;
    }
    output($message, $level);
});

// Handle CTRL+C gracefully
if (function_exists('pcntl_signal')) {
    pcntl_signal(SIGINT, function () use ($runner) {
        output('', 'info');
        output('Received interrupt signal...', 'warning');
        $runner->stop();
        exit(130);
    });
    pcntl_async_signals(true);
}

// Run simulation
try {
    $results = $runner->run();
    exit($results['failed'] > 0 ? 1 : 0);
} catch (\Exception $e) {
    output('Fatal error: ' . $e->getMessage(), 'error');

    if ($args['verbose']) {
        output('Stack trace:', 'error');
        output($e->getTraceAsString(), 'error');
    }

    exit(2);
}
