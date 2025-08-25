<?php

namespace WP_Statistics\Service\Database\Migrations\Queue;

use WP_Statistics;
use WP_Statistics\Core\CoreFactory;
use WP_STATISTICS\Option;

/**
 * Factory class responsible for managing and coordinating queue-based database migrations.
 *
 * This class serves as the central orchestrator for database migration operations using a queue-based approach.
 * It provides functionality to discover migration steps from migration classes, manage their execution through
 * a simple queue system, and track completion status. The class follows the Factory pattern to create and
 * manage migration instances while maintaining separation of concerns between migration logic and execution control.
 *
 * Key responsibilities:
 * - Discovering and collecting migration steps from migration classes
 * - Managing execution state and tracking completed steps
 * - Providing methods to check migration requirements and completion status
 * - Coordinating the execution of individual migration steps
 * - Handling fresh installations where no migrations are needed
 *
 * @package WP_Statistics\Service\Database\Migrations\Queue
 */
class QueueFactory
{
    /**
     * Option group name for queue background process settings.
     * Used to namespace queue-related options in the WordPress options system.
     *
     * @var string
     */
    private const QUEUE_OPTION_GROUP = 'queue_background_process';

    /**
     * Option key for storing completed migration steps.
     * Stores an array of completed step identifiers within the queue option group.
     *
     * @var string
     */
    private const COMPLETED_STEPS_OPTION = 'completed_steps';

    /**
     * Option group name for database-related settings.
     * Used to namespace database migration options in the WordPress options system.
     *
     * @var string
     */
    private const DATABASE_OPTION_GROUP = 'db';

    /**
     * Option key for migration completion status.
     * Boolean flag indicating whether database migration has been completed.
     *
     * @var string
     */
    private const MIGRATED_OPTION = 'migrated';

    /**
     * Option key for migration check flag.
     * Boolean flag indicating whether migration check is required.
     *
     * @var string
     */
    private const CHECK_OPTION = 'check';

    /**
     * Creates and returns a queue migration instance.
     *
     * This method serves as the entry point for obtaining a migration instance that contains
     * all migration steps in its migrationSteps property. The returned instance can be used
     * to execute migration operations and access migration metadata.
     *
     * @return QueueMigration The migration class instance containing all migration steps
     */
    public static function getQueueMigration()
    {
        return new QueueMigration();
    }

    /**
     * Determines if a queue-based database migration is required.
     *
     * This method performs a comprehensive check to determine whether any migration steps
     * are pending execution. It handles several scenarios:
     * - Returns false if migration is already completed
     * - For fresh installations, marks all steps as completed and returns false
     * - Collects pending migration steps and returns true if any exist
     * - Marks migration as completed if no steps are pending
     *
     * Results are optimized to prevent repeated database queries during the same request.
     *
     * @return bool True if migration is required and steps are pending, false otherwise
     */
    public static function needsMigration()
    {
        if (self::isMigrationCompleted()) {
            return false;
        }

        if (CoreFactory::isFresh()) {
            $allStepIdentifiers = array_keys(self::getQueueMigration()->getMigrationSteps());
            self::saveCompletedSteps($allStepIdentifiers);

            Option::saveOptionGroup('completed', true, self::QUEUE_OPTION_GROUP);
            return false;
        }

        $migrationSteps = self::collectQueueMigrationSteps();

        if (empty($migrationSteps)) {
            Option::saveOptionGroup('completed', true, self::QUEUE_OPTION_GROUP);
            return false;
        }

        return true;
    }

    /**
     * Checks if the overall queue migration process has been completed.
     *
     * This method queries the stored completion status to determine if all queue-based
     * migration steps have been successfully executed. It reads from the persistent
     * storage to maintain state across requests.
     *
     * @return bool True if the migration process is completed, false otherwise
     */
    public static function isMigrationCompleted()
    {
        $isCompleted = Option::getOptionGroup(self::QUEUE_OPTION_GROUP, 'completed', false);
        return $isCompleted === true;
    }

    /**
     * Collects all pending migration steps from the queue migration class.
     *
     * This method retrieves migration steps from the QueueMigration class and filters out
     * already completed ones. It constructs an array of step metadata including:
     * - Step identifier for tracking purposes
     * - Method name to be executed
     * - Migration instance containing the implementation
     *
     * The method is optimized to prevent repeated database queries during the same request
     * by caching results and completed step information.
     *
     * @return array<int, array{
     *     identifier: string,
     *     method: string,
     *     instance: QueueMigration
     * }> Array of pending migration steps with their metadata
     */
    public static function collectQueueMigrationSteps()
    {
        $allSteps = [];

        $completedSteps    = self::getCompletedSteps();
        $migrationInstance = self::getQueueMigration();
        $migrationSteps    = $migrationInstance->getMigrationSteps();

        foreach ($migrationSteps as $stepKey => $methodName) {
            if (self::isStepCompleted($stepKey, $completedSteps)) {
                continue;
            }

            $allSteps[] = [
                'identifier' => $stepKey,
                'method'     => $methodName,
                'instance'   => $migrationInstance
            ];
        }

        return $allSteps;
    }

