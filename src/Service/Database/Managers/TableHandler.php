<?php

namespace WP_Statistics\Service\Database\Managers;

use WP_STATISTICS\Install;
use WP_STATISTICS\Option;
use WP_Statistics\Service\Database\DatabaseFactory;
use WP_Statistics\Service\Database\Schema\Manager;


/**
 * Handles database table operations, including creation, inspection,
 * and deletion of tables.
 *
 * This class provides methods to create all tables, create individual tables,
 * drop individual tables, and drop all tables as required for managing
 * the database schema in WP Statistics.
 */
class TableHandler
{
    /**
     * Create all database tables if they do not already exist.
     *
     * This method iterates through all known table names, inspects each table,
     * and creates it if it is missing using the predefined schema.
     *
     * @return void
     * @throws \RuntimeException If a table creation or inspection fails.
     */
    public static function createAllTables()
    {
        $tableNames = Manager::getAllTableNames();

        foreach ($tableNames as $tableName) {
            try {
                $inspect = DatabaseFactory::table('inspect')
                    ->setName($tableName)
                    ->execute();

                if (!$inspect->getResult()) {
                    $schema = Manager::getSchemaForTable($tableName);

                    DatabaseFactory::table('create')
                        ->setName($tableName)
                        ->setArgs($schema)
                        ->execute();
                }
            } catch (\Exception $e) {
                throw new \RuntimeException("Failed to inspect or create table `$tableName`: " . $e->getMessage(), 0, $e);
            }
        }

        Option::saveOptionGroup('check', false, 'db');

        if (Install::isFresh()) {
            Option::saveOptionGroup('migrated', true, 'db');
            Option::saveOptionGroup('manual_migration_tasks', [], 'db');
            Option::saveOptionGroup('auto_migration_tasks', [], 'db');
            Option::saveOptionGroup('version', WP_STATISTICS_VERSION, 'db');
            return;
        }

        Option::saveOptionGroup('migrated', false, 'db');
        Option::saveOptionGroup('migration_status_detail', null, 'db');

        $dismissedNotices = get_option('wp_statistics_dismissed_notices', []);

        if (in_array('database_manual_migration_done', $dismissedNotices, true)) {
            $dismissedNotices = array_diff($dismissedNotices, ['database_manual_migration_done']);

            update_option('wp_statistics_dismissed_notices', $dismissedNotices);
        }

        if (in_array('database_manual_migration_progress', $dismissedNotices, true)) {
            $dismissedNotices = array_diff($dismissedNotices, ['database_manual_migration_progress']);

            update_option('wp_statistics_dismissed_notices', $dismissedNotices);
        }
    }

    /**
     * Create a single table.
     *
     * @param string $tableName The name of the table to create.
     * @param array $schema The schema for the table.
     * @return void
     * @throws \RuntimeException If the table creation fails.
     */
    public static function createTable(string $tableName, array $schema)
    {
        try {
            $createOperation = DatabaseFactory::table('create');
            $createOperation
                ->setName($tableName)
                ->setArgs($schema)
                ->execute();
        } catch (\Exception $e) {
            throw new \RuntimeException("Failed to create table `$tableName`: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Drop a single table.
     *
     * @param string $tableName The name of the table to drop.
     * @return void
     * @throws \RuntimeException If the table drop operation fails.
     */
    public static function dropTable(string $tableName)
    {
        try {
            DatabaseFactory::table('drop')
                ->setName($tableName)
                ->execute();
        } catch (\Exception $e) {
            throw new \RuntimeException("Failed to drop table `$tableName`: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Drop all known tables.
     *
     * @return void
     * @throws \RuntimeException If any table drop operation fails.
     */
    public static function dropAllTables()
    {
        $tableNames = Manager::getAllTableNames();

        foreach ($tableNames as $tableName) {
            try {
                self::dropTable($tableName);
            } catch (\Exception $e) {
                throw new \RuntimeException("Failed to drop table `$tableName`: " . $e->getMessage(), 0, $e);
            }
        }
    }
}
