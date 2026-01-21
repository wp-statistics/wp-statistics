<?php

namespace WP_Statistics\Service\AnalyticsQuery\Comparison;

use WP_Statistics\Service\AnalyticsQuery\Helpers\PublishedContentHelper;
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
     * Comparison mode constants.
     */
    const MODE_PREVIOUS_PERIOD = 'previous_period';
    const MODE_PREVIOUS_PERIOD_DOW = 'previous_period_dow';
    const MODE_SAME_PERIOD_LAST_YEAR = 'same_period_last_year';
    const MODE_CUSTOM = 'custom';

    /**
     * Valid comparison modes.
     *
     * @var array
     */
    public static $validModes = [
        self::MODE_PREVIOUS_PERIOD,
        self::MODE_PREVIOUS_PERIOD_DOW,
        self::MODE_SAME_PERIOD_LAST_YEAR,
        self::MODE_CUSTOM,
    ];

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
     * Previous period date range for filling missing dates.
     *
     * @var array|null ['from' => string, 'to' => string]
     */
    private $previousPeriodRange = null;

    /**
     * Current period date range for filling missing dates.
     *
     * @var array|null ['from' => string, 'to' => string]
     */
    private $currentPeriodRange = null;

    /**
     * Active filters for published content queries.
     *
     * @var array
     */
    private $filters = [];

    /**
     * Constructor.
     *
     * @param array $sources    List of source WP_Statistics_names.
     * @param array $groupBy List of group by WP_Statistics_names.
     */
    public function __construct(array $sources = [], array $groupBy = [])
    {
        $this->sources    = $sources;
        $this->groupBy = $groupBy;
    }

    /**
     * Check if a comparison mode is valid.
     *
     * @param string|null $mode Comparison mode to validate.
     * @return bool
     */
    public static function isValidMode(?string $mode): bool
    {
        return $mode !== null && in_array($mode, self::$validModes, true);
    }

    /**
     * Calculate comparison period based on mode.
     *
     * @param string $dateFrom Current period start date.
     * @param string $dateTo   Current period end date.
     * @param string $mode     Comparison mode (default: previous_period).
     * @return array ['from' => string, 'to' => string]
     */
    public function calculateComparisonPeriod(string $dateFrom, string $dateTo, string $mode = self::MODE_PREVIOUS_PERIOD): array
    {
        switch ($mode) {
            case self::MODE_PREVIOUS_PERIOD_DOW:
                return $this->calculatePreviousPeriodDOW($dateFrom, $dateTo);

            case self::MODE_SAME_PERIOD_LAST_YEAR:
                return $this->calculateSamePeriodLastYear($dateFrom, $dateTo);

            case self::MODE_PREVIOUS_PERIOD:
            default:
                return $this->calculatePreviousPeriod($dateFrom, $dateTo);
        }
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
     * Calculate previous period with day-of-week alignment.
     *
     * Shifts by full weeks (7, 14, 21 days, etc.) to maintain the same weekdays.
     * This is useful for comparing traffic patterns that vary by day of week.
     *
     * @param string $dateFrom Current period start date.
     * @param string $dateTo   Current period end date.
     * @return array ['from' => string, 'to' => string]
     */
    public function calculatePreviousPeriodDOW(string $dateFrom, string $dateTo): array
    {
        // Normalize ISO 8601 format (replace T with space)
        $dateFrom = str_replace('T', ' ', $dateFrom);
        $dateTo   = str_replace('T', ' ', $dateTo);

        // Check if times are included
        $hasTime = strlen($dateFrom) > 10 || strlen($dateTo) > 10;

        $from = new \DateTime(substr($dateFrom, 0, 10));
        $to   = new \DateTime(substr($dateTo, 0, 10));

        // Calculate the number of days in current period
        $daysDiff = $from->diff($to)->days + 1;

        // Calculate weeks to shift (must be full weeks to preserve day-of-week)
        $weeksToShift = (int) ceil($daysDiff / 7);
        $daysToShift = $weeksToShift * 7;

        // Create previous period with same day-of-week
        $prevFrom = (clone $from)->modify("-{$daysToShift} days");
        $prevTo = (clone $to)->modify("-{$daysToShift} days");

        // Preserve time if included
        if ($hasTime && strlen($dateFrom) > 10) {
            $fromTime = substr($dateFrom, 11);
            list($hours, $minutes, $seconds) = explode(':', $fromTime);
            $prevFrom->setTime((int)$hours, (int)$minutes, (int)$seconds);
        }

        if ($hasTime && strlen($dateTo) > 10) {
            $toTime = substr($dateTo, 11);
            list($hours, $minutes, $seconds) = explode(':', $toTime);
            $prevTo->setTime((int)$hours, (int)$minutes, (int)$seconds);
        }

        $format = $hasTime ? 'Y-m-d H:i:s' : 'Y-m-d';

        return [
            'from' => $prevFrom->format($format),
            'to'   => $prevTo->format($format),
        ];
    }

    /**
     * Calculate same period last year (exact dates).
     *
     * Subtracts one year from both dates for year-over-year comparison.
     *
     * @param string $dateFrom Current period start date.
     * @param string $dateTo   Current period end date.
     * @return array ['from' => string, 'to' => string]
     */
    public function calculateSamePeriodLastYear(string $dateFrom, string $dateTo): array
    {
        // Normalize ISO 8601 format (replace T with space)
        $dateFrom = str_replace('T', ' ', $dateFrom);
        $dateTo   = str_replace('T', ' ', $dateTo);

        // Check if times are included
        $hasTime = strlen($dateFrom) > 10 || strlen($dateTo) > 10;

        $from = new \DateTime($dateFrom);
        $to   = new \DateTime($dateTo);

        // Subtract one year
        $prevFrom = (clone $from)->modify('-1 year');
        $prevTo = (clone $to)->modify('-1 year');

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

        // Sort BOTH arrays by date to ensure correct positional alignment
        // The queries might not return rows sorted by date
        $sortByDate = function($a, $b) {
            $dateA = $a['date'] ?? '';
            $dateB = $b['date'] ?? '';
            return strcmp($dateA, $dateB);
        };

        usort($current, $sortByDate);
        usort($previous, $sortByDate);

        // Fill missing dates in BOTH arrays BEFORE merging
        // Aggregate queries may skip rows with 0 values, causing misalignment
        // Must fill current first so PP aligns to full date range, not just returned rows
        $current = $this->fillMissingDatesForCurrent($current);
        $previous = $this->fillMissingDatesForPrevious($current, $previous);

        $previousCount = count($previous);

        foreach ($current as $index => &$row) {
            // Only add previous data if this index has corresponding previous period data
            if ($index < $previousCount) {
                $prevRow = $previous[$index];
                $row['previous'] = [];

                foreach ($this->sources as $source) {
                    $row['previous'][$source] = isset($prevRow[$source]) ? (float) $prevRow[$source] : 0;
                }
            }
            // Don't add 'previous' key for indices beyond the previous period
            // This allows charts to show gaps instead of zeros
        }

        return $current;
    }

    /**
     * Fill missing dates in current period data.
     *
     * Aggregate queries may skip rows with 0 values, causing misalignment.
     * This ensures all dates in the current period range have rows.
     *
     * @param array $current Current period rows (sorted by date).
     * @return array Current period rows with missing dates filled.
     */
    private function fillMissingDatesForCurrent(array $current): array
    {
        // Need current date range to fill missing dates
        if ($this->currentPeriodRange === null) {
            return $current;
        }

        // Index existing current rows by date (normalize to Y-m-d format)
        $currentIndex = [];
        foreach ($current as $row) {
            $date = $row['date'] ?? '';
            if ($date !== '') {
                $dateKey = substr($date, 0, 10);
                $currentIndex[$dateKey] = $row;
            }
        }

        // Use the actual current date range to generate all expected dates
        $startDate = new \DateTime($this->currentPeriodRange['from']);
        $endDate = new \DateTime($this->currentPeriodRange['to']);

        // Generate all dates in current range
        $filledCurrent = [];
        $interval = new \DateInterval('P1D');
        $period = new \DatePeriod($startDate, $interval, (clone $endDate)->modify('+1 day'));

        // Pre-fetch published_content for missing dates if needed
        $hasPublishedContent = in_array('published_content', $this->sources, true);
        $publishedContentByDate = [];
        if ($hasPublishedContent) {
            $allDates = [];
            foreach ($period as $date) {
                $allDates[] = $date->format('Y-m-d');
            }
            $missingDates = array_diff($allDates, array_keys($currentIndex));
            if (!empty($missingDates)) {
                $publishedContentByDate = PublishedContentHelper::getPublishedContentByDates(
                    $missingDates,
                    'date',
                    $this->filters
                );
            }
            // Reset the period iterator
            $period = new \DatePeriod($startDate, $interval, (clone $endDate)->modify('+1 day'));
        }

        foreach ($period as $date) {
            $dateStr = $date->format('Y-m-d');
            if (isset($currentIndex[$dateStr])) {
                // Use existing row but normalize date format
                $row = $currentIndex[$dateStr];
                $row['date'] = $dateStr;
                $filledCurrent[] = $row;
            } else {
                // Create row with 0 values for missing date
                $emptyRow = ['date' => $dateStr];
                foreach ($this->sources as $source) {
                    if ($source === 'published_content' && isset($publishedContentByDate[$dateStr])) {
                        $emptyRow[$source] = $publishedContentByDate[$dateStr];
                    } else {
                        $emptyRow[$source] = 0;
                    }
                }
                $filledCurrent[] = $emptyRow;
            }
        }

        return $filledCurrent;
    }

    /**
     * Fill missing dates in previous period data.
     *
     * Aggregate queries may skip rows with 0 values, causing misalignment.
     * This ensures all dates in the PP range have rows with proper values.
     *
     * @param array $current  Current period rows (sorted by date).
     * @param array $previous Previous period rows (sorted by date).
     * @return array Previous period rows with missing dates filled.
     */
    private function fillMissingDatesForPrevious(array $current, array $previous): array
    {
        // Need PP date range to fill missing dates
        if ($this->previousPeriodRange === null) {
            return $previous;
        }

        // Index existing previous rows by date (normalize to Y-m-d format)
        $previousIndex = [];
        foreach ($previous as $row) {
            $date = $row['date'] ?? '';
            if ($date !== '') {
                // Extract just the date part (handle both "Y-m-d" and "Y-m-d H:i:s" formats)
                $dateKey = substr($date, 0, 10);
                $previousIndex[$dateKey] = $row;
            }
        }

        // Use the actual PP date range to generate all expected dates
        $startDate = new \DateTime($this->previousPeriodRange['from']);
        $endDate = new \DateTime($this->previousPeriodRange['to']);

        // Generate all dates in PP range
        $filledPrevious = [];
        $interval = new \DateInterval('P1D');
        $period = new \DatePeriod($startDate, $interval, (clone $endDate)->modify('+1 day'));

        foreach ($period as $date) {
            $dateStr = $date->format('Y-m-d');
            if (isset($previousIndex[$dateStr])) {
                // Use existing row but normalize date format
                $row = $previousIndex[$dateStr];
                $row['date'] = $dateStr; // Ensure consistent date format
                $filledPrevious[] = $row;
            } else {
                // Create row with 0 values for missing date
                $emptyRow = ['date' => $dateStr];
                foreach ($this->sources as $source) {
                    $emptyRow[$source] = 0;
                }
                $filledPrevious[] = $emptyRow;
            }
        }

        return $filledPrevious;
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
     * @param array $sources List of source WP_Statistics_names.
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
     * @param array $groupBy List of group by WP_Statistics_names.
     * @return self
     */
    public function setGroupBy(array $groupBy): self
    {
        $this->groupBy = $groupBy;
        return $this;
    }

    /**
     * Set previous period date range for filling missing dates.
     *
     * @param string $from Start date.
     * @param string $to   End date.
     * @return self
     */
    public function setPreviousPeriodRange(string $from, string $to): self
    {
        $this->previousPeriodRange = [
            'from' => substr($from, 0, 10), // Extract date part only
            'to'   => substr($to, 0, 10),
        ];
        return $this;
    }

    /**
     * Set current period date range for filling missing dates.
     *
     * @param string $from Start date.
     * @param string $to   End date.
     * @return self
     */
    public function setCurrentPeriodRange(string $from, string $to): self
    {
        $this->currentPeriodRange = [
            'from' => substr($from, 0, 10), // Extract date part only
            'to'   => substr($to, 0, 10),
        ];
        return $this;
    }

    /**
     * Set filters for published content queries.
     *
     * @param array $filters Array of filter configurations.
     * @return self
     */
    public function setFilters(array $filters): self
    {
        $this->filters = $filters;
        return $this;
    }
}
