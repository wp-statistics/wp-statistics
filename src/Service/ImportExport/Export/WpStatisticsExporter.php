<?php

namespace WP_Statistics\Service\ImportExport\Export;

use WP_Statistics\Service\Database\DatabaseSchema;
use WP_Statistics\Utils\Query;

/**
 * WP Statistics Backup Exporter.
 *
 * Exports WP Statistics data to JSON backup format with metadata and lookup references.
 * Supports date range filtering and selective table export.
 *
 * @since 15.0.0
 */
class WpStatisticsExporter
{
    /**
     * Schema version for backup compatibility.
     */
    private const SCHEMA_VERSION = '1.0';

    /**
     * Default batch size for queries.
     */
    private const BATCH_SIZE = 1000;

    /**
     * Tables available for export.
     *
     * @var array
     */
    private $exportableTables = [
        'visitors',
        'sessions',
        'views',
        'resources',
        'resource_uris',
        'summary',
        'summary_totals',
    ];

    /**
     * Lookup tables for reference data.
     *
     * @var array
     */
    private $lookupTables = [
        'countries',
        'cities',
        'device_types',
        'device_browsers',
        'device_browser_versions',
        'device_oss',
        'referrers',
        'resolutions',
        'languages',
        'timezones',
    ];

    /**
     * Export configuration.
     *
     * @var array
     */
    private $config = [];

    /**
     * Start date for export.
     *
     * @var string|null
     */
    private $dateFrom = null;

    /**
     * End date for export.
     *
     * @var string|null
     */
    private $dateTo = null;

    /**
     * Tables to export.
     *
     * @var array
     */
    private $tables = [];

    /**
     * Whether to include lookup references.
     *
     * @var bool
     */
    private $includeLookups = true;

    /**
     * Set date range for export.
     *
     * @param string|null $from Start date (YYYY-MM-DD)
     * @param string|null $to   End date (YYYY-MM-DD)
     * @return self
     */
    public function setDateRange(?string $from, ?string $to): self
    {
        $this->dateFrom = $from;
        $this->dateTo   = $to;
        return $this;
    }

    /**
     * Set tables to export.
     *
     * @param array $tables List of table names
     * @return self
     */
    public function setTables(array $tables): self
    {
        $this->tables = array_intersect($tables, $this->exportableTables);
        return $this;
    }

    /**
     * Set whether to include lookup references.
     *
     * @param bool $include True to include lookups
     * @return self
     */
    public function setIncludeLookups(bool $include): self
    {
        $this->includeLookups = $include;
        return $this;
    }

    /**
     * Export data to JSON backup format.
     *
     * @return array Backup data structure
     */
    public function export(): array
    {
        // Use all exportable tables if none specified
        if (empty($this->tables)) {
            $this->tables = $this->exportableTables;
        }

        // Build backup structure
        $backup = [
            'meta'        => $this->buildMetadata(),
            'lookup_refs' => $this->includeLookups ? $this->exportLookupReferences() : [],
            'data'        => $this->exportData(),
        ];

        // Calculate checksum
        $backup['meta']['checksum'] = $this->calculateChecksum($backup['data']);

        return $backup;
    }

