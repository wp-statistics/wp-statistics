<?php

namespace WP_Statistics\Service\AnalyticsQuery\Contracts;

/**
 * Interface for query executors.
 *
 * @since 15.0.0
 */
interface QueryExecutorInterface
{
    /**
     * Execute a query and return results.
     *
     * @param QueryInterface $query The query to execute.
     * @return array ['rows' => array, 'total' => int]
     */
    public function execute(QueryInterface $query): array;

    /**
     * Execute a totals query (no group by).
     *
     * @param QueryInterface $query The query to execute.
     * @return array Totals associative array.
     */
    public function executeTotals(QueryInterface $query): array;
}
