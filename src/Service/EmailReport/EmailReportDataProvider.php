<?php

namespace WP_Statistics\Service\EmailReport;

use WP_Statistics\Service\AnalyticsQuery\AnalyticsQueryHandler;
use WP_Statistics\Utils\Math;

/**
 * Gathers analytics data for email reports via batch queries.
 *
 * @since 15.0.0
 */
class EmailReportDataProvider
{
    /**
     * Gather report data for the given frequency.
     *
     * @param string $frequency 'daily', 'weekly', or 'monthly'.
     * @return array Structured report data.
     */
    public function gather(string $frequency): array
    {
        $dates = $this->calculateDateRange($frequency);

        $queries = [
            [
                'id'      => 'current_metrics',
                'sources' => ['visitors', 'views'],
                'format'  => 'flat',
            ],
            [
                'id'        => 'daily_chart',
                'sources'   => ['visitors'],
                'group_by'  => ['date'],
                'format'    => 'table',
                'per_page'  => 31,
                'order_by'  => 'date',
                'order'     => 'ASC',
            ],
            [
                'id'       => 'top_pages',
                'sources'  => ['views'],
                'group_by' => ['page'],
                'columns'  => ['page_title', 'page_uri', 'views'],
                'format'   => 'table',
                'per_page' => 10,
                'order_by' => 'views',
                'order'    => 'DESC',
            ],
            [
                'id'       => 'top_referrers',
                'sources'  => ['visitors'],
                'group_by' => ['referrer'],
                'columns'  => ['referrer_domain', 'visitors'],
                'format'   => 'table',
                'per_page' => 5,
                'order_by' => 'visitors',
                'order'    => 'DESC',
            ],
        ];

        /**
         * Filter the email report batch queries.
         *
         * @since 15.0.0
         * @param array  $queries   Batch query array.
         * @param string $frequency Report frequency.
         * @param array  $dates     Date range info.
         */
        $queries = apply_filters('wp_statistics_email_report_queries', $queries, $frequency, $dates);
        $queries = is_array($queries) ? $queries : [];

        $handler = new AnalyticsQueryHandler(true);
        $batch   = $handler->handleBatch(
            $queries,
            $dates['date_from'],
            $dates['date_to'],
            [],
            true,
            null,
            $dates['prev_from'],
            $dates['prev_to']
        );

        $items = is_array($batch['items'] ?? null) ? $batch['items'] : [];
        $data  = $this->processResults($items, $dates, $frequency);

        /**
         * Filter processed email report data.
         *
         * Allows premium modules/extensions to map additional batch query
         * responses into email-renderable sections.
         *
         * @since 15.0.0
         * @param array  $data      Processed email report data.
         * @param array  $items     Raw batch query items keyed by query ID.
         * @param array  $dates     Date range info.
         * @param string $frequency Report frequency.
         */
        return apply_filters('wp_statistics_email_report_data', $data, $items, $dates, $frequency);
    }

    /**
     * Calculate date ranges based on frequency.
     *
     * @param string $frequency
     * @return array Keys: date_from, date_to, prev_from, prev_to.
     */
    private function calculateDateRange(string $frequency): array
    {
        $timezone = wp_timezone();
        $now      = new \DateTimeImmutable('now', $timezone);

        switch ($frequency) {
            case 'daily':
                $dateFrom = $now->modify('-1 day')->format('Y-m-d');
                $dateTo   = $dateFrom;
                $prevFrom = $now->modify('-2 days')->format('Y-m-d');
                $prevTo   = $prevFrom;
                break;

            case 'monthly':
                $firstOfThisMonth = $now->modify('first day of this month');
                $lastMonth        = $firstOfThisMonth->modify('-1 month');
                $dateFrom         = $lastMonth->format('Y-m-d');
                $dateTo           = $firstOfThisMonth->modify('-1 day')->format('Y-m-d');
                $prevMonth        = $lastMonth->modify('-1 month');
                $prevFrom         = $prevMonth->format('Y-m-d');
                $prevTo           = $lastMonth->modify('-1 day')->format('Y-m-d');
                break;

            case 'weekly':
            default:
                $dateFrom = $now->modify('-7 days')->format('Y-m-d');
                $dateTo   = $now->modify('-1 day')->format('Y-m-d');
                $prevFrom = $now->modify('-14 days')->format('Y-m-d');
                $prevTo   = $now->modify('-8 days')->format('Y-m-d');
                break;
        }

        return [
            'date_from' => $dateFrom,
            'date_to'   => $dateTo,
            'prev_from' => $prevFrom,
            'prev_to'   => $prevTo,
        ];
    }

