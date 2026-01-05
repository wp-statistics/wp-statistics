<?php

namespace WP_Statistics\Service\Admin\Metabox;

use WP_STATISTICS\Helper;
use WP_Statistics\Utils\Request;
use WP_Statistics\Models\OnlineModel;
use WP_Statistics\Components\DateRange;
use WP_Statistics\Service\Admin\Posts\PostsManager;
use WP_Statistics\Service\Charts\ChartDataProviderFactory;
use WP_Statistics\Service\AnalyticsQuery\AnalyticsQueryHandler;
use WP_Statistics\Service\AnalyticsQuery\Sources\OnlineVisitorsSource;

/**
 * Provides data for admin metabox widgets.
 *
 * @since 15.0.0 Refactored to use AnalyticsQueryHandler instead of legacy models.
 */
class MetaboxDataProvider
{
    /**
     * Analytics query handler instance.
     *
     * @var AnalyticsQueryHandler
     */
    protected $queryHandler;

    /**
     * Online model for detailed online visitor data (listing).
     *
     * @var OnlineModel
     */
    protected $onlineModel;

    public function __construct()
    {
        $this->queryHandler = new AnalyticsQueryHandler();
        $this->onlineModel  = new OnlineModel();
    }

    /**
     * Get count of currently online visitors.
     *
     * Uses AnalyticsQueryHandler with online_visitors source which filters
     * by sessions.ended_at to find visitors active within the last 5 minutes.
     *
     * @return int Count of online visitors.
     */
    protected function countOnlineVisitors(): int
    {
        $threshold = OnlineVisitorsSource::getOnlineThreshold();
        $now       = gmdate('Y-m-d H:i:s');

        $result = $this->queryHandler->handle([
            'sources'   => ['online_visitors'],
            'date_from' => $threshold,
            'date_to'   => $now,
            'format'    => 'flat',
        ]);

        return intval($result['data']['totals']['online_visitors'] ?? 0);
    }

    /**
     * Get traffic summary data with visitors and views for predefined periods.
     *
     * @param array $args Optional filter arguments.
     * @return array Traffic summary data with online count, visitors, hits, labels, and keys.
     */
    public function getTrafficSummaryData($args = [])
    {
        $periods = [
            'today'      => ['label' => esc_html__('Today', 'wp-statistics'), 'date' => 'today'],
            'yesterday'  => ['label' => esc_html__('Yesterday', 'wp-statistics'), 'date' => 'yesterday'],
            'this_week'  => ['label' => esc_html__('This week', 'wp-statistics'), 'date' => 'this_week'],
            'last_week'  => ['label' => esc_html__('Last week', 'wp-statistics'), 'date' => 'last_week'],
            'this_month' => ['label' => esc_html__('This month', 'wp-statistics'), 'date' => 'this_month'],
            'last_month' => ['label' => esc_html__('Last month', 'wp-statistics'), 'date' => 'last_month'],
            '7days'      => ['label' => esc_html__('Last 7 days', 'wp-statistics'), 'date' => '7days'],
            '30days'     => ['label' => esc_html__('Last 30 days', 'wp-statistics'), 'date' => '30days'],
            '90days'     => ['label' => esc_html__('Last 90 days', 'wp-statistics'), 'date' => '90days'],
            '6months'    => ['label' => esc_html__('Last 6 months', 'wp-statistics'), 'date' => '6months'],
            'this_year'  => ['label' => esc_html__('This year (Jan-Today)', 'wp-statistics'), 'date' => 'this_year'],
        ];

        $visitors = [];
        $hits     = [];
        $labels   = [];
        $keys     = [];

        foreach ($periods as $key => $period) {
            $dateRange = DateRange::get($period['date']);

            $result = $this->queryHandler->handle([
                'sources'   => ['visitors', 'views'],
                'date_from' => $dateRange['from'],
                'date_to'   => $dateRange['to'],
                'format'    => 'flat',
            ]);

            $visitors[] = intval($result['data']['totals']['visitors'] ?? 0);
            $hits[]     = intval($result['data']['totals']['views'] ?? 0);
            $labels[]   = $period['label'];
            $keys[]     = $key;
        }

        return [
            'online'   => $this->countOnlineVisitors(),
            'visitors' => $visitors,
            'hits'     => $hits,
            'labels'   => $labels,
            'keys'     => $keys,
        ];
    }

