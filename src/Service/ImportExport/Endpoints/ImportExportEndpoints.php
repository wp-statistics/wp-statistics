<?php

namespace WP_Statistics\Service\ImportExport\Endpoints;

use WP_Statistics\Abstracts\BaseEndpoint;
use WP_Statistics\Service\ImportExport\BackupService;
use WP_Statistics\Service\ImportExport\DataRetentionService;
use WP_Statistics\Service\ImportExport\ImportExportManager;
use WP_Statistics\Service\ImportExport\ImportSessionManager;
use WP_Statistics\Utils\FileDownloadHelper;
use WP_Statistics\Utils\Request;
use Exception;

/**
 * Import/Export AJAX Endpoints.
 *
 * Thin routing layer — business logic delegated to:
 * - ImportSessionManager (upload, preview, start, status, cancel)
 * - BackupService (list, create, delete, restore)
 * - DataRetentionService (purge)
 * - FileDownloadHelper (download)
 *
 * Uses `wp_statistics_import_export` action with `sub_action` parameter.
 *
 * @since 15.0.0
 */
class ImportExportEndpoints extends BaseEndpoint
{
    /**
     * @var ImportExportManager
     */
    private $manager;

    /**
     * @param ImportExportManager $manager
     */
    public function __construct(ImportExportManager $manager)
    {
        $this->manager = $manager;
    }

    protected function getActionName(): string
    {
        return 'import_export';
    }

    protected function getSubActions(): array
    {
        return [
            // Import operations
            'get_adapters'    => 'getAdapters',
            'upload'          => 'uploadFile',
            'preview'         => 'preview',
            'start_import'    => 'startImport',
            'get_status'      => 'getStatus',
            'cancel_import'   => 'cancelImport',
            // Export operations
            'start_export'    => 'startExport',
            'download'        => 'download',
            // Backup operations
            'list_backups'    => 'listBackups',
            'create_backup'   => 'createBackup',
            'delete_backup'   => 'deleteBackup',
            'download_backup' => 'downloadBackup',
            'restore_backup'  => 'restoreBackup',
            // Data management
            'purge_data'      => 'purgeDataNow',
        ];
    }

    protected function getErrorCode(): string
    {
        return 'import_export_error';
    }

    // ------------------------------------------------------------------
    // Import operations
    // ------------------------------------------------------------------

    /**
     * Get available import adapters.
     */
    public function getAdapters(): void
    {
        wp_send_json_success([
            'adapters' => $this->manager->getAdaptersMetadata(),
        ]);
    }

    /**
     * Handle file upload for import.
     */
    public function uploadFile(): void
    {
        $session = $this->getImportSessionManager();
        $result  = $session->createFromUpload(
            $_FILES['file'] ?? [],
            sanitize_text_field($_POST['adapter'] ?? '')
        );

        wp_send_json_success($result);
    }

    /**
     * Preview import data before processing.
     */
    public function preview(): void
    {
        $importId = sanitize_text_field($_POST['import_id'] ?? '');

        if (empty($importId)) {
            throw new Exception(__('Import ID is required.', 'wp-statistics'));
        }

        $session = $this->getImportSessionManager();

        wp_send_json_success($session->preview($importId));
    }

    /**
     * Start the import process.
     */
    public function startImport(): void
    {
        $importId     = sanitize_text_field($_POST['import_id'] ?? '');
        $fieldMapping = isset($_POST['field_mapping']) ? json_decode(stripslashes($_POST['field_mapping']), true) : [];

        if (empty($importId)) {
            throw new Exception(__('Import ID is required.', 'wp-statistics'));
        }

        $session = $this->getImportSessionManager();
        $session->start($importId, $fieldMapping);

        wp_send_json_success([
            'import_id' => $importId,
            'status'    => 'processing',
            'message'   => __('Import started successfully.', 'wp-statistics'),
        ]);
    }

    /**
     * Get import status.
     */
    public function getStatus(): void
    {
        $importId = sanitize_text_field($_REQUEST['import_id'] ?? '');

        if (empty($importId)) {
            throw new Exception(__('Import ID is required.', 'wp-statistics'));
        }

        $session = $this->getImportSessionManager();

        wp_send_json_success($session->getStatus($importId));
    }

    /**
     * Cancel an import.
     */
    public function cancelImport(): void
    {
        $importId = sanitize_text_field($_POST['import_id'] ?? '');

        if (empty($importId)) {
            throw new Exception(__('Import ID is required.', 'wp-statistics'));
        }

        $session = $this->getImportSessionManager();
        $session->cancel($importId);

        wp_send_json_success([
            'message' => __('Import cancelled successfully.', 'wp-statistics'),
        ]);
    }

    // ------------------------------------------------------------------
    // Export operations
    // ------------------------------------------------------------------

