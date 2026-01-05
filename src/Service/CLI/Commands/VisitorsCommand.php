<?php

namespace WP_Statistics\Service\CLI\Commands;

use WP_CLI;
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
        $number = \WP_CLI\Utils\get_flag_value($assoc_args, 'number', 15);
        $format = $assoc_args['format'] ?? 'table';

        $result = $this->queryHandler->handle([
            'sources'   => ['visitors'],
            'group_by'  => ['visitor'],
            'per_page'  => $number,
            'order_by'  => 'last_visit',
            'order'     => 'DESC',
            'date_from' => date('Y-m-d', strtotime('-30 days')),
            'date_to'   => date('Y-m-d'),
        ]);

        $lists = $result['data'] ?? [];

        if (empty($lists)) {
            WP_CLI::error('There are no visitors.');
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
    }
}