    /**
     * Get traffic overview data.
     *
     * @param array $args Filter arguments for the queries.
     * @return array Traffic overview with online count, visitors, and hits summaries.
     */
    public function getTrafficOverviewData($args = [])
    {
        $periods = [
            'today'      => ['label' => esc_html__('Today', 'wp-statistics'), 'date' => 'today'],
            'yesterday'  => ['label' => esc_html__('Yesterday', 'wp-statistics'), 'date' => 'yesterday'],
            'this_week'  => ['label' => esc_html__('This week', 'wp-statistics'), 'date' => 'this_week'],
            'last_week'  => ['label' => esc_html__('Last week', 'wp-statistics'), 'date' => 'last_week'],
            'this_month' => ['label' => esc_html__('This month', 'wp-statistics'), 'date' => 'this_month'],
            'last_month' => ['label' => esc_html__('Last month', 'wp-statistics'), 'date' => 'last_month'],
            '7days'      => ['label' => esc_html__('Last 7 days', 'wp-statistics'), 'date' => '7days'],
            '30days'     => ['label' => esc_html__('Last 30 days', 'wp-statistics'), 'date' => '30days'],
            '90days'     => ['label' => esc_html__('Last 90 days', 'wp-statistics'), 'date' => '90days'],
            '6months'    => ['label' => esc_html__('Last 6 months', 'wp-statistics'), 'date' => '6months'],
            'this_year'  => ['label' => esc_html__('This year (Jan-Today)', 'wp-statistics'), 'date' => 'this_year'],
        ];

        $visitors = [];
        $hits     = [];

        foreach ($periods as $key => $period) {
            $dateRange = DateRange::get($period['date']);

            $result = $this->queryHandler->handle([
                'sources'   => ['visitors', 'views'],
                'date_from' => $dateRange['from'],
                'date_to'   => $dateRange['to'],
                'format'    => 'flat',
            ]);

            $visitors[$key] = [
                'label'    => $period['label'],
                'visitors' => intval($result['data']['totals']['visitors'] ?? 0),
            ];

            $hits[$key] = [
                'label' => $period['label'],
                'hits'  => intval($result['data']['totals']['views'] ?? 0),
            ];
        }

        return [
            'online'   => $this->countOnlineVisitors(),
            'visitors' => $visitors,
            'hits'     => $hits,
        ];
    }

    /**
     * Get referrers data.
     *
     * @param array $args Filter arguments.
     * @return array Referrers data with visitors count and referrer info.
     */
    public function getReferrersData($args = [])
    {
        $dateFrom = $args['date']['from'] ?? date('Y-m-d', strtotime('-30 days'));
        $dateTo   = $args['date']['to'] ?? date('Y-m-d');

        $result = $this->queryHandler->handle([
            'sources'   => ['visitors'],
            'group_by'  => ['referrer'],
            'date_from' => $dateFrom,
            'date_to'   => $dateTo,
            'format'    => 'table',
            'per_page'  => 5,
            'page'      => 1,
        ]);

        $referrers = [];
        if (!empty($result['data']['rows'])) {
            foreach ($result['data']['rows'] as $row) {
                $referrers[] = (object) [
                    'visitors'       => intval($row['visitors'] ?? 0),
                    'referred'       => $row['referrer'] ?? '',
                    'source_channel' => $row['referrer_channel'] ?? '',
                    'source_name'    => $row['referrer_name'] ?? '',
                ];
            }
        }

        return $referrers;
    }

