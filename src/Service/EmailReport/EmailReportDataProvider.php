<?php

namespace WP_Statistics\Service\EmailReport;

use WP_Statistics\Components\DateRange;
use WP_Statistics\Service\AnalyticsQuery\AnalyticsQueryHandler;
use WP_Statistics\Utils\Math;

/**
 * Email Report Data Provider
 *
 * Provides all data needed for the email report template.
 * Uses AnalyticsQueryHandler for all metrics and data.
 *
 * @package WP_Statistics\Service\EmailReport
 * @since 15.0.0
 */
class EmailReportDataProvider
{
    /**
     * Period type (daily, weekly, biweekly, monthly)
     *
     * @var string
     */
    private $period;

    /**
     * Date range for the period
     *
     * @var array
     */
    private $dateRange;

    /**
     * Previous date range for comparison
     *
     * @var array
     */
    private $prevDateRange;

    /**
     * Analytics query handler
     *
     * @var AnalyticsQueryHandler
     */
    private $queryHandler;

    /**
     * Constructor
     *
     * @param string $period Period type (daily, weekly, biweekly, monthly)
     */
    public function __construct($period = 'weekly')
    {
        $this->period        = $period;
        $this->dateRange     = $this->calculateDateRange($period);
        $this->prevDateRange = $this->calculatePrevDateRange($period);
        $this->queryHandler  = new AnalyticsQueryHandler(false); // Disable cache for email reports
    }

    /**
     * Calculate date range based on period using DateRange component.
     *
     * @param string $period Period type
     * @return array ['from' => 'Y-m-d', 'to' => 'Y-m-d']
     */
    private function calculateDateRange($period)
    {
        $dateRangeName = $this->mapPeriodToDateRange($period);

        return DateRange::get($dateRangeName, true);
    }

    /**
     * Calculate previous date range for comparison.
     *
     * @param string $period Period type
     * @return array ['from' => 'Y-m-d', 'to' => 'Y-m-d']
     */
    private function calculatePrevDateRange($period)
    {
        $dateRangeName = $this->mapPeriodToDateRange($period);

        return DateRange::getPrevPeriod($dateRangeName, true);
    }

    /**
     * Map email report period to DateRange component period name.
     *
     * @param string $period Period type (daily, weekly, biweekly, monthly)
     * @return string DateRange period name
     */
    private function mapPeriodToDateRange($period)
    {
        $mapping = [
            'daily'    => 'yesterday',
            'weekly'   => '7days',
            'biweekly' => '14days',
            'monthly'  => 'last_month',
        ];

        return $mapping[$period] ?? '7days';
    }

    /**
     * Get metrics data (visitors, views, referrals, contents with percentage changes)
     *
     * @return array
     */
    public function getMetrics()
    {
        // Get current period metrics
        $currentResult = $this->queryHandler->handle([
            'sources'   => ['visitors', 'views'],
            'date_from' => $this->dateRange['from'],
            'date_to'   => $this->dateRange['to'],
            'format'    => 'flat',
        ]);

        // Get previous period metrics for comparison
        $prevResult = $this->queryHandler->handle([
            'sources'   => ['visitors', 'views'],
            'date_from' => $this->prevDateRange['from'],
            'date_to'   => $this->prevDateRange['to'],
            'format'    => 'flat',
        ]);

        // Get referrals count (visitors with referrer)
        $referralsResult = $this->queryHandler->handle([
            'sources'   => ['visitors'],
            'group_by'  => ['referrer'],
            'date_from' => $this->dateRange['from'],
            'date_to'   => $this->dateRange['to'],
            'format'    => 'flat',
            'per_page'  => 1000,
        ]);

        $prevReferralsResult = $this->queryHandler->handle([
            'sources'   => ['visitors'],
            'group_by'  => ['referrer'],
            'date_from' => $this->prevDateRange['from'],
            'date_to'   => $this->prevDateRange['to'],
            'format'    => 'flat',
            'per_page'  => 1000,
        ]);

        // Calculate referrals total (sum of visitors from all referrers)
        $referralsCount     = $currentResult['data']['totals']['visitors'] ?? 0;
        $prevReferralsCount = $prevResult['data']['totals']['visitors'] ?? 0;

        // Use grouped referrer data for proper referral count
        if (!empty($referralsResult['data']['rows'])) {
            $referralsCount = array_sum(array_column($referralsResult['data']['rows'], 'visitors'));
        }
        if (!empty($prevReferralsResult['data']['rows'])) {
            $prevReferralsCount = array_sum(array_column($prevReferralsResult['data']['rows'], 'visitors'));
        }

        // Get published contents count
        $contentsCount     = $this->getPublishedContentsCount($this->dateRange);
        $prevContentsCount = $this->getPublishedContentsCount($this->prevDateRange);

        // Extract current values
        $currentVisitors = $currentResult['data']['totals']['visitors'] ?? 0;
        $currentViews    = $currentResult['data']['totals']['views'] ?? 0;

        // Extract previous values
        $prevVisitors = $prevResult['data']['totals']['visitors'] ?? 0;
        $prevViews    = $prevResult['data']['totals']['views'] ?? 0;

        return [
            'visitors' => [
                'value'  => $currentVisitors,
                'change' => Math::percentageChange($prevVisitors, $currentVisitors, 1, 'hundred'),
                'label'  => __('Visitors', 'wp-statistics'),
            ],
            'views' => [
                'value'  => $currentViews,
                'change' => Math::percentageChange($prevViews, $currentViews, 1, 'hundred'),
                'label'  => __('Views', 'wp-statistics'),
            ],
            'referrals' => [
                'value'  => $referralsCount,
                'change' => Math::percentageChange($prevReferralsCount, $referralsCount, 1, 'hundred'),
                'label'  => __('Referrals', 'wp-statistics'),
            ],
            'contents' => [
                'value'  => $contentsCount,
                'change' => Math::percentageChange($prevContentsCount, $contentsCount, 1, 'hundred'),
                'label'  => __('Published', 'wp-statistics'),
            ],
        ];
    }

