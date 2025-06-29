<?php

namespace WP_Statistics\Service\Database\Managers;

use WP_STATISTICS\Option;
use WP_Statistics\Service\Database\DatabaseFactory;

/**
 * Handles database schema migrations.
 *
 * This class is responsible for managing database schema changes across different versions.
 * It ensures that the database structure is always up to date with the current version
 * by applying necessary schema modifications.
 */
class MigrationHandler
{
    /**
     * Initialize schema migration process.
     *
     * @return void
     */
    public static function init()
    {
        add_action('admin_init', [self::class, 'runSchemaMigrations']);
    }

    /**
     * Execute pending schema migrations if any exist.
     *
     * @return void
     */
    public static function runSchemaMigrations()
    {
        if (self::isMigrationComplete()) {
            return;
        }

        try {
            $migrationData = self::collectSchemaMigrations();

            foreach ($migrationData['versions'] as $version) {
                $migrations = $migrationData['mappings'][$version];

                foreach ($migrations as $migration) {
                    $instance = self::createMigrationInstance($migration['class']);
                    if (!$instance) {
                        continue;
                    }

                    foreach ($migration['methods'] as $method) {
                        if (method_exists($instance, $method)) {
                            $instance->$method();
                            Option::saveOptionGroup('version', $version, 'db');
                        }
                    }
                }
            }

            Option::saveOptionGroup('migrated', true, 'db');

        } catch (\Exception $e) {
            Option::saveOptionGroup('migration_status_detail', [
                'status'  => 'failed',
                'message' => $e->getMessage()
            ], 'db');
        }
    }

    /**
     * Check if all schema migrations have been completed.
     *
     * @return bool Returns true if all schema migrations are complete.
     */
    private static function isMigrationComplete()
    {
        return Option::getOptionGroup('db', 'migrated', false) || Option::getOptionGroup('db', 'check', true);
    }

    /**
     * Collect and prepare schema migration data.
     *
     * @return array Contains versions and their respective schema changes.
     */
    private static function collectSchemaMigrations()
    {
        $currentVersion  = Option::getOptionGroup('db', 'version', '0.0.0');
        $allVersions     = [];
        $versionMappings = [];

        foreach (DatabaseFactory::migration() as $instance) {
            foreach ($instance->getMigrationSteps() as $version => $methods) {
                if (version_compare($currentVersion, $version, '>=')) {
                    continue;
                }

                $allVersions[]               = $version;
                $versionMappings[$version][] = [
                    'class'   => get_class($instance),
                    'methods' => $methods,
                    'type'    => $instance->getName()
                ];
            }
        }

        if (empty($allVersions)) {
            Option::saveOptionGroup('migrated', true, 'db');
        }

        usort($allVersions, 'version_compare');

        return [
            'versions' => $allVersions,
            'mappings' => $versionMappings
        ];
    }

    /**
     * Create an instance of the schema migration class.
     *
     * @param string $class Fully qualified class name.
     * @return object|null Instance of the class or null if it doesn't exist.
     */
    private static function createMigrationInstance($class)
    {
        if (!class_exists($class)) {
            return null;
        }
        return new $class();
    }
}
