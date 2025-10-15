<?php

namespace WP_Statistics\Service\Database\Migrations\BackgroundProcess;

use WP_Statistics\Core\CoreFactory;
use WP_STATISTICS\Option;

/**
 * Factory class to get background process instances.
 *
 * @package WP_Statistics\Service\Database\Migrations\BackgroundProcess
 */
class BackgroundProcessFactory
{
    /**
     * Get a background process instance by its key.
     *
     * @param string $processKey The key identifying the background process.
     *
     * @return object|null The background process instance or null if not found.
     */
    public static function getBackgroundProcess($processKey)
    {
        return (new BackgroundProcessManager())->getBackgroundProcess($processKey);
    }

    /**
     * Get the admin-post action name used to trigger background processes.
     *
     * @return string Action name (hook suffix) for admin-post.
     */
    public static function getActionName()
    {
        return BackgroundProcessManager::BACKGROUND_PROCESS_ACTION;
    }

    /**
     * Create a nonce for the background process action.
     *
     * @return string Nonce string tied to BACKGROUND_PROCESS_NONCE.
     */
    public static function getActionNonce()
    {
        return wp_create_nonce(BackgroundProcessManager::BACKGROUND_PROCESS_NONCE);
    }

    /**
     * Check the initiation state of a background process.
     *
     * Note: Currently returns whether the job has been initiated; returns null if
     * the key is unknown. Adjust the underlying job method if a true "done"
     * state is required.
     *
     * @param string $processKey Background process key.
     * @return bool|null True if initiated, false if not initiated, null if job not found.
     */
    public static function isProcessDone($processKey)
    {
        $job = self::getBackgroundProcess($processKey);

        if (empty($job)) {
            return;
        }

        return $job->isInitiated() && !$job->is_active();
    }

    /**
     * Get all registered background migration jobs.
     *
     * @return array
     */
    public static function getAllJobs()
    {
        return (new BackgroundProcessManager())->getAllBackgroundProcesses();
    }

    /**
     * Get all available data migrations (distinct from background jobs list).
     *
     * @return array
     */
    public static function getAllMigrations()
    {
        return (new BackgroundProcessManager())->getAllDataMirations();
    }

    /**
     * Mark all registered background migration processes as "initiated".
     *
     * @return void
     */
    public static function markBackgroundProcessesAsInitiated()
    {
        Option::deleteOptionGroup('data_migration_process_started', 'jobs');

        if (!CoreFactory::isFresh()) {
            return;
        }

        $jobs = self::getAllJobs();

        foreach ($jobs as $key => $job) {
            if (!class_exists($job)) {
                continue;
            }

            $jobKey = (new $job())->getInitiatedKey();

            Option::saveOptionGroup($jobKey, true, 'jobs');
        }
    }
}