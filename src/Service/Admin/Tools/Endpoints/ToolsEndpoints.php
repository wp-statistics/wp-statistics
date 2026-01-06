<?php

namespace WP_Statistics\Service\Admin\Tools\Endpoints;

use WP_Statistics\Components\Ajax;
use WP_Statistics\Components\Option;
use WP_Statistics\Utils\Request;
use WP_Statistics\Utils\User;
use WP_Statistics\Service\Cron\CronManager;
use WP_Statistics\Service\Database\DatabaseSchema;
use WP_Statistics\Service\Database\Managers\SchemaMaintainer;
use WP_Statistics\BackgroundProcess\BackgroundProcessMonitor;
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
        'background_jobs'  => 'getBackgroundJobs',
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
                'isLegacy'    => DatabaseSchema::isLegacyTable($key),
                'isAddon'     => DatabaseSchema::isAddonTable($key),
                'addonName'   => DatabaseSchema::getAddonName($key),
            ];
        }

        wp_send_json_success([
            'tables' => $tables,
            'plugin' => [
                'version'    => WP_STATISTICS_VERSION,
                'db_version' => get_option('wp_statistics_db_version', '-'),
                'php'        => PHP_VERSION,
                'mysql'      => $GLOBALS['wpdb']->db_version(),
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
     * Get background jobs status.
     *
     * Returns information about all registered background processes
     * including their current status and progress.
     *
     * @return void
     */
    private function getBackgroundJobs(): void
    {
        // Define known background jobs with metadata
        $jobDefinitions = [
            'calculate_post_words_count' => [
                'label'       => __('Post Word Count', 'wp-statistics'),
                'description' => __('Calculates word count for posts without this meta.', 'wp-statistics'),
                'optionKey'   => 'word_count_process_initiated',
            ],
            'update_unknown_visitor_geoip' => [
                'label'       => __('Visitor GeoIP Update', 'wp-statistics'),
                'description' => __('Updates location data for visitors with incomplete GeoIP info.', 'wp-statistics'),
                'optionKey'   => 'update_geoip_process_initiated',
            ],
            'geolocation_database_download' => [
                'label'       => __('GeoIP Database Download', 'wp-statistics'),
                'description' => __('Downloads and updates the GeoIP database.', 'wp-statistics'),
                'optionKey'   => null,
            ],
            'update_visitors_source_channel' => [
                'label'       => __('Source Channel Update', 'wp-statistics'),
                'description' => __('Updates source channel data for visitors.', 'wp-statistics'),
                'optionKey'   => 'update_source_channel_process_initiated',
            ],
            'calculate_daily_summary' => [
                'label'       => __('Daily Summary', 'wp-statistics'),
                'description' => __('Calculates daily metrics summary for resources.', 'wp-statistics'),
                'optionKey'   => 'calculate_daily_summary_initiated',
            ],
            'calculate_daily_summary_total' => [
                'label'       => __('Daily Summary Totals', 'wp-statistics'),
                'description' => __('Calculates site-wide daily summary totals.', 'wp-statistics'),
                'optionKey'   => 'calculate_daily_summary_total_initiated',
            ],
            'update_resouce_cache_fields' => [
                'label'       => __('Resource Cache Update', 'wp-statistics'),
                'description' => __('Updates cache fields for all resources.', 'wp-statistics'),
                'optionKey'   => 'update_resouce_cache_fields_initiated',
            ],
        ];

        $jobs = [];

        foreach ($jobDefinitions as $key => $definition) {
            // Get progress from BackgroundProcessMonitor
            $progress = BackgroundProcessMonitor::getStatus($key);

            // Determine job status
            $status = 'idle';
            if ($progress['total'] > 0 && $progress['remain'] > 0) {
                $status = 'running';
            } elseif ($progress['total'] > 0 && $progress['remain'] === 0 && $progress['completed'] > 0) {
                $status = 'idle'; // Completed
            }

            // Check if job was initiated via option
            $isInitiated = false;
            if (!empty($definition['optionKey'])) {
                $isInitiated = Option::getGroupValue('jobs', $definition['optionKey'], false);
            }

            // Build job info
            $jobs[] = [
                'key'         => $key,
                'label'       => $definition['label'],
                'description' => $definition['description'],
                'status'      => $status,
                'progress'    => $status === 'running' ? [
                    'total'      => $progress['total'],
                    'completed'  => $progress['completed'],
                    'remain'     => $progress['remain'],
                    'percentage' => $progress['percentage'],
                ] : null,
            ];
        }

        wp_send_json_success([
            'jobs' => $jobs,
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
}