    /**
     * Export to file.
     *
     * @param string $filePath Path to save the backup file
     * @return bool Success
     */
    public function exportToFile(string $filePath): bool
    {
        $backup = $this->export();
        $json   = json_encode($backup, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        if ($json === false) {
            return false;
        }

        return file_put_contents($filePath, $json) !== false;
    }

    /**
     * Build metadata for the backup.
     *
     * @return array Metadata
     */
    private function buildMetadata(): array
    {
        return [
            'version'        => defined('WP_STATISTICS_VERSION') ? WP_STATISTICS_VERSION : '15.0.0',
            'schema_version' => self::SCHEMA_VERSION,
            'created_at'     => current_time('mysql'),
            'site_url'       => get_site_url(),
            'date_range'     => [
                'from' => $this->dateFrom,
                'to'   => $this->dateTo,
            ],
            'record_counts'  => $this->getRecordCounts(),
            'tables'         => $this->tables,
            'checksum'       => '', // Calculated after data export
        ];
    }

    /**
     * Get record counts for each table.
     *
     * @return array<string, int>
     */
    private function getRecordCounts(): array
    {
        $counts = [];

        foreach ($this->tables as $table) {
            $counts[$table] = $this->getTableRowCount($table);
        }

        return $counts;
    }

    /**
     * Get row count for a table with date filtering.
     *
     * @param string $table Table name
     * @return int Row count
     */
    private function getTableRowCount(string $table): int
    {
        $query = Query::select('COUNT(*)')
            ->from($table);

        $this->applyDateFilter($query, $table);

        return (int)$query->getVar();
    }

    /**
     * Export lookup reference tables.
     *
     * @return array<string, array>
     */
    private function exportLookupReferences(): array
    {
        $refs = [];

        foreach ($this->lookupTables as $table) {
            if (!DatabaseSchema::tableExists(DatabaseSchema::table($table))) {
                continue;
            }

            $records = Query::select('*')
                ->from($table)
                ->getAll();

            if (!empty($records)) {
                $refs[$table] = [];

                foreach ($records as $record) {
                    $id             = $record->ID ?? 0;
                    $refs[$table][$id] = (array)$record;
                }
            }
        }

        return $refs;
    }

    /**
     * Export main data tables.
     *
     * @return array<string, array>
     */
    private function exportData(): array
    {
        $data = [];

        foreach ($this->tables as $table) {
            if (!DatabaseSchema::tableExists(DatabaseSchema::table($table))) {
                continue;
            }

            $data[$table] = $this->exportTable($table);
        }

        return $data;
    }

    /**
     * Export a single table.
     *
     * @param string $table Table name
     * @return array Table data
     */
    private function exportTable(string $table): array
    {
        $records = [];
        $offset  = 0;

        while (true) {
            $query = Query::select('*')
                ->from($table);

            $this->applyDateFilter($query, $table);

            $batch = $query
                ->limit(self::BATCH_SIZE)
                ->offset($offset)
                ->getAll();

            if (empty($batch)) {
                break;
            }

            foreach ($batch as $record) {
                $records[] = (array)$record;
            }

            if (count($batch) < self::BATCH_SIZE) {
                break;
            }

            $offset += self::BATCH_SIZE;
        }

        return $records;
    }

    /**
     * Apply date filter to query based on table type.
     *
     * @param mixed  $query Query builder
     * @param string $table Table name
     * @return void
     */
    private function applyDateFilter($query, string $table): void
    {
        if (empty($this->dateFrom) && empty($this->dateTo)) {
            return;
        }

        // Determine date column based on table
        $dateColumn = $this->getDateColumn($table);

        if (empty($dateColumn)) {
            return;
        }

        if (!empty($this->dateFrom)) {
            $query->where($dateColumn, '>=', $this->dateFrom);
        }

        if (!empty($this->dateTo)) {
            $query->where($dateColumn, '<=', $this->dateTo . ' 23:59:59');
        }
    }

    /**
     * Get the date column name for a table.
     *
     * @param string $table Table name
     * @return string|null Date column name or null if no date filter applicable
     */
    private function getDateColumn(string $table): ?string
    {
        $dateColumns = [
            'visitors'       => 'created_at',
            'sessions'       => 'started_at',
            'views'          => 'viewed_at',
            'summary'        => 'date',
            'summary_totals' => 'date',
        ];

        return $dateColumns[$table] ?? null;
    }

    /**
     * Calculate checksum for data integrity.
     *
     * @param array $data Data to checksum
     * @return string Checksum string
     */
    private function calculateChecksum(array $data): string
    {
        $json = json_encode($data);
        return 'sha256:' . hash('sha256', $json);
    }

    /**
     * Get exportable tables list.
     *
     * @return array
     */
    public function getExportableTables(): array
    {
        return $this->exportableTables;
    }

    /**
     * Get lookup tables list.
     *
     * @return array
     */
    public function getLookupTables(): array
    {
        return $this->lookupTables;
    }

    /**
     * Estimate export size.
     *
     * @return array Size estimate
     */
    public function estimateSize(): array
    {
        $counts    = $this->getRecordCounts();
        $totalRows = array_sum($counts);

        // Rough estimate: ~500 bytes per row average
        $estimatedBytes = $totalRows * 500;

        return [
            'total_rows'      => $totalRows,
            'estimated_bytes' => $estimatedBytes,
            'estimated_size'  => size_format($estimatedBytes),
            'tables'          => $counts,
        ];
    }
}
