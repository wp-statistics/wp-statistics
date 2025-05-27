<?php

namespace WP_Statistics\Service\Charts\DataProvider;

use WP_Statistics\Components\DateRange;
use WP_STATISTICS\Helper;
use WP_Statistics\Models\PostsModel;
use WP_Statistics\Models\ViewsModel;
use WP_Statistics\Models\VisitorsModel;
use WP_Statistics\Service\Charts\AbstractChartDataProvider;
use WP_Statistics\Service\Charts\Traits\LineChartResponseTrait;
use WP_STATISTICS\TimeZone;

class PerformanceChartDataProvider extends AbstractChartDataProvider
{
    use LineChartResponseTrait;

    protected $visitorsModel;
    protected $viewsModel;
    protected $postsModel;

    public function __construct($args)
    {
        parent::__construct($args);

        $this->visitorsModel    = new VisitorsModel();
        $this->viewsModel       = new ViewsModel();
        $this->postsModel       = new PostsModel();
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
        $currentPeriod  = isset($this->args['date']) ? $this->args['date'] : DateRange::get();
        $currentDates   = array_keys(TimeZone::getListDays($currentPeriod));

        $visitors   = $this->visitorsModel->countDailyVisitors($this->args);
        $views      = $this->viewsModel->countDailyViews($this->args);
        $posts      = empty($this->args['post_id']) && empty($this->args['hide_post']) ? $this->postsModel->countDailyPosts($this->args) : []; // On single post view, no need to count posts

        $parsedData = $this->parseData($currentDates, [
            'visitors'  => $visitors,
            'views'     => $views,
            'posts'     => $posts
        ]);


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
        $currentPeriod  = isset($this->args['date']) ? $this->args['date'] : DateRange::get();
        $prevPeriod     = DateRange::getPrevPeriod($currentPeriod);
        $pervDates      = array_keys(TimeZone::getListDays($prevPeriod));

        $visitors   = $this->visitorsModel->countDailyVisitors(array_merge($this->args, ['date' => $prevPeriod]));
        $views      = $this->viewsModel->countDailyViews(array_merge($this->args, ['date' => $prevPeriod]));

        $parsedData = $this->parseData($pervDates, [
            'visitors'  => $visitors,
            'views'     => $views,
            'posts'     => []
        ]);

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

    protected function parseData($dates, $data)
    {
        $visitors   = wp_list_pluck($data['visitors'], 'visitors', 'date');
        $views      = wp_list_pluck($data['views'], 'views', 'date');
        $posts      = wp_list_pluck($data['posts'], 'posts', 'date');

        $parsedData = [];
        foreach ($dates as $date) {
            $parsedData['labels'][]     = [
                'formatted_date'    => date_i18n(Helper::getDefaultDateFormat(false, true, true), strtotime($date)),
                'date'              => date_i18n('Y-m-d', strtotime($date)),
                'day'               => date_i18n('D', strtotime($date))
            ];
            $parsedData['visitors'][]   = isset($visitors[$date]) ? intval($visitors[$date]) : 0;
            $parsedData['views'][]      = isset($views[$date]) ? intval($views[$date]) : 0;
            $parsedData['posts'][]      = isset($posts[$date]) ? intval($posts[$date]) : 0;
        }

        return $parsedData;
    }
}