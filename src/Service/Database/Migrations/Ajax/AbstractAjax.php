<?php

namespace WP_Statistics\Service\Database\Migrations\Ajax;

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
abstract class AbstractAjax
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
     * Percentage of records processed (0-100).
     *
     * @var int
     */
    protected $percentage = 0;

    /**
     * Holds the current process class name.
     *
     * @var string|null
     */
    protected static $currentProcess;

    /**
     * Holds the key that uniquely identifies the current process.
     *
     * @var string|null
     */
    protected static $currentProcessKey;

    /**
     * Cached 'totals' section from the 'ajax_background_process' option group.
     *
     * This holds per-migration total counts to avoid repeated database reads during the background process.
     *
     * Example: ['visitor_columns_migrate' => 1234]
     *
     * @var array|null
     */
    protected $cachedProcessTotals = null;

    /**
     * Cached 'attempts' section from the 'ajax_background_process' option group.
     *
     * This stores the number of attempts made for each migration key to help
     * prevent infinite retries or stuck processes. Used to track how many times
     * a background migration process has executed.
     *
     * Example: ['visitor_columns_migrate' => 3]
     *
     * @var array|null
     */
    protected $cachedProcessAttempts = null;

    /**
     * Calculates the total number of records to migrate.
     *
     * @param bool $needCaching Whether to load/save the total from cache. Defaults to true.
     * @return void
     */
    abstract protected function getTotal($needCaching = true);

    /**
     * Calculates how many records have already been processed and sets the offset.
     *
     * @return void
     */
    abstract protected function calculateOffset();

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

        $pendingMigrations = array_diff(array_keys(AjaxFactory::$migrations), $completedMigrations);

        if (empty($pendingMigrations)) {
            return null;
        }

        $nextMigrationKey = reset($pendingMigrations);

        self::$currentProcessKey = $nextMigrationKey;
        self::$currentProcess    = AjaxFactory::$migrations[$nextMigrationKey];

        if (!class_exists(self::$currentProcess)) {
            return null;
        }

        if (method_exists(self::$currentProcess, 'isAlreadyDone')) {
            return (new self::$currentProcess())->isAlreadyDone() ? null : new self::$currentProcess();
        }

        return new self::$currentProcess();
    }

    /**
     * Retrieves a previously saved attempt count by its key from the 'ajax_background_process' option group.
     *
     * This is used to track how many times a particular migration has been attempted,
     * which can help prevent infinite loops in case of persistent failures.
     *
     * @param string $key The key associated with the attempt count.
     * @return int The cached attempt count, or 0 if not found.
     */
    protected function getCachedAttempts($key = '')
    {
        if (empty($key)) {
            $key = self::$currentProcessKey;
        }

        if ($this->cachedProcessAttempts === null) {
            $this->cachedProcessAttempts = Option::getOptionGroup('ajax_background_process', 'attempts', []);
        }

        return isset($this->cachedProcessAttempts[$key]) ? (int)$this->cachedProcessAttempts[$key] : 0;
    }

    /**
     * Stores an attempt count under the 'attempts' key in the 'ajax_background_process' option group.
     *
     * This allows the background process to persist the number of attempts between executions,
     * ensuring the process can detect excessive retries and exit gracefully.
     *
     * @param string $key The unique key to associate with the attempt count.
     * @param int $count The number of attempts to store.
     * @return void
     */
    protected function saveAttempts($key, $count)
    {
        $meta       = Option::getOptionGroup('ajax_background_process', 'attempts', []);
        $meta[$key] = $count;

        Option::saveOptionGroup('attempts', $meta, 'ajax_background_process');
    }

    /**
     * Increments and returns the number of attempts for the current migration process.
     *
     * This is used to count how many times a process has executed and can be used to prevent
     * infinite loops or excessive processing if the process becomes stuck.
     *
     * @return int The updated attempt count after incrementing.
     */
    protected function trackAttempts()
    {
        $key     = self::$currentProcessKey;
        $current = $this->getCachedAttempts($key);
        $current++;

        $this->saveAttempts($key, $current);
        return $current;
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
            return AjaxFactory::$migrations;
        }

        return AjaxFactory::$migrations[$key] ?? null;
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
     * Sets the batch size for processing.
     *
     * @param int $size Number of records to process per batch.
     * @return void
     */
    protected function setBatchSize($size)
    {
        if (is_int($size) && $size > 0) {
            $this->batchSize = $size;
        }
    }

    /**
     * Updates the cached total for the current process from the request.
     *
     * Retrieves the 'total' parameter from the current request and, if a value is provided,
     * stores it in the cachedProcessTotals array under the key defined in the current process.
     *
     * This helps ensure that the process's cached total is updated without having to
     * re-read the option group, reducing extra database lookups.
     *
     * @return void
     */
    protected function setCachedTotal()
    {
        $total = Request::get('total', 0);

        if (empty($total)) {
            return;
        }


        if (empty($this->cachedProcessTotals[self::$currentProcessKey])) {
            $this->cachedProcessTotals[self::$currentProcessKey] = $total;
        }
    }

    /**
     * Calculates and updates the remaining records to be migrated as well as the percentage complete.
     *
     * This method calculates the percentage of records processed based on the total and done counts,
     * and also updates the remains count.
     *
     * @return void
     */
    protected function setCalculatedPercentage()
    {
        if ($this->total > 0) {
            $this->percentage = round(($this->done / $this->total) * 100);
            return;
        }

        $this->percentage = 0;
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
     * Retrieves the calculated percentage of records processed in the migration process.
     *
     * @return int The percentage of records processed.
     */
    protected function getCalculatedPercentage()
    {
        return $this->percentage;
    }

    /**
     * Retrieves a previously saved total value by its key from the 'ajax_background_process' option group.
     *
     * Returns the stored total if it exists, or false if not found.
     *
     * @param string $key The key associated with the total value.
     * @return int The cached total value, or 0 if not found.
     */
    protected function getCachedTotal($key = '')
    {
        if (empty($key)) {
            $key = self::$currentProcessKey;
        }

        if ($this->cachedProcessTotals === null) {
            $this->cachedProcessTotals = Option::getOptionGroup('ajax_background_process', 'totals', []);
        }

        if (!empty($this->cachedProcessTotals[$key])) {
            return (int)$this->cachedProcessTotals[$key];
        }

        return false;
    }

    /**
     * Stores a total value under the 'totals' key in the 'ajax_background_process' option group.
     *
     * This allows background processes to persist total counts between executions
     * without recalculating them each time.
     *
     * @param string $key The unique key to associate with the total value.
     * @param int $total The total value to store.
     * @return void
     */
    protected function saveTotal($key, $total)
    {
        $meta = Option::getOptionGroup('ajax_background_process', 'totals', []);

        $meta[$key] = $total;

        Option::saveOptionGroup('totals', $meta, 'ajax_background_process');
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

        $completedMigrationKey = array_search($migrationClassName, AjaxFactory::$migrations, true);

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

        if (!Request::isFrom('ajax') || !User::Access('manage')) {
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

        $migrationInstance->setCachedTotal();
        $migrationInstance->migrate();
        $migrationInstance->setCalculatedPercentage();

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
            'completed'  => false,
            'percentage' => $migrationInstance->getCalculatedPercentage(),
            'total'      => $migrationInstance->getCachedTotal()
        ]);
    }
}
