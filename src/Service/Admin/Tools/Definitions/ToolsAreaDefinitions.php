<?php

namespace WP_Statistics\Service\Admin\Tools\Definitions;

/**
 * Declarative definitions for the 'tools' area tabs.
 *
 * All tools tabs are component-based (rendered entirely in React),
 * so no cards or fields are defined here.
 *
 * @since 15.3.0
 */
class ToolsAreaDefinitions
{
    /**
     * Tools-area tabs.
     *
     * Note: The `area` field is NOT defined here — it is injected automatically
     * by SettingsConfigProvider::getCoreTabs() based on which definition class
     * provides the tabs (settings vs tools).
     */
    public function getTabs(): array
    {
        return [
            'system-info' => [
                'label'     => __('System Info', 'wp-statistics'),
                'icon'      => 'info',
                'order'     => 10,
                'component' => 'SystemInfoPage',
            ],
            'diagnostics' => [
                'label'     => __('Diagnostics', 'wp-statistics'),
                'icon'      => 'stethoscope',
                'order'     => 20,
                'component' => 'DiagnosticsPage',
            ],
            'scheduled-tasks' => [
                'label'     => __('Scheduled Tasks', 'wp-statistics'),
                'icon'      => 'clock',
                'order'     => 30,
                'component' => 'ScheduledTasksPage',
            ],
            'background-jobs' => [
                'label'     => __('Background Jobs', 'wp-statistics'),
                'icon'      => 'activity',
                'order'     => 40,
                'component' => 'BackgroundJobsPage',
            ],
            'import-export' => [
                'label'     => __('Import / Export', 'wp-statistics'),
                'icon'      => 'upload',
                'order'     => 50,
                'component' => 'ImportExportPage',
            ],
            'backups' => [
                'label'     => __('Backups', 'wp-statistics'),
                'icon'      => 'database',
                'order'     => 60,
                'component' => 'BackupsPage',
            ],
        ];
    }
}
