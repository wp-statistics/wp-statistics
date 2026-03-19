<?php
/**
 * WP Statistics Direct File Tracker
 *
 * SHORTINIT-compatible hit recording for maximum performance.
 * This file is a template — DirectFileHandler bakes absolute paths
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

// ── 3. Polyfills ────────────────────────────────────────────────────
//
// Lightweight replacements for WordPress functions NOT loaded in
// SHORTINIT mode (l10n.php, link-template.php, http.php).
//
// Functions already available (no polyfill needed):
// - Hooks: add_filter, apply_filters, do_action, has_filter
// - Sanitization: sanitize_text_field, esc_html, sanitize_url
// - Options: get_option, update_option
// - Utilities: wp_parse_args, absint, wp_privacy_anonymize_ip

if (!function_exists('__')) {
    function __($text, $domain = 'default')
    {
        return $text;
    }
}

if (!function_exists('esc_html__')) {
    function esc_html__($text, $domain = 'default')
    {
        return $text;
    }
}

if (!function_exists('home_url')) {
    function home_url($path = '', $scheme = null)
    {
        $home = get_option('home');

        if ($path && is_string($path)) {
            return rtrim($home, '/') . '/' . ltrim($path, '/');
        }

        return $home;
    }
}

if (!function_exists('wp_parse_url')) {
    function wp_parse_url($url, $component = -1)
    {
        return parse_url($url, $component);
    }
}

// ── 4. Plugin bootstrap ─────────────────────────────────────────────

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

// ── 5. Handle request ───────────────────────────────────────────────

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $fail(405, 'Method not allowed');
}

// ── 6. Record hit ───────────────────────────────────────────────────

use WP_Statistics\Service\Tracking\Core\Tracker;

try {
    (new Tracker())->record();
    echo '{"status":true}';
} catch (\Exception $e) {
    $code = $e->getCode() ?: 500;
    http_response_code($code);
    $message = $code < 500 ? $e->getMessage() : 'Internal error';
    echo json_encode(['status' => false, 'data' => $message]);
} catch (\Throwable $e) {
    $fail(500, 'Internal error');
}
