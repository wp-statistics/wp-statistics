#!/usr/bin/env php
<?php
/**
 * WP Statistics Data Cleanup Script
 *
 * Clears all WP Statistics data from the database for fresh dummy data generation.
 * Use this before running dummy-tracker-simulator.php for a clean slate.
 *
 * Usage: php bin/clear-dummy-data.php [options]
 *
 * @package WP_Statistics
 * @since 15.0.0
 */

// Load WordPress
define('WP_USE_THEMES', false);
require_once(__DIR__ . '/../../../../wp-load.php');

// Parse command line arguments
$options = getopt('', ['force', 'help']);

if (isset($options['help'])) {
    echo "Usage: php bin/clear-dummy-data.php [options]\n\n";
    echo "Clears all WP Statistics data from the database.\n\n";
    echo "Options:\n";
    echo "  --force    Skip confirmation prompt\n";
    echo "  --help     Show this help\n\n";
    echo "WARNING: This action is irreversible. All statistics data will be deleted.\n";
    exit(0);
}

global $wpdb;

// Tables to truncate (in order to handle foreign keys if any)
$tables = [
    'statistics_views',
    'statistics_sessions',
    'statistics_visitors',
    'statistics_resource_uris',
    'statistics_resources',
    'statistics_countries',
    'statistics_cities',
    'statistics_referrers',
    'statistics_device_types',
    'statistics_device_browsers',
    'statistics_device_browser_versions',
    'statistics_device_os',
    'statistics_resolutions',
    'statistics_languages',
    'statistics_timezones',
    'statistics_summary',
    'statistics_summary_totals',
];

// Confirmation
if (!isset($options['force'])) {
    echo "=== WP Statistics Data Cleanup ===\n\n";
    echo "WARNING: This will DELETE ALL data from the following tables:\n";
    foreach ($tables as $table) {
        $fullTable = $wpdb->prefix . $table;
        $count = $wpdb->get_var("SELECT COUNT(*) FROM {$fullTable}");
        if ($count !== null) {
            echo "  - {$fullTable}: " . number_format($count) . " rows\n";
        }
    }
    echo "\nThis action is IRREVERSIBLE.\n";
    echo "Type 'yes' to confirm: ";

    $handle = fopen("php://stdin", "r");
    $line = trim(fgets($handle));
    fclose($handle);

    if (strtolower($line) !== 'yes') {
        echo "\nAborted.\n";
        exit(1);
    }
    echo "\n";
}

echo "Clearing WP Statistics data...\n\n";

$errors = [];
$cleared = [];

// Disable foreign key checks temporarily
$wpdb->query('SET FOREIGN_KEY_CHECKS = 0');

foreach ($tables as $table) {
    $fullTable = $wpdb->prefix . $table;

    // Check if table exists
    $tableExists = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = %s AND table_name = %s",
        DB_NAME,
        $fullTable
    ));

    if (!$tableExists) {
        echo "  Skipped: {$fullTable} (table does not exist)\n";
        continue;
    }

    $result = $wpdb->query("TRUNCATE TABLE {$fullTable}");

    if ($result === false) {
        $errors[] = $fullTable;
        echo "  FAILED:  {$fullTable} - " . $wpdb->last_error . "\n";
    } else {
        $cleared[] = $fullTable;
        echo "  Cleared: {$fullTable}\n";
    }
}

// Re-enable foreign key checks
$wpdb->query('SET FOREIGN_KEY_CHECKS = 1');

echo "\n=== Summary ===\n";
echo "Tables cleared: " . count($cleared) . "\n";

if (!empty($errors)) {
    echo "Tables failed:  " . count($errors) . "\n";
    echo "\nFailed tables:\n";
    foreach ($errors as $error) {
        echo "  - {$error}\n";
    }
}

echo "\nAll data cleared. Run dummy-tracker-simulator.php to generate fresh data:\n";
echo "  php bin/dummy-tracker-simulator.php --days=14 --visitors-per-day=100\n\n";