    /**
     * Process batch results into structured report data.
     *
     * @param array  $items     Batch response items from AnalyticsQueryHandler.
     * @param array  $dates     Date range info.
     * @param string $frequency Report frequency.
     * @return array
     */
    private function processResults(array $items, array $dates, string $frequency): array
    {
        // KPI metrics
        $kpis    = [];
        $metrics = $this->getItemTotals($items['current_metrics'] ?? []);

        if (!empty($metrics)) {
            foreach ($metrics as $key => $totals) {
                if (!is_array($totals)) {
                    continue;
                }

                $current  = (float) ($totals['current'] ?? 0);
                $previous = (float) ($totals['previous'] ?? 0);
                $change   = (int) Math::percentageChange($previous, $current, 0, 'zero');

                $label = $key === 'visitors'
                    ? __('Visitors', 'wp-statistics')
                    : __('Page Views', 'wp-statistics');

                $kpis[] = [
                    'label'          => $label,
                    'value'          => number_format_i18n($current),
                    'change_percent' => $change,
                ];
            }
        }

        // Daily chart data
        $dailyChart = [];
        $chartData  = $this->getQueryRows($items, 'daily_chart');
        foreach ($chartData as $row) {
            if (!is_array($row)) {
                continue;
            }
            $dateLabel    = isset($row['date']) ? date_i18n('D', strtotime($row['date'])) : '';
            $dailyChart[] = [
                'label' => $dateLabel,
                'value' => intval($row['visitors'] ?? 0),
            ];
        }

        // Top pages
        $topPages = [];
        $pageRows = $this->getQueryRows($items, 'top_pages');
        foreach ($pageRows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $topPages[] = [
                'label' => $row['page_title'] ?? ($row['page_uri'] ?? ''),
                'value' => number_format_i18n(intval($row['views'] ?? 0)),
                'url'   => !empty($row['page_uri']) ? home_url($row['page_uri']) : '',
            ];
        }

        // Top referrers
        $topReferrers = [];
        $refRows      = $this->getQueryRows($items, 'top_referrers');
        foreach ($refRows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $topReferrers[] = [
                'label' => $row['referrer_domain'] ?? __('Direct', 'wp-statistics'),
                'value' => number_format_i18n(intval($row['visitors'] ?? 0)),
            ];
        }

        // Report title and period
        $reportTitle = $this->getReportTitle($frequency);
        $dateFormat  = get_option('date_format', 'F j, Y');
        $periodFrom  = date_i18n($dateFormat, strtotime($dates['date_from']));
        $periodTo    = date_i18n($dateFormat, strtotime($dates['date_to']));
        $reportPeriod = ($dates['date_from'] === $dates['date_to'])
            ? $periodFrom
            : $periodFrom . ' – ' . $periodTo;

        return [
            'kpis'                => $kpis,
            'daily_chart'         => $dailyChart,
            'top_pages'           => $topPages,
            'top_referrers'       => $topReferrers,
            'engagement_kpis'     => [],
            'top_entry_pages'     => [],
            'top_exit_pages'      => [],
            'top_countries'       => [],
            'device_breakdown'    => [
                'types'             => [],
                'browsers'          => [],
                'operating_systems' => [],
            ],
            'report_title'        => $reportTitle,
            'report_period'       => $reportPeriod,
        ];
    }

    /**
     * Get table rows for a query item.
     *
     * @param array  $items   Batch query items.
     * @param string $queryId Query item ID.
     * @return array
     */
    private function getQueryRows(array $items, string $queryId): array
    {
        $rows = $items[$queryId]['data']['rows'] ?? [];
        return is_array($rows) ? $rows : [];
    }

    /**
     * Get totals payload for a query item.
     *
     * Supports both flat (`totals`) and table (`data.totals`) responses.
     *
     * @param array $item Query item payload.
     * @return array
     */
    private function getItemTotals(array $item): array
    {
        if (!is_array($item)) {
            return [];
        }

        $totals = $item['totals'] ?? ($item['data']['totals'] ?? []);
        return is_array($totals) ? $totals : [];
    }

    /**
     * Get localized report title based on frequency.
     *
     * @param string $frequency
     * @return string
     */
    private function getReportTitle(string $frequency): string
    {
        switch ($frequency) {
            case 'daily':
                return __('Daily Performance Report', 'wp-statistics');
            case 'monthly':
                return __('Monthly Performance Report', 'wp-statistics');
            case 'weekly':
            default:
                return __('Weekly Performance Report', 'wp-statistics');
        }
    }
}
