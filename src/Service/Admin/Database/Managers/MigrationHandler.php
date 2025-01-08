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
     * Initialize migration processes and register hooks.
     *
     * @return void
     */
    public static function init()
    {
        add_action('admin_post_run_manual_migration', [self::class, 'processManualMigrations']);
        self::runMigrations();
    }

    /**
     * Run schema migrations and prepare manual migration notices.
     *
     * @return void
     */
    public static function runMigrations()
    {
        self::showManualMigrationNotice();

        $isMigrated = Option::get('db_migrated', false);
        $isDbChecked = Option::get('check_database', false);

        if ($isMigrated || $isDbChecked) {
            return;
        }

        $process = WP_Statistics()->getBackgroundProcess('schema_migration_process');

        if ($process->isActive()) {
            return;
        }

        $migrationInstances = DatabaseFactory::Migration();
        $currentVersion = Option::get('db_version', '0.0.0');

        $manualTasks = Option::get('manual_migration_tasks', []);

        foreach ($migrationInstances as $instance) {
            foreach ($instance->getMigrationSteps() as $version => $methods) {
                if (version_compare($currentVersion, $version, '>=')) {
                    continue;
                }

                if (!isset($mergedMigrations[$version])) {
                    $mergedMigrations[$version] = [];
                }

                foreach ($methods as $method) {
                    if ('data' === $instance->getName()) {
                        $manualTasks[$version][$method] = get_class($instance);
                    } else {
                        $process->pushToQueue([
                            'class' => get_class($instance),
                            'method' => $method,
                            'version' => $version,
                        ]);
                    }
                }
            }
        }

        Option::update('manual_migration_tasks', $manualTasks);
        Option::saveOptionGroup('schema_migration_process_started', true, 'jobs');

        $process->save()->dispatch();
    }

    /**
     * Display notice for pending manual migrations.
     *
     * @return void
     * @todo it should be change to the real notice.
     */
    public static function showManualMigrationNotice()
    {
        $manualTasks = Option::get('manual_migration_tasks', []);

        if (empty($manualTasks)) {
            return;
        }

        $taskList = '';
        foreach ($manualTasks as $version => $methods) {
            foreach ($methods as $method => $class) {
                $taskList .= sprintf(
                    '<li>%s - %s => Class --> %s</li>',
                    esc_html($version),
                    esc_html($method),
                    $class
                );
            }
        }

        $actionUrl = add_query_arg(
            [
                'action' => 'run_manual_migration',
                'nonce' => wp_create_nonce('run_manual_migration_nonce')
            ],
            admin_url('admin-post.php')
        );

        $message = sprintf(
            '<p>%s</p><ul>%s</ul><p><a href="%s" class="button button-primary">%s</a></p>',
            esc_html__('The following manual database migrations are required:', 'wp-statistics'),
            $taskList,
            esc_url($actionUrl),
            esc_html__('Run Migrations', 'wp-statistics')
        );

        Notice::addNotice($message, 'database_manual_migration', 'warning');
    }

    /**
     * Handle manual migrations triggered via an admin post action.
     *
     * @return void
     * @throws \RuntimeException If a migration class or method is invalid, or tasks are not in array format.
     */
    public static function processManualMigrations()
    {
        if (!Request::compare('action', 'run_manual_migration')) {
            return;
        }

        check_admin_referer('run_manual_migration_nonce', 'nonce');

        $manualTasks = Option::get('manual_migration_tasks', []);

        if (empty($manualTasks)) {
            return;
        }

        $process = WP_Statistics()->getBackgroundProcess('data_migration_process');

        if ($process->isActive()) {
            return;
        }

        foreach ($manualTasks as $version => $methods) {
            foreach ($methods as $method => $class) {
                if (!class_exists($class)) {
                    continue;
                }

                $instance = new $class();

                if (!method_exists($instance, $method)) {
                    continue;
                }

                $tasks = $instance->$method();

                if (!is_array($tasks)) {
                    throw new \RuntimeException("The method {$method} must return an array of tasks.");
                }

                foreach ($tasks as $task) {
                    $process->pushToQueue([
                        'class' => $class,
                        'method' => $method,
                        'version' => $version,
                        'task' => $task,
                    ]);
                }
            }

            unset($manualTasks[$version]);
        }

        Option::update('manual_migration_tasks', $manualTasks);
        Option::saveOptionGroup('data_migration_process_started', true, 'jobs');

        $process->save()->dispatch();

        $referer = wp_get_referer();

        if ($referer) {
            wp_redirect($referer);
        } else {
            wp_redirect(admin_url());
        }
        exit;
    }
}
