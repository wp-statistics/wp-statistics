<?php

namespace WP_Statistics\BackgroundProcess\AjaxBackgroundProcess;

use WP_STATISTICS\Option;
use WP_STATISTICS\User;
use WP_Statistics\Utils\Request;

/**
 * Base class for handling background database migrations in an asynchronous manner.
 *
 * This class provides the core structure for running database migrations in the background 
 * via AJAX requests. It ensures that migrations are executed in a controlled manner, preventing 
 * duplication, tracking progress, and marking migrations as completed when finished.
 *
 * It maintains a list of registered migrations, handles the retrieval of pending tasks, 
 * and ensures proper state tracking to resume operations in case of interruptions.
 */
abstract class AbstractAjaxBackgroundProcess
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
     * Holds the current migration class name.
     *
     * @var string|null
     */
    protected static $currentMigration;

    /**
     * Perform the migration. Must be implemented by child classes.
     */
    abstract protected function migrate();

    /**
     * Retrieves the next pending migration instance.
     *
     * @return object|null Returns an instance of the next migration class, or null if no pending migrations exist.
     */
    public static function getMigration()
    {
        $completedMigrations = Option::getOptionGroup('ajax_background_process', 'jobs', []);

        $pendingMigrations = array_diff(array_keys(AjaxBackgroundProcessFactory::$migrations), $completedMigrations);

        if (empty($pendingMigrations)) {
            return null;
        }

        $nextMigrationKey = reset($pendingMigrations);

        self::$currentMigration = AjaxBackgroundProcessFactory::$migrations[$nextMigrationKey];

        return new self::$currentMigration();
    }

    /**
     * Retrieves the full list of migrations or a specific migration by key.
     *
     * @param string|null $key If provided, returns only the migration class for that key.
     * @return string|null Returns a migration class name or null if not found.
     */
    public static function getMigrations($key = null)
    {
        if ($key === null) {
            return AjaxBackgroundProcessFactory::$migrations;
        }

        return AjaxBackgroundProcessFactory::$migrations[$key] ?? null;
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
    protected function getRemains()
    {
        return $this->remains;
    }

    /**
     * Marks a migration as completed and updates the stored jobs.
     *
     * @param string $migrationClassName The completed migration's class name.
     * @return void
     */
    protected function markAsCompleted($migrationClassName)
    {
        $completedMigrations = Option::getOptionGroup('ajax_background_process', 'jobs', []);

        $completedMigrationKey = array_search($migrationClassName, AjaxBackgroundProcessFactory::$migrations, true);

        if ($completedMigrationKey !== false) {
            $completedMigrations[] = $completedMigrationKey;
        }

        $completedMigrations = array_unique($completedMigrations);

        Option::saveOptionGroup('jobs', $completedMigrations, 'ajax_background_process');
    }

    /**
     * Marks the background process as completed.
     *
     * Updates the status in the database to 'done' when all tasks finish.
     *
     * @return void
     */
    protected function markProcessCompleted()
    {
        Option::saveOptionGroup('status', 'done', 'ajax_background_process');
        Option::saveOptionGroup('is_done', true, 'ajax_background_process');
    }

    /**
     * Handles AJAX migration requests, executes migrations, and updates the database tracking.
     */
    public function background_process_action_callback()
    {
        check_ajax_referer('wp_rest', 'wps_nonce');

        if (! Request::isFrom('ajax') || ! User::Access('manage')) {
            wp_send_json_error([
                'message' => esc_html__('Unauthorized request or insufficient permissions.', 'wp-statistics')
            ]);
        }

        $migrationInstance = self::getMigration();

        if ($migrationInstance === null) {
            wp_send_json_success([
                'completed' => true,
            ]);
        }

        $migrationInstance->migrate();
        $migrationInstance->setRemains();

        if ($migrationInstance->isCompleted()) {
            $this->markAsCompleted(get_class($migrationInstance));

            $nextMigration = self::getMigration();

            if ($nextMigration !== null) {
                wp_send_json_success([
                    'completed' => false,
                ]);
            }

            $this->markProcessCompleted();
            wp_send_json_success([
                'completed' => true,
            ]);
        }

        wp_send_json_success([
            'completed' => false,
            'remains' => $migrationInstance->getRemains()
        ]);
    }
}
