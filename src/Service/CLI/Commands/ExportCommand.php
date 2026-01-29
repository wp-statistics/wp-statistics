<?php

namespace WP_Statistics\Service\CLI\Commands;

use WP_CLI;
use WP_Statistics\Components\DateRange;
use WP_Statistics\Service\AnalyticsQuery\AnalyticsQueryHandler;

/**
 * Export analytics data.
 *
 * @since 15.0.0
 */
class ExportCommand
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
     * Export analytics data to a file.
     *
     * ## OPTIONS
     *
     * <type>
     * : Type of data to export.
     * ---
     * options:
     *   - visitors
     *   - views
     *   - pages
     *   - countries
     *   - browsers
     *   - referrers
     * ---
     *
     * [--date-from=<date>]
     * : Start date for the export (Y-m-d format).
     *
     * [--date-to=<date>]
     * : End date for the export (Y-m-d format).
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
     * [--output=<file>]
     * : Output file path. If not specified, outputs to STDOUT.
     *
     * [--format=<format>]
     * : Export format.
     * ---
     * default: csv
     * options:
     *   - csv
     *   - json
     * ---
     *
     * [--limit=<number>]
     * : Maximum number of records to export.
     * ---
     * default: 1000
     * ---
     *
     * ## EXAMPLES
     *
     *      # Export visitors to CSV
     *      $ wp statistics export visitors --output=visitors.csv
     *
     *      # Export page views from last 7 days as JSON
     *      $ wp statistics export views --period=7days --format=json --output=views.json
     *
     *      # Export top countries to STDOUT
     *      $ wp statistics export countries --period=30days
     *
     *      # Export referrers with custom date range
     *      $ wp statistics export referrers --date-from=2024-01-01 --date-to=2024-01-31 --output=referrers.csv
     *
     * @param array $args       Positional arguments.
     * @param array $assoc_args Associative arguments.
     * @return void
     */
    public function __invoke($args, $assoc_args)
    {
        $type   = $args[0];
        $format = $assoc_args['format'] ?? 'csv';
        $limit  = (int) ($assoc_args['limit'] ?? 1000);
        $output = $assoc_args['output'] ?? null;

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

        // Map export type to query configuration
        $queryConfig = $this->getQueryConfig($type);
        if (!$queryConfig) {
            WP_CLI::error(sprintf('Unknown export type: %s', $type));
            return;
        }

        // Build and execute query
        // Use 'table' format which preserves all enriched columns (IP, country, browser, etc.)
        $query = array_merge($queryConfig, [
            'date_from' => $dateFrom,
            'date_to'   => $dateTo,
            'per_page'  => $limit,
            'format'    => 'table',
        ]);

        try {
            $result = $this->queryHandler->handle($query);

            // TableFormatter returns rows under data.rows with all enriched columns
            $rows = $result['data']['rows'] ?? [];

            if (empty($rows)) {
                WP_CLI::warning('No data found for the specified criteria.');
                return;
            }

            // Filter out internal columns that shouldn't be exported
            $rows = $this->filterExportColumns($rows);

            // Format output
            if ($format === 'json') {
                $content = json_encode($rows, JSON_PRETTY_PRINT);
            } else {
                $content = $this->arrayToCsv($rows);
            }

            // Output to file or STDOUT
            if ($output) {
                $bytesWritten = file_put_contents($output, $content);
                if ($bytesWritten === false) {
                    WP_CLI::error(sprintf('Failed to write to file: %s', $output));
                    return;
                }
                WP_CLI::success(sprintf(
                    'Exported %d records to %s (%s bytes)',
                    count($rows),
                    $output,
                    number_format($bytesWritten)
                ));
            } else {
                echo $content;
            }
        } catch (\Exception $e) {
            WP_CLI::error(sprintf('Export failed: %s', $e->getMessage()));
        }
    }

    /**
     * Get query configuration for export type.
     *
     * @param string $type Export type.
     * @return array|null Query configuration or null if unknown type.
     */
    private function getQueryConfig(string $type): ?array
    {
        $configs = [
            'visitors' => [
                'sources'  => ['visitors'],
                'group_by' => ['visitor'],
                'order_by' => 'last_visit',
                'order'    => 'DESC',
            ],
            'views' => [
                'sources'  => ['views'],
                'group_by' => ['date'],
                'order_by' => 'date',
                'order'    => 'DESC',
            ],
            'pages' => [
                'sources'  => ['views', 'visitors'],
                'group_by' => ['page'],
                'order_by' => 'views',
                'order'    => 'DESC',
            ],
            'countries' => [
                'sources'  => ['visitors', 'views'],
                'group_by' => ['country'],
                'order_by' => 'visitors',
                'order'    => 'DESC',
            ],
            'browsers' => [
                'sources'  => ['visitors'],
                'group_by' => ['browser'],
                'order_by' => 'visitors',
                'order'    => 'DESC',
            ],
            'referrers' => [
                'sources'  => ['visitors', 'views'],
                'group_by' => ['referrer'],
                'order_by' => 'visitors',
                'order'    => 'DESC',
            ],
        ];

        return $configs[$type] ?? null;
    }

    /**
     * Filter out internal columns that shouldn't be exported.
     *
     * @param array $rows Data rows.
     * @return array Filtered rows.
     */
    private function filterExportColumns(array $rows): array
    {
        // Columns to exclude from export
        $excludeColumns = [
            'previous',
            'is_other',
            'visitor_id',
            'attributed_session_id',
        ];

        return array_map(function ($row) use ($excludeColumns) {
            return array_filter(
                (array) $row,
                function ($key) use ($excludeColumns) {
                    return !in_array($key, $excludeColumns, true);
                },
                ARRAY_FILTER_USE_KEY
            );
        }, $rows);
    }

    /**
     * Convert associative array rows to CSV string.
     *
     * @param array $rows Data rows (associative arrays).
     * @return string CSV string.
     */
    private function arrayToCsv(array $rows): string
    {
        if (empty($rows)) {
            return '';
        }

        $output = fopen('php://temp', 'r+');

        // Write header row from first row's keys
        $firstRow = reset($rows);
        $headers  = array_keys((array) $firstRow);
        fputcsv($output, $headers);

        // Write data rows
        foreach ($rows as $row) {
            $values = [];
            foreach ($headers as $header) {
                $value = $row[$header] ?? '';
                // Handle arrays/objects
                if (is_array($value) || is_object($value)) {
                    $value = json_encode($value);
                }
                $values[] = $value;
            }
            fputcsv($output, $values);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }
}
