<?php
/**
 * WP Statistics Direct File Endpoint
 *
 * SHORTINIT-compatible hit recording for maximum performance.
 * This file is a template — DirectEndpointManager bakes absolute paths
 * into the placeholders when copying to mu-plugins/.
 *
 * @since 15.0.0
 */

// Skip when WordPress auto-loads this as a mu-plugin.
if (defined('ABSPATH')) {
    return;
}

header('Content-Type: application/json');

$fail = static function (int $code, string $message): void {
    http_response_code($code);
    echo json_encode(['status' => false, 'data' => $message]);
    exit;
};

// ── 1. SHORTINIT Bootstrap ──────────────────────────────────────────

define('WP_STATISTICS_SHORTINIT', true);
define('SHORTINIT', true);

$wpLoadPath = '{{ABSPATH}}wp-load.php';

if (!file_exists($wpLoadPath)) {
    $fail(503, 'Endpoint needs reconfiguration');
}

require $wpLoadPath;

// ── 2. Verify DB connection ─────────────────────────────────────────

global $wpdb;

if (!$wpdb->check_connection(false)) {
    $fail(503, 'Service unavailable');
}

// Load files that wp-settings.php normally loads after the SHORTINIT check
// but that the tracking pipeline depends on at runtime.
wp_plugin_directory_constants();
require_once ABSPATH . WPINC . '/kses.php';

// ── 3. Load polyfills + plugin bootstrap ────────────────────────────

require __DIR__ . '/wp-statistics-polyfills.php';

$pluginDir = '{{PLUGIN_DIR}}';

if (!defined('WP_STATISTICS_DIR')) {
    define('WP_STATISTICS_DIR', $pluginDir);
}
if (!defined('WP_STATISTICS_VERSION')) {
    define('WP_STATISTICS_VERSION', '{{VERSION}}');
}
if (!defined('WP_STATISTICS_UPLOADS_DIR')) {
    define('WP_STATISTICS_UPLOADS_DIR', 'wp-statistics');
}

require_once $pluginDir . 'includes/class-wp-statistics-db.php';
require_once $pluginDir . 'packages/autoload.php';

// Stub for WP_Statistics() — only called by BaseRecord on DB insert failure.
if (!function_exists('WP_Statistics')) {
    function WP_Statistics()
    {
        return new class {
            public function log($message, $level = 'info')
            {
                error_log('WP Statistics: ' . $message);
            }
        };
    }
}

// ── 4. Handle request ───────────────────────────────────────────────

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $fail(405, 'Method not allowed');
}

// ── 5. Record hit ───────────────────────────────────────────────────

use WP_Statistics\Service\Tracking\Core\Tracker;

try {
    (new Tracker())->record();
    echo '{"status":true}';
} catch (\Exception $e) {
    $code = $e->getCode();
    http_response_code($code ?: 500);
    echo json_encode(['status' => false, 'data' => $e->getMessage()]);
} catch (\Throwable $e) {
    $fail(500, 'Internal error');
}
