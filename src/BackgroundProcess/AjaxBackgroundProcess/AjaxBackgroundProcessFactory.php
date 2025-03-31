<?php

namespace WP_Statistics\BackgroundProcess\AjaxBackgroundProcess;

use WP_Statistics\BackgroundProcess\AjaxBackgroundProcess\Jobs\ResourceMigrator;
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
        'resource_migrate' => ResourceMigrator::class,
    ];

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

        $isMigrated = Option::getOptionGroup('db', 'migrated', false) && !Option::getOptionGroup('db', 'check', true);

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
}