    /**
     * Get top visitors data.
     *
     * @param array $args Filter arguments.
     * @return array Top visitors sorted by hits.
     */
    public function getTopVisitorsData($args = [])
    {
        $dateFrom = $args['date']['from'] ?? date('Y-m-d', strtotime('-30 days'));
        $dateTo   = $args['date']['to'] ?? date('Y-m-d');

        $result = $this->queryHandler->handle([
            'sources'   => ['views'],
            'group_by'  => ['visitor'],
            'date_from' => $dateFrom,
            'date_to'   => $dateTo,
            'format'    => 'table',
            'per_page'  => 10,
            'page'      => 1,
        ]);

        return $result['data']['rows'] ?? [];
    }

    /**
     * Get latest visitors data.
     *
     * @param array $args Filter arguments.
     * @return array Latest visitors sorted by ID descending.
     */
    public function getLatestVisitorsData($args = [])
    {
        $dateFrom = $args['date']['from'] ?? date('Y-m-d', strtotime('-30 days'));
        $dateTo   = $args['date']['to'] ?? date('Y-m-d');

        $result = $this->queryHandler->handle([
            'sources'   => ['views'],
            'group_by'  => ['visitor'],
            'date_from' => $dateFrom,
            'date_to'   => $dateTo,
            'format'    => 'table',
            'per_page'  => 10,
            'page'      => 1,
        ]);

        return $result['data']['rows'] ?? [];
    }

    /**
     * Get top pages data.
     *
     * @param array $args Filter arguments.
     * @return array Top pages with views.
     */
    public function getTopPages($args = [])
    {
        $dateFrom = $args['date']['from'] ?? date('Y-m-d', strtotime('-30 days'));
        $dateTo   = $args['date']['to'] ?? date('Y-m-d');

        $result = $this->queryHandler->handle([
            'sources'   => ['views'],
            'group_by'  => ['page'],
            'date_from' => $dateFrom,
            'date_to'   => $dateTo,
            'format'    => 'table',
            'per_page'  => $args['per_page'] ?? 10,
            'page'      => $args['page'] ?? 1,
        ]);

        return $result['data']['rows'] ?? [];
    }

    /**
     * Get online visitors data.
     *
     * @param array $args Filter arguments.
     * @return array Online visitors with total count.
     */
    public function getOnlineVisitorsData($args = [])
    {
        return [
            'visitors' => $this->onlineModel->getOnlineVisitorsData(array_merge($args, ['per_page' => 10])),
            'total'    => $this->countOnlineVisitors()
        ];
    }

    /**
     * Get post summary data.
     *
     * @param array $args Filter arguments.
     * @return array Post statistics summary.
     */
    public function getPostSummaryData($args = [])
    {
        $postId = Request::get('post', '', 'number');

        if (empty($postId) && Request::has('current_page')) {
            $postId = Request::get('current_page', [], 'array')['ID'] ?? 0;
        }

        return PostsManager::getPostStatisticsSummary($postId);
    }

    /**
     * Get single post visitors data.
     *
     * @param array $args Filter arguments.
     * @return array Visitors for a specific post.
     */
    public function getSinglePostData($args = [])
    {
        $currentPage = Request::get('current_page', [], 'array');
        $postId      = $currentPage['ID'] ?? 0;

        if (empty($postId)) {
            return [];
        }

        $result = $this->queryHandler->handle([
            'sources'   => ['views'],
            'group_by'  => ['visitor'],
            'filters'   => [
                'page_id' => $postId,
            ],
            'format'    => 'table',
            'per_page'  => 15,
            'page'      => 1,
        ]);

        return $result['data']['rows'] ?? [];
    }

