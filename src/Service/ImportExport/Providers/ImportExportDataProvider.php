<?php

namespace WP_Statistics\Service\ImportExport\Providers;

use WP_Statistics\Service\Admin\ReactApp\Contracts\LocalizeDataProviderInterface;

/**
 * Provider for import/export localized data.
 *
 * Provides nonces, adapter information, and action names to the React frontend
 * for the import/export functionality.
 *
 * @since 15.0.0
 */
class ImportExportDataProvider implements LocalizeDataProviderInterface
{
    /**
     * Get import/export data for React.
     *
     * @return array Array of import/export configuration data
     */
    public function getData()
    {
        return [
            'nonce'   => wp_create_nonce('wp_statistics_import_export_nonce'),
            'actions' => [
                'adapters'     => 'wp_statistics_import_adapters',
                'upload'       => 'wp_statistics_import_upload',
                'preview'      => 'wp_statistics_import_preview',
                'start'        => 'wp_statistics_import_start',
                'status'       => 'wp_statistics_import_status',
                'cancel'       => 'wp_statistics_import_cancel',
                'exportStart'  => 'wp_statistics_export_start',
                'download'     => 'wp_statistics_export_download',
                'backupsList'  => 'wp_statistics_backups_list',
                'backupDelete' => 'wp_statistics_backup_delete',
                'purgeDataNow' => 'wp_statistics_purge_data_now',
            ],
            'uploadDir' => wp_upload_dir()['basedir'] . '/wp-statistics/imports/',
        ];
    }

    /**
     * Get the localize data key.
     *
     * @return string The key 'importExport' for import/export data
     */
    public function getKey()
    {
        return 'importExport';
    }
}
