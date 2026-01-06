<?php

namespace WP_Statistics\Service\ImportExport\Endpoints;

use WP_Statistics\Components\Ajax;
use WP_Statistics\Service\Cron\Events\DatabaseMaintenanceEvent;
use WP_Statistics\Service\Database\DatabaseSchema;
use WP_Statistics\Service\ImportExport\ImportExportManager;
use WP_Statistics\Service\Options\OptionManager;
use WP_Statistics\Utils\FileSystem;
use WP_Statistics\Utils\User;

/**
 * Import/Export AJAX Endpoints.
 *
 * Handles all AJAX requests for import/export functionality.
 * Uses Admin AJAX instead of REST API to avoid ad-blocker issues.
 *
 * All endpoints are admin-only (false = no public access).
 *
 * @since 15.0.0
 */
class ImportExportEndpoints
{
    /**
     * Import/Export Manager instance.
     *
     * @var ImportExportManager
     */
    private $manager;

    /**
     * Constructor.
     *
     * @param ImportExportManager $manager The import/export manager
     */
    public function __construct(ImportExportManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Register all AJAX endpoints.
     *
     * @return void
     */
    public function register(): void
    {
        // All endpoints are admin-only (false = no public access)
        Ajax::register('import_adapters', [$this, 'getAdapters'], false);
        Ajax::register('import_upload', [$this, 'uploadFile'], false);
        Ajax::register('import_preview', [$this, 'preview'], false);
        Ajax::register('import_start', [$this, 'startImport'], false);
        Ajax::register('import_status', [$this, 'getStatus'], false);
        Ajax::register('import_cancel', [$this, 'cancelImport'], false);
        Ajax::register('export_start', [$this, 'startExport'], false);
        Ajax::register('export_download', [$this, 'download'], false);
        Ajax::register('backups_list', [$this, 'listBackups'], false);
        Ajax::register('backup_delete', [$this, 'deleteBackup'], false);
        Ajax::register('backup_download', [$this, 'downloadBackup'], false);
        Ajax::register('backup_restore', [$this, 'restoreBackup'], false);
        Ajax::register('backup_create', [$this, 'createBackup'], false);
        Ajax::register('purge_data_now', [$this, 'purgeDataNow'], false);
    }

    /**
     * Verify request security.
     *
     * Accepts either the import_export nonce or the dashboard nonce
     * for flexibility when called from different React contexts.
     *
     * @return bool|void Returns true if valid, sends error response otherwise
     */
    private function verifyRequest()
    {
        // Check nonce from header
        $nonce = sanitize_text_field($_SERVER['HTTP_X_WP_NONCE'] ?? '');

        if (empty($nonce)) {
            // Fallback to POST/GET parameter
            $nonce = sanitize_text_field($_REQUEST['_wpnonce'] ?? '');
        }

        // Accept either import_export nonce or dashboard nonce
        $validNonce = wp_verify_nonce($nonce, 'wp_statistics_import_export_nonce')
                   || wp_verify_nonce($nonce, 'wp_statistics_dashboard_nonce');

        if (!$validNonce) {
            wp_send_json_error([
                'code'    => 'bad_nonce',
                'message' => __('Security check failed. Please refresh the page and try again.', 'wp-statistics')
            ], 403);
        }

        if (!User::hasAccess()) {
            wp_send_json_error([
                'code'    => 'forbidden',
                'message' => __('You do not have permission to perform this action.', 'wp-statistics')
            ], 403);
        }

        return true;
    }

    /**
     * Get available import adapters.
     *
     * @return void
     */
    public function getAdapters(): void
    {
        $this->verifyRequest();

        try {
            $adapters = $this->manager->getAdaptersMetadata();

            wp_send_json_success([
                'adapters' => $adapters,
            ]);
        } catch (\Exception $e) {
            wp_send_json_error([
                'code'    => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle file upload for import.
     *
     * @return void
     */
    public function uploadFile(): void
    {
        $this->verifyRequest();

        try {
            if (empty($_FILES['file'])) {
                throw new \RuntimeException(__('No file uploaded.', 'wp-statistics'));
            }

            $file       = $_FILES['file'];
            $adapterKey = sanitize_text_field($_POST['adapter'] ?? '');

            if (empty($adapterKey)) {
                throw new \RuntimeException(__('Please select an import adapter.', 'wp-statistics'));
            }

            if (!$this->manager->hasAdapter($adapterKey)) {
                throw new \RuntimeException(__('Invalid import adapter selected.', 'wp-statistics'));
            }

            $adapter = $this->manager->getAdapter($adapterKey);

            // Validate file extension
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if (!in_array($extension, $adapter->getSupportedExtensions(), true)) {
                throw new \RuntimeException(
                    sprintf(
                        __('Invalid file type. Supported formats: %s', 'wp-statistics'),
                        implode(', ', $adapter->getSupportedExtensions())
                    )
                );
            }

            // Move to temp directory
            $tempDir = FileSystem::getImportsDir();
            FileSystem::ensureImportsDir();

            $fileName = wp_unique_filename($tempDir, sanitize_file_name($file['name']));
            $filePath = trailingslashit($tempDir) . $fileName;

            if (!move_uploaded_file($file['tmp_name'], $filePath)) {
                throw new \RuntimeException(__('Failed to save uploaded file.', 'wp-statistics'));
            }

            // Generate import ID
            $importId = wp_generate_uuid4();

            // Store import metadata
            set_transient('wp_statistics_import_' . $importId, [
                'file_path'   => $filePath,
                'file_name'   => $file['name'],
                'adapter'     => $adapterKey,
                'uploaded_at' => current_time('mysql'),
                'status'      => 'uploaded',
            ], HOUR_IN_SECONDS);

            wp_send_json_success([
                'import_id' => $importId,
                'file_name' => $file['name'],
                'file_size' => size_format($file['size']),
            ]);
        } catch (\Exception $e) {
            wp_send_json_error([
                'code'    => 'upload_error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Preview import data before processing.
     *
     * @return void
     */
    public function preview(): void
    {
        $this->verifyRequest();

        try {
            $importId = sanitize_text_field($_POST['import_id'] ?? '');

            if (empty($importId)) {
                throw new \RuntimeException(__('Import ID is required.', 'wp-statistics'));
            }

            $importData = get_transient('wp_statistics_import_' . $importId);

            if (!$importData) {
                throw new \RuntimeException(__('Import session expired. Please upload the file again.', 'wp-statistics'));
            }

            $adapter  = $this->manager->getAdapter($importData['adapter']);
            $filePath = $importData['file_path'];

            // Get parser based on file extension
            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $parser    = $this->getParser($extension);

            $parser->open($filePath);

            // Get preview data
            $headers    = $parser->getHeaders();
            $totalRows  = $parser->getTotalRows();
            $sampleRows = $parser->readBatch(5);

            $parser->close();

            // Validate source
            $isValid = $adapter->validateSource($filePath, $headers);

            // Get field mapping
            $fieldMapping = $adapter->getFieldMapping();

            wp_send_json_success([
                'headers'        => $headers,
                'total_rows'     => $totalRows,
                'sample_rows'    => $sampleRows,
                'is_valid'       => $isValid,
                'required'       => $adapter->getRequiredColumns(),
                'optional'       => $adapter->getOptionalColumns(),
                'field_mapping'  => $fieldMapping,
                'target_tables'  => $adapter->getTargetTables(),
                'is_aggregate'   => $adapter->isAggregateImport(),
            ]);
        } catch (\Exception $e) {
            wp_send_json_error([
                'code'    => 'preview_error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Start the import process.
     *
     * @return void
     */
    public function startImport(): void
    {
        $this->verifyRequest();

        try {
            $importId     = sanitize_text_field($_POST['import_id'] ?? '');
            $fieldMapping = isset($_POST['field_mapping']) ? json_decode(stripslashes($_POST['field_mapping']), true) : [];

            if (empty($importId)) {
                throw new \RuntimeException(__('Import ID is required.', 'wp-statistics'));
            }

            $importData = get_transient('wp_statistics_import_' . $importId);

            if (!$importData) {
                throw new \RuntimeException(__('Import session expired. Please upload the file again.', 'wp-statistics'));
            }

            // Update import data with status
            $importData['status']        = 'processing';
            $importData['started_at']    = current_time('mysql');
            $importData['field_mapping'] = $fieldMapping;
            $importData['offset']        = 0;
            $importData['processed']     = 0;
            $importData['imported']      = 0;
            $importData['skipped']       = 0;
            $importData['errors']        = [];

            set_transient('wp_statistics_import_' . $importId, $importData, DAY_IN_SECONDS);

            // Queue background processing
            // TODO: Queue via AjaxBackgroundProcess

            wp_send_json_success([
                'import_id' => $importId,
                'status'    => 'processing',
                'message'   => __('Import started successfully.', 'wp-statistics'),
            ]);
        } catch (\Exception $e) {
            wp_send_json_error([
                'code'    => 'start_error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get import status.
     *
     * @return void
     */
    public function getStatus(): void
    {
        $this->verifyRequest();

        try {
            $importId = sanitize_text_field($_REQUEST['import_id'] ?? '');

            if (empty($importId)) {
                throw new \RuntimeException(__('Import ID is required.', 'wp-statistics'));
            }

            $importData = get_transient('wp_statistics_import_' . $importId);

            if (!$importData) {
                throw new \RuntimeException(__('Import session not found.', 'wp-statistics'));
            }

            wp_send_json_success([
                'import_id'  => $importId,
                'status'     => $importData['status'],
                'processed'  => $importData['processed'] ?? 0,
                'imported'   => $importData['imported'] ?? 0,
                'skipped'    => $importData['skipped'] ?? 0,
                'total'      => $importData['total'] ?? 0,
                'errors'     => array_slice($importData['errors'] ?? [], -10), // Last 10 errors
            ]);
        } catch (\Exception $e) {
            wp_send_json_error([
                'code'    => 'status_error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Cancel an import.
     *
     * @return void
     */
    public function cancelImport(): void
    {
        $this->verifyRequest();

        try {
            $importId = sanitize_text_field($_POST['import_id'] ?? '');

            if (empty($importId)) {
                throw new \RuntimeException(__('Import ID is required.', 'wp-statistics'));
            }

            $importData = get_transient('wp_statistics_import_' . $importId);

            if ($importData) {
                // Clean up file
                if (!empty($importData['file_path']) && file_exists($importData['file_path'])) {
                    @unlink($importData['file_path']);
                }

                delete_transient('wp_statistics_import_' . $importId);
            }

            wp_send_json_success([
                'message' => __('Import cancelled successfully.', 'wp-statistics'),
            ]);
        } catch (\Exception $e) {
            wp_send_json_error([
                'code'    => 'cancel_error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Start data export.
     *
     * @return void
     */
    public function startExport(): void
    {
        $this->verifyRequest();

        try {
            $dateFrom = sanitize_text_field($_POST['date_from'] ?? '');
            $dateTo   = sanitize_text_field($_POST['date_to'] ?? '');
            $tables   = isset($_POST['tables']) ? array_map('sanitize_text_field', (array)$_POST['tables']) : [];

            $exporter = $this->manager->getExporter();

            // Generate export ID
            $exportId = wp_generate_uuid4();

            // Store export metadata
            set_transient('wp_statistics_export_' . $exportId, [
                'date_from'  => $dateFrom,
                'date_to'    => $dateTo,
                'tables'     => $tables,
                'started_at' => current_time('mysql'),
                'status'     => 'processing',
            ], HOUR_IN_SECONDS);

            // TODO: Queue background processing for large exports

            wp_send_json_success([
                'export_id' => $exportId,
                'status'    => 'processing',
                'message'   => __('Export started successfully.', 'wp-statistics'),
            ]);
        } catch (\Exception $e) {
            wp_send_json_error([
                'code'    => 'export_error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Download exported file.
     *
     * @return void
     */
    public function download(): void
    {
        $this->verifyRequest();

        try {
            $exportId = sanitize_text_field($_REQUEST['export_id'] ?? '');

            if (empty($exportId)) {
                throw new \RuntimeException(__('Export ID is required.', 'wp-statistics'));
            }

            $exportData = get_transient('wp_statistics_export_' . $exportId);

            if (!$exportData) {
                throw new \RuntimeException(__('Export session not found or expired.', 'wp-statistics'));
            }

            if (empty($exportData['file_path']) || !file_exists($exportData['file_path'])) {
                throw new \RuntimeException(__('Export file not found.', 'wp-statistics'));
            }

            // Send file for download
            $fileName = basename($exportData['file_path']);

            header('Content-Type: application/json');
            header('Content-Disposition: attachment; filename="' . $fileName . '"');
            header('Content-Length: ' . filesize($exportData['file_path']));

            readfile($exportData['file_path']);

            // Clean up
            @unlink($exportData['file_path']);
            delete_transient('wp_statistics_export_' . $exportId);

            exit;
        } catch (\Exception $e) {
            wp_send_json_error([
                'code'    => 'download_error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * List available backups.
     *
     * @return void
     */
    public function listBackups(): void
    {
        $this->verifyRequest();

        try {
            $backupDir = FileSystem::getBackupsDir();
            $backups   = [];

            if (file_exists($backupDir)) {
                $files = glob(trailingslashit($backupDir) . '*.json');

                foreach ($files as $file) {
                    $backups[] = [
                        'name'       => basename($file),
                        'size'       => size_format(filesize($file)),
                        'created_at' => date('Y-m-d H:i:s', filemtime($file)),
                    ];
                }

                // Sort by date descending
                usort($backups, function ($a, $b) {
                    return strtotime($b['created_at']) - strtotime($a['created_at']);
                });
            }

            wp_send_json_success([
                'backups' => $backups,
            ]);
        } catch (\Exception $e) {
            wp_send_json_error([
                'code'    => 'list_error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Delete a backup file.
     *
     * @return void
     */
    public function deleteBackup(): void
    {
        $this->verifyRequest();

        try {
            $fileName = sanitize_file_name($_POST['file_name'] ?? '');

            if (empty($fileName)) {
                throw new \RuntimeException(__('Backup file name is required.', 'wp-statistics'));
            }

            $filePath = trailingslashit(FileSystem::getBackupsDir()) . $fileName;

            if (!file_exists($filePath)) {
                throw new \RuntimeException(__('Backup file not found.', 'wp-statistics'));
            }

            if (!@unlink($filePath)) {
                throw new \RuntimeException(__('Failed to delete backup file.', 'wp-statistics'));
            }

            wp_send_json_success([
                'message' => __('Backup deleted successfully.', 'wp-statistics'),
            ]);
        } catch (\Exception $e) {
            wp_send_json_error([
                'code'    => 'delete_error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get parser for file extension.
     *
     * @param string $extension File extension
     * @return \WP_Statistics\Service\ImportExport\Contracts\ParserInterface
     * @throws \RuntimeException If no parser available
     */
    private function getParser(string $extension)
    {
        switch ($extension) {
            case 'csv':
                return new \WP_Statistics\Service\ImportExport\Parsers\CsvParser();

            case 'json':
                return new \WP_Statistics\Service\ImportExport\Parsers\JsonParser();

            default:
                throw new \RuntimeException(
                    sprintf(__('No parser available for file type: %s', 'wp-statistics'), $extension)
                );
        }
    }

    /**
     * Immediately apply data retention policy.
     *
     * @return void
     */
    public function purgeDataNow(): void
    {
        $this->verifyRequest();

        try {
            $mode = OptionManager::get('data_retention_mode', 'forever');
            $days = (int) OptionManager::get('data_retention_days', 180);

            if ($mode === 'forever') {
                wp_send_json_error([
                    'code'    => 'retention_disabled',
                    'message' => __('Data retention mode is set to "Keep forever". No data will be purged.', 'wp-statistics'),
                ]);
                return;
            }

            if ($days <= 0) {
                wp_send_json_error([
                    'code'    => 'invalid_days',
                    'message' => __('Invalid retention period. Please set a valid number of days.', 'wp-statistics'),
                ]);
                return;
            }

            // Execute the maintenance immediately
            $maintenanceEvent = new DatabaseMaintenanceEvent();
            $cutoffDate       = date('Y-m-d', strtotime("-{$days} days"));

            if ($mode === 'delete') {
                $results = $maintenanceEvent->deleteOldData($cutoffDate);
                $action  = __('deleted', 'wp-statistics');
            } else {
                $results = $maintenanceEvent->archiveOldData($cutoffDate);
                $action  = __('archived', 'wp-statistics');
            }

            // Calculate total affected records
            $totalAffected = array_sum($results);

            wp_send_json_success([
                'message'  => sprintf(
                    /* translators: 1: action taken (deleted/archived), 2: number of records */
                    __('Data cleanup completed. %1$s %2$d records.', 'wp-statistics'),
                    ucfirst($action),
                    $totalAffected
                ),
                'results'  => $results,
                'mode'     => $mode,
                'days'     => $days,
                'cutoff'   => $cutoffDate,
            ]);
        } catch (\Exception $e) {
            wp_send_json_error([
                'code'    => 'purge_error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Download a backup file.
     *
     * @return void
     */
    public function downloadBackup(): void
    {
        $this->verifyRequest();

        try {
            $fileName = sanitize_file_name($_REQUEST['file_name'] ?? '');

            if (empty($fileName)) {
                throw new \RuntimeException(__('Backup file name is required.', 'wp-statistics'));
            }

            $filePath = trailingslashit(FileSystem::getBackupsDir()) . $fileName;

            if (!file_exists($filePath)) {
                throw new \RuntimeException(__('Backup file not found.', 'wp-statistics'));
            }

            // Send file for download
            header('Content-Type: application/json');
            header('Content-Disposition: attachment; filename="' . $fileName . '"');
            header('Content-Length: ' . filesize($filePath));
            header('Pragma: no-cache');
            header('Expires: 0');

            readfile($filePath);
            exit;
        } catch (\Exception $e) {
            wp_send_json_error([
                'code'    => 'download_error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Restore data from a backup file.
     *
     * @return void
     */
    public function restoreBackup(): void
    {
        $this->verifyRequest();

        try {
            $fileName = sanitize_file_name($_POST['file_name'] ?? '');

            if (empty($fileName)) {
                throw new \RuntimeException(__('Backup file name is required.', 'wp-statistics'));
            }

            $filePath = trailingslashit(FileSystem::getBackupsDir()) . $fileName;

            if (!file_exists($filePath)) {
                throw new \RuntimeException(__('Backup file not found.', 'wp-statistics'));
            }

            // Read and parse backup file
            $content = file_get_contents($filePath);
            $backup  = json_decode($content, true);

            if (!$backup || !isset($backup['data'])) {
                throw new \RuntimeException(__('Invalid backup file format.', 'wp-statistics'));
            }

            // Verify checksum if present
            if (isset($backup['checksum'])) {
                $calculatedChecksum = hash('sha256', json_encode($backup['data']));
                if ($calculatedChecksum !== $backup['checksum']) {
                    throw new \RuntimeException(__('Backup file checksum mismatch. The file may be corrupted.', 'wp-statistics'));
                }
            }

            // Use the WpStatisticsBackupAdapter to restore
            $adapter = $this->manager->getAdapter('wp_statistics_backup');

            // Save backup data to temp file for import
            $tempDir = FileSystem::getImportsDir();
            FileSystem::ensureImportsDir();

            $tempFile = trailingslashit($tempDir) . 'restore-' . wp_generate_uuid4() . '.json';
            file_put_contents($tempFile, $content);

            // Generate import ID
            $importId = wp_generate_uuid4();

            // Store import metadata
            set_transient('wp_statistics_import_' . $importId, [
                'file_path'   => $tempFile,
                'file_name'   => $fileName,
                'adapter'     => 'wp_statistics_backup',
                'uploaded_at' => current_time('mysql'),
                'status'      => 'processing',
                'type'        => 'restore',
            ], HOUR_IN_SECONDS);

            // Process the restore
            $results = $adapter->processImport($tempFile);

            // Clean up temp file
            @unlink($tempFile);
            delete_transient('wp_statistics_import_' . $importId);

            wp_send_json_success([
                'message' => sprintf(
                    __('Backup restored successfully. Imported %d records.', 'wp-statistics'),
                    $results['imported'] ?? 0
                ),
                'results' => $results,
            ]);
        } catch (\Exception $e) {
            wp_send_json_error([
                'code'    => 'restore_error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Create a manual backup.
     *
     * @return void
     */
    public function createBackup(): void
    {
        $this->verifyRequest();

        try {
            global $wpdb;

            $backupDir = FileSystem::getBackupsDir();

            if (!FileSystem::ensureBackupsDir()) {
                throw new \RuntimeException(__('Failed to create backup directory.', 'wp-statistics'));
            }

            $backupData = [
                'meta' => [
                    'version'    => WP_STATISTICS_VERSION,
                    'created_at' => current_time('mysql'),
                    'type'       => 'manual',
                    'site_url'   => home_url(),
                ],
                'data' => [],
            ];

            // Tables to backup
            $tablesToBackup = [
                'visitors' => \WP_Statistics\Service\Database\DatabaseSchema::table('visitors'),
                'sessions' => \WP_Statistics\Service\Database\DatabaseSchema::table('sessions'),
                'views'    => \WP_Statistics\Service\Database\DatabaseSchema::table('views'),
            ];

            foreach ($tablesToBackup as $tableKey => $tableName) {
                if (!\WP_Statistics\Service\Database\DatabaseSchema::tableExists($tableName)) {
                    continue;
                }

                // Get all data (for manual backup, we backup everything)
                $rows = $wpdb->get_results("SELECT * FROM `{$tableName}`", ARRAY_A);

                if (!empty($rows)) {
                    $backupData['data'][$tableKey] = $rows;
                }
            }

            // Only create backup if there's data
            if (empty($backupData['data'])) {
                throw new \RuntimeException(__('No data to backup.', 'wp-statistics'));
            }

            // Calculate checksum
            $backupData['checksum'] = hash('sha256', json_encode($backupData['data']));

            // Generate filename
            $fileName = sprintf(
                'wp-statistics-manual-backup-%s-%s.json',
                date('Y-m-d'),
                substr(md5(uniqid()), 0, 8)
            );
            $filePath = $backupDir . '/' . $fileName;

            // Write backup file
            $result = file_put_contents(
                $filePath,
                json_encode($backupData, JSON_PRETTY_PRINT)
            );

            if ($result === false) {
                throw new \RuntimeException(__('Failed to write backup file.', 'wp-statistics'));
            }

            wp_send_json_success([
                'message'   => __('Backup created successfully.', 'wp-statistics'),
                'file_name' => $fileName,
                'file_size' => size_format(filesize($filePath)),
            ]);
        } catch (\Exception $e) {
            wp_send_json_error([
                'code'    => 'backup_error',
                'message' => $e->getMessage(),
            ]);
        }
    }
}
