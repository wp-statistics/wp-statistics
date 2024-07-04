<?php 
namespace WP_Statistics\Service\Admin\ContentAnalytics;

use WP_STATISTICS\Helper;
use WP_Statistics\Models\PostsModel;
use WP_Statistics\Models\ViewsModel;
use WP_Statistics\Models\VisitorsModel;
use WP_STATISTICS\TimeZone;
use WP_Statistics\Utils\Request;

class ContentAnalyticsDataProvider
{
    protected $args;
    private $postsModel;
    private $viewsModel;
    private $visitorsModel;
    
    public function __construct($args)
    {
        $this->args = $args;

        $this->postsModel       = new PostsModel();
        $this->viewsModel       = new ViewsModel();
        $this->visitorsModel    = new VisitorsModel();
    }

    public function getPerformanceChartData()
    {
        $result = [
            'labels'    => [],
            'views'     => [],
            'visitors'  => []
        ];

        for ($i = 14; $i >= 0; $i--) {
            $date       = date('Y-m-d', strtotime("-$i days"));
            $dateFilter = ['date' => ['from' => $date, 'to' => $date]];

            $result['labels'][]     = date_i18n(Helper::getDefaultDateFormat(false, true), strtotime($date));
            $result['visitors'][]   = $this->visitorsModel->countVisitors(array_merge($this->args, $dateFilter));
            $result['views'][]      = $this->viewsModel->countViews(array_merge($this->args, $dateFilter));
            $result['posts'][]      = $this->postsModel->countPosts(array_merge($this->args, $dateFilter));
        }

        return $result;
    }

    public function getChartsData()
    {
        $visitorsData = $this->visitorsModel->getVisitorsPlatformData($this->args);

        return [
            'performance_chart_data'    => $this->getPerformanceChartData(),
            'search_engine_chart_data'  => $this->visitorsModel->getSearchEnginesChartData($this->args),
            'post_type'                 => Helper::getPostTypeName(Request::get('tab', 'post')),
            'os_chart_data'             => [
                'labels'    => array_keys($visitorsData['platform']), 
                'data'      => array_values($visitorsData['platform'])
            ],
            'browser_chart_data'        => [
                'labels'    => array_keys($visitorsData['agent']), 
                'data'      => array_values($visitorsData['agent'])
            ],
            'device_chart_data'         => [
                'labels'    => array_keys($visitorsData['device']), 
                'data'      => array_values($visitorsData['device'])
            ],
            'model_chart_data'          => [
                'labels'    => array_keys($visitorsData['model']), 
                'data'      => array_values($visitorsData['model'])
            ],
        ];
    }

    public function getPostTypeData()
    {
        $totalPosts     = $this->postsModel->countPosts($this->args);
        $totalViews     = $this->viewsModel->countViews($this->args);
        $totalVisitors  = $this->visitorsModel->countVisitors($this->args);
        $totalWords     = $this->postsModel->countWords($this->args);
        $totalComments  = $this->postsModel->countComments($this->args);

        $visitorsCountry    = $this->visitorsModel->getVisitorsGeoData(array_merge($this->args, ['per_page' => 10]));
        
        $visitorsSummary    = $this->visitorsModel->getVisitorsSummary($this->args);
        $viewsSummary       = $this->viewsModel->getViewsSummary($this->args);
        
        $referrersData = $this->visitorsModel->getReferrers($this->args);
        
        $performanceArgs = ['date' => ['from' => date('Y-m-d', strtotime('-14 days')), 'to' => date('Y-m-d')]];
        $performanceData = [
            'posts'     => $this->postsModel->countPosts(array_merge($this->args, $performanceArgs)),
            'visitors'  => $this->visitorsModel->countVisitors(array_merge($this->args, $performanceArgs)),
            'views'     => $this->viewsModel->countViews(array_merge($this->args, $performanceArgs)),
        ];

        $topPostsByView     = $this->postsModel->getPostsViewsData($this->args);
        $topPostsByComment  = $this->postsModel->getPostsCommentsData($this->args);
        $recentPosts        = $this->postsModel->getPostsViewsData(array_merge($this->args, ['date' => '', 'date_field' => 'post_date', 'order_by' => 'post_date']));

        return [
            'visits_summary'    => array_replace_recursive($visitorsSummary, $viewsSummary),
            'overview'          => [
                'published' => [
                    'total' => $totalPosts
                ],
                'views'     => [
                    'total' => $totalViews,
                    'avg'   => Helper::divideNumbers($totalViews, $totalPosts)
                ],
                'visitors'  => [
                    'total' => $totalVisitors,
                    'avg'   => Helper::divideNumbers($totalVisitors, $totalPosts)
                ],
                'words'     => [
                    'total' => $totalWords,
                    'avg'   => Helper::divideNumbers($totalWords, $totalPosts)
                ],
                'comments'  => [
                    'total' => $totalComments,
                    'avg'   => Helper::divideNumbers($totalComments, $totalPosts)
                ]
            ],
            'visitors_country'  => $visitorsCountry,
            'performance'       => $performanceData,
            'referrers'         => $referrersData,
            'posts'             => [
                'top_viewing'   => $topPostsByView,
                'top_commented' => $topPostsByComment,
                'recent'        => $recentPosts
            ]
        ];
    }

    public function getSinglePostData()
    {
        $totalViews         = $this->viewsModel->countViews(Helper::filterArrayByKeys($this->args, ['post_id', 'query_param']));
        $totalVisitors      = $this->visitorsModel->countVisitors(Helper::filterArrayByKeys($this->args, ['post_id', 'query_param']));
        $recentViews        = $this->viewsModel->countViews($this->args);
        $recentVisitors     = $this->visitorsModel->countVisitors($this->args);
        $totalWords         = $this->postsModel->countWords($this->args);
        $totalComments      = $this->postsModel->countComments($this->args);

        $visitorsCountry    = $this->visitorsModel->getVisitorsGeoData(array_merge($this->args, ['per_page' => 10]));
        
        $visitorsSummary    = $this->visitorsModel->getVisitorsSummary($this->args);
        $viewsSummary       = $this->viewsModel->getViewsSummary($this->args);
        
        $referrersData      = $this->visitorsModel->getReferrers($this->args);
        
        $performanceArgs    = ['date' => ['from' => date('Y-m-d', strtotime('-14 days')), 'to' => date('Y-m-d')]];
        $performanceData    = [
            'visitors'  => $this->visitorsModel->countVisitors(array_merge($this->args, $performanceArgs)),
            'views'     => $this->viewsModel->countViews(array_merge($this->args, $performanceArgs)),
        ];
        
        return [
            'visitors_country'  => $visitorsCountry,
            'visits_summary'    => array_replace_recursive($visitorsSummary, $viewsSummary),
            'performance'       => $performanceData,
            'referrers'         => $referrersData,
            'overview'          => [
                'views'     => [
                    'total' => $totalViews,
                    'recent'=> $recentViews,
                ],
                'visitors'  => [
                    'total' => $totalVisitors,
                    'recent'=> $recentVisitors,
                ],
                'words'     => [
                    'total' => $totalWords,
                ],
                'comments'  => [
                    'total' => $totalComments,
                ]
            ]
        ];
    }
}