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

    public $args;
    protected $visitorsModel;
    protected $viewsModel;
    protected $postsModel;

    public function __construct($args)
    {
        $this->args = $args;

        $this->visitorsModel    = new VisitorsModel();
        $this->viewsModel       = new ViewsModel();
        $this->postsModel       = new PostsModel();
    }

    public function getData()
    {
        // Get data from database
        $visitors   = $this->visitorsModel->countDailyVisitors($this->args);
        $views      = $this->viewsModel->countDailyViews($this->args);

        // On single post view, no need to count posts
        $posts = empty($this->args['post_id']) ? $this->postsModel->countDailyPosts($this->args) : [];

        // Parse data
        $parsedData = $this->parseData([
            'visitors'  => $visitors,
            'views'     => $views,
            'posts'     => $posts
        ]);

        // Prepare data
        $result = $this->prepareResult($parsedData);

        return $result;
    }

    protected function parseData($data)
    {
        $datePeriod = isset($this->args['date']) ? $this->args['date'] : DateRange::get();
        $dates      = array_keys(TimeZone::getListDays($datePeriod));

        $visitors   = wp_list_pluck($data['visitors'], 'visitors', 'date');
        $views      = wp_list_pluck($data['views'], 'views', 'date');
        $posts      = wp_list_pluck($data['posts'], 'posts', 'date');

        $parsedData = [];
        foreach ($dates as $date) {
            $parsedData['labels'][]     = [
                'formatted_date'    => date_i18n(Helper::getDefaultDateFormat(false, true, true), strtotime($date)),
                'date'              => date_i18n('Y-m-d', strtotime($date)),
                'day'               => date_i18n('l', strtotime($date))
            ];
            $parsedData['visitors'][]   = isset($visitors[$date]) ? intval($visitors[$date]) : 0;
            $parsedData['views'][]      = isset($views[$date]) ? intval($views[$date]) : 0;
            $parsedData['posts'][]      = isset($posts[$date]) ? intval($posts[$date]) : 0;
        }

        return $parsedData;
    }

    protected function prepareResult($data)
    {
        $this->initChartData();

        $this->setChartLabels($data['labels']);

        $this->addChartDataset(
            esc_html__('Visitors', 'wp-statistics'),
            $data['visitors']
        );

        $this->addChartDataset(
            esc_html__('Views', 'wp-statistics'),
            $data['views']
        );

        // On single post view, no need to count posts
        if (empty($this->args['post_id'])) {
            $this->addChartDataset(
                sprintf(
                    esc_html__('Published %s', 'wp-statistics'),
                    isset($this->args['post_type']) ? Helper::getPostTypeName($this->args['post_type']) : esc_html__('Contents', 'wp-statistics')
                ),
                $data['posts']
            );
        }

        return $this->getChartData();
    }
}
