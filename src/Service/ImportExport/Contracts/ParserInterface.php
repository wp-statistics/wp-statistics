<?php

namespace WP_Statistics\Service\ImportExport\Contracts;

/**
 * Interface for file parsers.
 *
 * Parsers handle reading data from different file formats (CSV, JSON, etc.)
 * with support for streaming/batch reading for large files.
 *
 * @since 15.0.0
 */
interface ParserInterface
{
    /**
     * Open a file for reading.
     *
     * @param string $filePath Absolute path to the file
     * @return bool True if file opened successfully
     * @throws \RuntimeException If file cannot be opened
     */
    public function open(string $filePath): bool;

    /**
     * Get column headers from the file.
     *
     * For CSV: First row
     * For JSON: Top-level keys or first object keys
     *
     * @return array<string> List of header/column names
     */
    public function getHeaders(): array;

    /**
     * Read a batch of rows from the file.
     *
     * Returns associative arrays with headers as keys.
     *
     * @param int $batchSize Number of rows to read (default: 100)
     * @return array<array<string, mixed>> Array of rows
     */
    public function readBatch(int $batchSize = 100): array;

    /**
     * Check if more data is available to read.
     *
     * @return bool True if more rows available
     */
    public function hasMore(): bool;

    /**
     * Get current position/offset in the file.
     *
     * @return int Current row offset (0-based)
     */
    public function getOffset(): int;

    /**
     * Seek to a specific position in the file.
     *
     * @param int $offset Row offset to seek to (0-based)
     * @return bool True if seek successful
     */
    public function seek(int $offset): bool;

    /**
     * Get total row count (excluding header).
     *
     * May require full file scan for some formats.
     *
     * @return int Total number of data rows
     */
    public function getTotalRows(): int;

    /**
     * Close the file handle and release resources.
     *
     * @return void
     */
    public function close(): void;

    /**
     * Get the file path currently being parsed.
     *
     * @return string|null File path or null if no file open
     */
    public function getFilePath(): ?string;
}
