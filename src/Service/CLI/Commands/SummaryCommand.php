<?php

namespace WP_Statistics\Service\CLI\Commands;

use WP_CLI;
use WP_Statistics\Components\DateRange;
use WP_Statistics\Service\AnalyticsQuery\AnalyticsQueryHandler;

/**
 * Show summary of statistics.
 *
 * @since 15.0.0
 */
class SummaryCommand
{
    /**
     * Analytics query handler.
     *
     * @var AnalyticsQueryHandler
     */
    private $queryHandler;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->queryHandler = new AnalyticsQueryHandler(false);
    }

    /**
     * Show Summary of statistics.
     *
     * ## OPTIONS
     *
     * [--date-from=<date>]
     * : Start date (Y-m-d format). When specified with --date-to, shows stats for that range only.
     *
     * [--date-to=<date>]
     * : End date (Y-m-d format). When specified with --date-from, shows stats for that range only.
     *
     * [--period=<period>]
     * : Predefined period. When specified, shows stats for that period only.
     * ---
     * options:
     *   - today
     *   - yesterday
     *   - 7days
     *   - 30days
     *   - 90days
     *   - 12months
     *   - total
     * ---
     *
     * [--format=<format>]
     * : Render output in a particular format.
     * ---
     * default: table
     * options:
     *   - table
     *   - csv
     *   - json
     *   - count
     *   - yaml
     * ---
     *
     * ## EXAMPLES
     *
     *      # Show summary of statistics (all periods)
     *      $ wp statistics summary
     *
     *      # Show summary for a specific date range
     *      $ wp statistics summary --date-from=2024-01-01 --date-to=2024-01-31
     *
     *      # Show summary for last 7 days only
     *      $ wp statistics summary --period=7days
     *
     *      # Show summary as JSON
     *      $ wp statistics summary --format=json
     *
     * @alias overview
     *
     * @param array $args       Positional arguments.
     * @param array $assoc_args Associative arguments.
     * @return void
     */
    public function __invoke($args, $assoc_args)
    {
        $format   = $assoc_args['format'] ?? 'table';
        $dateFrom = $assoc_args['date-from'] ?? null;
        $dateTo   = $assoc_args['date-to'] ?? null;
        $period   = $assoc_args['period'] ?? null;

        try {
            // If custom date range or period is specified, show single summary
            if ($period || ($dateFrom && $dateTo)) {
                $this->showSinglePeriodSummary($format, $dateFrom, $dateTo, $period);
                return;
            }

            // Validate partial date range
            if ($dateFrom xor $dateTo) {
                WP_CLI::error('Both --date-from and --date-to must be specified together.');
            }

            // Default: show all predefined periods
            $this->showAllPeriodsSummary($format);
        } catch (\Exception $e) {
            WP_CLI::error(sprintf('Failed to retrieve statistics: %s', $e->getMessage()));
        }
    }

    /**
     * Show summary for all predefined periods.
     *
     * @param string $format Output format.
     * @return void
     */
    private function showAllPeriodsSummary(string $format): void
    {
        $items       = [];
        $timePeriods = [
            'Today'     => 'today',
            'Yesterday' => 'yesterday',
            'Week'      => '7days',
            'Month'     => '30days',
            'Year'      => '12months',
            'Total'     => 'total',
        ];

        foreach ($timePeriods as $label => $period) {
            $dateRange = DateRange::resolveDate($period);

            $result = $this->queryHandler->handle([
                'sources'   => ['visitors', 'views'],
                'date_from' => $dateRange['from'],
                'date_to'   => $dateRange['to'],
                'format'    => 'flat',
            ]);

            // FlatFormatter returns data in 'items' array (single row for no groupBy)
            $data     = $result['items'][0] ?? [];
            $visitors = $data['visitors'] ?? 0;
            $views    = $data['views'] ?? 0;

            $items[] = [
                'Time'     => $label,
                'Visitors' => number_format($visitors),
                'Views'    => number_format($views),
            ];
        }

        \WP_CLI\Utils\format_items($format, $items, ['Time', 'Visitors', 'Views']);
    }

    /**
     * Show summary for a single period or date range.
     *
     * @param string      $format   Output format.
     * @param string|null $dateFrom Start date.
     * @param string|null $dateTo   End date.
     * @param string|null $period   Predefined period.
     * @return void
     */
    private function showSinglePeriodSummary(string $format, ?string $dateFrom, ?string $dateTo, ?string $period): void
    {
        // Resolve date range
        if ($period) {
            $dateRange = DateRange::resolveDate($period);
            $dateFrom  = $dateRange['from'];
            $dateTo    = $dateRange['to'];
            $label     = ucfirst($period);
        } else {
            // Validate date format
            if (!$this->isValidDate($dateFrom) || !$this->isValidDate($dateTo)) {
                WP_CLI::error('Invalid date format. Use Y-m-d (e.g., 2024-01-15).');
            }
            $label = "{$dateFrom} to {$dateTo}";
        }

        $result = $this->queryHandler->handle([
            'sources'   => ['visitors', 'views'],
            'date_from' => $dateFrom,
            'date_to'   => $dateTo,
            'format'    => 'flat',
        ]);

        // FlatFormatter returns data in 'items' array (single row for no groupBy)
        $data     = $result['items'][0] ?? [];
        $visitors = $data['visitors'] ?? 0;
        $views    = $data['views'] ?? 0;

        $items = [
            [
                'Period'   => $label,
                'Visitors' => number_format($visitors),
                'Views'    => number_format($views),
            ],
        ];

        \WP_CLI\Utils\format_items($format, $items, ['Period', 'Visitors', 'Views']);
    }

    /**
     * Validate date format (Y-m-d).
     *
     * @param string $date Date string.
     * @return bool
     */
    private function isValidDate(string $date): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
}
