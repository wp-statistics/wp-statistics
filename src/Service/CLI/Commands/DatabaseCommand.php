<?php

namespace WP_Statistics\Service\CLI\Commands;

use WP_CLI;
use WP_Statistics\Service\Admin\Tools\SystemInfoService;
use WP_Statistics\Service\Cron\DatabaseMaintenanceManager;
use WP_Statistics\Service\Database\DatabaseSchema;
use WP_Statistics\Service\Database\Managers\TableHandler;

/**
 * Manage WP Statistics database.
 *
 * Provides commands for inspecting, maintaining, and managing
 * the WP Statistics database tables.
 *
 * @since 15.0.0
 */
class DatabaseCommand
{
    /**
     * Show all database tables with details.
     *
     * ## OPTIONS
     *
     * [--format=<format>]
     * : Output format.
     * ---
     * default: table
     * options:
     *   - table
     *   - csv
     *   - json
     *   - yaml
     * ---
     *
     * ## EXAMPLES
     *
     *      $ wp statistics db tables
     *      $ wp statistics db tables --format=json
     *
     * @param array $args       Positional arguments.
     * @param array $assoc_args Associative arguments.
     * @return void
     */
    public function tables($args, $assoc_args)
    {
        $format = $assoc_args['format'] ?? 'table';

        $systemInfo = new SystemInfoService();
        $tables     = $systemInfo->getTables();

        if (empty($tables)) {
            WP_CLI::warning('No tables found.');
            return;
        }

        $items = [];
        foreach ($tables as $table) {
            $items[] = [
                'Table'       => $table['name'] ?? $table['key'],
                'Description' => $table['description'] ?? '-',
                'Records'     => number_format($table['records'] ?? 0),
                'Size'        => $table['size'] ?? '-',
                'Engine'      => $table['engine'] ?? '-',
            ];
        }

        \WP_CLI\Utils\format_items($format, $items, ['Table', 'Description', 'Records', 'Size', 'Engine']);
    }

    /**
     * Show quick row counts for core tables.
     *
     * ## OPTIONS
     *
     * [--format=<format>]
     * : Output format.
     * ---
     * default: table
     * options:
     *   - table
     *   - csv
     *   - json
     *   - yaml
     * ---
     *
     * ## EXAMPLES
     *
     *      $ wp statistics db stats
     *
     * @param array $args       Positional arguments.
     * @param array $assoc_args Associative arguments.
     * @return void
     */
    public function stats($args, $assoc_args)
    {
        $format = $assoc_args['format'] ?? 'table';
        $stats  = DatabaseMaintenanceManager::getTableStats();

        if (empty($stats)) {
            WP_CLI::warning('No table stats available.');
            return;
        }

        $items = [];
        foreach ($stats as $table => $count) {
            $items[] = [
                'Table'   => $table,
                'Records' => number_format($count),
            ];
        }

        \WP_CLI\Utils\format_items($format, $items, ['Table', 'Records']);
    }

    /**
     * Optimize database tables.
     *
     * ## OPTIONS
     *
     * [<table>]
     * : Specific table key to optimize. If omitted, optimizes all tables.
     *
     * ## EXAMPLES
     *
     *      # Optimize all tables
     *      $ wp statistics db optimize
     *
     *      # Optimize a specific table
     *      $ wp statistics db optimize visitors
     *
     * @param array $args       Positional arguments.
     * @param array $assoc_args Associative arguments.
     * @return void
     */
    public function optimize($args, $assoc_args)
    {
        $schema = new DatabaseSchema();

        if (!empty($args[0])) {
            $tableKey = $args[0];
            WP_CLI::log(sprintf('Optimizing table: %s', $tableKey));

            if ($schema->optimizeTable($tableKey)) {
                WP_CLI::success(sprintf('Table "%s" optimized.', $tableKey));
            } else {
                WP_CLI::error(sprintf('Failed to optimize table "%s".', $tableKey));
            }
            return;
        }

        // Optimize all tables
        $tables   = $schema->getAllTables(true);
        $success  = 0;
        $failed   = 0;

        foreach ($tables as $key => $name) {
            if ($schema->optimizeTable($key)) {
                $success++;
            } else {
                WP_CLI::warning(sprintf('Failed to optimize: %s', $key));
                $failed++;
            }
        }

        WP_CLI::success(sprintf('Optimized %d tables. %d failed.', $success, $failed));
    }

