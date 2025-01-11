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
        return Option::get('db_migrated', false) || Option::get('check_database', false);
    }

    /**
     * Collect and prepare data for migration tasks.
     *
     * @return array Contains versions and their respective mappings.
     */
    private static function collectMigrationData()
    {
        $currentVersion = Option::get('db_version', '0.0.0');
        $allVersions = [];
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
        return ['versions' => $allVersions, 'mappings' => $versionMappings];
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
        $manualTasks = Option::get('manual_migration_tasks', []);

        foreach ($versions as $version) {
            $migrations = $mappings[$version];
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

        Option::update('manual_migration_tasks', $manualTasks);
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
        $manualTasks = Option::get('manual_migration_tasks', []);
        if (empty($manualTasks)) {
            return;
        }

        $taskList = self::buildTaskList($manualTasks);
        $actionUrl = self::buildActionUrl();
        $message = self::buildNoticeMessage($taskList, $actionUrl);

        Notice::addNotice($message, 'database_manual_migration', 'warning');
    }

    /**
     * Build an HTML list of pending tasks.
     *
     * @param array $manualTasks Pending manual tasks.
     * @return string HTML list of tasks.
     */
    private static function buildTaskList($manualTasks)
    {
        $taskList = '';
        foreach ($manualTasks as $version => $methods) {
            foreach ($methods as $method => $info) {
                $taskList .= sprintf(
                    '<li>%s - %s (%s) => Class --> %s</li>',
                    esc_html($version),
                    esc_html($method),
                    esc_html($info['type']),
                    $info['class']
                );
            }
        }
        return $taskList;
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
     * @param string $taskList HTML list of tasks.
     * @param string $actionUrl URL for the action button.
     * @return string Notice message HTML.
     */
    private static function buildNoticeMessage($taskList, $actionUrl)
    {
        return sprintf(
            '<p>%s</p><ul>%s</ul><p><a href="%s" class="button button-primary">%s</a></p>',
            esc_html__('The following manual database migrations are required:', 'wp-statistics'),
            $taskList,
            esc_url($actionUrl),
            esc_html__('Run Migrations', 'wp-statistics')
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

        $manualTasks = Option::get('manual_migration_tasks', []);
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
        Option::update('manual_migration_tasks', $manualTasks);
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
}
