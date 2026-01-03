<?php

namespace WP_Statistics\Service\CLI\Commands;

use WP_CLI;

/**
 * Show summary of statistics.
 *
 * @since 15.0.0
 */
class SummaryCommand
{
    /**
     * Show Summary of statistics.
     *
     * ## OPTIONS
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
     *      # Show summary of statistics
     *      $ wp statistics summary
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
        $format = $assoc_args['format'] ?? 'table';

        // Show users online count
        WP_CLI::line('Users Online: ' . number_format(wp_statistics_useronline()));

        // Build statistics table
        $items      = [];
        $timePeriods = ['Today', 'Yesterday', 'Week', 'Month', 'Year', 'Total'];

        foreach ($timePeriods as $time) {
            $timeLower = strtolower($time);
            $items[] = [
                'Time'     => $time,
                'Visitors' => number_format(wp_statistics_visitor($timeLower, null, true)),
                'Views'    => number_format(wp_statistics_visit($timeLower)),
            ];
        }

        \WP_CLI\Utils\format_items($format, $items, ['Time', 'Visitors', 'Views']);
    }
}
