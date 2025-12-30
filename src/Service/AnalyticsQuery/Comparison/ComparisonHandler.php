<?php

namespace WP_Statistics\Service\AnalyticsQuery\Comparison;

use WP_Statistics\Service\AnalyticsQuery\Registry\GroupByRegistry;

/**
 * Handles previous period calculations for comparison data.
 *
 * Calculates the appropriate previous period based on the current date range
 * and merges comparison data into results.
 *
 * @since 15.0.0
 */
class ComparisonHandler
{
    /**
     * Sources being compared.
     *
     * @var array
     */
    private $sources = [];

    /**
     * Group by for matching rows.
     *
     * @var array
     */
    private $groupBy = [];

    /**
     * Constructor.
     *
     * @param array $sources    List of source names.
     * @param array $groupBy List of group by names.
     */
    public function __construct(array $sources = [], array $groupBy = [])
    {
        $this->sources    = $sources;
        $this->groupBy = $groupBy;
    }

    /**
     * Calculate the previous period dates based on current period.
     *
     * The previous period has the same duration as the current period,
     * ending one day before the current period starts.
     * Time components are preserved from the original dates.
     *
     * Supported input formats:
     * - Date only: YYYY-MM-DD
     * - With space: YYYY-MM-DD HH:mm:ss (24-hour format)
     * - ISO 8601: YYYY-MM-DDTHH:mm:ss
     *
     * @param string $dateFrom Current period start date/time.
     * @param string $dateTo   Current period end date/time.
     * @return array ['from' => string, 'to' => string]
     */
    public function calculatePreviousPeriod(string $dateFrom, string $dateTo): array
    {
        // Normalize ISO 8601 format (replace T with space)
        $dateFrom = str_replace('T', ' ', $dateFrom);
        $dateTo   = str_replace('T', ' ', $dateTo);

        // Check if times are included
        $hasTime = strlen($dateFrom) > 10 || strlen($dateTo) > 10;

        $from = new \DateTime($dateFrom);
        $to   = new \DateTime($dateTo);

        // Calculate the number of days in current period (based on date portion only)
        $fromDate = new \DateTime(substr($dateFrom, 0, 10));
        $toDate   = new \DateTime(substr($dateTo, 0, 10));
        $diff     = $fromDate->diff($toDate)->days + 1;

        // Previous period ends one day before current period starts
        // Preserve the time from dateTo for prevTo
        $prevTo = (clone $from)->modify('-1 day');
        if ($hasTime && strlen($dateTo) > 10) {
            // Set the time from the original dateTo
            $toTime = substr($dateTo, 11);
            list($hours, $minutes, $seconds) = explode(':', $toTime);
            $prevTo->setTime((int)$hours, (int)$minutes, (int)$seconds);
        }

        // Previous period starts based on the same duration
        // Preserve the time from dateFrom for prevFrom
        $prevFrom = (clone $prevTo)->modify("-" . ($diff - 1) . " days");
        if ($hasTime && strlen($dateFrom) > 10) {
            // Set the time from the original dateFrom
            $fromTime = substr($dateFrom, 11);
            list($hours, $minutes, $seconds) = explode(':', $fromTime);
            $prevFrom->setTime((int)$hours, (int)$minutes, (int)$seconds);
        }

        // Return in appropriate format
        $format = $hasTime ? 'Y-m-d H:i:s' : 'Y-m-d';

        return [
            'from' => $prevFrom->format($format),
            'to'   => $prevTo->format($format),
        ];
    }

    /**
     * Time-series group by types that should use index-based matching.
     *
     * @var array
     */
    private static $timeSeriesGroupBy = ['date', 'week', 'month', 'hour'];

    /**
     * Merge comparison data into current results.
     *
     * For time-series data (date/week/month/hour), matches by position/index.
     * For other data (country/browser/etc), matches by group by values.
     *
     * @param array $current  Current period results.
     * @param array $previous Previous period results.
     * @return array Results with comparison data added.
     */
    public function mergeResults(array $current, array $previous): array
    {
        // Check if this is time-series data (grouped by date/week/month/hour)
        $isTimeSeries = $this->isTimeSeriesGroupBy();

        if ($isTimeSeries) {
            // For time-series: match by index/position
            return $this->mergeByIndex($current, $previous);
        }

        // For non-time-series: match by group by values
        return $this->mergeByKey($current, $previous);
    }

