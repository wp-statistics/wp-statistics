<?php

namespace WP_Statistics\Service\CLI\Commands;

use WP_CLI;
use WP_Statistics\Service\AnalyticsQuery\AnalyticsQueryHandler;

/**
 * Show list of users online.
 *
 * @since 15.0.0
 */
class OnlineCommand
{
    /**
     * Online threshold in seconds (5 minutes).
     */
    const ONLINE_THRESHOLD = 300;

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
     * Show list of users online.
     *
     * ## OPTIONS
     *
     * [--number=<number>]
     * : Number of users to return.
     * ---
     * default: 15
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
     *      # Show list of users online
     *      $ wp statistics online
     *
     *      # Show list of five users online
     *      $ wp statistics online --number=5
     *
     *      # Show online users from the United States
     *      $ wp statistics online --country=US
     *
     *      # Show online Chrome users
     *      $ wp statistics online --browser=Chrome
     *
     *      # Show online users as JSON
     *      $ wp statistics online --format=json
     *
     * @param array $args       Positional arguments.
     * @param array $assoc_args Associative arguments.
     * @return void
     */
    public function __invoke($args, $assoc_args)
    {
        $number = \WP_CLI\Utils\get_flag_value($assoc_args, 'number', 15);
        $format = $assoc_args['format'] ?? 'table';

        try {
            // Online visitors are those with last activity within the last 5 minutes
            $fiveMinutesAgo = gmdate('Y-m-d H:i:s', time() - self::ONLINE_THRESHOLD);
            $currentTime    = gmdate('Y-m-d H:i:s');

            // Get total online count
            $countResult = $this->queryHandler->handle([
                'sources'   => ['visitors'],
                'date_from' => $fiveMinutesAgo,
                'date_to'   => $currentTime,
            ]);

            $totalOnline = $countResult['data']['total'] ?? 0;
            WP_CLI::line(sprintf('Total Users Online: %d', $totalOnline));
            WP_CLI::line('');

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
                'group_by'  => ['online_visitor'],
                'per_page'  => $number,
                'order_by'  => 'last_visit',
                'order'     => 'DESC',
                'date_from' => $fiveMinutesAgo,
                'date_to'   => $currentTime,
                'filters'   => $filters,
            ]);

            // TableFormatter returns data.rows for grouped queries
            $lists = $result['data']['rows'] ?? [];

            if (empty($lists)) {
                WP_CLI::warning('No online users found matching the criteria.');
                return;
            }

            $columns = ['IP', 'Browser', 'Online For', 'Referrer', 'Page', 'User ID', 'Country'];
            $items   = [];

            foreach ($lists as $row) {
                $items[] = [
                    'IP'         => $row['ip_address'] ?? '-',
                    'Browser'    => $row['browser_name'] ?? '-',
                    'Online For' => $this->calculateOnlineTime($row['last_visit'] ?? null),
                    'Referrer'   => wp_strip_all_tags($row['referrer_domain'] ?? ''),
                    'Page'       => $row['entry_page_title'] ?? $row['entry_page'] ?? '-',
                    'User ID'    => (!empty($row['user_id']) ? $row['user_id'] : '-'),
                    'Country'    => $row['country_name'] ?? '-',
                ];
            }

            \WP_CLI\Utils\format_items($format, $items, $columns);
        } catch (\Exception $e) {
            WP_CLI::error(sprintf('Failed to retrieve online users: %s', $e->getMessage()));
        }
    }

    /**
     * Calculate how long a visitor has been online.
     *
     * @param string|null $lastVisit Last visit timestamp.
     * @return string Human readable time duration.
     */
    private function calculateOnlineTime(?string $lastVisit): string
    {
        if (empty($lastVisit)) {
            return '-';
        }

        $lastVisitTime = strtotime($lastVisit);
        $now           = time();
        $diff          = $now - $lastVisitTime;

        if ($diff < 60) {
            return sprintf('%d sec', $diff);
        } elseif ($diff < 3600) {
            return sprintf('%d min', floor($diff / 60));
        } else {
            return sprintf('%d hr', floor($diff / 3600));
        }
    }
}
