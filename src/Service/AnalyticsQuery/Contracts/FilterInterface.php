<?php

namespace WP_Statistics\Service\AnalyticsQuery\Contracts;

/**
 * Interface for analytics query filters.
 *
 * @since 15.0.0
 */
interface FilterInterface
{
    /**
     * Get the filter name/identifier.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get the SQL column expression.
     *
     * @return string
     */
    public function getColumn(): string;

    /**
     * Get the data type for sanitization (string, integer, boolean, float).
     *
     * @return string
     */
    public function getType(): string;

    /**
     * Get JOIN configurations required for this filter.
     *
     * @return array|null
     */
    public function getJoins(): ?array;

    /**
     * Get the table requirement (e.g., 'views').
     *
     * @return string|null
     */
    public function getRequirement(): ?string;

    /**
     * Get supported operators for this filter.
     *
     * @return array
     */
    public function getSupportedOperators(): array;

    /**
     * Get human-readable label for the filter.
     *
     * @return string
     */
    public function getLabel(): string;

    /**
     * Get the input type for UI rendering.
     *
     * @return string
     */
    public function getInputType(): string;

    /**
     * Get static options for dropdown/multi-select filters.
     *
     * @return array|null
     */
    public function getOptions(): ?array;

    /**
     * Get the pages where this filter is available.
     *
     * @return array
     */
    public function getPages(): array;

    /**
     * Check if this filter is searchable (requires AJAX).
     *
     * @return bool
     */
    public function isSearchable(): bool;

    /**
     * Get options for searchable filters via AJAX.
     *
     * @param string $search Search term.
     * @param int    $limit  Maximum results.
     * @return array Array of options with 'value' and 'label'.
     */
    public function searchOptions(string $search = '', int $limit = 20): array;

    /**
     * Get filter configuration as array (for serialization/localization).
     *
     * @return array
     */
    public function toArray(): array;
}
