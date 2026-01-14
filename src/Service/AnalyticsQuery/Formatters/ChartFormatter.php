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
     * Time-series group by types that need date filling.
     *
     * @var array
     */
    private static $timeSeriesGroupBy = ['date', 'week', 'month'];

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

        $primaryGroupBy = $groupBy[0];

        // For time-series groupBy, always use 'date' as the label alias
        // because summary tables and aggregation use 'date' column
        if (in_array($primaryGroupBy, self::$timeSeriesGroupBy, true)) {
            $labelAlias = 'date';
            $rows = $this->fillMissingDates($rows, $query, $primaryGroupBy, $labelAlias, $sources);
        } else {
            $labelAlias = $this->getGroupByAlias($primaryGroupBy);
        }

        // Build labels from the first group by field
        $labels = [];
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
                    $prevData[] = isset($row['previous'][$source]) ? (float) $row['previous'][$source] : null;
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

        // Add comparison info and previousLabels if present
        if (isset($result['compare_from'])) {
            $response['meta']['compare_from'] = $result['compare_from'];
            $response['meta']['compare_to']   = $result['compare_to'];

            // Generate previousLabels for time-series charts
            // Uses ISO format (Y-m-d) for JavaScript Date parsing compatibility
            if (in_array($primaryGroupBy, self::$timeSeriesGroupBy, true)) {
                $response['previousLabels'] = $this->generatePreviousLabels(
                    $result['compare_from'],
                    $result['compare_to'],
                    $primaryGroupBy
                );
            }
        }

        return $response;
    }

    /**
     * Fill in missing dates for time-series data.
     *
     * Ensures all dates in the query range are present, with zero values for missing dates.
     *
     * @param array  $rows        Existing data rows.
     * @param Query  $query       Query object with date range.
     * @param string $groupByType Type of grouping (date, week, month).
     * @param string $labelAlias  Alias for the label field.
     * @param array  $sources     Source fields to fill with zeros.
     * @return array Complete rows with all dates filled.
     */
    private function fillMissingDates(array $rows, Query $query, string $groupByType, string $labelAlias, array $sources): array
    {
        $dateFrom = $query->getDateFrom();
        $dateTo   = $query->getDateTo();

        if (empty($dateFrom) || empty($dateTo)) {
            return $rows;
        }

        // Generate all expected labels for the date range
        $allLabels = $this->generateDateLabels($dateFrom, $dateTo, $groupByType);

        if (empty($allLabels)) {
            return $rows;
        }

        // Index existing rows by their label
        $rowIndex = [];
        foreach ($rows as $row) {
            $label = $row[$labelAlias] ?? '';
            if ($label !== '') {
                $rowIndex[$label] = $row;
            }
        }

        // Build complete result with all dates
        $filledRows = [];
        foreach ($allLabels as $label) {
            if (isset($rowIndex[$label])) {
                $filledRows[] = $rowIndex[$label];
            } else {
                // Create empty row with zeros
                $emptyRow = [$labelAlias => $label];
                foreach ($sources as $source) {
                    $emptyRow[$source] = 0;
                }
                // Don't add previous data for filled dates - let them be null
                // This ensures missing previous period dates show as gaps, not zeros
                $filledRows[] = $emptyRow;
            }
        }

        return $filledRows;
    }

    /**
     * Generate all date labels for a date range based on grouping type.
     *
     * @param string $dateFrom    Start date (YYYY-MM-DD or with time).
     * @param string $dateTo      End date (YYYY-MM-DD or with time).
     * @param string $groupByType Type of grouping (date, week, month).
     * @return array Array of date labels.
     */
    private function generateDateLabels(string $dateFrom, string $dateTo, string $groupByType): array
    {
        // Extract just the date part
        $startDate = substr($dateFrom, 0, 10);
        $endDate   = substr($dateTo, 0, 10);

        $start = new \DateTime($startDate);
        $end   = new \DateTime($endDate);

        $labels = [];

        switch ($groupByType) {
            case 'date':
                // Daily: Generate each day
                $interval = new \DateInterval('P1D');
                $period   = new \DatePeriod($start, $interval, $end->modify('+1 day'));
                foreach ($period as $date) {
                    $labels[] = $date->format('Y-m-d');
                }
                break;

            case 'week':
                // Weekly: Generate week start dates (Monday)
                // Adjust start to Monday of that week
                $dayOfWeek = (int) $start->format('N'); // 1 = Monday, 7 = Sunday
                if ($dayOfWeek !== 1) {
                    $start->modify('monday this week');
                }
                $interval = new \DateInterval('P1W');
                while ($start <= $end) {
                    $labels[] = $start->format('Y-m-d');
                    $start->add($interval);
                }
                break;

            case 'month':
                // Monthly: Generate first day of each month
                $start->modify('first day of this month');
                $interval = new \DateInterval('P1M');
                while ($start <= $end) {
                    $labels[] = $start->format('Y-m');
                    $start->add($interval);
                }
                break;
        }

        return $labels;
    }

    /**
     * Generate previous period labels in ISO format for JavaScript Date parsing.
     *
     * Unlike generateDateLabels which matches database format, this always returns
     * Y-m-d format that JavaScript can reliably parse with new Date().
     *
     * @param string $dateFrom    Start date (YYYY-MM-DD or with time).
     * @param string $dateTo      End date (YYYY-MM-DD or with time).
     * @param string $groupByType Type of grouping (date, week, month).
     * @return array Array of ISO date labels (Y-m-d format).
     */
    private function generatePreviousLabels(string $dateFrom, string $dateTo, string $groupByType): array
    {
        // Extract just the date part
        $startDate = substr($dateFrom, 0, 10);
        $endDate   = substr($dateTo, 0, 10);

        $start = new \DateTime($startDate);
        $end   = new \DateTime($endDate);

        $labels = [];

        switch ($groupByType) {
            case 'date':
                // Daily: Generate each day
                $interval = new \DateInterval('P1D');
                $period   = new \DatePeriod($start, $interval, $end->modify('+1 day'));
                foreach ($period as $date) {
                    $labels[] = $date->format('Y-m-d');
                }
                break;

            case 'week':
                // Weekly: Generate week start dates (Monday)
                $dayOfWeek = (int) $start->format('N');
                if ($dayOfWeek !== 1) {
                    $start->modify('monday this week');
                }
                $interval = new \DateInterval('P1W');
                while ($start <= $end) {
                    $labels[] = $start->format('Y-m-d');
                    $start->add($interval);
                }
                break;

            case 'month':
                // Monthly: Generate first day of each month (always Y-m-d for JS parsing)
                $start->modify('first day of this month');
                $interval = new \DateInterval('P1M');
                while ($start <= $end) {
                    $labels[] = $start->format('Y-m-d');
                    $start->add($interval);
                }
                break;
        }

        return $labels;
    }
}
