<?php
/**
 * WP Statistics Uninstall Handler
 *
 * Called by WordPress when the plugin is DELETED (not deactivated).
 * Delegates cleanup to the PSR-4 Uninstaller class which:
 * - Always clears cron events and temporary files
 * - Optionally removes all data if delete_data_on_uninstall is enabled
 *
 * @package WP_Statistics
 * @since 15.0.0
 */

// Exit if not being uninstalled by WordPress
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Load Composer autoloader
if (file_exists(__DIR__ . '/packages/autoload.php')) {
    require_once __DIR__ . '/packages/autoload.php';
} else {
    return;
}

// Run the uninstaller
if (class_exists('WP_Statistics\Service\Installation\Uninstaller')) {
    WP_Statistics\Service\Installation\Uninstaller::uninstall();
}
