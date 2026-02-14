<?php

namespace WP_Statistics\Service\Admin\Tools;

use WP_Statistics\BackgroundProcess\BackgroundProcessMonitor;
use WP_Statistics\Components\Option;

/**
 * Registry of background job definitions with status tracking.
 *
 * Centralizes job metadata that was previously hardcoded in ToolsEndpoints.
 * Third-party and premium plugins can register additional jobs via the
 * `wp_statistics_background_jobs` filter.
 *
 * @since 15.0.0
 */
class BackgroundJobRegistry
{
    /**
     * Get all registered background jobs with their current status.
     *
     * @return array[] Each entry: key, label, description, status, progress.
     */
    public function getAll(): array
    {
        $definitions = $this->getDefinitions();
        $jobs        = [];

        foreach ($definitions as $key => $definition) {
            $jobs[] = $this->buildJobInfo($key, $definition);
        }

        return $jobs;
    }

    /**
     * Get status for a specific job.
     *
     * @param string $key Job key.
     * @return array{key: string, label: string, description: string, status: string, progress: ?array}|null
     */
    public function getJob(string $key): ?array
    {
        $definitions = $this->getDefinitions();

        if (!isset($definitions[$key])) {
            return null;
        }

        return $this->buildJobInfo($key, $definitions[$key]);
    }

    /**
     * Get all registered job definitions.
     *
     * @return array<string, array{label: string, description: string, optionKey: ?string}>
     */
    public function getDefinitions(): array
    {
        $definitions = $this->getCoreDefinitions();

        /**
         * Filter registered background job definitions.
         *
         * Allows premium and third-party plugins to register additional background jobs.
         *
         * Each definition must include:
         * - `label`       (string) Human-readable name.
         * - `description` (string) What the job does.
         * - `optionKey`   (?string) Option key in 'jobs' group to check if initiated, or null.
         *
         * @param array<string, array> $definitions Job key => definition map.
         */
        return apply_filters('wp_statistics_background_jobs', $definitions);
    }

    /**
     * Core background job definitions.
     *
     * @return array<string, array{label: string, description: string, optionKey: ?string}>
     */
    private function getCoreDefinitions(): array
    {
        return [
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
    }

    /**
     * Build job info array with status and progress.
     *
     * @param string $key        Job key.
     * @param array  $definition Job definition.
     * @return array
     */
    private function buildJobInfo(string $key, array $definition): array
    {
        $progress = BackgroundProcessMonitor::getStatus($key);

        // Determine job status
        $status = 'idle';
        if ($progress['total'] > 0 && $progress['remain'] > 0) {
            $status = 'running';
        }

        return [
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
}
