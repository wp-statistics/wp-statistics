<?php

namespace WP_Statistics\Service\CLI\Commands;

use WP_CLI;
use WP_Statistics\Components\DateRange;
use WP_Statistics\Service\AnalyticsQuery\AnalyticsQueryHandler;

/**
 * Query analytics data directly.
 *
 * @since 15.0.0
 */
class QueryCommand
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
     * Query analytics data using the v15 AnalyticsQuery system.
     *
     * ## OPTIONS
     *
     * <sources>
     * : Comma-separated list of data sources to query.
     *   Available sources: visitors, views, sessions, searches, events,
     *   bounce_rate, pages_per_session, avg_session_duration
     *
     * [--group-by=<group_by>]
     * : Comma-separated list of dimensions to group by.
     *   Available: date, country, browser, os, page, referrer, visitor
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
     * : Filter by browser name (e.g., Chrome, Firefox).
     *
     * [--os=<os>]
     * : Filter by operating system (e.g., Windows, macOS).
     *
     * [--post-type=<post_type>]
     * : Filter by post type (e.g., post, page).
     *
     * [--per-page=<number>]
     * : Number of results per page.
     * ---
     * default: 10
     * ---
     *
     * [--order-by=<field>]
     * : Field to order by.
     *
     * [--order=<direction>]
     * : Order direction.
     * ---
     * default: DESC
     * options:
     *   - ASC
     *   - DESC
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
     *   - yaml
     * ---
     *
     * ## EXAMPLES
     *
     *      # Get total visitors and views for the last 30 days
     *      $ wp statistics query visitors,views
     *
     *      # Get visitors grouped by country
     *      $ wp statistics query visitors --group-by=country
     *
     *      # Get daily views for the last 7 days
     *      $ wp statistics query views --group-by=date --period=7days
     *
     *      # Get top browsers by visitor count
     *      $ wp statistics query visitors --group-by=browser --order-by=visitors
     *
     *      # Get visitors from US in JSON format
     *      $ wp statistics query visitors --country=US --format=json
     *
     *      # Get bounce rate and pages per session
     *      $ wp statistics query bounce_rate,pages_per_session --period=30days
     *
     * @param array $args       Positional arguments.
     * @param array $assoc_args Associative arguments.
     * @return void
     */
    public function __invoke($args, $assoc_args)
    {
        // Parse sources
        $sources = explode(',', $args[0]);
        $sources = array_map('trim', $sources);

        // Parse group_by
        $groupBy = [];
        if (!empty($assoc_args['group-by'])) {
            $groupBy = explode(',', $assoc_args['group-by']);
            $groupBy = array_map('trim', $groupBy);
        }

        // Determine date range
        $period = $assoc_args['period'] ?? '30days';
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

        if (!empty($assoc_args['post-type'])) {
            $filters['post_type'] = $assoc_args['post-type'];
        }

        // Build query
        $query = [
            'sources'   => $sources,
            'group_by'  => $groupBy,
            'date_from' => $dateFrom,
            'date_to'   => $dateTo,
            'per_page'  => (int) ($assoc_args['per-page'] ?? 10),
            'filters'   => $filters,
            'format'    => 'table',
        ];

        if (!empty($assoc_args['order-by'])) {
            $query['order_by'] = $assoc_args['order-by'];
        }

        if (!empty($assoc_args['order'])) {
            $query['order'] = strtoupper($assoc_args['order']);
        }

        try {
            $result = $this->queryHandler->handle($query);
            $format = $assoc_args['format'] ?? 'table';

            // Show totals if available
            if (!empty($result['data']['totals'])) {
                WP_CLI::line('Totals:');
                foreach ($result['data']['totals'] as $key => $value) {
                    if ($key === 'previous') {
                        continue;
                    }
                    WP_CLI::line(sprintf('  %s: %s', $key, number_format($value)));
                }
                WP_CLI::line('');
            }

            // Handle flat format (no group_by) - totals contain the aggregated values
            if (empty($groupBy)) {
                $totals  = $result['data']['totals'] ?? [];
                $items   = [];
                $columns = $sources;

                $row = [];
                foreach ($sources as $source) {
                    $row[$source] = isset($totals[$source]) ? number_format($totals[$source]) : '0';
                }
                $items[] = $row;

                \WP_CLI\Utils\format_items($format, $items, $columns);
                return;
            }

            // Handle grouped data - TableFormatter returns data.rows
            $rows = $result['data']['rows'] ?? [];

            if (empty($rows)) {
                WP_CLI::warning('No data found for the specified query.');
                return;
            }

            // Determine columns from first row
            $columns = array_keys((array) reset($rows));

            // Filter out internal columns
            $columns = array_filter($columns, function ($col) {
                return !in_array($col, ['previous', 'is_other'], true);
            });

            // Format items
            $items = [];
            foreach ($rows as $row) {
                $item = [];
                foreach ($columns as $col) {
                    $value = $row[$col] ?? '-';
                    // Format numbers
                    if (is_numeric($value) && !in_array($col, ['visitor_id', 'user_id', 'post_id', 'resource_id'])) {
                        $value = number_format((float) $value, is_float($value) ? 2 : 0);
                    }
                    $item[$col] = $value;
                }
                $items[] = $item;
            }

            \WP_CLI\Utils\format_items($format, $items, $columns);

            // Show meta info
            if (!empty($result['meta'])) {
                WP_CLI::line('');
                WP_CLI::line(sprintf(
                    'Showing %d of %d total results.',
                    count($items),
                    $result['meta']['total_rows'] ?? count($items)
                ));
            }
        } catch (\Exception $e) {
            WP_CLI::error(sprintf('Query failed: %s', $e->getMessage()));
        }
    }
}
