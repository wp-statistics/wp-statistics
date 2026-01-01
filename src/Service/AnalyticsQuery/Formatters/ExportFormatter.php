<?php

namespace WP_Statistics\Service\AnalyticsQuery\Formatters;

use WP_Statistics\Service\AnalyticsQuery\Query\Query;

/**
 * Export response formatter.
 *
 * Produces a CSV-ready format with headers and row arrays.
 * Use cases: CSV/Excel exports, PDF reports, third-party integrations.
 *
 * Output structure:
 * {
 *   "success": true,
 *   "headers": ["Date", "Visitors", "Views"],
 *   "rows": [
 *     ["2024-11-01", 100, 250],
 *     ["2024-11-02", 120, 280]
 *   ],
 *   "meta": {...}
 * }
 *
 * @since 15.0.0
 */
class ExportFormatter extends AbstractFormatter
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'export';
    }

    /**
     * {@inheritdoc}
     */
    public function format(Query $query, array $result): array
    {
        $groupBy    = $query->getGroupBy();
        $sources    = $query->getSources();
        $rows       = $result['rows'] ?? [];
        $hasCompare = $query->hasComparison();

        // Build headers
        $headers = [];

        // Add group by headers first
        foreach ($groupBy as $groupByItem) {
            $headers[] = $this->getGroupByLabel($groupByItem);
        }

        // Add source headers
        foreach ($sources as $source) {
            $headers[] = $this->getSourceLabel($source);

            // If comparison, add previous and change columns
            if ($hasCompare) {
                $headers[] = sprintf(
                    /* translators: %s: metric name */
                    __('%s (Previous)', 'wp-statistics'),
                    $this->getSourceLabel($source)
                );
                $headers[] = __('Change %', 'wp-statistics');
            }
        }

        // Build row arrays
        $exportRows = [];

        foreach ($rows as $row) {
            $exportRow = [];

            // Add group by values
            foreach ($groupBy as $groupByItem) {
                $alias       = $this->getGroupByAlias($groupByItem);
                $exportRow[] = $row[$alias] ?? '';
            }

            // Add source values
            foreach ($sources as $source) {
                $currentValue = isset($row[$source]) ? (float) $row[$source] : 0;
                $exportRow[]  = $currentValue;

                // If comparison, add previous and change
                if ($hasCompare) {
                    $previousValue = isset($row['previous'][$source]) ? (float) $row['previous'][$source] : 0;
                    $change        = $this->calculateChange($currentValue, $previousValue);

                    $exportRow[] = $previousValue;
                    $exportRow[] = $this->formatChangeString($change);
                }
            }

            $exportRows[] = $exportRow;
        }

        $response = [
            'success' => true,
            'headers' => $headers,
            'rows'    => $exportRows,
            'meta'    => $this->buildBaseMeta($query),
        ];

        // Add comparison info if present
        if (isset($result['compare_from'])) {
            $response['meta']['compare_from'] = $result['compare_from'];
            $response['meta']['compare_to']   = $result['compare_to'];
        }

        return $response;
    }
}