    /**
     * Start data export.
     */
    public function startExport(): void
    {
        $dateFrom = sanitize_text_field($_POST['date_from'] ?? '');
        $dateTo   = sanitize_text_field($_POST['date_to'] ?? '');
        $tables   = isset($_POST['tables']) ? array_map('sanitize_text_field', (array)$_POST['tables']) : [];

        $exportId = wp_generate_uuid4();

        set_transient('wp_statistics_export_' . $exportId, [
            'date_from'  => $dateFrom,
            'date_to'    => $dateTo,
            'tables'     => $tables,
            'started_at' => current_time('mysql'),
            'status'     => 'processing',
        ], HOUR_IN_SECONDS);

        wp_send_json_success([
            'export_id' => $exportId,
            'status'    => 'processing',
            'message'   => __('Export started successfully.', 'wp-statistics'),
        ]);
    }

    /**
     * Download exported file.
     */
    public function download(): void
    {
        $exportId = sanitize_text_field($_REQUEST['export_id'] ?? '');

        if (empty($exportId)) {
            throw new Exception(__('Export ID is required.', 'wp-statistics'));
        }

        $exportData = get_transient('wp_statistics_export_' . $exportId);

        if (!$exportData) {
            throw new Exception(__('Export session not found or expired.', 'wp-statistics'));
        }

        if (empty($exportData['file_path']) || !file_exists($exportData['file_path'])) {
            throw new Exception(__('Export file not found.', 'wp-statistics'));
        }

        delete_transient('wp_statistics_export_' . $exportId);

        FileDownloadHelper::send(
            $exportData['file_path'],
            basename($exportData['file_path']),
            true
        );
    }

    // ------------------------------------------------------------------
    // Backup operations
    // ------------------------------------------------------------------

    /**
     * List available backups.
     */
    public function listBackups(): void
    {
        $service = $this->getBackupService();

        wp_send_json_success([
            'backups' => $service->list(),
        ]);
    }

    /**
     * Create a manual backup.
     */
    public function createBackup(): void
    {
        $service = $this->getBackupService();
        $result  = $service->create();

        wp_send_json_success([
            'message'   => __('Backup created successfully.', 'wp-statistics'),
            'file_name' => $result['name'],
            'file_size' => $result['size'],
        ]);
    }

    /**
     * Delete a backup file.
     */
    public function deleteBackup(): void
    {
        $fileName = sanitize_file_name($_POST['file_name'] ?? '');

        if (empty($fileName)) {
            throw new Exception(__('Backup file name is required.', 'wp-statistics'));
        }

        $service = $this->getBackupService();
        $service->delete($fileName);

        wp_send_json_success([
            'message' => __('Backup deleted successfully.', 'wp-statistics'),
        ]);
    }

    /**
     * Download a backup file.
     */
    public function downloadBackup(): void
    {
        $fileName = sanitize_file_name($_REQUEST['file_name'] ?? '');

        if (empty($fileName)) {
            throw new Exception(__('Backup file name is required.', 'wp-statistics'));
        }

        $service  = $this->getBackupService();
        $filePath = $service->getFilePath($fileName);

        FileDownloadHelper::send($filePath, $fileName, false);
    }

    /**
     * Restore data from a backup file.
     */
    public function restoreBackup(): void
    {
        $fileName = sanitize_file_name($_POST['file_name'] ?? '');

        if (empty($fileName)) {
            throw new Exception(__('Backup file name is required.', 'wp-statistics'));
        }

        $service = $this->getBackupService();
        $results = $service->restore($fileName);

        wp_send_json_success([
            'message' => sprintf(
                __('Backup restored successfully. Imported %d records.', 'wp-statistics'),
                $results['imported'] ?? 0
            ),
            'results' => $results,
        ]);
    }

    // ------------------------------------------------------------------
    // Data management
    // ------------------------------------------------------------------

    /**
     * Immediately apply data retention policy.
     */
    public function purgeDataNow(): void
    {
        $service = new DataRetentionService();
        $result  = $service->purge();

        $action = ($result['mode'] === 'delete')
            ? __('deleted', 'wp-statistics')
            : __('archived', 'wp-statistics');

        wp_send_json_success([
            'message' => sprintf(
                /* translators: 1: action taken (deleted/archived), 2: number of records */
                __('Data cleanup completed. %1$s %2$d records.', 'wp-statistics'),
                ucfirst($action),
                $result['affected']
            ),
            'results' => $result['results'],
            'mode'    => $result['mode'],
            'days'    => $result['days'],
            'cutoff'  => $result['cutoff'],
        ]);
    }

    // ------------------------------------------------------------------
    // Service factories
    // ------------------------------------------------------------------

    /**
     * @return BackupService
     */
    private function getBackupService(): BackupService
    {
        return new BackupService($this->manager);
    }

    /**
     * @return ImportSessionManager
     */
    private function getImportSessionManager(): ImportSessionManager
    {
        return new ImportSessionManager($this->manager);
    }
}
