<?php
/**
 * WP Statistics Uninstall Handler
 *
 * This file is called by WordPress when the plugin is deleted.
 * It bootstraps the autoloader and delegates to the PSR-4 Uninstaller class.
 *
 * @package WP_Statistics
 * @since 15.0.0
 */

// Exit if accessed directly or not being uninstalled
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Load Composer autoloader
$autoloader = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloader)) {
    require_once $autoloader;
}

// Run the uninstaller
if (class_exists('WP_Statistics\Service\Installation\Uninstaller')) {
    WP_Statistics\Service\Installation\Uninstaller::uninstall();
}
