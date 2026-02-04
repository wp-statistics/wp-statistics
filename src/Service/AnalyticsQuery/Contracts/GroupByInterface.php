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
     * @param array $requestedColumns Optional list of requested column aliases to filter which columns to include.
     * @return array
     */
    public function getSelectColumns(array $requestedColumns = []): array;

    /**
     * Get aliases of extra columns.
     *
     * @return array Array of extra column aliases.
     */
    public function getExtraColumnAliases(): array;

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

    /**
     * Post-process query results.
     *
     * Called after the main query to allow groupBy-specific transformations.
     *
     * @param array  $rows  Query result rows.
     * @param \wpdb  $wpdb  WordPress database instance.
     * @return array Processed rows.
     */
    public function postProcess(array $rows, \wpdb $wpdb): array;
}
