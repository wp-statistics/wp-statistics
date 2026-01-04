<?php

namespace WP_Statistics\Service\CLI\Commands;

use WP_CLI;
use WP_Statistics\Models\VisitorsModel;

/**
 * Show list of visitors.
 *
 * @since 15.0.0
 */
class VisitorsCommand
{
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

        $visitorsModel = new VisitorsModel();
        $lists = $visitorsModel->getVisitorsData(['per_page' => $number]);

        if (empty($lists)) {
            WP_CLI::error('There are no visitors.');
            return;
        }

        $columns = ['IP', 'Date', 'Browser', 'Referrer', 'Operating System', 'User ID', 'Country'];
        $items   = [];

        foreach ($lists as $row) {
            $items[] = [
                'IP'               => $row['hash_ip'] ?? $row['ip']['value'] ?? '-',
                'Date'             => $row['date'] ?? '-',
                'Browser'          => $row['browser']['name'] ?? '-',
                'Referrer'         => wp_strip_all_tags($row['referred'] ?? ''),
                'Operating System' => $row['platform'] ?? '-',
                'User ID'          => (!empty($row['user']['ID']) ? $row['user']['ID'] : '-'),
                'Country'          => $row['country']['name'] ?? '-',
            ];
        }

        \WP_CLI\Utils\format_items($format, $items, $columns);
    }
}
