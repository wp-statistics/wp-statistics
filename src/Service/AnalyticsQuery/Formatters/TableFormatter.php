<?php

namespace WP_Statistics\Service\AnalyticsQuery\Formatters;

use WP_Statistics\Service\AnalyticsQuery\Query\Query;

/**
 * Table response formatter.
 *
 * Produces a structured response format optimized for data tables, grids, and complex widgets.
 * Returns data with rows, totals, and comprehensive metadata in a nested structure.
 *
 * @since 15.0.0
 */
class TableFormatter extends AbstractFormatter
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'table';
    }

    /**
     * {@inheritdoc}
     */
    public function format(Query $query, array $result): array
    {
        $groupBy    = $query->getGroupBy();
        $perPage    = $query->getPerPage();
        $page       = $query->getPage();
        $totalRows  = $result['total'] ?? 0;
        $rows       = $result['rows'] ?? [];
        $showTotals = $query->showTotals();

        $response = [
            'success' => true,
            'data'    => [],
            'meta'    => $this->buildBaseMeta($query),
        ];

        // Add total rows count
        $response['meta']['total_rows'] = $totalRows;

        // Add rows if group by are present
        if (!empty($groupBy)) {
            $response['data']['rows']        = $rows;
            $response['meta']['page']        = $page;
            $response['meta']['per_page']    = $perPage;
            $response['meta']['total_pages'] = $perPage > 0 ? ceil($totalRows / $perPage) : 0;
        }

        // Add totals only if requested
        if ($showTotals && isset($result['totals']) && $result['totals'] !== null) {
            $response['data']['totals'] = $result['totals'];
        }

        // Add comparison info if present
        if (isset($result['compare_from'])) {
            $response['meta']['compare_from'] = $result['compare_from'];
            $response['meta']['compare_to']   = $result['compare_to'];
        }

        return $response;
    }
}
