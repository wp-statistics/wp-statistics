<?php

namespace WP_Statistics\Service\ImportExport\Parsers;

use WP_Statistics\Service\ImportExport\Contracts\ParserInterface;

/**
 * JSON file parser with streaming support.
 *
 * Handles reading JSON files, optimized for WP Statistics backup format.
 * Supports both array of objects and nested structure.
 *
 * @since 15.0.0
 */
class JsonParser implements ParserInterface
{
    /**
     * File path.
     *
     * @var string|null
     */
    private $filePath = null;

    /**
     * Parsed JSON data.
     *
     * @var array|null
     */
    private $data = null;

    /**
     * Data section to iterate (for nested JSON).
     *
     * @var string|null
     */
    private $dataSection = null;

    /**
     * Current array of records to iterate.
     *
     * @var array
     */
    private $records = [];

    /**
     * Column headers (keys).
     *
     * @var array<string>
     */
    private $headers = [];

    /**
     * Current offset.
     *
     * @var int
     */
    private $offset = 0;

    /**
     * Total rows.
     *
     * @var int
     */
    private $totalRows = 0;

    /**
     * Constructor.
     *
     * @param string|null $dataSection For nested JSON, path to data array (e.g., 'data.sessions')
     */
    public function __construct(?string $dataSection = null)
    {
        $this->dataSection = $dataSection;
    }

    /**
     * Open a JSON file for reading.
     *
     * @param string $filePath Absolute path to the JSON file
     * @return bool True if file opened successfully
     * @throws \RuntimeException If file cannot be opened or parsed
     */
    public function open(string $filePath): bool
    {
        if (!file_exists($filePath)) {
            throw new \RuntimeException("File not found: {$filePath}");
        }

        if (!is_readable($filePath)) {
            throw new \RuntimeException("File not readable: {$filePath}");
        }

        $content = file_get_contents($filePath);

        if ($content === false) {
            throw new \RuntimeException("Failed to read file: {$filePath}");
        }

        // Remove BOM if present
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);

        $this->data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException("Invalid JSON: " . json_last_error_msg());
        }

        $this->filePath = $filePath;

        // Navigate to data section if specified
        $this->records = $this->getDataRecords();

        if (empty($this->records)) {
            throw new \RuntimeException("No data records found in JSON file");
        }

        // Extract headers from first record
        $firstRecord   = reset($this->records);
        $this->headers = is_array($firstRecord) ? array_keys($firstRecord) : [];

        $this->totalRows = count($this->records);
        $this->offset    = 0;

        return true;
    }

    /**
     * Get data records from parsed JSON.
     *
     * Handles both flat arrays and nested WP Statistics backup format.
     *
     * @return array
     */
    private function getDataRecords(): array
    {
        if ($this->data === null) {
            return [];
        }

        // If data section specified, navigate to it
        if ($this->dataSection !== null) {
            $parts   = explode('.', $this->dataSection);
            $current = $this->data;

            foreach ($parts as $part) {
                if (!is_array($current) || !isset($current[$part])) {
                    return [];
                }
                $current = $current[$part];
            }

            return is_array($current) ? $current : [];
        }

        // Check if it's WP Statistics backup format
        if (isset($this->data['data']) && is_array($this->data['data'])) {
            // Flatten all data arrays for iteration
            $records = [];
            foreach ($this->data['data'] as $table => $tableData) {
                if (is_array($tableData)) {
                    foreach ($tableData as $row) {
                        $row['_table'] = $table; // Tag with source table
                        $records[] = $row;
                    }
                }
            }
            return $records;
        }

        // If it's a simple array of objects
        if (isset($this->data[0]) && is_array($this->data[0])) {
            return $this->data;
        }

        // Single object - wrap in array
        if (!empty($this->data) && !isset($this->data[0])) {
            return [$this->data];
        }

        return [];
    }

    /**
     * Get column headers.
     *
     * @return array<string>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Read a batch of rows.
     *
     * @param int $batchSize Number of rows to read
     * @return array<array<string, mixed>>
     */
    public function readBatch(int $batchSize = 100): array
    {
        $rows = [];

        for ($i = 0; $i < $batchSize && $this->offset < $this->totalRows; $i++) {
            $rows[] = $this->records[$this->offset];
            $this->offset++;
        }

        return $rows;
    }

    /**
     * Check if more data is available.
     *
     * @return bool
     */
    public function hasMore(): bool
    {
        return $this->offset < $this->totalRows;
    }

    /**
     * Get current row offset.
     *
     * @return int
     */
    public function getOffset(): int
    {
        return $this->offset;
    }

    /**
     * Seek to a specific row offset.
     *
     * @param int $offset Row offset (0-based)
     * @return bool
     */
    public function seek(int $offset): bool
    {
        if ($offset < 0 || $offset > $this->totalRows) {
            return false;
        }

        $this->offset = $offset;
        return true;
    }

    /**
     * Get total row count.
     *
     * @return int
     */
    public function getTotalRows(): int
    {
        return $this->totalRows;
    }

    /**
     * Close and release resources.
     *
     * @return void
     */
    public function close(): void
    {
        $this->data      = null;
        $this->records   = [];
        $this->headers   = [];
        $this->offset    = 0;
        $this->totalRows = 0;
        $this->filePath  = null;
    }

    /**
     * Get current file path.
     *
     * @return string|null
     */
    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    /**
     * Get metadata from WP Statistics backup format.
     *
     * @return array|null Metadata or null if not available
     */
    public function getMetadata(): ?array
    {
        if ($this->data === null || !isset($this->data['meta'])) {
            return null;
        }

        return $this->data['meta'];
    }

    /**
     * Get lookup references from WP Statistics backup format.
     *
     * @return array|null Lookup references or null if not available
     */
    public function getLookupReferences(): ?array
    {
        if ($this->data === null || !isset($this->data['lookup_refs'])) {
            return null;
        }

        return $this->data['lookup_refs'];
    }

    /**
     * Get specific table data from WP Statistics backup format.
     *
     * @param string $table Table name (e.g., 'visitors', 'sessions')
     * @return array Table data or empty array
     */
    public function getTableData(string $table): array
    {
        if ($this->data === null || !isset($this->data['data'][$table])) {
            return [];
        }

        return $this->data['data'][$table];
    }

    /**
     * Get all available table names from backup.
     *
     * @return array<string> List of table names
     */
    public function getAvailableTables(): array
    {
        if ($this->data === null || !isset($this->data['data'])) {
            return [];
        }

        return array_keys($this->data['data']);
    }

    /**
     * Set the data section to iterate.
     *
     * @param string $section Data section path (e.g., 'data.sessions')
     * @return self
     */
    public function setDataSection(string $section): self
    {
        $this->dataSection = $section;

        if ($this->data !== null) {
            $this->records   = $this->getDataRecords();
            $this->totalRows = count($this->records);
            $this->offset    = 0;

            if (!empty($this->records)) {
                $firstRecord   = reset($this->records);
                $this->headers = is_array($firstRecord) ? array_keys($firstRecord) : [];
            }
        }

        return $this;
    }
}
