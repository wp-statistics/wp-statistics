<?php

namespace WP_Statistics\Service\Database\Managers;

use WP_Statistics\BackgroundProcess\AsyncBackgroundProcess\BackgroundProcessMonitor;
use WP_STATISTICS\Option;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;
use WP_Statistics\Service\Database\DatabaseFactory;
use WP_Statistics\Utils\Request;

/**
 * Handles database migration processes, including schema migrations
 * and manual migration tasks.
 *
 * This class provides methods to initialize migration hooks, run migrations,
 * display notices for pending manual migrations, and process manual migration tasks.
 */
class MigrationHandler
{
    /**
     * Action for triggering manual migration.
     * @var string
     */
    private const MIGRATION_ACTION = 'run_manual_migration';

    /**
     * Action for triggering retry manual migration.
     * @var string
     */
    private const MIGRATION_RETRY_ACTION = 'retry_manual_migration';

    /**
     * Nonce name for manual migration action.
     * @var string
     */
    private const MIGRATION_NONCE = 'run_manual_migration_nonce';

    /**
     * Initialize migration processes and register WordPress hooks.
     *
     * @return void
     */
    public static function init()
    {
        add_action('admin_post_' . self::MIGRATION_RETRY_ACTION, [self::class, 'retryManualMigration']);
        add_action( 'admin_init', [self::class, 'handleMigrationEvents']);
    }

    /**
     * Handle migration events and status notices.
     *
     * This method runs pending database migration tasks and then displays
     * any relevant notices about migration progress or failure.
     *
     * @return void
     */
    public static function handleMigrationEvents() {
        self::handleMigrationStatusNotices();
        self::runMigrations();
    }

    /**
     * Run schema migrations and prepare manual migration notices if required.
     *
     * @return void
     */
    public static function runMigrations()
    {
        if (self::isMigrationComplete()) {
            return;
        }

        $process = WP_Statistics()->getBackgroundProcess('schema_migration_process');
        if ($process->is_active()) {
            return;
        }

        $migrationData = self::collectMigrationData();
        self::processMigrations($migrationData['versions'], $migrationData['mappings'], $process);

        self::finalizeMigrationProcess($process);
    }

    /**
     * Check if all migrations have been completed.
     *
     * @return bool Returns true if migrations are complete.
     */
    private static function isMigrationComplete()
    {
        return Option::getOptionGroup('db', 'migrated', false) || Option::getOptionGroup('db', 'check', true);
    }

