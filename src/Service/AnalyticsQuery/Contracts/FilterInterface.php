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
     * Get filter configuration as array (for serialization/localization).
     *
     * @return array
     */
    public function toArray(): array;
}
