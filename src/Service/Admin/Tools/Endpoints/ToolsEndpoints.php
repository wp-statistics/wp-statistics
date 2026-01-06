<?php

namespace WP_Statistics\Service\Admin\Tools\Endpoints;

use WP_Statistics\Components\Ajax;
use WP_Statistics\Utils\Request;
use WP_Statistics\Utils\User;
use WP_Statistics\Service\Cron\CronManager;
use WP_Statistics\Service\Database\DatabaseSchema;
use WP_Statistics\Service\Database\Managers\SchemaMaintainer;
use Exception;

/**
 * Tools AJAX Endpoints for the React SPA.
 *
 * Provides a centralized endpoint with sub-actions for:
 * - System information (database tables, plugin info)
 * - Scheduled tasks management
 * - Schema health check and repair
 *
 * Uses `wp_statistics_tools` action with `sub_action` parameter.
 *
 * @since 15.0.0
 */
class ToolsEndpoints
{
    /**
     * Available sub-actions and their handler methods.
     *
     * @var array<string, string>
     */
    private $subActions = [
        'system_info'      => 'getSystemInfo',
        'scheduled_tasks'  => 'getScheduledTasks',
        'run_task'         => 'runScheduledTask',
        'schema_check'     => 'checkSchema',
        'schema_repair'    => 'repairSchema',
    ];

    /**
     * Register AJAX handlers.
     *
     * @return void
     */
    public function register(): void
    {
        // Single centralized endpoint with sub_action parameter
        Ajax::register('tools', [$this, 'handleRequest'], false);
    }

    /**
     * Handle incoming tools requests and route to appropriate sub-action.
     *
     * @return void
     */
    public function handleRequest(): void
    {
        try {
            $this->verifyRequest();

            $subAction = sanitize_key(Request::get('sub_action', ''));

            if (empty($subAction)) {
                throw new Exception(__('Sub-action is required.', 'wp-statistics'));
            }

            if (!isset($this->subActions[$subAction])) {
                throw new Exception(
                    sprintf(__('Invalid sub-action: %s', 'wp-statistics'), $subAction)
                );
            }

            $method = $this->subActions[$subAction];
            $this->$method();
        } catch (Exception $e) {
            wp_send_json_error([
                'code'    => 'tools_error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get system information including database tables.
     *
     * @return void
     */
    private function getSystemInfo(): void
    {
        $tables = [];

        foreach (DatabaseSchema::getAllTables(true) as $key => $tableName) {
            $tableInfo = DatabaseSchema::getTableInfo($key);

            $tables[] = [
                'key'         => $key,
                'name'        => $tableName,
                'description' => DatabaseSchema::getTableDescription($key),
                'records'     => DatabaseSchema::getRowCount($key),
                'size'        => isset($tableInfo['Data_length'])
                    ? size_format($tableInfo['Data_length'] + ($tableInfo['Index_length'] ?? 0))
                    : '-',
                'engine'      => $tableInfo['Engine'] ?? '-',
            ];
        }

        wp_send_json_success([
            'tables' => $tables,
            'plugin' => [
                'version'    => WP_STATISTICS_VERSION,
                'db_version' => get_option('wp_statistics_db_version', '-'),
                'php'        => PHP_VERSION,
                'mysql'      => $this->getMysqlVersion(),
                'wp'         => get_bloginfo('version'),
            ],
        ]);
    }

    /**
     * Get scheduled tasks information.
     *
     * @return void
     */
    private function getScheduledTasks(): void
    {
        $events = CronManager::getScheduledEvents();

        $tasks = [];
        foreach ($events as $hook => $event) {
            $tasks[] = [
                'hook'       => $hook,
                'label'      => $event['label'] ?? $hook,
                'recurrence' => $event['recurrence'] ?? '-',
                'scheduled'  => $event['scheduled'] ?? false,
                'enabled'    => $event['enabled'] ?? false,
                'next_run'   => $event['next_run'] ?? null,
            ];
        }

        wp_send_json_success([
            'tasks' => $tasks,
        ]);
    }

    /**
     * Run a scheduled task manually.
     *
     * @return void
     * @throws Exception If hook is invalid.
     */
    private function runScheduledTask(): void
    {
        $hook = sanitize_text_field(Request::get('hook', ''));

        if (empty($hook)) {
            throw new Exception(__('Task hook is required.', 'wp-statistics'));
        }

        // Validate hook exists in our registered events
        $events = CronManager::getScheduledEvents();
        if (!isset($events[$hook])) {
            throw new Exception(__('Invalid task hook.', 'wp-statistics'));
        }

        // Execute the cron event
        do_action($hook);

        wp_send_json_success([
            'message' => __('Task executed successfully.', 'wp-statistics'),
        ]);
    }

    /**
     * Check database schema for issues.
     *
     * @return void
     */
    private function checkSchema(): void
    {
        $results = SchemaMaintainer::check();

        wp_send_json_success([
            'status' => $results['status'] ?? 'unknown',
            'issues' => $results['issues'] ?? [],
            'errors' => $results['errors'] ?? [],
        ]);
    }

    /**
     * Repair database schema issues.
     *
     * @return void
     */
    private function repairSchema(): void
    {
        $results = SchemaMaintainer::repair();

        wp_send_json_success([
            'status'  => $results['status'] ?? 'unknown',
            'fixed'   => $results['fixed'] ?? [],
            'failed'  => $results['failed'] ?? [],
            'message' => __('Schema repair completed.', 'wp-statistics'),
        ]);
    }

    /**
     * Verify the AJAX request.
     *
     * @throws Exception If verification fails.
     * @return void
     */
    private function verifyRequest(): void
    {
        if (!Request::isFrom('ajax')) {
            throw new Exception(__('Invalid request.', 'wp-statistics'));
        }

        if (!User::hasAccess('manage')) {
            throw new Exception(__('You do not have permission to perform this action.', 'wp-statistics'));
        }

        if (!check_ajax_referer('wp_statistics_dashboard_nonce', 'wps_nonce', false)) {
            throw new Exception(__('Security check failed. Please refresh the page and try again.', 'wp-statistics'));
        }
    }

    /**
     * Get MySQL version.
     *
     * @return string
     */
    private function getMysqlVersion(): string
    {
        global $wpdb;

        $version = $wpdb->get_var('SELECT VERSION()');

        return $version ?: '-';
    }
}
