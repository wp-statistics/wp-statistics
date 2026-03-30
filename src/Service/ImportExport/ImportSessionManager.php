<?php

namespace WP_Statistics\Service\ImportExport;

use WP_Statistics\Utils\FileSystem;
use RuntimeException;

/**
 * Manages import session lifecycle using transients.
 *
 * Centralizes the import session state management that was previously
 * scattered across multiple ImportExportEndpoints methods.
 *
 * @since 15.0.0
 */
class ImportSessionManager
{
    /**
     * Transient prefix for import sessions.
     */
    private const TRANSIENT_PREFIX = 'wp_statistics_import_';

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

    /**
     * Create a new import session from an uploaded file.
     *
     * @param array  $file       The $_FILES entry (name, tmp_name, size, etc.).
     * @param string $adapterKey Import adapter key.
     * @return array{import_id: string, file_name: string, file_size: string}
     * @throws RuntimeException On validation or file move failure.
     */
    public function createFromUpload(array $file, string $adapterKey): array
    {
        if (empty($file['tmp_name'])) {
            throw new RuntimeException(__('No file uploaded.', 'wp-statistics'));
        }

        if (empty($adapterKey)) {
            throw new RuntimeException(__('Please select an import adapter.', 'wp-statistics'));
        }

        if (!$this->manager->hasAdapter($adapterKey)) {
            throw new RuntimeException(__('Invalid import adapter selected.', 'wp-statistics'));
        }

        $adapter   = $this->manager->getAdapter($adapterKey);
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($extension, $adapter->getSupportedExtensions(), true)) {
            throw new RuntimeException(
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
            throw new RuntimeException(__('Failed to save uploaded file.', 'wp-statistics'));
        }

        $importId = wp_generate_uuid4();

        set_transient(self::TRANSIENT_PREFIX . $importId, [
            'file_path'   => $filePath,
            'file_name'   => $file['name'],
            'adapter'     => $adapterKey,
            'uploaded_at' => current_time('mysql'),
            'status'      => 'uploaded',
        ], HOUR_IN_SECONDS);

        return [
            'import_id' => $importId,
            'file_name' => $file['name'],
            'file_size' => size_format($file['size']),
        ];
    }

    /**
     * Get preview data for an import session.
     *
     * @param string $importId Import session ID.
     * @return array Preview data (headers, sample_rows, total_rows, field_mapping, etc.).
     * @throws RuntimeException If session not found or expired.
     */
    public function preview(string $importId): array
    {
        $importData = $this->getSessionData($importId);
        $adapter    = $this->manager->getAdapter($importData['adapter']);
        $filePath   = $importData['file_path'];

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $parser    = ParserFactory::create($extension);

        $parser->open($filePath);

        $headers    = $parser->getHeaders();
        $totalRows  = $parser->getTotalRows();
        $sampleRows = $parser->readBatch(5);

        $parser->close();

        $isValid      = $adapter->validateSource($filePath, $headers);
        $fieldMapping = $adapter->getFieldMapping();

        return [
            'headers'       => $headers,
            'total_rows'    => $totalRows,
            'sample_rows'   => $sampleRows,
            'is_valid'      => $isValid,
            'required'      => $adapter->getRequiredColumns(),
            'optional'      => $adapter->getOptionalColumns(),
            'field_mapping' => $fieldMapping,
            'target_tables' => $adapter->getTargetTables(),
            'is_aggregate'  => $adapter->isAggregateImport(),
        ];
    }

    /**
     * Start processing an import session.
     *
     * @param string $importId    Session ID.
     * @param array  $fieldMapping Field mapping configuration.
     * @return void
     * @throws RuntimeException If session not found or expired.
     */
    public function start(string $importId, array $fieldMapping): void
    {
        $importData = $this->getSessionData($importId);

        $importData['status']        = 'processing';
        $importData['started_at']    = current_time('mysql');
        $importData['field_mapping'] = $fieldMapping;
        $importData['offset']        = 0;
        $importData['processed']     = 0;
        $importData['imported']      = 0;
        $importData['skipped']       = 0;
        $importData['errors']        = [];

        set_transient(self::TRANSIENT_PREFIX . $importId, $importData, DAY_IN_SECONDS);
    }

    /**
     * Get import session status and progress.
     *
     * @param string $importId Session ID.
     * @return array Session data including status and progress counters.
     * @throws RuntimeException If session not found.
     */
    public function getStatus(string $importId): array
    {
        $importData = $this->getSessionData($importId);

        return [
            'import_id' => $importId,
            'status'    => $importData['status'],
            'processed' => $importData['processed'] ?? 0,
            'imported'  => $importData['imported'] ?? 0,
            'skipped'   => $importData['skipped'] ?? 0,
            'total'     => $importData['total'] ?? 0,
            'errors'    => array_slice($importData['errors'] ?? [], -10),
        ];
    }

    /**
     * Cancel and clean up an import session.
     *
     * @param string $importId Session ID.
     * @return void
     */
    public function cancel(string $importId): void
    {
        $importData = get_transient(self::TRANSIENT_PREFIX . $importId);

        if ($importData) {
            if (!empty($importData['file_path']) && file_exists($importData['file_path'])) {
                @unlink($importData['file_path']);
            }

            delete_transient(self::TRANSIENT_PREFIX . $importId);
        }
    }

    /**
     * Get session data or throw.
     *
     * @param string $importId Session ID.
     * @return array Session data.
     * @throws RuntimeException If session not found or expired.
     */
    private function getSessionData(string $importId): array
    {
        $data = get_transient(self::TRANSIENT_PREFIX . $importId);

        if (!$data) {
            throw new RuntimeException(__('Import session expired. Please upload the file again.', 'wp-statistics'));
        }

        return $data;
    }
}
