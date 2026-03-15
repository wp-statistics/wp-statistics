<?php

namespace WP_Statistics\Service\AnalyticsQuery\Contracts;

/**
 * Interface for analytics sources.
 *
 * @since 15.0.0
 */
interface SourceInterface
{
    /**
     * Get the source name/identifier.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get the SQL aggregation expression.
     *
     * @return string
     */
    public function getExpression(): string;

    /**
     * Get the SQL expression with alias.
     *
     * @return string
     */
    public function getExpressionWithAlias(): string;

    /**
     * Get the primary table required for this source.
     *
     * @return string
     */
    public function getTable(): string;

    /**
     * Get the data type (integer, float).
     *
     * @return string
     */
    public function getType(): string;

    /**
     * Get the format hint (number, percent, duration).
     *
     * @return string
     */
    public function getFormat(): string;

    /**
     * Get special requirements (e.g., subqueries).
     *
     * @return string|null
     */
    public function getRequirement(): ?string;

    /**
     * Check if this source can use summary tables.
     *
     * Sources that can be retrieved from pre-aggregated summary tables
     * should return true. Dimensional sources (country, device, etc.)
     * should return false.
     *
     * @return bool
     */
    public function supportsSummaryTable(): bool;

    /**
     * Set query context for context-aware expressions.
     *
     * @param array       $groupBy  Array of group_by dimension names
     * @param array       $filters  Array of active filters with their values
     * @param string|null $dateFrom Start date for the query (Y-m-d format)
     * @param string|null $dateTo   End date for the query (Y-m-d format)
     * @return void
     */
    public function setContext(array $groupBy, array $filters = [], ?string $dateFrom = null, ?string $dateTo = null): void;
}
