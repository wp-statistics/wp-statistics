<?php
/**
 * WP Statistics Lightweight Tracking Endpoint
 *
 * Handles tracking requests with minimal WordPress bootstrap (SHORTINIT).
 *
 * @since 15.0.0
 */

// Only process POST requests
if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    http_response_code(405);
    echo json_encode(['status' => false, 'data' => 'Method not allowed']);
    exit;
}

// Detect wp-load.php location
$wpLoadPath = wp_statistics_find_wp_load();

if (!$wpLoadPath) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['status' => false, 'data' => 'WordPress installation not found']);
    exit;
}

// Use SHORTINIT for minimal bootstrap
define('SHORTINIT', true);
require_once $wpLoadPath;

// Load essential WordPress functions not loaded by SHORTINIT
require_once ABSPATH . WPINC . '/l10n.php';
require_once ABSPATH . WPINC . '/plugin.php';
require_once ABSPATH . WPINC . '/option.php';
require_once ABSPATH . WPINC . '/class-wp-error.php';

// Verify WP Statistics is active
$activePlugins = get_option('active_plugins', []);
$isActive      = false;

foreach ($activePlugins as $plugin) {
    if (strpos($plugin, 'wp-statistics') !== false) {
        $isActive = true;
        break;
    }
}

// Also check network-activated plugins on multisite
if (!$isActive && function_exists('is_multisite') && is_multisite()) {
    $networkPlugins = get_site_option('active_sitewide_plugins', []);
    if (is_array($networkPlugins)) {
        foreach (array_keys($networkPlugins) as $plugin) {
            if (strpos($plugin, 'wp-statistics') !== false) {
                $isActive = true;
                break;
            }
        }
    }
}

if (!$isActive) {
    header('Content-Type: application/json');
    http_response_code(403);
    echo json_encode(['status' => false, 'data' => 'Plugin not active']);
    exit;
}

// Resolve plugin directory — WP_PLUGIN_DIR is not defined under SHORTINIT
$pluginDir = WP_CONTENT_DIR . '/plugins/wp-statistics';

// Load autoloader — production uses packages/, dev uses vendor/
$autoloader = $pluginDir . '/packages/autoload.php';
if (!file_exists($autoloader)) {
    $autoloader = $pluginDir . '/vendor/autoload.php';
}

if (!file_exists($autoloader)) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['status' => false, 'data' => 'Plugin files not found']);
    exit;
}

require_once $autoloader;

// Define essential constants manually — constants.php requires functions
// (plugin_dir_url, get_plugin_data) not available under SHORTINIT
if (!defined('WP_STATISTICS_VERSION')) {
    define('WP_STATISTICS_VERSION', '15.0');
}
if (!defined('WP_STATISTICS_DIR')) {
    define('WP_STATISTICS_DIR', $pluginDir . '/');
}

// Determine request type from POST body
$contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
$isBatch     = false;

if (strpos($contentType, 'multipart/form-data') !== false || strpos($contentType, 'application/x-www-form-urlencoded') !== false) {
    $isBatch = isset($_POST['batch_data']);
}

header('Content-Type: application/json');
header('Cache-Control: no-cache');

try {
    if ($isBatch) {
        // Process batch request — wp_unslash only, not sanitize_text_field (corrupts JSON)
        $batchData = isset($_POST['batch_data']) ? wp_unslash($_POST['batch_data']) : '';

        if (empty($batchData)) {
            throw new \Exception('Missing batch data', 400);
        }

        $data = json_decode($batchData, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON payload', 400);
        }

        $controller = new \WP_Statistics\Service\Tracking\Controllers\BatchTracking();
        $result     = $controller->processEvents($data);

        echo json_encode([
            'status'    => true,
            'processed' => $result['processed'],
            'errors'    => $result['errors'],
        ]);
    } else {
        // Process hit request
        $controllerFactory = \WP_Statistics\Service\Tracking\TrackingFactory::hits();
        \WP_Statistics\Service\Tracking\TrackerHelper::validateHitRequest();
        $controllerFactory->record();

        echo json_encode(['status' => true]);
    }
} catch (\Exception $e) {
    $code = $e->getCode();
    http_response_code(($code >= 400 && $code < 600) ? $code : 400);
    echo json_encode([
        'status' => false,
        'data'   => $e->getMessage(),
    ]);
}

exit;

/**
 * Find wp-load.php by walking up the directory tree.
 *
 * @return string|false Path to wp-load.php or false if not found.
 */
function wp_statistics_find_wp_load()
{
    $dir = dirname(__FILE__);

    for ($i = 0; $i < 10; $i++) {
        $dir = dirname($dir);
        if (file_exists($dir . '/wp-load.php')) {
            return $dir . '/wp-load.php';
        }
    }

    if (defined('ABSPATH') && file_exists(ABSPATH . 'wp-load.php')) {
        return ABSPATH . 'wp-load.php';
    }

    return false;
}
