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
        $views      = $this->visitorsModel->getHitsSummary($args);

        $data = [
            'online'    => $this->onlineModel->countOnlines($args),
            'visitors'  => array_values(wp_list_pluck($visitors, 'visitors')),
            'hits'      => array_values(wp_list_pluck($views, 'hits')),
            'labels'    => array_values(wp_list_pluck($views, 'label')),
            'keys'      => array_keys($views),
        ];

        return $data;
    }

    public function getTrafficOverviewData($args = [])
    {
        $data = [
            'online'    => $this->onlineModel->countOnlines($args),
            'visitors'  => $this->visitorsModel->getVisitorsSummary($args),
            'hits'      => $this->visitorsModel->getHitsSummary($args)
        ];

        return $data;
    }

    public function getReferrersData($args = [])
    {
        $args = array_merge($args, [
            'decorate'  => true,
            'per_page'  => 5,
            'page'      => 1
        ]);

        return $this->visitorsModel->getReferrers($args);
    }

    public function getTopVisitorsData($args = [])
    {
        $visitors = $this->visitorsModel->getVisitorsData(array_merge($args, [
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
            'user_info' => true,
            'order_by'  => 'visitor.ID',
            'order'     => 'DESC',
            'page'      => 1,
            'per_page'  => 10
        ]));
    }

    public function getTopPages($args = [])
    {
        return $this->viewsModel->getResourcesViews($args);
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
        return $this->visitorsModel->getVisitorsGeoData(array_merge($args, ['per_page' => 5, 'not_null' => 'location']));
    }

    public function getPostSummaryData($args = [])
    {
        $postId = Request::get('post', '', 'number');

        if (empty($postId) && Request::has('current_page')) {
            $postId = Request::get('current_page', [], 'array')['ID'] ?? 0;
        }

        return PostsManager::getPostStatisticsSummary($postId);
    }

    public function getSinglePostData($args = [])
    {
        $currentPage = Request::get('current_page', [], 'array');
        $postId = $currentPage['ID'] ?? 0;

        $args = [
            'resource_id'   => $postId,
            'resource_type' => get_post_type($postId) ?? '',
            'ignore_date'   => true,
            'user_info'     => true,
            'page'          => 1,
            'per_page'      => 15,
        ];

        return $this->visitorsModel->getVisitorsData($args);
    }

    public function getWeeklyPerformanceData($args = [])
    {
        $currentPeriod  = DateRange::get('7days', true);
        $prevPeriod     = DateRange::getPrevPeriod('7days', true);

        $data = [
            'visitors'  => [
                'current_period' => $this->visitorsModel->countVisitors(['date' => $currentPeriod]),
                'prev_period'    => $this->visitorsModel->countVisitors(['date' => $prevPeriod])
            ],
            'visits'    => [
                'current_period' => $this->visitorsModel->countHits(['date' => $currentPeriod]),
                'prev_period'    => $this->visitorsModel->countHits(['date' => $prevPeriod])
            ],
            'posts'     => [
                'current_period' => $this->postsModel->countPosts(['date' => $currentPeriod]),
                'prev_period'    => $this->postsModel->countPosts(['date' => $prevPeriod])
            ],
            'referrals' => [
                'current_period' => $this->visitorsModel->countReferrers(['date' => $currentPeriod]),
                'prev_period'    => $this->visitorsModel->countReferrers(['date' => $prevPeriod])
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

        $topReferrer    = $this->visitorsModel->getReferrers(['per_page' => 1, 'decorate' => true, 'date' => $currentPeriod]);
        $topAuthor      = $this->authorsModel->getTopViewingAuthors(['date' => $currentPeriod, 'per_page' => 1]);
        $topCategory    = $this->taxonomyModel->getTermsData(['date' => $currentPeriod, 'per_page' => 5, 'taxonomy' => Helper::get_list_taxonomy()]);
        $topContent     = $this->postsModel->getPostsViewsData(['date' => $currentPeriod, 'per_page' => 1]);

        $data['top_author']   = $topAuthor[0] ?? '';
        $data['top_referrer'] = $topReferrer[0] ?? '';
        $data['top_category'] = $topCategory[0] ?? '';
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