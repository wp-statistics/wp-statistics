<?php

namespace WP_Statistics\Service\AnalyticsQuery\Formatters;

use WP_Statistics\Service\AnalyticsQuery\Query\Query;

/**
 * Chart response formatter.
 *
 * Produces a structure optimized for chart libraries (Chart.js, ApexCharts, Recharts).
 * Use cases: Line charts, bar charts, area charts, multi-series charts.
 *
 * Output structure:
 * {
 *   "success": true,
 *   "labels": ["2024-11-01", "2024-11-02", ...],
 *   "datasets": [
 *     { "label": "Visitors", "data": [100, 120, ...] },
 *     { "label": "Views", "data": [250, 280, ...] }
 *   ],
 *   "meta": {...}
 * }
 *
 * @since 15.0.0
 */
class ChartFormatter extends AbstractFormatter
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'chart';
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

        // Chart format requires group_by to generate labels
        if (empty($groupBy)) {
            return [
                'success' => false,
                'error'   => [
                    'code'    => 'chart_requires_group_by',
                    'message' => __('Chart format requires at least one group_by field to generate labels.', 'wp-statistics'),
                ],
            ];
        }

        // Build labels from the first group by field
        $primaryGroupBy = $groupBy[0];
        $labelAlias     = $this->getGroupByAlias($primaryGroupBy);
        $labels         = [];

        foreach ($rows as $row) {
            $labels[] = $row[$labelAlias] ?? '';
        }

        // Build datasets for each source
        $datasets = [];

        foreach ($sources as $source) {
            $data = [];
            foreach ($rows as $row) {
                $data[] = isset($row[$source]) ? (float) $row[$source] : 0;
            }

            $datasets[] = [
                'label' => $this->getSourceLabel($source),
                'key'   => $source,
                'data'  => $data,
            ];

            // If comparison is enabled, add a dataset for previous period
            if ($hasCompare) {
                $prevData = [];
                foreach ($rows as $row) {
                    $prevData[] = isset($row['previous'][$source]) ? (float) $row['previous'][$source] : 0;
                }

                $datasets[] = [
                    'label'      => sprintf(
                        /* translators: %s: metric name */
                        __('%s (Previous)', 'wp-statistics'),
                        $this->getSourceLabel($source)
                    ),
                    'key'        => $source . '_previous',
                    'data'       => $prevData,
                    'comparison' => true,
                ];
            }
        }

        $response = [
            'success'  => true,
            'labels'   => $labels,
            'datasets' => $datasets,
            'meta'     => $this->buildBaseMeta($query),
        ];

        // Add comparison info if present
        if (isset($result['compare_from'])) {
            $response['meta']['compare_from'] = $result['compare_from'];
            $response['meta']['compare_to']   = $result['compare_to'];
        }

        return $response;
    }
}
