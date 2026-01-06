<?php

namespace WP_Statistics\Service\ImportExport\Processors;

use WP_Statistics\Components\Option;
use WP_Statistics\Service\Database\Managers\TransactionHandler;
use WP_Statistics\Service\ImportExport\Contracts\ImportAdapterInterface;
use WP_Statistics\Service\ImportExport\Contracts\ParserInterface;

/**
 * Handles batch processing of imports.
 *
 * This processor manages the state of an import operation, processing
 * records in batches to avoid timeouts and memory issues with large files.
 *
 * @since 15.0.0
 */
class BatchImportProcessor
{
    /**
     * Default batch size.
     *
     * @var int
     */
    const BATCH_SIZE = 100;

    /**
     * Option key prefix for import state.
     *
     * @var string
     */
    const OPTION_PREFIX = 'wp_statistics_import_';

    /**
     * Start a new import process.
     *
     * @param string $importId   Unique import identifier.
     * @param string $filePath   Path to the import file.
     * @param string $adapterKey Adapter key to use.
     * @param int    $totalRows  Total number of rows to import.
     * @return bool
     */
    public function initImport(string $importId, string $filePath, string $adapterKey, int $totalRows): bool
    {
        $state = [
            'import_id'    => $importId,
            'file_path'    => $filePath,
            'adapter_key'  => $adapterKey,
            'total_rows'   => $totalRows,
            'processed'    => 0,
            'imported'     => 0,
            'skipped'      => 0,
            'errors'       => [],
            'status'       => 'pending',
            'started_at'   => current_time('mysql'),
            'updated_at'   => current_time('mysql'),
        ];

        return update_option(self::OPTION_PREFIX . $importId, $state, false);
    }

    /**
     * Get import state.
     *
     * @param string $importId Import identifier.
     * @return array|null
     */
    public function getState(string $importId): ?array
    {
        $state = get_option(self::OPTION_PREFIX . $importId);
        return is_array($state) ? $state : null;
    }

    /**
     * Update import state.
     *
     * @param string $importId Import identifier.
     * @param array  $updates  State updates.
     * @return bool
     */
    public function updateState(string $importId, array $updates): bool
    {
        $state = $this->getState($importId);
        if (!$state) {
            return false;
        }

        $state = array_merge($state, $updates, ['updated_at' => current_time('mysql')]);
        return update_option(self::OPTION_PREFIX . $importId, $state, false);
    }

    /**
     * Process a batch of records.
     *
     * @param string                 $importId Import identifier.
     * @param ImportAdapterInterface $adapter  Import adapter.
     * @param ParserInterface        $parser   File parser.
     * @param int                    $batchSize Batch size (default: 100).
     * @return array Result with status and progress info.
     */
    public function processBatch(
        string $importId,
        ImportAdapterInterface $adapter,
        ParserInterface $parser,
        int $batchSize = self::BATCH_SIZE
    ): array {
        $state = $this->getState($importId);
        if (!$state) {
            return [
                'success' => false,
                'message' => 'Import not found',
            ];
        }

        // Check if already completed or cancelled
        if (in_array($state['status'], ['completed', 'cancelled', 'failed'])) {
            return [
                'success' => false,
                'message' => 'Import already ' . $state['status'],
                'status'  => $state['status'],
            ];
        }

        // Update status to processing
        $this->updateState($importId, ['status' => 'processing']);

        // Seek to current position
        $parser->seek($state['processed']);

        $imported = 0;
        $skipped = 0;
        $errors = [];

        // Start transaction for batch
        $transaction = new TransactionHandler();
        $transaction->begin();

        try {
            $batch = $parser->readBatch($batchSize);

            foreach ($batch as $row) {
                try {
                    $result = $adapter->importRow($row);
                    if ($result === true) {
                        $imported++;
                    } elseif ($result === false) {
                        $skipped++;
                    }
                } catch (\Exception $e) {
                    $errors[] = [
                        'row'     => $state['processed'] + count($batch),
                        'message' => $e->getMessage(),
                    ];
                    $skipped++;
                }
            }

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollback();
            $this->updateState($importId, [
                'status' => 'failed',
                'errors' => array_merge($state['errors'], [['message' => $e->getMessage()]]),
            ]);

            return [
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage(),
                'status'  => 'failed',
            ];
        }

        $processed = $state['processed'] + count($batch);
        $totalImported = $state['imported'] + $imported;
        $totalSkipped = $state['skipped'] + $skipped;
        $allErrors = array_merge($state['errors'], $errors);

        // Determine if complete
        $isComplete = $processed >= $state['total_rows'] || !$parser->hasMore();
        $status = $isComplete ? 'completed' : 'processing';

        // Update state
        $this->updateState($importId, [
            'processed' => $processed,
            'imported'  => $totalImported,
            'skipped'   => $totalSkipped,
            'errors'    => array_slice($allErrors, -50), // Keep only last 50 errors
            'status'    => $status,
        ]);

        $percentage = $state['total_rows'] > 0
            ? round(($processed / $state['total_rows']) * 100, 1)
            : 100;

        return [
            'success'     => true,
            'status'      => $status,
            'processed'   => $processed,
            'total'       => $state['total_rows'],
            'imported'    => $totalImported,
            'skipped'     => $totalSkipped,
            'percentage'  => $percentage,
            'has_more'    => !$isComplete,
            'errors'      => count($allErrors),
            'message'     => $isComplete
                ? sprintf('Import completed. %d records imported, %d skipped.', $totalImported, $totalSkipped)
                : sprintf('Processing... %d of %d records (%d%%)', $processed, $state['total_rows'], $percentage),
        ];
    }