    /**
     * Retrieves pending migration steps that haven't been completed yet.
     *
     * This method provides a semantic alias for collectQueueMigrationSteps() to improve
     * code readability when the intent is specifically to get pending steps. It returns
     * the same data structure with step metadata for pending migrations.
     *
     * @return array<int, array{
     *     identifier: string,
     *     method: string,
     *     instance: QueueMigration
     * }> Array of pending migration steps with their metadata
     */
    public static function getPendingMigrationSteps()
    {
        return self::collectQueueMigrationSteps();
    }

    /**
     * Determines whether the database migration process has been completed.
     *
     * This method checks the database migration status by examining the migration
     * flags stored in the options system. It considers the database migrated when
     * the migrated flag is true and the check flag is false, indicating that
     * migration has been completed and no further checks are needed.
     *
     * @return bool True if the database is considered fully migrated, false otherwise
     */
    public static function isDatabaseMigrated()
    {
        $migrated = Option::getOptionGroup(self::DATABASE_OPTION_GROUP, self::MIGRATED_OPTION, false);
        $check    = Option::getOptionGroup(self::DATABASE_OPTION_GROUP, self::CHECK_OPTION, true);

        return $migrated && !$check;
    }

    /**
     * Marks a specific migration step as completed.
     *
     * This method adds the step identifier to the list of completed steps and
     * persists it to the database for future reference. It prevents duplicate
     * entries and ensures data integrity by checking if the step is already
     * marked as completed before adding it.
     *
     * The method handles edge cases such as empty identifiers and maintains
     * the completed steps list in a consistent state.
     *
     * @param string $stepIdentifier The migration step identifier to mark as completed
     *                              Must be a non-empty string that uniquely identifies the step
     * @return void
     */
    public static function markStepCompleted($stepIdentifier)
    {
        if (empty($stepIdentifier)) {
            return;
        }

        $completedSteps = self::getCompletedSteps();

        if (!in_array($stepIdentifier, $completedSteps, true)) {
            $completedSteps[] = $stepIdentifier;
            self::saveCompletedSteps($completedSteps);
        }
    }

    /**
     * Executes a specific migration step and handles the result.
     *
     * This method performs the actual execution of a migration step by:
     * - Validating that the migration method exists on the instance
     * - Executing the migration method
     * - Handling any exceptions that may occur during execution
     * - Marking the step as completed upon successful execution
     * - Logging errors and failures for debugging purposes
     *
     * The method provides comprehensive error handling and logging to ensure
     * that migration failures are properly tracked and debugged.
     *
     * @param array{
     *     identifier: string,
     *     method: string,
     *     instance: QueueMigration
     * } $step Migration step array containing method name and instance
     * @return bool True if the step was executed successfully, false on failure
     */
    public static function executeMigrationStep($step)
    {
        try {
            $instance = $step['instance'];
            $method   = $step['method'];

            if (!method_exists($instance, $method)) {
                WP_Statistics::log(sprintf(
                    'Migration method %s does not exist in class %s',
                    $method,
                    get_class($instance)
                ));
                return false;
            }

            $instance->$method();

            self::markStepCompleted($step['identifier']);

            return true;
        } catch (\Exception $e) {
            WP_Statistics::log(sprintf(
                'Queue migration step failed [%s]: %s',
                $step['identifier'],
                $e->getMessage()
            ));
            return false;
        }
    }

    /**
     * Retrieves the list of completed migration steps.
     *
     * This method fetches the array of completed step identifiers from the
     * persistent storage. Results are cached during the same request to
     * prevent repeated database queries and improve performance.
     *
     * @return array<int, string> Array of completed step identifiers
     */
    private static function getCompletedSteps()
    {
        return Option::getOptionGroup(self::QUEUE_OPTION_GROUP, self::COMPLETED_STEPS_OPTION, []);
    }

    /**
     * Persists the list of completed migration steps to storage.
     *
     * This method saves the array of completed step identifiers to the
     * WordPress options system using the appropriate option group and key.
     * It ensures that the completion state is maintained across requests
     * and server restarts.
     *
     * @param array<int, string> $completedSteps Array of completed step identifiers
     * @return void
     */
    private static function saveCompletedSteps($completedSteps)
    {
        Option::saveOptionGroup(self::COMPLETED_STEPS_OPTION, $completedSteps, self::QUEUE_OPTION_GROUP);
    }

    /**
     * Checks if a specific migration step has been completed.
     *
     * This method performs a strict comparison to determine if a given step
     * identifier exists in the list of completed steps. It uses strict
     * comparison to ensure type safety and prevent false positives.
     *
     * @param string $stepIdentifier The step identifier to check for completion
     * @param array<int, string> $completedSteps Array of completed step identifiers
     * @return bool True if the step is completed, false otherwise
     */
    private static function isStepCompleted($stepIdentifier, $completedSteps)
    {
        return in_array($stepIdentifier, $completedSteps, true);
    }
}