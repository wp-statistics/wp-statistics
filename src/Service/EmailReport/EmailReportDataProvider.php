<?php

namespace WP_Statistics\Service\EmailReport;

use WP_STATISTICS\Helper;
use WP_STATISTICS\TimeZone;
use WP_Statistics\Models\ViewsModel;
use WP_Statistics\Models\VisitorsModel;
use WP_Statistics\Service\Admin\WebsitePerformance\WebsitePerformanceDataProvider;

/**
 * Email Report Data Provider
 *
 * Provides all data needed for the email report template.
 * Leverages WebsitePerformanceDataProvider for metrics.
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
     * Website performance data provider
     *
     * @var WebsitePerformanceDataProvider
     */
    private $performanceProvider;

    /**
     * Constructor
     *
     * @param string $period Period type (daily, weekly, biweekly, monthly)
     */
    public function __construct($period = 'weekly')
    {
        $this->period    = $period;
        $this->dateRange = $this->calculateDateRange($period);

        $this->performanceProvider = new WebsitePerformanceDataProvider(
            $this->dateRange['from'],
            $this->dateRange['to']
        );
    }

    /**
     * Calculate date range based on period
     *
     * @param string $period Period type
     * @return array ['from' => 'Y-m-d', 'to' => 'Y-m-d']
     */
    private function calculateDateRange($period)
    {
        $to = TimeZone::getTimeAgo(1); // Yesterday

        switch ($period) {
            case 'daily':
                $from = TimeZone::getTimeAgo(1);
                break;
            case 'weekly':
                $from = TimeZone::getTimeAgo(7);
                break;
            case 'biweekly':
                $from = TimeZone::getTimeAgo(14);
                break;
            case 'monthly':
                $from = date('Y-m-d', strtotime('First day of previous month'));
                $to   = date('Y-m-d', strtotime('Last day of previous month'));
                break;
            default:
                $from = TimeZone::getTimeAgo(7);
        }

        return ['from' => $from, 'to' => $to];
    }

    /**
     * Get metrics data (visitors, views, referrals, contents with percentage changes)
     *
     * @return array
     */
    public function getMetrics()
    {
        return [
            'visitors' => [
                'value'   => $this->performanceProvider->getCurrentPeriodVisitors(),
                'change'  => $this->performanceProvider->getPercentageChangeVisitors(),
                'label'   => __('Visitors', 'wp-statistics'),
            ],
            'views' => [
                'value'   => $this->performanceProvider->getCurrentPeriodViews(),
                'change'  => $this->performanceProvider->getPercentageChangeViews(),
                'label'   => __('Views', 'wp-statistics'),
            ],
            'referrals' => [
                'value'   => $this->performanceProvider->getCurrentPeriodReferralsCount(),
                'change'  => $this->performanceProvider->getPercentageChangeReferrals(),
                'label'   => __('Referrals', 'wp-statistics'),
            ],
            'contents' => [
                'value'   => $this->performanceProvider->getCurrentPeriodContents(),
                'change'  => $this->performanceProvider->getPercentageChangeContents(),
                'label'   => __('Published', 'wp-statistics'),
            ],
        ];
    }

    /**
     * Get top pages
     *
     * @param int $limit Number of pages to return
     * @return array
     */
    public function getTopPages($limit = 5)
    {
        $viewsModel = new ViewsModel();

        $pages = $viewsModel->getPostsViewsData([
            'date'     => $this->dateRange,
            'order_by' => 'views',
            'order'    => 'DESC',
            'per_page' => $limit,
            'page'     => 1,
        ]);

        $result = [];
        foreach ($pages as $page) {
            $result[] = [
                'title'   => $page->post_title ?: __('(No title)', 'wp-statistics'),
                'url'     => get_permalink($page->post_id),
                'views'   => intval($page->views ?? 0),
                'visitors' => intval($page->visitors ?? 0),
            ];
        }

        return $result;
    }

    /**
     * Get top referrers
     *
     * @param int $limit Number of referrers to return
     * @return array
     */
    public function getTopReferrers($limit = 5)
    {
        $visitorsModel = new VisitorsModel();

        $referrers = $visitorsModel->getReferrers([
            'date'     => $this->dateRange,
            'per_page' => $limit,
            'page'     => 1,
        ]);

        $result = [];
        foreach ($referrers as $referrer) {
            if (empty($referrer->referred)) {
                continue;
            }

            $domain = wp_parse_url($referrer->referred, PHP_URL_HOST);
            $domain = str_replace('www.', '', $domain);

            $result[] = [
                'domain'   => $domain ?: $referrer->referred,
                'url'      => $referrer->referred,
                'visitors' => intval($referrer->visitors ?? 0),
            ];
        }

        return $result;
    }

    /**
     * Get top author
     *
     * @return string|null
     */
    public function getTopAuthor()
    {
        $author = $this->performanceProvider->getTopAuthor();
        return !empty($author) ? $author : null;
    }

    /**
     * Get top category
     *
     * @return string|null
     */
    public function getTopCategory()
    {
        $category = $this->performanceProvider->getTopCategory();
        return !empty($category) ? $category : null;
    }

    /**
     * Get top post
     *
     * @return string|null
     */
    public function getTopPost()
    {
        $post = $this->performanceProvider->getTopPost();
        return !empty($post) ? $post : null;
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
            'site_name'         => get_bloginfo('name'),
            'site_url'          => home_url(),
            'period'            => $this->period,
            'period_label'      => $this->getPeriodLabel(),
            'date_range'        => $this->getFormattedPeriod(),
            'metrics'           => $this->getMetrics(),
            'top_pages'         => $this->getTopPages(5),
            'top_referrers'     => $this->getTopReferrers(5),
            'top_author'        => $this->getTopAuthor(),
            'top_category'      => $this->getTopCategory(),
            'top_post'          => $this->getTopPost(),
            'dashboard_url'     => admin_url('admin.php?page=wps_overview_page'),
            'settings_url'      => admin_url('admin.php?page=wps_settings_page'),
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

    /**
     * Format number for display (K, M notation)
     *
     * @param int $number
     * @return string
     */
    public static function formatNumber($number)
    {
        if ($number >= 1000000) {
            return round($number / 1000000, 1) . 'M';
        }
        if ($number >= 1000) {
            return round($number / 1000, 1) . 'K';
        }
        return number_format($number);
    }
}