    /**
     * Get weekly performance data with comparison.
     *
     * @param array $args Filter arguments.
     * @return array Weekly performance metrics with percentage changes.
     */
    public function getWeeklyPerformanceData($args = [])
    {
        $currentPeriod = DateRange::get('7days', true);
        $prevPeriod    = DateRange::getPrevPeriod('7days', true);

        // Get current period visitors
        $currentVisitorsResult = $this->queryHandler->handle([
            'sources'   => ['visitors'],
            'date_from' => $currentPeriod['from'],
            'date_to'   => $currentPeriod['to'],
            'format'    => 'flat',
        ]);
        $currentVisitors = intval($currentVisitorsResult['data']['totals']['visitors'] ?? 0);

        // Get previous period visitors
        $prevVisitorsResult = $this->queryHandler->handle([
            'sources'   => ['visitors'],
            'date_from' => $prevPeriod['from'],
            'date_to'   => $prevPeriod['to'],
            'format'    => 'flat',
        ]);
        $prevVisitors = intval($prevVisitorsResult['data']['totals']['visitors'] ?? 0);

        // Get current period views
        $currentViewsResult = $this->queryHandler->handle([
            'sources'   => ['views'],
            'date_from' => $currentPeriod['from'],
            'date_to'   => $currentPeriod['to'],
            'format'    => 'flat',
        ]);
        $currentViews = intval($currentViewsResult['data']['totals']['views'] ?? 0);

        // Get previous period views
        $prevViewsResult = $this->queryHandler->handle([
            'sources'   => ['views'],
            'date_from' => $prevPeriod['from'],
            'date_to'   => $prevPeriod['to'],
            'format'    => 'flat',
        ]);
        $prevViews = intval($prevViewsResult['data']['totals']['views'] ?? 0);

        // Get posts count (use direct WordPress query as AnalyticsQueryHandler doesn't handle posts)
        $currentPosts = $this->countPublishedPosts($currentPeriod);
        $prevPosts    = $this->countPublishedPosts($prevPeriod);

        // Get referrers count
        $currentReferrersResult = $this->queryHandler->handle([
            'sources'   => ['visitors'],
            'group_by'  => ['referrer'],
            'date_from' => $currentPeriod['from'],
            'date_to'   => $currentPeriod['to'],
            'format'    => 'table',
            'per_page'  => 1000,
        ]);
        $currentReferrers = count($currentReferrersResult['data']['rows'] ?? []);

        $prevReferrersResult = $this->queryHandler->handle([
            'sources'   => ['visitors'],
            'group_by'  => ['referrer'],
            'date_from' => $prevPeriod['from'],
            'date_to'   => $prevPeriod['to'],
            'format'    => 'table',
            'per_page'  => 1000,
        ]);
        $prevReferrers = count($prevReferrersResult['data']['rows'] ?? []);

        $data = [
            'visitors'  => [
                'current_period' => $currentVisitors,
                'prev_period'    => $prevVisitors
            ],
            'visits'    => [
                'current_period' => $currentViews,
                'prev_period'    => $prevViews
            ],
            'posts'     => [
                'current_period' => $currentPosts,
                'prev_period'    => $prevPosts
            ],
            'referrals' => [
                'current_period' => $currentReferrers,
                'prev_period'    => $prevReferrers
            ]
        ];

        foreach ($data as $key => $value) {
            $data[$key]['diff_percentage'] = Helper::calculatePercentageChange($value['prev_period'], $value['current_period']);
            if ($data[$key]['diff_percentage'] > 0) {
                $data[$key]['diff_type'] = 'plus';
            } elseif ($data[$key]['diff_percentage'] < 0) {
                $data[$key]['diff_type'] = 'minus';
            } else {
                $data[$key]['diff_type'] = 'equal';
            }

            $data[$key]['diff_percentage'] = abs($data[$key]['diff_percentage']);
        }

        // Get top referrer
        $topReferrerResult = $this->queryHandler->handle([
            'sources'   => ['visitors'],
            'group_by'  => ['referrer'],
            'date_from' => $currentPeriod['from'],
            'date_to'   => $currentPeriod['to'],
            'format'    => 'table',
            'per_page'  => 1,
        ]);
        $topReferrer = [];
        if (!empty($topReferrerResult['data']['rows'][0])) {
            $row = $topReferrerResult['data']['rows'][0];
            $topReferrer = (object) [
                'visitors'       => intval($row['visitors'] ?? 0),
                'referred'       => $row['referrer'] ?? '',
                'source_channel' => $row['referrer_channel'] ?? '',
                'source_name'    => $row['referrer_name'] ?? '',
            ];
        }

        // Get top author by views
        $topAuthorResult = $this->queryHandler->handle([
            'sources'   => ['views'],
            'group_by'  => ['author'],
            'date_from' => $currentPeriod['from'],
            'date_to'   => $currentPeriod['to'],
            'format'    => 'table',
            'per_page'  => 1,
        ]);
        $topAuthor = '';
        if (!empty($topAuthorResult['data']['rows'][0])) {
            $row = $topAuthorResult['data']['rows'][0];
            $topAuthor = (object) [
                'ID'           => intval($row['author_id'] ?? 0),
                'display_name' => $row['author_name'] ?? '',
                'views'        => intval($row['views'] ?? 0),
                'avatar'       => $row['author_avatar'] ?? '',
            ];
        }

        // Get top category by views
        $topCategoryResult = $this->queryHandler->handle([
            'sources'   => ['views'],
            'group_by'  => ['taxonomy'],
            'filters'   => ['taxonomy_type' => ['is' => 'category']],
            'date_from' => $currentPeriod['from'],
            'date_to'   => $currentPeriod['to'],
            'format'    => 'table',
            'per_page'  => 1,
        ]);
        $topCategory = '';
        if (!empty($topCategoryResult['data']['rows'][0])) {
            $row = $topCategoryResult['data']['rows'][0];
            $topCategory = (object) [
                'term_id'     => intval($row['term_id'] ?? 0),
                'name'        => $row['term_name'] ?? '',
                'total_views' => intval($row['views'] ?? 0),
                'term_link'   => $row['term_link'] ?? '',
            ];
        }

        // Get top content
        $topContentResult = $this->queryHandler->handle([
            'sources'   => ['views'],
            'group_by'  => ['page'],
            'date_from' => $currentPeriod['from'],
            'date_to'   => $currentPeriod['to'],
            'format'    => 'table',
            'per_page'  => 1,
        ]);
        $topContent = $topContentResult['data']['rows'][0] ?? '';

        $data['top_author']   = $topAuthor;
        $data['top_referrer'] = $topReferrer;
        $data['top_category'] = $topCategory;
        $data['top_content']  = $topContent;

        return $data;
    }