    /**
     * Reinitialize all WP Statistics database tables.
     *
     * Recreates all database tables. Existing data structure will be
     * updated to match the current schema.
     *
     * ## OPTIONS
     *
     * [--yes]
     * : Skip confirmation prompt.
     *
     * ## EXAMPLES
     *
     *      $ wp statistics db reinitialize
     *      $ wp statistics db reinitialize --yes
     *
     * @param array $args       Positional arguments.
     * @param array $assoc_args Associative arguments.
     * @return void
     */
    public function reinitialize($args, $assoc_args)
    {
        WP_CLI::confirm('This will reinitialize all WP Statistics database tables. Are you sure?', $assoc_args);

        TableHandler::createAllTables();

        WP_CLI::success('Reinitialized WP Statistics database.');
    }

    /**
     * Delete data older than a specified number of days.
     *
     * Removes views, sessions, and visitors older than the given threshold.
     *
     * ## OPTIONS
     *
     * --days=<days>
     * : Delete data older than this many days.
     *
     * [--yes]
     * : Skip confirmation prompt.
     *
     * ## EXAMPLES
     *
     *      $ wp statistics db purge-old --days=90
     *      $ wp statistics db purge-old --days=365 --yes
     *
     * @subcommand purge-old
     *
     * @param array $args       Positional arguments.
     * @param array $assoc_args Associative arguments.
     * @return void
     */
    public function purgeOld($args, $assoc_args)
    {
        $days = (int) ($assoc_args['days'] ?? 0);

        if ($days <= 0) {
            WP_CLI::error('Please provide a valid --days value greater than 0.');
            return;
        }

        WP_CLI::confirm(
            sprintf('This will permanently delete all analytics data older than %d days. Continue?', $days),
            $assoc_args
        );

        $cutoffDate = gmdate('Y-m-d H:i:s', strtotime(sprintf('-%d days', $days)));
        $total      = 0;

        WP_CLI::log(sprintf('Deleting data older than %s...', $cutoffDate));

        $viewsDeleted    = DatabaseMaintenanceManager::deleteViewsOlderThan($cutoffDate, false);
        $sessionsDeleted = DatabaseMaintenanceManager::deleteSessionsOlderThan($cutoffDate, false);
        $visitorsDeleted = DatabaseMaintenanceManager::deleteVisitorsOlderThan($cutoffDate, false);

        $total = $viewsDeleted + $sessionsDeleted + $visitorsDeleted;

        WP_CLI::success(sprintf(
            'Purged %d records (%d views, %d sessions, %d visitors).',
            $total,
            $viewsDeleted,
            $sessionsDeleted,
            $visitorsDeleted
        ));
    }

    /**
     * Remove orphaned records from the database.
     *
     * Cleans up sessions and views that reference non-existent visitors.
     *
     * ## OPTIONS
     *
     * [--yes]
     * : Skip confirmation prompt.
     *
     * ## EXAMPLES
     *
     *      $ wp statistics db cleanup-orphans
     *      $ wp statistics db cleanup-orphans --yes
     *
     * @subcommand cleanup-orphans
     *
     * @param array $args       Positional arguments.
     * @param array $assoc_args Associative arguments.
     * @return void
     */
    public function cleanupOrphans($args, $assoc_args)
    {
        WP_CLI::confirm('This will remove orphaned records. Continue?', $assoc_args);

        $result = DatabaseMaintenanceManager::cleanupAllOrphanedRecords(false, false);

        $total = array_sum($result);

        WP_CLI::success(sprintf('Removed %d orphaned records.', $total));
    }

    /**
     * Truncate all analytics data.
     *
     * WARNING: This permanently deletes ALL analytics data from all tables.
     *
     * ## OPTIONS
     *
     * [--yes]
     * : Skip confirmation prompt.
     *
     * ## EXAMPLES
     *
     *      $ wp statistics db purge-all
     *      $ wp statistics db purge-all --yes
     *
     * @subcommand purge-all
     *
     * @param array $args       Positional arguments.
     * @param array $assoc_args Associative arguments.
     * @return void
     */
    public function purgeAll($args, $assoc_args)
    {
        WP_CLI::confirm(
            'WARNING: This will permanently delete ALL analytics data. This cannot be undone. Are you absolutely sure?',
            $assoc_args
        );

        $result = DatabaseMaintenanceManager::purgeAllData();

        WP_CLI::success('All analytics data has been purged.');
    }
}