    /**
     * Check if the current groupBy is time-series based.
     *
     * @return bool
     */
    private function isTimeSeriesGroupBy(): bool
    {
        if (empty($this->groupBy)) {
            return false;
        }

        // Check if primary groupBy is a time-series type
        $primaryGroupBy = $this->groupBy[0];
        return in_array($primaryGroupBy, self::$timeSeriesGroupBy, true);
    }

    /**
     * Merge results by index/position (for time-series data).
     *
     * @param array $current  Current period results.
     * @param array $previous Previous period results.
     * @return array Results with comparison data added.
     */
    private function mergeByIndex(array $current, array $previous): array
    {
        // Re-index arrays to ensure numeric keys for positional matching
        $current  = array_values($current);
        $previous = array_values($previous);
        $previousCount = count($previous);

        foreach ($current as $index => &$row) {
            $prevRow = ($index < $previousCount) ? $previous[$index] : null;

            $row['previous'] = [];

            foreach ($this->sources as $source) {
                if ($prevRow && isset($prevRow[$source])) {
                    $row['previous'][$source] = (float) $prevRow[$source];
                } else {
                    $row['previous'][$source] = 0;
                }
            }
        }

        return $current;
    }

    /**
     * Merge results by group by key (for non-time-series data).
     *
     * @param array $current  Current period results.
     * @param array $previous Previous period results.
     * @return array Results with comparison data added.
     */
    private function mergeByKey(array $current, array $previous): array
    {
        // Index previous results by group by key for fast lookup
        $previousIndex = [];
        foreach ($previous as $row) {
            $key                 = $this->getMatchKey($row);
            $previousIndex[$key] = $row;
        }

        // Add comparison data to current results
        foreach ($current as &$row) {
            $key     = $this->getMatchKey($row);
            $prevRow = $previousIndex[$key] ?? null;

            $row['previous'] = [];

            foreach ($this->sources as $source) {
                if ($prevRow && isset($prevRow[$source])) {
                    $row['previous'][$source] = (float) $prevRow[$source];
                } else {
                    $row['previous'][$source] = 0;
                }
            }
        }

        return $current;
    }

    /**
     * Merge comparison data for totals.
     *
     * @param array $current  Current period totals.
     * @param array $previous Previous period totals.
     * @return array Totals with comparison data.
     */
    public function mergeTotals(array $current, array $previous): array
    {
        $result = [];

        foreach ($this->sources as $source) {
            $currentValue  = isset($current[$source]) ? (float) $current[$source] : 0;
            $previousValue = isset($previous[$source]) ? (float) $previous[$source] : 0;

            $result[$source] = [
                'current'  => $currentValue,
                'previous' => $previousValue,
            ];
        }

        return $result;
    }

    /**
     * Get a match key for a row based on group by values.
     *
     * @param array $row Result row.
     * @return string Match key.
     */
    private function getMatchKey(array $row): string
    {
        $keyParts = [];

        foreach ($this->groupBy as $groupByItem) {
            $groupByItemObj = GroupByRegistry::getInstance()->get($groupByItem);
            $alias        = $groupByItemObj ? $groupByItemObj->getAlias() : $groupByItem;
            $keyParts[]   = $row[$alias] ?? '';
        }

        return implode('|', $keyParts);
    }

    /**
     * Set sources for comparison.
     *
     * @param array $sources List of source names.
     * @return self
     */
    public function setSources(array $sources): self
    {
        $this->sources = $sources;
        return $this;
    }

    /**
     * Set group by for matching.
     *
     * @param array $groupBy List of group by names.
     * @return self
     */
    public function setGroupBy(array $groupBy): self
    {
        $this->groupBy = $groupBy;
        return $this;
    }
}
