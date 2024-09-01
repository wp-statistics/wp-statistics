<?php

namespace WP_Statistics\Service\Admin\Charts\DataProvider;

use WP_STATISTICS\Helper;
use WP_Statistics\Models\PostsModel;
use WP_Statistics\Models\ViewsModel;
use WP_Statistics\Models\VisitorsModel;

class PerformanceChartDataProvider extends AbstractChartDataProvider
{
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
        $result = [
            'labels'    => [],
            'visitors'  => [],
            'views'     => [],
            'posts'     => []
        ];

        $visitorsData   = $this->visitorsModel->countDailyVisitors($this->args);
        $visitorsData   = wp_list_pluck($visitorsData, 'visitors', 'date');

        $viewsData  = $this->viewsModel->countDailyViews($this->args);
        $viewsData  = wp_list_pluck($viewsData, 'views', 'date');

        $postsData  = $this->postsModel->countDailyPosts($this->args);
        $postsData  = wp_list_pluck($postsData, 'posts', 'date');

        for ($i = 14; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));

            $result['labels'][]     = [
                'date'  => date_i18n(Helper::getDefaultDateFormat(false, true, true), strtotime($date)),
                'day'   => date_i18n('l', strtotime($date)),
            ];
            $result['views'][]      = isset($viewsData[$date]) ? intval($viewsData[$date]) : 0;
            $result['visitors'][]   = isset($visitorsData[$date]) ? intval($visitorsData[$date]) : 0;
            $result['posts'][]      = isset($postsData[$date]) ? intval($postsData[$date]) : 0;
        }

        return $result;
    }
}