    /**
     * Get published contents count for a date range.
     *
     * @param array $dateRange Date range with 'from' and 'to' keys
     * @return int
     */
    private function getPublishedContentsCount($dateRange)
    {
        global $wpdb;

        $postTypes = get_post_types(['public' => true]);
        unset($postTypes['attachment']);

        if (empty($postTypes)) {
            return 0;
        }

        $placeholders = implode(',', array_fill(0, count($postTypes), '%s'));
        $params       = array_merge(
            array_values($postTypes),
            [$dateRange['from'] . ' 00:00:00', $dateRange['to'] . ' 23:59:59']
        );

        $count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->posts}
                 WHERE post_type IN ({$placeholders})
                 AND post_status = 'publish'
                 AND post_date BETWEEN %s AND %s",
                $params
            )
        );

        return intval($count);
    }

    /**
     * Get top pages
     *
     * @param int $limit Number of pages to return
     * @return array
     */
    public function getTopPages($limit = 5)
    {
        $result = $this->queryHandler->handle([
            'sources'   => ['views', 'visitors'],
            'group_by'  => ['page'],
            'date_from' => $this->dateRange['from'],
            'date_to'   => $this->dateRange['to'],
            'format'    => 'table',
            'per_page'  => $limit,
        ]);

        $pages = [];

        if (!empty($result['data']['rows'])) {
            foreach ($result['data']['rows'] as $row) {
                $pageId = $row['page_id'] ?? 0;
                $title  = $row['page_title'] ?? '';

                // Get URL from page_id or page_url
                $url = '';
                if (!empty($pageId)) {
                    $url = get_permalink($pageId);
                } elseif (!empty($row['page_url'])) {
                    $url = home_url($row['page_url']);
                }

                $pages[] = [
                    'title'    => $title ?: __('(No title)', 'wp-statistics'),
                    'url'      => $url,
                    'views'    => intval($row['views'] ?? 0),
                    'visitors' => intval($row['visitors'] ?? 0),
                ];
            }
        }

        return $pages;
    }

    /**
     * Get top referrers
     *
     * @param int $limit Number of referrers to return
     * @return array
     */
    public function getTopReferrers($limit = 5)
    {
        $result = $this->queryHandler->handle([
            'sources'   => ['visitors'],
            'group_by'  => ['referrer'],
            'date_from' => $this->dateRange['from'],
            'date_to'   => $this->dateRange['to'],
            'format'    => 'table',
            'per_page'  => $limit,
        ]);

        $referrers = [];

        if (!empty($result['data']['rows'])) {
            foreach ($result['data']['rows'] as $row) {
                $referrerUrl = $row['referrer'] ?? '';

                if (empty($referrerUrl)) {
                    continue;
                }

                $domain = wp_parse_url($referrerUrl, PHP_URL_HOST);
                $domain = $domain ? str_replace('www.', '', $domain) : $referrerUrl;

                $referrers[] = [
                    'domain'   => $domain,
                    'url'      => $referrerUrl,
                    'visitors' => intval($row['visitors'] ?? 0),
                ];
            }
        }

        return $referrers;
    }

    /**
     * Get top author by views
     *
     * @return string|null Author name or null
     */
    public function getTopAuthor()
    {
        $result = $this->queryHandler->handle([
            'sources'   => ['views'],
            'group_by'  => ['author'],
            'date_from' => $this->dateRange['from'],
            'date_to'   => $this->dateRange['to'],
            'format'    => 'table',
            'per_page'  => 1,
        ]);

        if (!empty($result['data']['rows'][0]['author_name'])) {
            return $result['data']['rows'][0]['author_name'];
        }

        return null;
    }

    /**
     * Get top category by views
     *
     * @return string|null Category name or null
     */
    public function getTopCategory()
    {
        $result = $this->queryHandler->handle([
            'sources'   => ['views'],
            'group_by'  => ['taxonomy'],
            'filters'   => ['taxonomy_type' => ['is' => 'category']],
            'date_from' => $this->dateRange['from'],
            'date_to'   => $this->dateRange['to'],
            'format'    => 'table',
            'per_page'  => 1,
        ]);

        if (!empty($result['data']['rows'][0]['term_name'])) {
            return $result['data']['rows'][0]['term_name'];
        }

        return null;
    }

    /**
     * Get top post by views
     *
     * @return string|null Post title or null
     */
    public function getTopPost()
    {
        $result = $this->queryHandler->handle([
            'sources'   => ['views'],
            'group_by'  => ['page'],
            'date_from' => $this->dateRange['from'],
            'date_to'   => $this->dateRange['to'],
            'format'    => 'table',
            'per_page'  => 1,
        ]);

        if (!empty($result['data']['rows'][0]['page_title'])) {
            return $result['data']['rows'][0]['page_title'];
        }

        return null;
    }

    /**
     * Get date range
     *
     * @return array ['from' => 'Y-m-d', 'to' => 'Y-m-d']
     */
    public function getDateRange()
    {
        return $this->dateRange;
    }

    /**
     * Get formatted period label
     *
     * @return string
     */
    public function getFormattedPeriod()
    {
        $from = date_i18n(get_option('date_format'), strtotime($this->dateRange['from']));
        $to   = date_i18n(get_option('date_format'), strtotime($this->dateRange['to']));

        if ($from === $to) {
            return $from;
        }

        return sprintf('%s - %s', $from, $to);
    }

    /**
     * Get period label
     *
     * @return string
     */
    public function getPeriodLabel()
    {
        $labels = [
            'daily'    => __('Daily', 'wp-statistics'),
            'weekly'   => __('Weekly', 'wp-statistics'),
            'biweekly' => __('Bi-Weekly', 'wp-statistics'),
            'monthly'  => __('Monthly', 'wp-statistics'),
        ];

        return $labels[$this->period] ?? $labels['weekly'];
    }

    /**
     * Get all data for template as array
     *
     * @return array
     */
    public function toArray()
    {
        $data = [
            'site_name'     => get_bloginfo('name'),
            'site_url'      => home_url(),
            'period'        => $this->period,
            'period_label'  => $this->getPeriodLabel(),
            'date_range'    => $this->getFormattedPeriod(),
            'metrics'       => $this->getMetrics(),
            'top_pages'     => $this->getTopPages(5),
            'top_referrers' => $this->getTopReferrers(5),
            'top_author'    => $this->getTopAuthor(),
            'top_category'  => $this->getTopCategory(),
            'top_post'      => $this->getTopPost(),
            'dashboard_url' => admin_url('admin.php?page=wps_overview_page'),
            'settings_url'  => admin_url('admin.php?page=wps_settings_page'),
        ];

        /**
         * Filter the email report data before template rendering.
         *
         * @since 15.0.0
         * @param array  $data   The report data array.
         * @param string $period The report period (daily, weekly, biweekly, monthly).
         */
        return apply_filters('wp_statistics_email_report_data', $data, $this->period);
    }
}
