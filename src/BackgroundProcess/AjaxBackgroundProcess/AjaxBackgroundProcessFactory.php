<?php

namespace WP_Statistics\BackgroundProcess\AjaxBackgroundProcess;

use WP_Statistics\BackgroundProcess\AjaxBackgroundProcess\Jobs\VisitorColumnsMigrator;
use WP_STATISTICS\Install;
use WP_STATISTICS\Option;

/**
 * Factory class responsible for managing and coordinating background database migrations.
 *
 * This class determines whether a migration is required based on the system state, tracks
 * completed migrations, and provides access to the next migration process that needs execution.
 *
 * It ensures that only necessary migrations are executed while skipping completed tasks.
 * The factory serves as a bridge between migration processes and the background process manager.
 */
class AjaxBackgroundProcessFactory
{
    /**
     * List of available migrations.
     *
     * @var array
     */
    public static $migrations = [
        'visitor_columns_migrate' => VisitorColumnsMigrator::class,
    ];

    /**
     * Cached list of completed migration job keys.
     *
     * @var array
     */
    private static $doneJobs = [];

    /**
     * Checks if a database migration is required.
     *
     * @return bool
     */
    public static function needsMigration()
    {
        if (!class_exists(AbstractAjaxBackgroundProcess::class)) {
            return;
        }

        if (Install::isFresh()) {
            return;
        }

        $isMigrated = self::isDatabaseMigrated();

        if (!$isMigrated) {
            return;
        }

        $isDone = Option::getOptionGroup('ajax_background_process', 'is_done', false);

        if ($isDone) {
            return;
        }

        $completedMigrations = Option::getOptionGroup('ajax_background_process', 'jobs', []);

        $registeredMigrations = array_keys(self::$migrations);

        return !empty(array_diff($registeredMigrations, $completedMigrations));
    }

    /**
     * Retrieves a specific migration instance or all migrations.
     *
     * @param string|null $key The migration key to retrieve. If null, returns all migrations.
     * @return mixed The migration instance or an array of all migrations.
     */
    public static function migrate($key = null)
    {
        return AbstractAjaxBackgroundProcess::getMigrations($key);
    }

    /**
     * Retrieves the currently required migration instance.
     *
     * @return mixed The current migration instance.
     */
    public static function getCurrentMigrate()
    {
        return AbstractAjaxBackgroundProcess::getMigration();
    }

    /**
     * Checks whether a specific migration job has been marked as completed.
     *
     * @param string $key The migration job key to check.
     * @return bool True if the migration job is completed, false otherwise.
     */
    public static function isDataMigrated($key)
    {
        $isFresh = get_option('wp_statistics_is_fresh', false);
        $jobs    = Option::getOptionGroup('ajax_background_process', 'jobs', []);

        if ($isFresh) {
            if (empty($jobs)) {
                $jobs = array_keys(self::$migrations);
                Option::saveOptionGroup('jobs', $jobs, 'ajax_background_process');
            }

            return true;
        }

        $isDone = Option::getOptionGroup('ajax_background_process', 'is_done', false);

        if ($isDone) {
            return true;
        }

        self::$doneJobs = ! empty(self::$doneJobs) ? self::$doneJobs : $jobs;

        if (empty(self::$doneJobs)) {
            return false;
        }

        return in_array($key, self::$doneJobs, true);
    }

    /**
     * Determines whether the database migration has been completed.
     *
     * This method checks that the 'migrated' value in the 'db' option group is set to true
     * and that the 'check' value is false.
     *
     * @return bool True if the database is considered migrated, false otherwise.
     */
    public static function isDatabaseMigrated()
    {
        $migrated = Option::getOptionGroup('db', 'migrated', false);
        $check    = Option::getOptionGroup('db', 'check', true);
        return $migrated && !$check;
    }
}
