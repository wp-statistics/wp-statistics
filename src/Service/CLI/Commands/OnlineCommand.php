<?php

namespace WP_Statistics\Service\CLI\Commands;

use WP_CLI;
use WP_STATISTICS\UserOnline;

/**
 * Show list of users online.
 *
 * @since 15.0.0
 */
class OnlineCommand
{
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

        $lists = UserOnline::get(['per_page' => $number]);

        if (empty($lists)) {
            WP_CLI::error('There are no users online.');
            return;
        }

        $columns = ['IP', 'Browser', 'Online For', 'Referrer', 'Page', 'User ID', 'Country'];
        $items   = [];

        foreach ($lists as $row) {
            $items[] = [
                'IP'         => $row['hash_ip'] ?? $row['ip']['value'] ?? '-',
                'Browser'    => $row['browser']['name'] ?? '-',
                'Online For' => $row['online_for'] ?? '-',
                'Referrer'   => wp_strip_all_tags($row['referred'] ?? ''),
                'Page'       => $row['page']['title'] ?? '-',
                'User ID'    => (!empty($row['user']['ID']) ? $row['user']['ID'] : '-'),
                'Country'    => $row['country']['name'] ?? '-',
            ];
        }

        \WP_CLI\Utils\format_items($format, $items, $columns);
    }
}
