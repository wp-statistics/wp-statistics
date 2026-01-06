<?php

namespace WP_Statistics\Service\ImportExport\Adapters;

use WP_Statistics\Service\ImportExport\Contracts\ImportAdapterInterface;

/**
 * Abstract base class for import adapters.
 *
 * Provides common functionality and default implementations for import adapters.
 *
 * @since 15.0.0
 */
abstract class AbstractImportAdapter implements ImportAdapterInterface
{
    /**
     * Adapter name/key.
     *
     * @var string
     */
    protected $name = '';

    /**
     * Human-readable label.
     *
     * @var string
     */
    protected $label = '';

    /**
     * Supported file extensions.
     *
     * @var array<string>
     */
    protected $extensions = [];

    /**
     * Required columns in source file.
     *
     * @var array<string>
     */
    protected $requiredColumns = [];

    /**
     * Optional columns in source file.
     *
     * @var array<string>
     */
    protected $optionalColumns = [];

    /**
     * Field mapping (source => target).
     *
     * @var array<string, string>
     */
    protected $fieldMapping = [];

    /**
     * Target tables for import.
     *
     * @var array<string>
     */
    protected $targetTables = [];

    /**
     * Whether this adapter imports aggregate data.
     *
     * @var bool
     */
    protected $isAggregate = false;

    /**
     * Get the unique identifier for this adapter.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get human-readable label for this adapter.
     *
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * Get supported file extensions.
     *
     * @return array<string>
     */
    public function getSupportedExtensions(): array
    {
        return $this->extensions;
    }

    /**
     * Get required columns in the source file.
     *
     * @return array<string>
     */
    public function getRequiredColumns(): array
    {
        return $this->requiredColumns;
    }

    /**
     * Get optional columns that can be imported.
     *
     * @return array<string>
     */
    public function getOptionalColumns(): array
    {
        return $this->optionalColumns;
    }

    /**
     * Get the default field mapping from source to WP Statistics schema.
     *
     * @return array<string, string>
     */
    public function getFieldMapping(): array
    {
        return $this->fieldMapping;
    }

    /**
     * Get the target table(s) for this adapter.
     *
     * @return array<string>
     */
    public function getTargetTables(): array
    {
        return $this->targetTables;
    }

    /**
     * Whether this adapter imports aggregate data.
     *
     * @return bool
     */
    public function isAggregateImport(): bool
    {
        return $this->isAggregate;
    }

    /**
     * Validate the source file format and structure.
     *
     * Default implementation checks for required columns.
     *
     * @param string $filePath Path to the source file
     * @param array  $headers  Headers/columns from the file
     * @return bool
     */
    public function validateSource(string $filePath, array $headers): bool
    {
        // Check if all required columns are present
        foreach ($this->requiredColumns as $required) {
            if (!in_array($required, $headers, true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Map a source value using field mapping.
     *
     * @param array $sourceRow   Source row data
     * @param array $fieldMapping Custom field mapping (uses default if empty)
     * @return array Mapped row with target field names
     */
    protected function mapFields(array $sourceRow, array $fieldMapping = []): array
    {
        $mapping = !empty($fieldMapping) ? $fieldMapping : $this->fieldMapping;
        $result  = [];

        foreach ($mapping as $sourceField => $targetField) {
            if (isset($sourceRow[$sourceField])) {
                $result[$targetField] = $sourceRow[$sourceField];
            }
        }

        // Include unmapped fields with their original names
        foreach ($sourceRow as $field => $value) {
            if (!isset($mapping[$field]) && !isset($result[$field])) {
                $result[$field] = $value;
            }
        }

        return $result;
    }

    /**
     * Normalize a date value to standard format.
     *
     * @param string $date   Date string
     * @param string $format Expected input format
     * @return string|null Normalized date (Y-m-d) or null if invalid
     */
    protected function normalizeDate(string $date, string $format = 'Y-m-d'): ?string
    {
        if (empty($date)) {
            return null;
        }

        // Try to parse with expected format
        $dateTime = \DateTime::createFromFormat($format, $date);

        if ($dateTime !== false) {
            return $dateTime->format('Y-m-d');
        }

        // Fallback: try standard parsing
        $timestamp = strtotime($date);

        if ($timestamp !== false) {
            return date('Y-m-d', $timestamp);
        }

        return null;
    }

    /**
     * Normalize a datetime value to standard format.
     *
     * @param string $datetime DateTime string
     * @param string $format   Expected input format
     * @return string|null Normalized datetime (Y-m-d H:i:s) or null if invalid
     */
    protected function normalizeDateTime(string $datetime, string $format = 'Y-m-d H:i:s'): ?string
    {
        if (empty($datetime)) {
            return null;
        }

        $dateTime = \DateTime::createFromFormat($format, $datetime);

        if ($dateTime !== false) {
            return $dateTime->format('Y-m-d H:i:s');
        }

        $timestamp = strtotime($datetime);

        if ($timestamp !== false) {
            return date('Y-m-d H:i:s', $timestamp);
        }

        return null;
    }

    /**
     * Normalize an integer value.
     *
     * @param mixed $value Value to normalize
     * @param int   $default Default value if invalid
     * @return int
     */
    protected function normalizeInt($value, int $default = 0): int
    {
        if (is_numeric($value)) {
            return (int)$value;
        }

        return $default;
    }

    /**
     * Normalize a string value.
     *
     * @param mixed $value Value to normalize
     * @param int   $maxLength Maximum length (0 = no limit)
     * @return string
     */
    protected function normalizeString($value, int $maxLength = 0): string
    {
        $string = is_string($value) ? trim($value) : (string)$value;

        if ($maxLength > 0 && strlen($string) > $maxLength) {
            $string = substr($string, 0, $maxLength);
        }

        return $string;
    }

    /**
     * Import a single row of data.
     *
     * Must be implemented by child classes to handle actual database operations.
     *
     * @param array $sourceRow Raw row data from source file
     * @return bool|null True if imported, false if skipped, null if error
     * @throws \Exception On import error
     */
    public function importRow(array $sourceRow)
    {
        // Transform the row
        $data = $this->transformRow($sourceRow);

        if (empty($data)) {
            return false; // Skip empty rows
        }

        // Child classes should override this to implement actual import logic
        throw new \Exception('importRow() must be implemented by ' . static::class);
    }

    /**
     * Initialize the adapter for import.
     *
     * Called before processing starts. Override in child classes
     * to warm caches, set up resolvers, etc.
     *
     * @return void
     */
    public function initialize(): void
    {
        // Default implementation does nothing
    }

    /**
     * Finalize the adapter after import.
     *
     * Called after all records are processed. Override in child classes
     * to perform cleanup, update summary tables, etc.
     *
     * @return void
     */
    public function finalize(): void
    {
        // Default implementation does nothing
    }
}
