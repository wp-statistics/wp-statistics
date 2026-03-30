<?php

namespace WP_Statistics\Service\CLI\Commands;

use WP_CLI;
use WP_Statistics\Service\AnalyticsQuery\AnalyticsQueryHandler;
use WP_Statistics\Service\AnalyticsQuery\Registry\FilterRegistry;

/**
 * Query analytics data from WP Statistics.
 *
 * Unified CLI interface to the AnalyticsQuery API.
 * Supports all sources, group_by dimensions, and filters.
 *
 * @since 15.0.0
 */
class AnalyticsCommand
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
     * Query analytics data.
     *
     * ## OPTIONS
     *
     * [--source=<source>]
     * : Comma-separated list of sources (e.g. visitors,views,bounce_rate).
     * ---
     * default: visitors
     * ---
     *
     * [--group-by=<group_by>]
     * : Comma-separated list of group_by dimensions (e.g. date,country).
     *
     * [--filter=<filter>]
     * : Repeatable filter in key:operator:value format (e.g. country:is:US).
     * Supported operators: is, is_not, in, not_in, contains, starts_with, ends_with, gt, gte, lt, lte, is_empty, is_not_empty.
     * For 'in'/'not_in', provide comma-separated values (e.g. country:in:US,GB,DE).
     * For 'is' shorthand, omit operator (e.g. country:US is same as country:is:US).
     *
     * [--date-from=<date>]
     * : Start date in Y-m-d or Y-m-d H:i:s format.
     * ---
     * default: 30 days ago
     * ---
     *
     * [--date-to=<date>]
     * : End date in Y-m-d or Y-m-d H:i:s format.
     * ---
     * default: today
     * ---
     *
     * [--per-page=<n>]
     * : Number of results per page.
     * ---
     * default: 10
     * ---
     *
     * [--page=<n>]
     * : Page number for pagination.
     * ---
     * default: 1
     * ---
     *
     * [--order-by=<field>]
     * : Column to sort by.
     *
     * [--order=<direction>]
     * : Sort direction.
     * ---
     * default: DESC
     * options:
     *   - ASC
     *   - DESC
     * ---
     *
     * [--columns=<columns>]
     * : Comma-separated list of columns to display.
     *
     * [--compare]
     * : Include previous period comparison data.
     *
     * [--format=<format>]
     * : Output format.
     * ---
     * default: table
     * options:
     *   - table
     *   - csv
     *   - json
     *   - yaml
     *   - count
     * ---
     *
     * ## EXAMPLES
     *
     *      # Basic visitor stats by date (replaces 'wp statistics summary')
     *      $ wp statistics analytics query --source=visitors,views --group-by=date
     *
     *      # Online users (replaces 'wp statistics online')
     *      $ wp statistics analytics query --source=visitors --group-by=online_visitor
     *
     *      # Recent visitors (replaces 'wp statistics visitors')
     *      $ wp statistics analytics query --source=visitors --group-by=visitor --per-page=15
     *
     *      # Top countries
     *      $ wp statistics analytics query --source=visitors --group-by=country --per-page=10
     *
     *      # Browser stats as JSON
     *      $ wp statistics analytics query --source=views,bounce_rate --group-by=browser --format=json
     *
     *      # Filtered query with comparison
     *      $ wp statistics analytics query --source=visitors --group-by=referrer_channel --filter='country:is:US' --compare
     *
     *      # Multiple filters
     *      $ wp statistics analytics query --source=views --group-by=page --filter='browser:contains:Chrome' --filter='country:in:US,GB,DE'
     *
     * @subcommand query
     *
     * @param array $args       Positional arguments.
     * @param array $assoc_args Associative arguments.
     * @return void
     */
    public function query($args, $assoc_args)
    {
        $format = $assoc_args['format'] ?? 'table';

        $request = $this->buildRequest($assoc_args);

        try {
            $result = $this->queryHandler->handle($request);
        } catch (\Exception $e) {
            WP_CLI::error($e->getMessage());
            return;
        }

        $rows = $result['data'] ?? [];

        if (empty($rows)) {
            WP_CLI::warning('No results found.');
            return;
        }

        // Determine columns to display
        $displayColumns = !empty($assoc_args['columns'])
            ? array_map('trim', explode(',', $assoc_args['columns']))
            : array_keys($rows[0]);

        \WP_CLI\Utils\format_items($format, $rows, $displayColumns);
    }

    /**
     * List available sources.
     *
     * ## OPTIONS
     *
     * [--format=<format>]
     * : Output format.
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
     *      $ wp statistics analytics list-sources
     *
     * @subcommand list-sources
     *
     * @param array $args       Positional arguments.
     * @param array $assoc_args Associative arguments.
     * @return void
     */
    public function listSources($args, $assoc_args)
    {
        $format  = $assoc_args['format'] ?? 'table';
        $sources = $this->queryHandler->getAvailableSources();

        $items = [];
        foreach ($sources as $source) {
            $items[] = [
                'Name'   => $source['name'] ?? $source,
                'Type'   => $source['type'] ?? '-',
                'Format' => $source['format'] ?? '-',
            ];
        }

        \WP_CLI\Utils\format_items($format, $items, ['Name', 'Type', 'Format']);
    }

    /**
     * List available group_by dimensions.
     *
     * ## OPTIONS
     *
     * [--format=<format>]
     * : Output format.
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
     *      $ wp statistics analytics list-groups
     *
     * @subcommand list-groups
     *
     * @param array $args       Positional arguments.
     * @param array $assoc_args Associative arguments.
     * @return void
     */
    public function listGroups($args, $assoc_args)
    {
        $format = $assoc_args['format'] ?? 'table';
        $groups = $this->queryHandler->getAvailableGroupBy();

        $items = [];
        foreach ($groups as $group) {
            $items[] = ['Name' => $group];
        }

        \WP_CLI\Utils\format_items($format, $items, ['Name']);
    }

    /**
     * List available filter keys.
     *
     * ## OPTIONS
     *
     * [--format=<format>]
     * : Output format.
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
     *      $ wp statistics analytics list-filters
     *
     * @subcommand list-filters
     *
     * @param array $args       Positional arguments.
     * @param array $assoc_args Associative arguments.
     * @return void
     */
    public function listFilters($args, $assoc_args)
    {
        $format  = $assoc_args['format'] ?? 'table';
        $filters = FilterRegistry::getInstance()->getAll();

        $items = [];
        foreach ($filters as $filter) {
            $items[] = ['Name' => $filter];
        }

        \WP_CLI\Utils\format_items($format, $items, ['Name']);
    }

    /**
     * Build a request array from CLI arguments.
     *
     * @param array $assoc_args Associative arguments from WP-CLI.
     * @return array Request array for AnalyticsQueryHandler::handle().
     */
    private function buildRequest(array $assoc_args): array
    {
        $request = [
            'sources'   => array_map('trim', explode(',', $assoc_args['source'] ?? 'visitors')),
            'per_page'  => (int) ($assoc_args['per-page'] ?? 10),
            'page'      => (int) ($assoc_args['page'] ?? 1),
            'order'     => strtoupper($assoc_args['order'] ?? 'DESC'),
            'date_from' => $assoc_args['date-from'] ?? gmdate('Y-m-d', strtotime('-30 days')),
            'date_to'   => $assoc_args['date-to'] ?? gmdate('Y-m-d'),
        ];

        if (!empty($assoc_args['group-by'])) {
            $request['group_by'] = array_map('trim', explode(',', $assoc_args['group-by']));
        }

        if (!empty($assoc_args['order-by'])) {
            $request['order_by'] = $assoc_args['order-by'];
        }

        if (!empty($assoc_args['columns'])) {
            $request['columns'] = array_map('trim', explode(',', $assoc_args['columns']));
        }

        if (isset($assoc_args['compare'])) {
            $request['compare'] = true;
        }

        // Parse filters
        $filters = $this->parseFilters($assoc_args);
        if (!empty($filters)) {
            $request['filters'] = $filters;
        }

        return $request;
    }

    /**
     * Parse filter arguments from CLI.
     *
     * Supports repeatable --filter flags in key:operator:value format.
     * Shorthand key:value maps to key:is:value.
     *
     * @param array $assoc_args Associative arguments.
     * @return array Associative filter array for AnalyticsQueryHandler.
     */
    private function parseFilters(array $assoc_args): array
    {
        if (empty($assoc_args['filter'])) {
            return [];
        }

        // WP-CLI passes repeatable args as array, single as string
        $rawFilters = (array) $assoc_args['filter'];
        $filters    = [];

        foreach ($rawFilters as $raw) {
            $parts = explode(':', $raw, 3);

            if (count($parts) === 2) {
                // key:value → simple equality
                $filters[$parts[0]] = $parts[1];
            } elseif (count($parts) === 3) {
                $key      = $parts[0];
                $operator = $parts[1];
                $value    = $parts[2];

                // For 'in' and 'not_in', split CSV values into array
                if (in_array($operator, ['in', 'not_in'], true)) {
                    $value = array_map('trim', explode(',', $value));
                }

                if ($operator === 'is') {
                    $filters[$key] = $value;
                } else {
                    $filters[$key] = [$operator => $value];
                }
            } else {
                WP_CLI::warning(sprintf('Invalid filter format: %s (expected key:operator:value)', $raw));
            }
        }

        return $filters;
    }
}
