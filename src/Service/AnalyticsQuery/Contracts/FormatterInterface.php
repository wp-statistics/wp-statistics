<?php

namespace WP_Statistics\Service\AnalyticsQuery\Contracts;

use WP_Statistics\Service\AnalyticsQuery\Query\Query;

/**
 * Interface for response formatters.
 *
 * Formatters transform raw query results into different output structures
 * optimized for various use cases (tables, charts, exports, etc.).
 *
 * @since 15.0.0
 */
interface FormatterInterface
{
    /**
     * Get the formatter name.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Format the query results.
     *
     * @param Query $query  The query object.
     * @param array $result Raw query results with 'rows', 'totals', 'total'.
     * @return array Formatted response structure.
     */
    public function format(Query $query, array $result): array;

    /**
     * Check if this formatter supports the given format name.
     *
     * @param string $format Format name.
     * @return bool
     */
    public function supports(string $format): bool;
}
