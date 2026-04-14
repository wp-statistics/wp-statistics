<?php

namespace WP_Statistics\Service\AnalyticsQuery\Formatters;

use WP_Statistics\Service\AnalyticsQuery\Helpers\PublishedContentHelper;
use WP_Statistics\Service\AnalyticsQuery\Query\Query;
use WP_Statistics\Service\AnalyticsQuery\Registry\GroupByRegistry;

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

        $primaryGroupBy = $groupBy[0];
        $groupByObj     = GroupByRegistry::getInstance()->get($primaryGroupBy);
        $tsConfig       = $groupByObj ? $groupByObj->getTimeSeriesConfig() : null;

        // For time-series groupBy, always use 'date' as the label alias
        // because QueryExecutor normalizes all time-series to 'date' column
        if ($tsConfig !== null) {
            $labelAlias = 'date';
            $rows = $this->fillMissingDates($rows, $query, $tsConfig, $labelAlias, $sources);

            // Weekly/monthly startAdjust can push before user-selected range — clamp back.
            if ($tsConfig['startAdjust'] !== null && !empty($rows)) {
                $originalFrom = substr($query->getDateFrom(), 0, 10);
                if (isset($rows[0][$labelAlias]) && $rows[0][$labelAlias] < $originalFrom) {
                    $rows[0][$labelAlias] = $originalFrom;
                }
            }
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
            if ($tsConfig !== null) {
                $response['previousLabels'] = $this->generatePreviousLabels(
                    $result['compare_from'],
                    $result['compare_to'],
                    $tsConfig
                );
            }
        }

        return $response;
    }

    /**
     * Fill in missing dates for time-series data.
     *
     * Ensures all dates in the query range are present, with zero values for missing dates.
     * Special handling for published_content which queries WordPress posts table directly.
     *
     * @param array  $rows       Existing data rows.
     * @param Query  $query      Query object with date range.
     * @param array  $tsConfig   Time-series config from GroupBy.
     * @param string $labelAlias Alias for the label field.
     * @param array  $sources    Source fields to fill with zeros.
     * @return array Complete rows with all dates filled.
     */
    private function fillMissingDates(array $rows, Query $query, array $tsConfig, string $labelAlias, array $sources): array
    {
        $dateFrom = $query->getDateFrom();
        $dateTo   = $query->getDateTo();

        if (empty($dateFrom) || empty($dateTo)) {
            return $rows;
        }

        $allLabels = $this->generateDateLabels($dateFrom, $dateTo, $tsConfig);

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

        // Pre-fetch published content for all missing dates if needed
        $hasPublishedContent    = in_array('published_content', $sources, true);
        $publishedContentByDate = [];
        if ($hasPublishedContent) {
            $missingLabels = array_diff($allLabels, array_keys($rowIndex));
            if (!empty($missingLabels)) {
                $groupByNames = $query->getGroupBy();
                $publishedContentByDate = PublishedContentHelper::getPublishedContentByDates(
                    $missingLabels,
                    $groupByNames[0],
                    $query->getFilters()
                );
            }
        }

        // Build complete result with all dates
        $filledRows = [];
        foreach ($allLabels as $label) {
            if (isset($rowIndex[$label])) {
                $filledRows[] = $rowIndex[$label];
            } else {
                $emptyRow = [$labelAlias => $label];
                foreach ($sources as $source) {
                    if ($source === 'published_content' && isset($publishedContentByDate[$label])) {
                        $emptyRow[$source] = $publishedContentByDate[$label];
                    } else {
                        $emptyRow[$source] = 0;
                    }
                }
                $filledRows[] = $emptyRow;
            }
        }

        return $filledRows;
    }

    /**
     * Generate all date labels for a date range using time-series config.
     *
     * @param string $dateFrom Start date (YYYY-MM-DD or with time).
     * @param string $dateTo   End date (YYYY-MM-DD or with time).
     * @param array  $tsConfig Time-series config with 'interval', 'format', 'startAdjust'.
     * @return array Array of date labels.
     */
    private function generateDateLabels(string $dateFrom, string $dateTo, array $tsConfig): array
    {
        $start    = new \DateTime(substr($dateFrom, 0, 10));
        $end      = new \DateTime(substr($dateTo, 0, 10));
        $interval = new \DateInterval($tsConfig['interval']);
        $format   = $tsConfig['format'];

        if ($tsConfig['startAdjust'] !== null) {
            $start->modify($tsConfig['startAdjust']);
        }

        $labels = [];

        if ($tsConfig['interval'] === 'P1D') {
            $period = new \DatePeriod($start, $interval, (clone $end)->modify('+1 day'));
            foreach ($period as $date) {
                $labels[] = $date->format($format);
            }
        } else {
            while ($start <= $end) {
                $labels[] = $start->format($format);
                $start->add($interval);
            }
        }

        return $labels;
    }

    /**
     * Generate previous period labels in ISO format for JavaScript Date parsing.
     *
     * Always returns Y-m-d format that JavaScript can reliably parse with new Date().
     *
     * @param string $dateFrom Start date (YYYY-MM-DD or with time).
     * @param string $dateTo   End date (YYYY-MM-DD or with time).
     * @param array  $tsConfig Time-series config with 'interval', 'format', 'startAdjust'.
     * @return array Array of ISO date labels (Y-m-d format).
     */
    private function generatePreviousLabels(string $dateFrom, string $dateTo, array $tsConfig): array
    {
        $start    = new \DateTime(substr($dateFrom, 0, 10));
        $end      = new \DateTime(substr($dateTo, 0, 10));
        $interval = new \DateInterval($tsConfig['interval']);

        if ($tsConfig['startAdjust'] !== null) {
            $start->modify($tsConfig['startAdjust']);
        }

        $labels = [];

        // Always Y-m-d for JS Date parsing compatibility
        if ($tsConfig['interval'] === 'P1D') {
            $period = new \DatePeriod($start, $interval, (clone $end)->modify('+1 day'));
            foreach ($period as $date) {
                $labels[] = $date->format('Y-m-d');
            }
        } else {
            while ($start <= $end) {
                $labels[] = $start->format('Y-m-d');
                $start->add($interval);
            }
        }

        // Weekly/monthly startAdjust can push before comparison start — clamp back.
        if ($tsConfig['startAdjust'] !== null && !empty($labels)) {
            $originalFrom = substr($dateFrom, 0, 10);
            if ($labels[0] < $originalFrom) {
                $labels[0] = $originalFrom;
            }
        }

        return $labels;
    }
}