    /**
     * Collect and prepare data for migration tasks.
     *
     * @return array Contains versions and their respective mappings.
     */
    private static function collectMigrationData()
    {
        $currentVersion  = Option::getOptionGroup('db', 'version', '0.0.0');
        $allVersions     = [];
        $versionMappings = [];

        foreach (DatabaseFactory::migration() as $instance) {
            foreach ($instance->getMigrationSteps() as $version => $methods) {
                if (version_compare($currentVersion, $version, '>=')) {
                    continue;
                }

                $allVersions[] = $version;

                $versionMappings[$version][] = [
                    'class' => get_class($instance),
                    'methods' => $methods,
                    'type' => $instance->getName()
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
     * Process migrations for each version based on provided data.
     *
     * @param array $versions List of versions to process.
     * @param array $mappings Mappings of versions to migration methods.
     * @param mixed $process Background process instance.
     * @return void
     */
    private static function processMigrations($versions, $mappings, $process)
    {
        foreach ($versions as $version) {
            $migrations = $mappings[$version];

            foreach ($migrations as $migration) {
                self::processMigrationMethods(
                    $version,
                    $migration,
                    $process
                );
            }
        }
    }

    /**
     * Process migration methods for a specific version.
     *
     * @param string $version Current migration version.
     * @param array $migration Migration methods for the version.
     * @param mixed $process Background process instance.
     * @return void
     */
    private static function processMigrationMethods( $version, $migration, $process ) {
        $autoMigrationTasks = Option::getOptionGroup('db', 'auto_migration_tasks', []);

        foreach ($migration['methods'] as $method) {
            $taskKey = $method . '_' . $version;

            if (! empty($autoMigrationTasks[$taskKey])) {
                continue;
            }

            $autoMigrationTasks[$taskKey] = true;

            $process->push_to_queue([
                'class' => $migration['class'],
                'method' => $method,
                'version' => $version,
            ]);
    }

        Option::saveOptionGroup('auto_migration_tasks', $autoMigrationTasks, 'db');
    }

    /**
     * Finalize the migration process by saving options and dispatching tasks.
     *
     * @param mixed $process Background process instance.
     * @return void
     */
    private static function finalizeMigrationProcess($process)
    {
        Option::saveOptionGroup('is_done', null, 'ajax_background_process');

        Option::saveOptionGroup('schema_migration_process_started', true, 'jobs');
        $process->save()->dispatch();
    }

    /**
     * Build a URL for initiating manual migrations.
     *
     * @return string URL for manual migrations.
     */
    private static function buildActionUrl($type = '')
    {
        $action = self::MIGRATION_ACTION;

        if ($type === 'retry') {
            $action = self::MIGRATION_RETRY_ACTION;
        }

        return add_query_arg(
            [
                'action' => $action,
                'nonce' => wp_create_nonce(self::MIGRATION_NONCE)
            ],
            admin_url('admin-post.php')
        );
    }

    /**
     * Retries the manual migration process.
     *
     * @return void
     */
    public static function retryManualMigration()
    {
        if (!self::validateMigrationRequest('retry')) {
            self::handleRedirect();
            return;
        }

        $schemaProcess = WP_Statistics()->getBackgroundProcess('schema_migration_process');
        $schemaProcess->stopProcess();

        if ($schemaProcess->is_active()) {
            self::handleRedirect();
            return;
        }

        $migrationData = self::collectMigrationData();
        self::processMigrations($migrationData['versions'], $migrationData['mappings'], $schemaProcess);

        $manualTasks = Option::getOptionGroup('db', 'manual_migration_tasks', []);

        if (empty($manualTasks)) {
            self::handleRedirect();
            return;
        }

        $dataProcess = WP_Statistics()->getBackgroundProcess('data_migration_process');
        $dataProcess->stopProcess();

        if ($dataProcess->is_active()) {
            self::handleRedirect();
            return;
        }

        self::processManualTasks($manualTasks, $dataProcess);
        self::handleRedirect();
    }

    /**
     * Validate the incoming manual migration request.
     *
     * @return bool Returns true if the request is valid.
     */
    private static function validateMigrationRequest($type = '')
    {
        $action = self::MIGRATION_ACTION;

        if ($type === 'retry') {
            $action = self::MIGRATION_RETRY_ACTION;
        }

        if (!Request::compare('action', $action)) {
            return false;
        }

        check_admin_referer(self::MIGRATION_NONCE, 'nonce');

        Option::saveOptionGroup('migration_status_detail', [
            'status' => 'progress'
        ], 'db');

        return true;
    }

    /**
     * Process all manual migration tasks.
     *
     * @param array $manualTasks Pending manual tasks.
     * @param mixed $process Background process instance.
     * @return void
     */
    private static function processManualTasks($manualTasks, $process)
    {
        foreach ($manualTasks as $version => $methods) {
            foreach ($methods as $method => $info) {
                self::processIndividualTask($version, $method, $info, $process);
            }
            unset($manualTasks[$version]);
        }

        self::finalizeManualTasks($manualTasks, $process);
    }

    /**
     * Process a single migration task.
     *
     * @param string $version Task version.
     * @param string $method Task method.
     * @param array $info Task information.
     * @param mixed $process Background process instance.
     * @return void
     */
    private static function processIndividualTask($version, $method, $info, $process)
    {
        $instance = self::createMigrationInstance($info['class']);
        if (!$instance) {
            return;
        }

        self::processSchemaTask($instance, $method, $version, $info['type'], $process);
    }

    /**
     * Create an instance of the migration class.
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

    /**
     * Process schema-related migration tasks.
     *
     * @param object $instance Migration class instance.
     * @param string $method Migration method to call.
     * @param string $version Task version.
     * @param string $type Task type.
     * @param mixed $process Background process instance.
     * @return void
     */
    private static function processSchemaTask($instance, $method, $version, $type, $process)
    {
        $process->push_to_queue([
            'class' => get_class($instance),
            'method' => $method,
            'version' => $version,
            'task' => '',
            'type' => $type
        ]);
    }

    /**
     * Finalize the manual migration tasks.
     *
     * @param array $manualTasks Updated manual tasks list.
     * @param mixed $process Background process instance.
     * @return void
     */
    private static function finalizeManualTasks($manualTasks, $process)
    {
        @ini_set('memory_limit', '-1');
        Option::saveOptionGroup('manual_migration_tasks', $manualTasks, 'db');
        Option::saveOptionGroup('data_migration_process_started', true, 'jobs');
        $process->save()->dispatch();
    }

    /**
     * Handle the redirect after processing manual migrations.
     *
     * @return void
     */
    private static function handleRedirect()
    {
        $referer = wp_get_referer();
        wp_redirect($referer ?: admin_url());
        exit;
    }

    /**
     * Handles displaying notices based on the current migration status.
     *
     * This method checks the `migration_status_detail` option to determine the
     * current status of the migration process. It displays appropriate notices
     * for progress, completion, or failure of the migration.
     *
     * @return void
     */
    public static function handleMigrationStatusNotices()
    {
        $details = Option::getOptionGroup('db', 'migration_status_detail', null);

        if (empty($details['status'])) {
            return;
        }

        $status = $details['status'];

        if ($status === 'failed') {
            BackgroundProcessMonitor::deleteOption('data_migration_process');
            
            $actionUrl = self::buildActionUrl('retry');

            $message = sprintf(
                '
                    <p>
                        <strong>%1$s</strong>
                        </br>%2$s
                        </br><strong>%3$s</strong> %4$s
                        </br><a href="%5$s" class="button button-primary" style="margin-top: 10px;">%6$s</a>
                        <a href="%7$s" style="margin: 10px" target="_blank">%8$s</a>
                    </p>
                ',
                esc_html__('WP Statistics: Process Failed', 'wp-statistics'),
                esc_html__('The Database Migration process encountered an error and could not be completed.', 'wp-statistics'),
                esc_html__('Error:', 'wp-statistics'),
                esc_html($details['message'] ?? ''),
                esc_url($actionUrl),
                esc_html__('Retry Process', 'wp-statistics'),
                esc_url('https://wp-statistics.com/support/?utm_source=wp-statistics&utm_medium=link&utm_campaign=db-error'),
                esc_html__('Contact Support', 'wp-statistics')
            );

            Notice::addNotice($message, 'database_manual_migration_failed', 'error', false);
        }
    }
}
