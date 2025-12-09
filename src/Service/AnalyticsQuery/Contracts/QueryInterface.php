<?php

namespace WP_Statistics\Service\AnalyticsQuery\Contracts;

/**
 * Interface for immutable query objects.
 *
 * @since 15.0.0
 */
interface QueryInterface
{
    /**
     * Get sources.
     *
     * @return array
     */
    public function getSources(): array;

    /**
     * Get group by.
     *
     * @return array
     */
    public function getGroupBy(): array;

    /**
     * Get filters.
     *
     * @return array
     */
    public function getFilters(): array;

    /**
     * Get start date.
     *
     * @return string|null
     */
    public function getDateFrom(): ?string;

    /**
     * Get end date.
     *
     * @return string|null
     */
    public function getDateTo(): ?string;

    /**
     * Get ORDER BY field.
     *
     * @return string|null
     */
    public function getOrderBy(): ?string;

    /**
     * Get ORDER direction.
     *
     * @return string
     */
    public function getOrder(): string;

    /**
     * Get page number.
     *
     * @return int
     */
    public function getPage(): int;

    /**
     * Get items per page.
     *
     * @return int
     */
    public function getPerPage(): int;

    /**
     * Check if comparison is enabled.
     *
     * @return bool
     */
    public function hasComparison(): bool;

    /**
     * Convert to array.
     *
     * @return array
     */
    public function toArray(): array;
}
