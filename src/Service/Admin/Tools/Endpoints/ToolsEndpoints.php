<?php

namespace WP_Statistics\Service\Admin\Tools\Endpoints;

use WP_Statistics\Abstracts\BaseEndpoint;
use WP_Statistics\Utils\Request;
use WP_Statistics\Service\Cron\CronManager;
use WP_Statistics\Service\Database\Managers\SchemaMaintainer;
use WP_Statistics\Service\Admin\Diagnostic\DiagnosticManager;
use WP_Statistics\Service\Admin\Tools\SystemInfoService;
use WP_Statistics\Service\Admin\Tools\BackgroundJobRegistry;
use WP_Statistics\Service\Admin\Tools\OptionInspectionService;
use Exception;

/**
 * Tools AJAX Endpoints for the React SPA.
 *
 * Thin routing layer — business logic delegated to:
 * - SystemInfoService (table info, plugin versions)
 * - BackgroundJobRegistry (job definitions + status)
 * - OptionInspectionService (options, transients, user meta)
 * - CronManager, SchemaMaintainer, DiagnosticManager (existing services)
 *
 * Uses `wp_statistics_tools` action with `sub_action` parameter.
 *
 * @since 15.0.0
 */
class ToolsEndpoints extends BaseEndpoint
{
    protected function getActionName(): string
    {
        return 'tools';
    }

    protected function getSubActions(): array
    {
        return [
            'system_info'           => 'getSystemInfo',
            'scheduled_tasks'       => 'getScheduledTasks',
            'run_task'              => 'runScheduledTask',
            'schema_check'          => 'checkSchema',
            'schema_repair'         => 'repairSchema',
            'background_jobs'       => 'getBackgroundJobs',
            'options_transients'    => 'getOptionsAndTransients',
            'diagnostics'           => 'getDiagnostics',
            'diagnostics_run'       => 'runDiagnostics',
            'diagnostics_run_check' => 'runDiagnosticCheck',
            'diagnostics_repair'    => 'repairDiagnostic',
        ];
    }

    protected function getErrorCode(): string
    {
        return 'tools_error';
    }

    /**
     * Get system information including database tables.
     */
    protected function getSystemInfo(): void
    {
        $service = new SystemInfoService();

        wp_send_json_success([
            'tables' => $service->getTables(),
            'plugin' => $service->getPluginInfo(),
        ]);
    }

    /**
     * Get scheduled tasks information.
     */
    protected function getScheduledTasks(): void
    {
        $events = CronManager::getScheduledEvents();
        $tasks  = [];

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
     * @throws Exception If hook is invalid.
     */
    protected function runScheduledTask(): void
    {
        $hook = sanitize_text_field(Request::get('hook', ''));

        if (empty($hook)) {
            throw new Exception(__('Task hook is required.', 'wp-statistics'));
        }

        $events = CronManager::getScheduledEvents();
        if (!isset($events[$hook])) {
            throw new Exception(__('Invalid task hook.', 'wp-statistics'));
        }

        do_action($hook);

        wp_send_json_success([
            'message' => __('Task executed successfully.', 'wp-statistics'),
        ]);
    }

    /**
     * Check database schema for issues.
     */
    protected function checkSchema(): void
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
     */
    protected function repairSchema(): void
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
     */
    protected function getBackgroundJobs(): void
    {
        $registry = new BackgroundJobRegistry();

        wp_send_json_success([
            'jobs' => $registry->getAll(),
        ]);
    }

    /**
     * Get WordPress options and transients used by WP Statistics.
     */
    protected function getOptionsAndTransients(): void
    {
        $service = new OptionInspectionService();

        wp_send_json_success([
            'options'    => $service->getOptions(),
            'transients' => $service->getTransients(),
            'user_meta'  => $service->getUserMeta(get_current_user_id()),
        ]);
    }

    /**
     * Get diagnostic check results.
     */
    protected function getDiagnostics(): void
    {
        $manager = new DiagnosticManager();
        $results = $manager->getResults();

        wp_send_json_success([
            'checks'        => $this->formatDiagnosticResults($manager, $results),
            'lastFullCheck' => $manager->getLastFullCheckTime(),
            'hasIssues'     => $manager->hasIssues(),
            'failCount'     => count($manager->getFailedChecks()),
            'warningCount'  => count($manager->getWarningChecks()),
        ]);
    }

    /**
     * Run all diagnostic checks fresh.
     */
    protected function runDiagnostics(): void
    {
        $manager = new DiagnosticManager();
        $results = $manager->runAll(true);

        wp_send_json_success([
            'checks'        => $this->formatDiagnosticResults($manager, $results),
            'lastFullCheck' => time(),
            'hasIssues'     => $manager->hasIssues(),
            'failCount'     => count($manager->getFailedChecks()),
            'warningCount'  => count($manager->getWarningChecks()),
        ]);
    }

    /**
     * Run a single diagnostic check.
     *
     * @throws Exception If check key is invalid.
     */
    protected function runDiagnosticCheck(): void
    {
        $key = sanitize_key(Request::get('check', ''));

        if (empty($key)) {
            throw new Exception(__('Check key is required.', 'wp-statistics'));
        }

        $manager = new DiagnosticManager();

        if (!$manager->hasCheck($key)) {
            throw new Exception(__('Invalid check key.', 'wp-statistics'));
        }

        $result = $manager->runCheck($key);

        wp_send_json_success([
            'check' => $this->formatDiagnosticResult($manager, $result),
        ]);
    }

    /**
     * Format a collection of diagnostic results for JSON response.
     *
     * @param DiagnosticManager $manager
     * @param iterable          $results
     * @return array
     */
    protected function formatDiagnosticResults(DiagnosticManager $manager, iterable $results): array
    {
        $checks = [];
        foreach ($results as $key => $result) {
            $checks[] = $this->formatDiagnosticResult($manager, $result);
        }
        return $checks;
    }

    /**
     * Format a single diagnostic result for JSON response.
     *
     * @param DiagnosticManager $manager
     * @param object            $result
     * @return array
     */
    protected function formatDiagnosticResult(DiagnosticManager $manager, object $result): array
    {
        $check = $manager->getCheck($result->key);

        return [
            'key'           => $result->key,
            'label'         => $result->label,
            'description'   => $check ? $check->getDescription() : '',
            'status'        => $result->status,
            'message'       => $result->message,
            'details'       => $result->details,
            'helpUrl'       => $result->helpUrl,
            'timestamp'     => $result->timestamp,
            'isLightweight' => $check ? $check->isLightweight() : false,
        ];
    }

    /**
     * Repair a diagnostic check.
     *
     * @throws Exception If check key is invalid or repair not supported.
     */
    protected function repairDiagnostic(): void
    {
        $key = sanitize_key(Request::get('check', ''));

        if (empty($key)) {
            throw new Exception(__('Check key is required.', 'wp-statistics'));
        }

        $repairActions = [
            'schema' => 'repairSchema',
        ];

        if (!isset($repairActions[$key])) {
            throw new Exception(__('This check does not support repair.', 'wp-statistics'));
        }

        $method = $repairActions[$key];
        $this->$method();
    }
}
