<?php

namespace WP_Statistics\Service\CLI\Commands;

use WP_CLI;
use WP_Statistics\Components\DateRange;
use WP_Statistics\Service\AnalyticsQuery\AnalyticsQueryHandler;

/**
 * Show list of visitors.
 *
 * @since 15.0.0
 */
class VisitorsCommand
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
     * Show list of visitors.
     *
     * ## OPTIONS
     *
     * [--number=<number>]
     * : Number of visitors to return.
     * ---
     * default: 15
     * ---
     *
     * [--date-from=<date>]
     * : Start date for the query (Y-m-d format).
     *
     * [--date-to=<date>]
     * : End date for the query (Y-m-d format).
     *
     * [--period=<period>]
     * : Predefined date period (overrides date-from/date-to).
     * ---
     * default: 30days
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
     * [--country=<country>]
     * : Filter by country code (e.g., US, GB, DE).
     *
     * [--browser=<browser>]
     * : Filter by browser name (e.g., Chrome, Firefox, Safari).
     *
     * [--os=<os>]
     * : Filter by operating system (e.g., Windows, macOS, Linux).
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
     *      # Show list of visitors
     *      $ wp statistics visitors
     *
     *      # Show list of last ten visitors
     *      $ wp statistics visitors --number=10
     *
     *      # Show visitors from the last 7 days
     *      $ wp statistics visitors --period=7days
     *
     *      # Show visitors from a custom date range
     *      $ wp statistics visitors --date-from=2024-01-01 --date-to=2024-01-31
     *
     *      # Show visitors from the United States
     *      $ wp statistics visitors --country=US
     *
     *      # Show Chrome users from Germany
     *      $ wp statistics visitors --country=DE --browser=Chrome
     *
     *      # Show visitors as JSON
     *      $ wp statistics visitors --format=json
     *
     * @alias visitor
     *
     * @param array $args       Positional arguments.
     * @param array $assoc_args Associative arguments.
     * @return void
     */
    public function __invoke($args, $assoc_args)
    {
        $number  = \WP_CLI\Utils\get_flag_value($assoc_args, 'number', 15);
        $format  = $assoc_args['format'] ?? 'table';
        $period  = $assoc_args['period'] ?? '30days';

        // Validate date format if provided
        if (isset($assoc_args['date-from']) && !$this->isValidDate($assoc_args['date-from'])) {
            WP_CLI::error('Invalid date-from format. Use Y-m-d (e.g., 2024-01-15).');
            return;
        }

        if (isset($assoc_args['date-to']) && !$this->isValidDate($assoc_args['date-to'])) {
            WP_CLI::error('Invalid date-to format. Use Y-m-d (e.g., 2024-01-15).');
            return;
        }

        try {
            // Determine date range
            if (isset($assoc_args['date-from']) && isset($assoc_args['date-to'])) {
                $dateFrom = $assoc_args['date-from'];
                $dateTo   = $assoc_args['date-to'];
            } else {
                $dateRange = DateRange::resolveDate($period);
                $dateFrom  = $dateRange['from'];
                $dateTo    = $dateRange['to'];
            }

            // Build filters
            $filters = [];

            if (!empty($assoc_args['country'])) {
                $filters['country'] = strtoupper($assoc_args['country']);
            }

            if (!empty($assoc_args['browser'])) {
                $filters['browser'] = $assoc_args['browser'];
            }

            if (!empty($assoc_args['os'])) {
                $filters['os'] = $assoc_args['os'];
            }

            $result = $this->queryHandler->handle([
                'sources'   => ['visitors'],
                'group_by'  => ['visitor'],
                'per_page'  => $number,
                'order_by'  => 'last_visit',
                'order'     => 'DESC',
                'date_from' => $dateFrom,
                'date_to'   => $dateTo,
                'filters'   => $filters,
            ]);

            // TableFormatter returns data.rows for grouped queries
            $lists = $result['data']['rows'] ?? [];

            if (empty($lists)) {
                WP_CLI::warning('No visitors found matching the criteria.');
                return;
            }

            $columns = ['IP', 'Date', 'Browser', 'Referrer', 'Operating System', 'User ID', 'Country'];
            $items   = [];

            foreach ($lists as $row) {
                $items[] = [
                    'IP'               => $row['ip_address'] ?? '-',
                    'Date'             => $row['last_visit'] ?? '-',
                    'Browser'          => $row['browser_name'] ?? '-',
                    'Referrer'         => wp_strip_all_tags($row['referrer_domain'] ?? ''),
                    'Operating System' => $row['os_name'] ?? '-',
                    'User ID'          => (!empty($row['user_id']) ? $row['user_id'] : '-'),
                    'Country'          => $row['country_name'] ?? '-',
                ];
            }

            \WP_CLI\Utils\format_items($format, $items, $columns);
        } catch (\Exception $e) {
            WP_CLI::error(sprintf('Failed to retrieve visitors: %s', $e->getMessage()));
        }
    }

    /**
     * Validate date string format.
     *
     * @param string $date Date string to validate.
     * @return bool True if valid Y-m-d format.
     */
    private function isValidDate(string $date): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
}