    /**
     * Count published posts in a date range.
     *
     * @param array $dateRange Date range with 'from' and 'to' keys.
     * @return int Count of published posts.
     */
    private function countPublishedPosts($dateRange)
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
     * Get source categories chart data.
     *
     * @param array $args Filter arguments.
     * @return array Chart data.
     */
    public function getSourceCategoriesData($args = [])
    {
        return ChartDataProviderFactory::topSourceCategories($args)->getData();
    }

    /**
     * Get traffic chart data.
     *
     * @param array $args Filter arguments.
     * @return array Chart data.
     */
    public function getTrafficChartData($args = [])
    {
        return ChartDataProviderFactory::trafficChart($args)->getData();
    }

    /**
     * Get search engines chart data.
     *
     * @param array $args Filter arguments.
     * @return array Chart data.
     */
    public function getSearchEnginesChartData($args = [])
    {
        return ChartDataProviderFactory::searchEngineChart($args)->getData();
    }

    /**
     * Get browsers chart data.
     *
     * @param array $args Filter arguments.
     * @return array Chart data.
     */
    public function getBrowsersChartData($args = [])
    {
        return ChartDataProviderFactory::browserChart($args)->getData();
    }

    /**
     * Get device chart data.
     *
     * @param array $args Filter arguments.
     * @return array Chart data.
     */
    public function getDeviceChartData($args = [])
    {
        return ChartDataProviderFactory::deviceChart($args)->getData();
    }

    /**
     * Get OS chart data.
     *
     * @param array $args Filter arguments.
     * @return array Chart data.
     */
    public function getOsChartData($args = [])
    {
        return ChartDataProviderFactory::osChart($args)->getData();
    }

    /**
     * Get model chart data.
     *
     * @param array $args Filter arguments.
     * @return array Chart data.
     */
    public function getModelChartData($args = [])
    {
        return ChartDataProviderFactory::modelChart($args)->getData();
    }

    /**
     * Get map chart data.
     *
     * @param array $args Filter arguments.
     * @return array Chart data.
     */
    public function getMapChartData($args = [])
    {
        return ChartDataProviderFactory::mapChart($args)->getData();
    }
}
