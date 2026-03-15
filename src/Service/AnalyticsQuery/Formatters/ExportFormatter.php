<?php

namespace WP_Statistics\Service\AnalyticsQuery\Formatters;

use WP_Statistics\Service\AnalyticsQuery\Query\Query;

/**
 * Export response formatter.
 *
 * Produces a CSV-ready format with headers and row arrays.
 * Use cases: CSV/Excel exports, PDF reports, third-party integrations.
 *
 * Output structure:
 * {
 *   "success": true,
 *   "headers": ["Date", "Visitors", "Views"],
 *   "rows": [
 *     ["2024-11-01", 100, 250],
 *     ["2024-11-02", 120, 280]
 *   ],
 *   "meta": {...}
 * }
 *
 * @since 15.0.0
 */
class ExportFormatter extends AbstractFormatter
{
    /**
     * Extra enrichment columns to include in export output.
     *
     * @var array
     */
    protected $exportColumns = [];

    /**
     * Column label map for enrichment columns.
     *
     * @var array
     */
    private static $columnLabels = [
        'ip_address'       => 'IP Address',
        'country_code'     => 'Country Code',
        'country_name'     => 'Country',
        'city_name'        => 'City',
        'city_region_name' => 'Region',
        'browser_name'     => 'Browser',
        'os_name'          => 'OS',
        'device_type_name' => 'Device',
        'referrer_domain'  => 'Referrer Domain',
        'referrer_name'    => 'Referrer Name',
        'referrer_channel' => 'Referrer Channel',
        'entry_page_title' => 'Entry Page',
        'exit_page_title'  => 'Exit Page',
        'page_title'       => 'Page Title',
        'last_visit'       => 'Last Visit',
        'total_views'      => 'Total Views',
        'total_sessions'   => 'Total Sessions',
        'user_login'       => 'Username',
        'user_email'       => 'Email',
        'user_role'        => 'Role',
        'author_name'      => 'Author Name',
        'author_email'     => 'Author Email',
        'term_name'        => 'Term Name',
        'term_slug'        => 'Term Slug',
        'term_count'       => 'Published Items',
        'timezone_offset'  => 'UTC Offset',
        'visitor_hash'     => 'Visitor Hash',
        'region_name'      => 'Region',
        'region_code'      => 'Region Code',
        'page_type'        => 'Content Type',
        'published_date'   => 'Published Date',
    ];

    /**
     * Group-by label overrides when export columns provide readable names.
     *
     * @var array
     */
    private static $groupByLabelOverrides = [
        'author'   => 'Author ID',
        'taxonomy' => 'Term ID',
    ];

    /**
     * Set extra enrichment columns to include in the export.
     *
     * @param array $columns Column aliases to include.
     * @return self
     */
    public function setExportColumns(array $columns): self
    {
        $this->exportColumns = $columns;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'export';
    }

    /**
     * Get human-readable label for an enrichment column.
     *
     * @param string $alias Column alias.
     * @return string Human-readable label.
     */
    protected function getColumnLabel(string $alias): string
    {
        return self::$columnLabels[$alias] ?? ucwords(str_replace('_', ' ', $alias));
    }

    /**
     * Format a source value for CSV output.
     *
     * @param string $source Source name.
     * @param mixed  $value  Raw value from the row.
     * @return mixed Formatted value.
     */
    protected function formatSourceValue(string $source, $value)
    {
        if ($source === 'avg_session_duration' || $source === 'avg_time_on_page') {
            $seconds = (int) $value;
            $hours   = intdiv($seconds, 3600);
            $minutes = intdiv($seconds % 3600, 60);
            $secs    = $seconds % 60;
            return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
        }

        if ($source === 'visitor_status') {
            return (string) $value;
        }

        return isset($value) ? (float) $value : 0;
    }

    /**
     * {@inheritdoc}
     */
    public function format(Query $query, array $result): array
    {
        try {
            $groupBy    = $query->getGroupBy();
            $sources    = $query->getSources();
            $rows       = $result['rows'] ?? [];
            $hasCompare = $query->hasComparison();

            $hasExportColumns = !empty($this->exportColumns);

            // Build headers
            $headers = [];

            // Add group by headers first
            foreach ($groupBy as $groupByItem) {
                if ($hasExportColumns && isset(self::$groupByLabelOverrides[$groupByItem])) {
                    $headers[] = self::$groupByLabelOverrides[$groupByItem];
                } else {
                    $headers[] = $this->getGroupByLabel($groupByItem);
                }
            }

            // Add export column headers (between group_by and sources)
            if ($hasExportColumns) {
                foreach ($this->exportColumns as $column) {
                    $headers[] = $this->getColumnLabel($column);
                }
            }

            // Add source headers
            foreach ($sources as $source) {
                $headers[] = $this->getSourceLabel($source);

                // If comparison, add previous and change columns
                if ($hasCompare) {
                    $headers[] = sprintf(
                        /* translators: %s: metric name */
                        __('%s (Previous)', 'wp-statistics'),
                        $this->getSourceLabel($source)
                    );
                    $headers[] = __('Change %', 'wp-statistics');
                }
            }

            // Build row arrays
            $exportRows = [];

            foreach ($rows as $row) {
                $exportRow = [];

                // Add group by values
                foreach ($groupBy as $groupByItem) {
                    $alias       = $this->getGroupByAlias($groupByItem);
                    $exportRow[] = $row[$alias] ?? '';
                }

                // Add export column values (between group_by and sources)
                if ($hasExportColumns) {
                    foreach ($this->exportColumns as $column) {
                        $exportRow[] = $row[$column] ?? '';
                    }
                }

                // Add source values
                foreach ($sources as $source) {
                    $currentValue = $this->formatSourceValue($source, $row[$source] ?? null);
                    $exportRow[]  = $currentValue;

                    // If comparison, add previous and change
                    if ($hasCompare) {
                        $previousRaw   = $row['previous'][$source] ?? null;
                        $previousValue = $this->formatSourceValue($source, $previousRaw);

                        $currentFloat  = isset($row[$source]) ? (float) $row[$source] : 0;
                        $previousFloat = isset($row['previous'][$source]) ? (float) $row['previous'][$source] : 0;
                        $change        = $this->calculateChange($currentFloat, $previousFloat);

                        $exportRow[] = $previousValue;
                        $exportRow[] = $this->formatChangeString($change);
                    }
                }

                $exportRows[] = $exportRow;
            }

            $response = [
                'success' => true,
                'headers' => $headers,
                'rows'    => $exportRows,
                'meta'    => $this->buildBaseMeta($query),
            ];

            // Add comparison info if present
            if (isset($result['compare_from'])) {
                $response['meta']['compare_from'] = $result['compare_from'];
                $response['meta']['compare_to']   = $result['compare_to'];
            }

            return $response;
        } finally {
            $this->exportColumns = [];
        }
    }
}
