<?php

namespace WP_Statistics\Service\Charts\DataProvider;

use WP_Statistics\Components\DateRange;
use WP_STATISTICS\Helper;
use WP_Statistics\Models\PostsModel;
use WP_Statistics\Service\AnalyticsQuery\AnalyticsQueryHandler;
use WP_Statistics\Service\Charts\AbstractChartDataProvider;
use WP_Statistics\Service\Charts\Traits\LineChartResponseTrait;
use WP_STATISTICS\TimeZone;

class PerformanceChartDataProvider extends AbstractChartDataProvider
{
    use LineChartResponseTrait;

    /**
     * @var AnalyticsQueryHandler
     */
    protected $queryHandler;

    /**
     * @var PostsModel
     */
    protected $postsModel;

    public function __construct($args)
    {
        parent::__construct($args);

        $this->queryHandler = new AnalyticsQueryHandler();
        $this->postsModel   = new PostsModel();
    }

    public function getData()
    {
        // Init chart data
        $this->initChartData($this->isPreviousDataEnabled());

        $this->setThisPeriodData();

        // Get previous data only if previous chart data option is enabled
        if ($this->isPreviousDataEnabled()) {
            $this->setPrevPeriodData();
        }

        return $this->getChartData();
    }

    protected function setThisPeriodData()
    {
        $currentPeriod = isset($this->args['date']) ? $this->args['date'] : DateRange::get();
        $currentDates  = array_keys(TimeZone::getListDays($currentPeriod));

        // Query visitors and views using AnalyticsQueryHandler
        $result = $this->queryHandler->handle([
            'sources'   => ['visitors', 'views'],
            'group_by'  => ['date'],
            'date_from' => $currentPeriod['from'] ?? null,
            'date_to'   => $currentPeriod['to'] ?? null,
            'format'    => 'table',
            'per_page'  => 1000,
        ]);

        $analyticsData = $result['data']['rows'] ?? [];

        // Get posts data separately (not part of analytics query system)
        $posts = empty($this->args['post_id']) && empty($this->args['hide_post'])
            ? $this->postsModel->countDailyPosts($this->args)
            : [];

        $parsedData = $this->parseData($currentDates, $analyticsData, $posts);

        $this->setChartLabels($parsedData['labels']);

        $this->addChartDataset(
            esc_html__('Visitors', 'wp-statistics'),
            $parsedData['visitors'],
            'visitors'
        );

        $this->addChartDataset(
            esc_html__('Views', 'wp-statistics'),
            $parsedData['views'],
            'views'
        );

        // On single post view and single resource, no need to count posts
        if (empty($this->args['post_id']) && empty($this->args['hide_post'])) {
            $this->addChartDataset(
                sprintf(
                    esc_html__('Published %s', 'wp-statistics'),
                    isset($this->args['post_type']) ? Helper::getPostTypeName($this->args['post_type']) : esc_html__('Contents', 'wp-statistics')
                ),
                $parsedData['posts'],
                'published'
            );
        }
    }

    protected function setPrevPeriodData()
    {
        $currentPeriod = isset($this->args['date']) ? $this->args['date'] : DateRange::get();
        $prevPeriod    = DateRange::getPrevPeriod($currentPeriod);
        $prevDates     = array_keys(TimeZone::getListDays($prevPeriod));

        // Query visitors and views for previous period using AnalyticsQueryHandler
        $result = $this->queryHandler->handle([
            'sources'   => ['visitors', 'views'],
            'group_by'  => ['date'],
            'date_from' => $prevPeriod['from'] ?? null,
            'date_to'   => $prevPeriod['to'] ?? null,
            'format'    => 'table',
            'per_page'  => 1000,
        ]);

        $analyticsData = $result['data']['rows'] ?? [];

        $parsedData = $this->parseData($prevDates, $analyticsData, []);

        $this->setChartPreviousLabels($parsedData['labels']);

        $this->addChartPreviousDataset(
            esc_html__('Visitors', 'wp-statistics'),
            $parsedData['visitors'],
            'visitors'
        );

        $this->addChartPreviousDataset(
            esc_html__('Views', 'wp-statistics'),
            $parsedData['views'],
            'views'
        );
    }

    /**
     * Parse analytics data and posts data into chart format.
     *
     * @param array $dates         Array of dates to include.
     * @param array $analyticsData Analytics data from AnalyticsQueryHandler (rows with date, visitors, views).
     * @param array $postsData     Posts data from PostsModel.
     * @return array Parsed data with labels, visitors, views, and posts arrays.
     */
    protected function parseData($dates, $analyticsData, $postsData)
    {
        // Index analytics data by date for quick lookup
        $visitorsMap = [];
        $viewsMap    = [];
        foreach ($analyticsData as $row) {
            $date = $row['date'] ?? '';
            if (!empty($date)) {
                $visitorsMap[$date] = intval($row['visitors'] ?? 0);
                $viewsMap[$date]    = intval($row['views'] ?? 0);
            }
        }

        // Index posts data by date
        $postsMap = wp_list_pluck($postsData, 'posts', 'date');

        $parsedData = [];
        foreach ($dates as $date) {
            $parsedData['labels'][] = [
                'formatted_date' => date_i18n(Helper::getDefaultDateFormat(false, true, true), strtotime($date)),
                'date'           => date_i18n('Y-m-d', strtotime($date)),
                'day'            => date_i18n('D', strtotime($date))
            ];
            $parsedData['visitors'][] = isset($visitorsMap[$date]) ? $visitorsMap[$date] : 0;
            $parsedData['views'][]    = isset($viewsMap[$date]) ? $viewsMap[$date] : 0;
            $parsedData['posts'][]    = isset($postsMap[$date]) ? intval($postsMap[$date]) : 0;
        }

        return $parsedData;
    }
}