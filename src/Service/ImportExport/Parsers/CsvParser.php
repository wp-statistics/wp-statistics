<?php

namespace WP_Statistics\Service\ImportExport\Parsers;

use WP_Statistics\Service\ImportExport\Contracts\ParserInterface;

/**
 * CSV file parser with streaming support.
 *
 * Handles reading CSV files in batches to support large imports
 * without memory issues.
 *
 * @since 15.0.0
 */
class CsvParser implements ParserInterface
{
    /**
     * File handle.
     *
     * @var resource|null
     */
    private $handle = null;

    /**
     * File path.
     *
     * @var string|null
     */
    private $filePath = null;

    /**
     * Column headers.
     *
     * @var array<string>
     */
    private $headers = [];

    /**
     * Current row offset.
     *
     * @var int
     */
    private $offset = 0;

    /**
     * Total row count (cached).
     *
     * @var int|null
     */
    private $totalRows = null;

    /**
     * CSV delimiter.
     *
     * @var string
     */
    private $delimiter = ',';

    /**
     * CSV enclosure character.
     *
     * @var string
     */
    private $enclosure = '"';

    /**
     * CSV escape character.
     *
     * @var string
     */
    private $escape = '\\';

    /**
     * Whether end of file reached.
     *
     * @var bool
     */
    private $eof = false;

    /**
     * Constructor.
     *
     * @param string $delimiter CSV delimiter (default: comma)
     * @param string $enclosure Enclosure character (default: double quote)
     * @param string $escape    Escape character (default: backslash)
     */
    public function __construct(string $delimiter = ',', string $enclosure = '"', string $escape = '\\')
    {
        $this->delimiter = $delimiter;
        $this->enclosure = $enclosure;
        $this->escape    = $escape;
    }

    /**
     * Open a CSV file for reading.
     *
     * @param string $filePath Absolute path to the CSV file
     * @return bool True if file opened successfully
     * @throws \RuntimeException If file cannot be opened
     */
    public function open(string $filePath): bool
    {
        if (!file_exists($filePath)) {
            throw new \RuntimeException("File not found: {$filePath}");
        }

        if (!is_readable($filePath)) {
            throw new \RuntimeException("File not readable: {$filePath}");
        }

        $this->handle = fopen($filePath, 'r');

        if ($this->handle === false) {
            throw new \RuntimeException("Failed to open file: {$filePath}");
        }

        $this->filePath  = $filePath;
        $this->offset    = 0;
        $this->totalRows = null;
        $this->eof       = false;

        // Read and store headers
        $this->headers = $this->readRow();

        if (empty($this->headers)) {
            throw new \RuntimeException("CSV file has no headers: {$filePath}");
        }

        // Clean BOM from first header if present (UTF-8 BOM)
        if (!empty($this->headers[0])) {
            $this->headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $this->headers[0]);
        }

        return true;
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
        if ($this->handle === null) {
            throw new \RuntimeException('No file opened. Call open() first.');
        }

        $rows = [];

        for ($i = 0; $i < $batchSize && !$this->eof; $i++) {
            $row = $this->readRow();

            if ($row === null) {
                $this->eof = true;
                break;
            }

            // Skip empty rows
            if (empty(array_filter($row))) {
                continue;
            }

            // Combine with headers to create associative array
            if (count($row) === count($this->headers)) {
                $rows[] = array_combine($this->headers, $row);
            } else {
                // Handle row with different column count
                $combined = [];
                foreach ($this->headers as $index => $header) {
                    $combined[$header] = $row[$index] ?? null;
                }
                $rows[] = $combined;
            }

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
        return !$this->eof && $this->handle !== null && !feof($this->handle);
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
     * @param int $offset Row offset (0-based, excluding header)
     * @return bool
     */
    public function seek(int $offset): bool
    {
        if ($this->handle === null) {
            throw new \RuntimeException('No file opened. Call open() first.');
        }

        // Rewind to beginning
        rewind($this->handle);
        $this->eof = false;

        // Skip header row
        $this->readRow();

        // Skip to desired offset
        for ($i = 0; $i < $offset && !$this->eof; $i++) {
            $row = $this->readRow();
            if ($row === null) {
                $this->eof = true;
                return false;
            }
        }

        $this->offset = $offset;
        return true;
    }

    /**
     * Get total row count (excluding header).
     *
     * @return int
     */
    public function getTotalRows(): int
    {
        if ($this->totalRows !== null) {
            return $this->totalRows;
        }

        if ($this->filePath === null) {
            return 0;
        }

        // Count lines in file (excluding header)
        $count = 0;
        $handle = fopen($this->filePath, 'r');

        if ($handle === false) {
            return 0;
        }

        // Skip header
        fgets($handle);

        while (!feof($handle)) {
            $line = fgets($handle);
            if ($line !== false && trim($line) !== '') {
                $count++;
            }
        }

        fclose($handle);

        $this->totalRows = $count;
        return $this->totalRows;
    }

    /**
     * Close file handle.
     *
     * @return void
     */
    public function close(): void
    {
        if ($this->handle !== null) {
            fclose($this->handle);
            $this->handle = null;
        }

        $this->filePath  = null;
        $this->headers   = [];
        $this->offset    = 0;
        $this->totalRows = null;
        $this->eof       = false;
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
     * Read a single row from CSV.
     *
     * @return array|null Row data or null if EOF/error
     */
    private function readRow(): ?array
    {
        if ($this->handle === null || feof($this->handle)) {
            return null;
        }

        $row = fgetcsv($this->handle, 0, $this->delimiter, $this->enclosure, $this->escape);

        if ($row === false) {
            return null;
        }

        // Convert encoding to UTF-8 if needed
        return array_map(function ($value) {
            if ($value === null) {
                return '';
            }

            // Detect and convert encoding
            $encoding = mb_detect_encoding($value, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);

            if ($encoding && $encoding !== 'UTF-8') {
                return mb_convert_encoding($value, 'UTF-8', $encoding);
            }

            return $value;
        }, $row);
    }

    /**
     * Auto-detect CSV delimiter.
     *
     * @param string $filePath Path to CSV file
     * @return string Detected delimiter
     */
    public static function detectDelimiter(string $filePath): string
    {
        $delimiters = [',', ';', "\t", '|'];
        $counts     = [];

        $handle = fopen($filePath, 'r');

        if ($handle === false) {
            return ',';
        }

        // Read first few lines
        $lines = [];
        for ($i = 0; $i < 5 && !feof($handle); $i++) {
            $line = fgets($handle);
            if ($line !== false) {
                $lines[] = $line;
            }
        }

        fclose($handle);

        // Count occurrences of each delimiter
        foreach ($delimiters as $delimiter) {
            $counts[$delimiter] = 0;
            foreach ($lines as $line) {
                $counts[$delimiter] += substr_count($line, $delimiter);
            }
        }

        // Return delimiter with highest count
        arsort($counts);
        return array_key_first($counts) ?: ',';
    }
}
