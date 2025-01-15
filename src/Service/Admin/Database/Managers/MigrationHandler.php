<?php

namespace WP_Statistics\Service\Admin\Database\Managers;

use WP_STATISTICS\Option;
use WP_Statistics\Service\Admin\Database\DatabaseFactory;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;
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
        add_action('admin_post_' . self::MIGRATION_ACTION, [self::class, 'processManualMigrations']);

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
        self::showManualMigrationNotice();

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
        return Option::getOptionGroup('db', 'migrated', false) || Option::getOptionGroup('db', 'check', false);
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

        foreach (DatabaseFactory::Migration() as $instance) {
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
        $manualTasks = Option::getOptionGroup('db', 'manual_migration_tasks', []);

        if (! empty($manualTasks)) {
            Option::saveOptionGroup('migrated', true, 'db');
        }

        foreach ($versions as $version) {
            $migrations       = $mappings[$version];
            $hasDataMigration = self::hasDataMigration($migrations);

            foreach ($migrations as $migration) {
                self::processMigrationMethods(
                    $version,
                    $migration,
                    $hasDataMigration,
                    $manualTasks,
                    $process
                );
            }
        }

        Option::saveOptionGroup('manual_migration_tasks', $manualTasks, 'db');
    }

    /**
     * Check if any migrations include data migrations.
     *
     * @param array $migrations List of migrations.
     * @return bool Returns true if any data migrations exist.
     */
    private static function hasDataMigration($migrations)
    {
        foreach ($migrations as $migration) {
            if ($migration['type'] === 'data') {
                return true;
            }
        }
        return false;
    }

    /**
     * Process migration methods for a specific version.
     *
     * @param string $version Current migration version.
     * @param array $migration Migration methods for the version.
     * @param bool $hasDataMigration Indicates if data migrations are involved.
     * @param array $manualTasks Existing manual tasks.
     * @param mixed $process Background process instance.
     * @return void
     */
    private static function processMigrationMethods(
        $version,
        $migration,
        $hasDataMigration,
        &$manualTasks,
        $process
    ) {
        foreach ($migration['methods'] as $method) {
            if ($hasDataMigration || !empty($manualTasks)) {
                $manualTasks[$version][$method] = [
                    'class' => $migration['class'],
                    'type' => $migration['type']
                ];
            } else {
                $process->push_to_queue([
                    'class' => $migration['class'],
                    'method' => $method,
                    'version' => $version,
                ]);
            }
        }
    }

    /**
     * Finalize the migration process by saving options and dispatching tasks.
     *
     * @param mixed $process Background process instance.
     * @return void
     */
    private static function finalizeMigrationProcess($process)
    {
        Option::saveOptionGroup('schema_migration_process_started', true, 'jobs');
        $process->save()->dispatch();
    }

    /**
     * Display a notice in the admin panel if manual migrations are required.
     *
     * @return void
     */
    public static function showManualMigrationNotice()
    {
        $manualTasks = Option::getOptionGroup('db', 'manual_migration_tasks', []);
        if (empty($manualTasks)) {
            return;
        }

        $details = Option::getOptionGroup('db', 'migration_status_detail', null);

        if (! empty($details['status']) && 'failed' === $details['status']) {
            return;
        }

        $message = self::buildNoticeMessage();

        Notice::addNotice($message, 'database_manual_migration', 'warning', false);
    }

    /**
     * Build a URL for initiating manual migrations.
     *
     * @return string URL for manual migrations.
     */
    private static function buildActionUrl()
    {
        return add_query_arg(
            [
                'action' => self::MIGRATION_ACTION,
                'nonce' => wp_create_nonce(self::MIGRATION_NONCE)
            ],
            admin_url('admin-post.php')
        );
    }

    /**
     * Build the complete notice message HTML.
     *
     * @todo The docs link should be updated.
     * @return string Notice message HTML.
     */
    private static function buildNoticeMessage()
    {
        $actionUrl = self::buildActionUrl();
        $documentationUrl = 'https://veronalabs.com/';

        return sprintf(
            '<p>
                <strong>%1$s</strong>
                </br>%2$s
                </br>%3$s
                </br><a href="%4$s" class="button button-primary">%5$s</a>
                <a href="%6$s" target="_blank" style="margin-left: 10px">%7$s</a>
            </p>',
            esc_html__('Action Required: Upgrade Needed for WP Statistics', 'wp-statistics'),
            esc_html__('The Database Upgrade process needs to be run to ensure WP Statistics works seamlessly with the latest updates.', 'wp-statistics'),
            esc_html__('Running this process will [specific benefits, e.g., “add new features” or “optimize data”].', 'wp-statistics'),
            esc_url($actionUrl),
            esc_html__('Run Process Now', 'wp-statistics'),
            esc_url($documentationUrl),
            esc_html__('For more details, see our documentation.', 'wp-statistics')
        );
    }

    /**
     * Process manual migration tasks when the action is triggered.
     *
     * @return void
     */
    public static function processManualMigrations()
    {
        if (!self::validateMigrationRequest()) {
            return;
        }

        $manualTasks = Option::getOptionGroup('db', 'manual_migration_tasks', []);
        if (empty($manualTasks)) {
            return;
        }

        $process = WP_Statistics()->getBackgroundProcess('data_migration_process');
        if ($process->is_active()) {
            return;
        }

        self::processManualTasks($manualTasks, $process);
        self::handleRedirect();
    }

    /**
     * Validate the incoming manual migration request.
     *
     * @return bool Returns true if the request is valid.
     */
    private static function validateMigrationRequest()
    {
        if (!Request::compare('action', self::MIGRATION_ACTION)) {
            return false;
        }
        check_admin_referer(self::MIGRATION_NONCE, 'nonce');
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

        if ($info['type'] === 'data') {
            self::processDataTask($instance, $method, $version, $info['type'], $process);
        } elseif ($info['type'] === 'schema') {
            self::processSchemaTask($instance, $method, $version, $info['type'], $process);
        }
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
     * Process data-related migration tasks.
     *
     * @param object $instance Migration class instance.
     * @param string $method Migration method to call.
     * @param string $version Task version.
     * @param string $type Task type.
     * @param mixed $process Background process instance.
     * @return void
     */
    private static function processDataTask($instance, $method, $version, $type, $process)
    {
        if (!method_exists($instance, $method)) {
            return;
        }

        $tasks = $instance->$method();
        if (!is_array($tasks)) {
            return;
        }

        foreach ($tasks as $task) {
            $process->push_to_queue([
                'class' => get_class($instance),
                'method' => $method,
                'version' => $version,
                'task' => $task,
                'type' => $type
            ]);
        }
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
    private static function handleMigrationStatusNotices()
    {
        $details = Option::getOptionGroup('db', 'migration_status_detail', null);

        if (empty($details['status'])) {
            return;
        }

        $status = $details['status'];

        if ($status === 'progress') {
            $message = sprintf(
                '
                    <p>
                        <strong>%1$s</strong>
                        </br>%2$s
                        </br>%3$s
                    </p>
                ',
                esc_html__('WP Statistics: Process Running', 'wp-statistics'),
                esc_html__('The Database Migration process is running in the background. This may take a few minutes depending on your site’s data size.', 'wp-statistics'),
                esc_html__('Please wait while the process completes. You can continue working in the admin area.', 'wp-statistics')
            );

            Notice::addNotice($message, 'database_manual_migration_progress', 'info', false);
            return;
        }

        if ($status === 'done') {
            $message = sprintf(
                '
                    <p>
                        <strong>%1$s</strong>
                        </br>%2$s
                        </br><strong>%3$s</strong>
                        </br>%4$s
                    </p>
                ',
                esc_html__('WP Statistics: Process Complete', 'wp-statistics'),
                esc_html__('The Database Migration process has been completed successfully.', 'wp-statistics'),
                esc_html__('Your WP Statistics plugin is now fully updated and optimized. 🎉', 'wp-statistics'),
                esc_html__('Thank you for keeping WP Statistics up-to-date!', 'wp-statistics')
            );

            Notice::addNotice($message, 'database_manual_migration_done', 'success');
            return;
        }

        if ($status === 'failed') {
            $message = sprintf(
                '
                    <p>
                        <strong>%1$s</strong>
                        </br>%2$s
                        </br><strong>%3$s</strong> %4$s
                        </br><a href="%5$s">%6$s</a>
                    </p>
                ',
                esc_html__('WP Statistics: Process Failed', 'wp-statistics'),
                esc_html__('The Database Migration process encountered an error and could not be completed.', 'wp-statistics'),
                esc_html__('Error:', 'wp-statistics'),
                esc_html($details['message'] ?? ''),
                esc_url('https://wp-statistics.com/support/?utm_source=wp-statistics&utm_medium=link&utm_campaign=db-error'),
                esc_html__('Contact Support', 'wp-statistics')
            );

            Notice::addNotice($message, 'database_manual_migration_failed', 'error', false);
        }
    }
}
