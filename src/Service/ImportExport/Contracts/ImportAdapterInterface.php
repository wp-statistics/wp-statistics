<?php

namespace WP_Statistics\Service\ImportExport\Contracts;

/**
 * Interface for import adapters.
 *
 * Each adapter handles a specific data source format (GA4, Plausible, backup, etc.).
 * Adapters are responsible for:
 * - Validating source files
 * - Transforming source data to WP Statistics schema
 * - Providing field mapping information
 *
 * @since 15.0.0
 */
interface ImportAdapterInterface
{
    /**
     * Get the unique identifier for this adapter.
     *
     * @return string Adapter key (e.g., 'google_analytics_4', 'wp_statistics_backup')
     */
    public function getName(): string;

    /**
     * Get human-readable label for this adapter.
     *
     * @return string Translated label for display in UI
     */
    public function getLabel(): string;

    /**
     * Get supported file extensions.
     *
     * @return array<string> List of extensions (e.g., ['csv'], ['json'])
     */
    public function getSupportedExtensions(): array;

    /**
     * Get required columns in the source file.
     *
     * @return array<string> List of required column names
     */
    public function getRequiredColumns(): array;

    /**
     * Get optional columns that can be imported.
     *
     * @return array<string> List of optional column names
     */
    public function getOptionalColumns(): array;

    /**
     * Get the default field mapping from source to WP Statistics schema.
     *
     * @return array<string, string> Source column => WP Statistics field mapping
     */
    public function getFieldMapping(): array;

    /**
     * Validate the source file format and structure.
     *
     * @param string $filePath Path to the source file
     * @param array  $headers  Headers/columns from the file
     * @return bool True if valid, false otherwise
     */
    public function validateSource(string $filePath, array $headers): bool;

    /**
     * Transform a single row from source format to WP Statistics schema.
     *
     * @param array $sourceRow   Raw row data from source file
     * @param array $fieldMapping Custom field mapping (optional override)
     * @return array Transformed data ready for import
     */
    public function transformRow(array $sourceRow, array $fieldMapping = []): array;

    /**
     * Get the target table(s) for this adapter.
     *
     * Different adapters may write to different tables:
     * - Backup adapter: visitors, sessions, views, resources
     * - GA4 adapter: summary, summary_totals (aggregate data)
     *
     * @return array<string> List of target table names
     */
    public function getTargetTables(): array;

    /**
     * Whether this adapter imports aggregate data (summary tables).
     *
     * GA4 and similar tools export daily totals, not individual sessions.
     *
     * @return bool True if imports to summary tables
     */
    public function isAggregateImport(): bool;

    /**
     * Estimate total record count in source file.
     *
     * Used for progress tracking.
     *
     * @param string $filePath Path to the source file
     * @return int Estimated number of records
     */
    public function estimateRecordCount(string $filePath): int;

    /**
     * Import a single row of data.
     *
     * This method handles the actual database insert/update for a single record.
     * It should transform the source row and insert it into the appropriate tables.
     *
     * @param array $sourceRow Raw row data from source file
     * @return bool|null True if imported, false if skipped, null if error
     * @throws \Exception On import error
     */
    public function importRow(array $sourceRow);

    /**
     * Initialize the adapter for import.
     *
     * Called before processing starts. Use for warming caches,
     * setting up resolvers, etc.
     *
     * @return void
     */
    public function initialize(): void;

    /**
     * Finalize the adapter after import.
     *
     * Called after all records are processed. Use for cleanup,
     * updating summary tables, etc.
     *
     * @return void
     */
    public function finalize(): void;
}