    /**
     * Cancel an import.
     *
     * @param string $importId Import identifier.
     * @return bool
     */
    public function cancelImport(string $importId): bool
    {
        $state = $this->getState($importId);
        if (!$state) {
            return false;
        }

        // Clean up temp file
        if (!empty($state['file_path']) && file_exists($state['file_path'])) {
            @unlink($state['file_path']);
        }

        return $this->updateState($importId, ['status' => 'cancelled']);
    }

    /**
     * Clean up completed/cancelled imports older than specified hours.
     *
     * @param int $hours Hours to keep imports (default: 24).
     * @return int Number of cleaned up imports.
     */
    public function cleanup(int $hours = 24): int
    {
        global $wpdb;

        $cleaned = 0;
        $cutoff = date('Y-m-d H:i:s', strtotime("-{$hours} hours"));

        // Find all import options
        $options = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s",
                self::OPTION_PREFIX . '%'
            )
        );

        foreach ($options as $option) {
            $importId = str_replace(self::OPTION_PREFIX, '', $option->option_name);
            $state = $this->getState($importId);

            if (!$state) {
                continue;
            }

            // Only clean up completed/cancelled/failed imports
            if (!in_array($state['status'], ['completed', 'cancelled', 'failed'])) {
                continue;
            }

            // Check if old enough
            if ($state['updated_at'] > $cutoff) {
                continue;
            }

            // Clean up file
            if (!empty($state['file_path']) && file_exists($state['file_path'])) {
                @unlink($state['file_path']);
            }

            // Delete option
            delete_option(self::OPTION_PREFIX . $importId);
            $cleaned++;
        }

        return $cleaned;
    }

    /**
     * Get progress information for an import.
     *
     * @param string $importId Import identifier.
     * @return array|null Progress information or null if not found.
     */
    public function getProgress(string $importId): ?array
    {
        $state = $this->getState($importId);
        if (!$state) {
            return null;
        }

        $percentage = $state['total_rows'] > 0
            ? round(($state['processed'] / $state['total_rows']) * 100, 1)
            : 0;

        return [
            'import_id'   => $importId,
            'status'      => $state['status'],
            'processed'   => $state['processed'],
            'total'       => $state['total_rows'],
            'imported'    => $state['imported'],
            'skipped'     => $state['skipped'],
            'percentage'  => $percentage,
            'errors'      => count($state['errors']),
            'started_at'  => $state['started_at'],
            'updated_at'  => $state['updated_at'],
        ];
    }
}
