<?php
namespace WP_Statistics\Service\Admin\Metabox;

use WP_Statistics\Utils\Request;
use WP_Statistics\Models\PostsModel;
use WP_Statistics\Models\ViewsModel;
use WP_Statistics\Models\OnlineModel;
use WP_Statistics\Components\DateRange;
use WP_Statistics\Models\VisitorsModel;
use WP_Statistics\Service\Admin\Posts\PostsManager;
use WP_Statistics\Service\Charts\ChartDataProviderFactory;

class MetaboxDataProvider
{
    protected $visitorsModel;
    protected $viewsModel;
    protected $onlineModel;
    protected $postsModel;

    public function __construct()
    {
        $this->visitorsModel    = new VisitorsModel();
        $this->viewsModel       = new ViewsModel();
        $this->onlineModel      = new OnlineModel();
        $this->postsModel       = new PostsModel();
    }

    public function getTrafficSummaryData($args = [])
    {
        $visitors   = $this->visitorsModel->getVisitorsSummary($args);
        $views      = $this->viewsModel->getViewsSummary($args);

        $data = [
            'online'    => $this->onlineModel->countOnlines($args),
            'visitors'  => array_values(wp_list_pluck($visitors, 'visitors')),
            'views'     => array_values(wp_list_pluck($views, 'views')),
            'labels'    => array_values(wp_list_pluck($views, 'label')),
            'keys'      => array_keys($views),
        ];

        return $data;
    }

    public function getReferrersData($args = [])
    {
        $args = array_merge(
            $args,
            [
                'decorate'  => true,
                'per_page'  => 10,
                'page'      => 1
            ]
        );

        return $this->visitorsModel->getReferrers($args);
    }

    public function getTopVisitorsData($args = [])
    {
        $visitors = $this->visitorsModel->getVisitorsData(array_merge($args, [
            'page_info' => true,
            'user_info' => true,
            'order_by'  => 'hits',
            'order'     => 'DESC',
            'per_page'  => 10,
            'page'      => 1
        ]));

        return $visitors;
    }

    public function getLatestVisitorsData($args = [])
    {
        return $this->visitorsModel->getVisitorsData(array_merge($args, [
            'page_info' => true,
            'user_info' => true,
            'order_by'  => 'visitor.ID',
            'order'     => 'DESC',
            'page'      => 1,
            'per_page'  => 10
        ]));
    }

    public function getTopPages($args = [])
    {
        return $this->viewsModel->getResourcesViews();
    }

    public function getOnlineVisitorsData($args = [])
    {
        return $this->onlineModel->getOnlineVisitorsData($args);
    }

    public function getTopCountiesData($args = [])
    {
        return $this->visitorsModel->getVisitorsGeoData(array_merge($args, ['per_page' => 10, 'not_null' => 'location']));
    }

    public function getPostSummaryData($args = [])
    {
        $postId = Request::get('post', '', 'number');
        return PostsManager::getPostStatisticsSummary($postId);
    }

    public function getSinglePostData($args = [])
    {
        $currentPage = Request::get('current_page', [], 'array');

        $args = [
            'post_id'       => $currentPage['ID'] ?? 0,
            'date_field'    => 'pages.date',
            'date'          => DateRange::get('14days'),
            'page_info'     => true,
            'user_info'     => true,
            'page'          => 1,
            'per_page'      => 15,
        ];

        return $this->visitorsModel->getVisitorsData($args);
    }

    public function getTrafficChartData($args = [])
    {
        return ChartDataProviderFactory::trafficChart($args)->getData();
    }

    public function getSearchEnginesChartData($args = [])
    {
        return ChartDataProviderFactory::searchEngineChart($args)->getData();
    }

    public function getBrowsersChartData($args = [])
    {
        return ChartDataProviderFactory::browserChart($args)->getData();
    }

    public function getDeviceChartData($args = [])
    {
        return ChartDataProviderFactory::deviceChart($args)->getData();
    }

    public function getOsChartData($args = [])
    {
        return ChartDataProviderFactory::osChart($args)->getData();
    }

    public function getModelChartData($args = [])
    {
        return ChartDataProviderFactory::modelChart($args)->getData();
    }

    public function getMapChartData($args = [])
    {
        return ChartDataProviderFactory::mapChart($args)->getData();
    }
}