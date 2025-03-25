<?php

namespace WP_Statistics\Async\DataMigrator;

use WP_STATISTICS\Option;
use WP_STATISTICS\User;
use WP_Statistics\Utils\Request;

/**
 * Manages database migrations, checks migration needs, and executes upgrades sequentially.
 */
abstract class MigrationManager
{
    /**
     * Number of records processed per batch.
     *
     * @var int
     */
    protected $batchSize = 50;

    /**
     * Total number of records to migrate.
     *
     * @var int
     */
    protected $total = 0;

    /**
     * Number of records already migrated.
     *
     * @var int
     */
    protected $done = 0;

    /**
     * Number of records remaining to be migrated.
     *
     * @var int
     */
    protected $remains = 0;

    /**
     * Current migration version.
     *
     * @var string
     */
    protected static $version;

    /**
     * Holds the current migration class name.
     *
     * @var string|null
     */
    protected static $currentMigration;

    /**
     * List of available migrations.
     *
     * @var array
     */
    protected static $migrations = [];

    /**
     * Perform the migration. Must be implemented by child classes.
     */
    abstract public function migrate();

    /**
     * Checks if a database migration is required.
     *
     * @return bool
     */
    public static function needsMigration()
    {
        $currentDbVersion = Option::getOptionGroup('db', 'version', '0.0.0');
        ksort(self::$migrations);

        $versions = array_keys(self::$migrations);
        $latestMigrationVersion = end($versions);

        return version_compare($currentDbVersion, $latestMigrationVersion, '<');
    }

    /**
     * Retrieves the next pending migration instance.
     *
     * @return object|null
     */
    public static function getMigration()
    {
        self::getMigrations();

        if (!empty(self::$currentMigration) && class_exists(self::$currentMigration)) {
            return new self::$currentMigration();
        }

        return null;
    }

    /**
     * Determines which migration should run next.
     *
     * @param string|null $version
     * @return string|null
     */
    public static function getMigrations($version = null)
    {
        if (!empty(self::$migrations[$version])) {
            self::$version          = $version;
            self::$currentMigration = self::$migrations[$version];

            return;
        }

        $currentDbVersion = Option::getOptionGroup('db', 'version', '0.0.0');
        ksort(self::$migrations);

        foreach (self::$migrations as $version => $className) {
            if (version_compare($version, $currentDbVersion, '<=')) {
                continue;
            }

            self::$version = $version;
            self::$currentMigration = $className;

            return $className;
        }

        return null;
    }

    /**
     * Checks whether the current migration has completed.
     *
     * @return bool
     */
    protected function isCompleted()
    {
        return $this->done >= $this->total;
    }

    /**
     * Calculates and updates the remaining records to be migrated.
     * 
     * @return void
     */
    protected function setRemains()
    {
        $this->remains = $this->total - $this->done;
    }

    /**
     * Retrieves the number of remaining records in the migration process.
     *
     * @return int
     */
    public function getRemains()
    {
        return $this->remains;
    }

    /**
     * Handles AJAX migration requests, executes migrations, and updates the database version.
     */
    public function data_migrate_action_callback()
    {
        check_ajax_referer('wp_rest', 'wps_nonce');

        if (!Request::isFrom('ajax') || !User::Access('manage')) {
            wp_send_json_error([
                'message' => esc_html__('Unauthorized request or insufficient permissions.', 'wp-statistics')
            ]);
        }

        $migrationInstance = self::getMigration();

        if ($migrationInstance === null) {
            wp_send_json_success([
                'message' => 'All migrations completed.',
                'completed' => true,
            ]);
        }

        $migrationInstance->migrate();
        $migrationInstance->setRemains();

        if ($migrationInstance->isCompleted()) {
            Option::saveOptionGroup('version', self::$version, 'db');

            $nextMigration = self::getMigration();
            
            if ($nextMigration !== null) {
                wp_send_json_success([
                    'message' => 'Moving to next migration version...',
                    'completed' => false,
                ]);
            }

            wp_send_json_success([
                'message' => 'All migrations completed.',
                'completed' => true,
            ]);
        }

        wp_send_json_success([
            'message' => 'Continuing current migration...',
            'completed' => false,
            'remains' => $migrationInstance->getRemains()
        ]);
    }
}
