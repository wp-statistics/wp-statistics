<?php

namespace WP_Statistics\Service\AnalyticsQuery\Contracts;

/**
 * Interface for analytics group by.
 *
 * @since 15.0.0
 */
interface GroupByInterface
{
    /**
     * Get the group by name/identifier.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get the primary column expression.
     *
     * @return string
     */
    public function getColumn(): string;

    /**
     * Get the column alias in results.
     *
     * @return string
     */
    public function getAlias(): string;

    /**
     * Get SELECT columns including extra columns.
     *
     * @return array
     */
    public function getSelectColumns(): array;

    /**
     * Get JOIN configurations.
     *
     * @return array
     */
    public function getJoins(): array;

    /**
     * Get GROUP BY expression.
     *
     * @return string|null
     */
    public function getGroupBy(): ?string;

    /**
     * Get default ORDER direction.
     *
     * @return string
     */
    public function getOrder(): string;

    /**
     * Get any required WHERE filter.
     *
     * @return string|null
     */
    public function getFilter(): ?string;

    /**
     * Get table requirement (e.g., 'views').
     *
     * @return string|null
     */
    public function getRequirement(): ?string;
}
