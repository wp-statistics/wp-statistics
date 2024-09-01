<?php
namespace WP_Statistics\Service\Admin\ContentAnalytics;

use WP_STATISTICS\Helper;
use WP_STATISTICS\TimeZone;
use WP_Statistics\Utils\Request;
use WP_Statistics\Models\PostsModel;
use WP_Statistics\Models\ViewsModel;
use WP_Statistics\Models\TaxonomyModel;
use WP_Statistics\Models\VisitorsModel;
use WP_Statistics\Service\Admin\Charts\ChartDataProviderFactory;

class ContentAnalyticsDataProvider
{
    protected $args;
    private $postsModel;
    private $viewsModel;
    private $visitorsModel;
    private $taxonomyModel;

    public function __construct($args)
    {
        $this->args = $args;

        $this->postsModel       = new PostsModel();
        $this->viewsModel       = new ViewsModel();
        $this->visitorsModel    = new VisitorsModel();
        $this->taxonomyModel    = new TaxonomyModel();
    }

    public function getChartsData()
    {
        $visitorsData = $this->visitorsModel->getVisitorsPlatformData($this->args);

        $performanceChartData = ChartDataProviderFactory::performanceChart($this->args)->getData();

        return [
            'performance_chart_data'    => $performanceChartData,
            'search_engine_chart_data'  => $this->visitorsModel->getSearchEnginesChartData($this->args),
            'post_type'                 => Helper::getPostTypeName(Request::get('tab', 'post')),
            'os_chart_data'             => [
                'labels'    => wp_list_pluck($visitorsData['platform'], 'label'),
                'data'      => wp_list_pluck($visitorsData['platform'], 'visitors'),
                'icons'     => wp_list_pluck($visitorsData['platform'], 'icon'),
            ],
            'browser_chart_data'        => [
                'labels'    => wp_list_pluck($visitorsData['agent'], 'label'),
                'data'      => wp_list_pluck($visitorsData['agent'], 'visitors'),
                'icons'     => wp_list_pluck($visitorsData['agent'], 'icon')
            ],
            'device_chart_data'         => [
                'labels'    => wp_list_pluck($visitorsData['device'], 'label'),
                'data'      => wp_list_pluck($visitorsData['device'], 'visitors')
            ],
            'model_chart_data'          => [
                'labels'    => wp_list_pluck($visitorsData['model'], 'label'),
                'data'      => wp_list_pluck($visitorsData['model'], 'visitors')
            ],
        ];
    }

    public function getPostTypeData()
    {
        $totalPosts     = $this->postsModel->countPosts(array_merge($this->args, ['ignore_date' => true]));
        $recentPosts    = $this->postsModel->countPosts($this->args);

        $recentViews    = $this->viewsModel->countViews($this->args);
        $recentVisitors = $this->visitorsModel->countVisitors($this->args);

        $totalWords     = $this->postsModel->countWords(array_merge($this->args, ['ignore_date' => true]));
        $recentWords    = $this->postsModel->countWords($this->args);

        $totalComments  = $this->postsModel->countComments(array_merge($this->args, ['ignore_date' => true]));
        $recentComments = $this->postsModel->countComments($this->args);

        $visitorsCountry = $this->visitorsModel->getVisitorsGeoData(array_merge($this->args, ['per_page' => 10]));

        $visitorsSummary = $this->visitorsModel->getVisitorsSummary($this->args);
        $viewsSummary    = $this->viewsModel->getViewsSummary($this->args);

        $referrersData   = $this->visitorsModel->getReferrers($this->args);

        $performanceData = [
            'posts'     => $this->postsModel->countPosts($this->args),
            'visitors'  => $this->visitorsModel->countVisitors($this->args),
            'views'     => $this->viewsModel->countViews($this->args),
        ];

        $topPostsByView     = $this->postsModel->getPostsViewsData($this->args);
        $topPostsByComment  = $this->postsModel->getPostsCommentsData($this->args);
        $recentPostsData    = $this->postsModel->getPostsViewsData(array_merge($this->args, ['order_by' => 'post_date', 'show_no_views' => true]));

        $taxonomies         = $this->taxonomyModel->getTaxonomiesData($this->args);

        return [
            'taxonomies'        => $taxonomies,
            'visits_summary'    => array_replace_recursive($visitorsSummary, $viewsSummary),
            'overview'          => [
                'published' => [
                    'total'     => $totalPosts,
                    'recent'    => $recentPosts
                ],
                'views'     => [
                    'recent'    => $recentViews,
                    'avg'       => Helper::divideNumbers($recentViews, $recentPosts)
                ],
                'visitors'  => [
                    'recent'    => $recentVisitors,
                    'avg'       => Helper::divideNumbers($recentVisitors, $recentPosts)
                ],
                'words'     => [
                    'total'     => $totalWords,
                    'recent'    => $recentWords,
                    'avg'       => Helper::divideNumbers($recentWords, $recentPosts),
                    'total_avg' => Helper::divideNumbers($totalWords, $totalPosts)
                ],
                'comments'  => [
                    'total'     => $totalComments,
                    'recent'    => $recentComments,
                    'avg'       => Helper::divideNumbers($recentComments, $recentPosts),
                    'total_avg' => Helper::divideNumbers($totalComments, $totalPosts)
                ]
            ],
            'visitors_country'  => $visitorsCountry,
            'performance'       => $performanceData,
            'referrers'         => $referrersData,
            'posts'             => [
                'top_viewing'   => $topPostsByView,
                'top_commented' => $topPostsByComment,
                'recent'        => $recentPostsData
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