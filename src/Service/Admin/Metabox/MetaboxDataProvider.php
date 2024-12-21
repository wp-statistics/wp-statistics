<?php
namespace WP_Statistics\Service\Admin\Metabox;

use WP_STATISTICS\Helper;
use WP_Statistics\Utils\Request;
use WP_Statistics\Models\PostsModel;
use WP_Statistics\Models\ViewsModel;
use WP_Statistics\Models\OnlineModel;
use WP_Statistics\Components\DateRange;
use WP_Statistics\Models\AuthorsModel;
use WP_Statistics\Models\TaxonomyModel;
use WP_Statistics\Models\VisitorsModel;
use WP_Statistics\Service\Admin\Posts\PostsManager;
use WP_Statistics\Service\Charts\ChartDataProviderFactory;

class MetaboxDataProvider
{
    protected $taxonomyModel;
    protected $authorsModel;
    protected $visitorsModel;
    protected $viewsModel;
    protected $onlineModel;
    protected $postsModel;

    public function __construct()
    {
        $this->visitorsModel    = new VisitorsModel();
        $this->authorsModel     = new AuthorsModel();
        $this->viewsModel       = new ViewsModel();
        $this->onlineModel      = new OnlineModel();
        $this->postsModel       = new PostsModel();
        $this->taxonomyModel    = new TaxonomyModel();
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
        return [
            'visitors'  => $this->onlineModel->getOnlineVisitorsData(array_merge($args, ['per_page' => 10])),
            'total'     => $this->onlineModel->countOnlines($args)
        ];
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

    public function getWeeklyPerformanceData($args = [])
    {
        $thisWeek = DateRange::get('this_week');
        $lastWeek = DateRange::get('last_week');

        $data = [
            'visitors'  => [
                'this_week' => $this->visitorsModel->countVisitors(['date' => $thisWeek]),
                'last_week' => $this->visitorsModel->countVisitors(['date' => $lastWeek])
            ],
            'visits'    => [
                'this_week' => $this->viewsModel->countViews(['date' => $thisWeek]),
                'last_week' => $this->viewsModel->countViews(['date' => $lastWeek])
            ],
            'posts'     => [
                'this_week' => $this->postsModel->countPosts(['date' => $thisWeek]),
                'last_week' => $this->postsModel->countPosts(['date' => $lastWeek])
            ],
            'referrals' => [
                'this_week' => $this->visitorsModel->countReferrers(['date' => $thisWeek]),
                'last_week' => $this->visitorsModel->countReferrers(['date' => $lastWeek])
            ]
        ];

        foreach ($data as $key => $value) {
            $data[$key]['diff_percentage'] = Helper::calculatePercentageChange($value['last_week'], $value['this_week']);
            if ($data[$key]['diff_percentage'] > 0) {
                $data[$key]['diff_type'] = 'plus';
            } elseif ($data[$key]['diff_percentage'] < 0) {
                $data[$key]['diff_type'] = 'minus';
            } else {
                $data[$key]['diff_type'] = 'equal';
            }

            $data[$key]['diff_percentage'] = abs($data[$key]['diff_percentage']);
        }

        $data['onlines']      = $this->onlineModel->countOnlines();

        $topReferrer          = $this->visitorsModel->getReferrers(['per_page' => 1, 'decorate' => true, 'date' => $thisWeek]);
        $data['top_referrer'] = $topReferrer[0] ?? '';

        $topAuthor            = $this->authorsModel->getTopViewingAuthors(['date' => $thisWeek, 'per_page' => 1]);
        $data['top_author']   = $topAuthor[0] ?? '';

        $topCategory          = $this->taxonomyModel->getTermsData(['date' => $thisWeek, 'per_page' => 5, 'taxonomy' => Helper::get_list_taxonomy()]);
        $data['top_category'] = $topCategory[0] ?? '';

        $topContent           = $this->postsModel->getPostsViewsData(['date' => $thisWeek, 'per_page' => 1]);
        $data['top_content']  = $topContent[0] ?? '';

        return $data;
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