<?php
namespace WP_Statistics\Service\Admin\ContentAnalytics;

use WP_STATISTICS\Helper;
use WP_Statistics\Models\PostsModel;
use WP_Statistics\Models\TaxonomyModel;
use WP_Statistics\Models\ViewsModel;
use WP_Statistics\Models\VisitorsModel;
use WP_Statistics\Service\Admin\Posts\WordCountService;
use WP_Statistics\Service\Charts\ChartDataProviderFactory;
use WP_Statistics\Utils\Request;

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
        $performanceDataProvider    = ChartDataProviderFactory::performanceChart($this->args);
        $searchEngineDataProvider   = ChartDataProviderFactory::searchEngineChart(array_merge($this->args, ['source_channel' => ['search', 'paid_search']]));
        $platformDataProvider       = ChartDataProviderFactory::platformCharts($this->args);

        return [
            'post_type'                 => Helper::getPostTypeName(Request::get('tab', 'post')),
            'performance_chart_data'    => $performanceDataProvider->getData(),
            'search_engine_chart_data'  => $searchEngineDataProvider->getData(),
            'os_chart_data'             => $platformDataProvider->getOsData(),
            'browser_chart_data'        => $platformDataProvider->getBrowserData(),
            'device_chart_data'         => $platformDataProvider->getDeviceData(),
            'model_chart_data'          => $platformDataProvider->getModelData(),
        ];
    }

    public function getPostTypeData()
    {
        $totalPosts     = $this->postsModel->countPosts(array_merge($this->args, ['ignore_date' => true]));
        $recentPosts    = $this->postsModel->countPosts($this->args);

        $recentViews    = $this->viewsModel->countViews($this->args);
        $recentVisitors = $this->visitorsModel->countVisitors($this->args);

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

        $result = [
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

        if (WordCountService::isActive()) {
            $totalWords     = $this->postsModel->countWords(array_merge($this->args, ['ignore_date' => true]));
            $recentWords    = $this->postsModel->countWords($this->args);

            $result['overview']['words'] = [
                'total'     => $totalWords,
                'recent'    => $recentWords,
                'avg'       => Helper::divideNumbers($recentWords, $recentPosts),
                'total_avg' => Helper::divideNumbers($totalWords, $totalPosts)
            ];
        }

        return $result;
    }

    public function getSingleResourceData()
    {
        $totalHitsArgs      = array_merge(Helper::filterArrayByKeys($this->args, ['query_param', 'ignore_post_type']), ['ignore_date' => true]);

        $totalViews         = $this->viewsModel->countViews(array_merge($totalHitsArgs, ['uri' => $this->args['query_param']]));
        $totalVisitors      = $this->visitorsModel->countVisitors($totalHitsArgs);

        $recentViews        = $this->viewsModel->countViews($this->args);
        $recentVisitors     = $this->visitorsModel->countVisitors($this->args);

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
                ]
            ]
        ];
    }

    public function getSinglePostData()
    {
        $totalHitsArgs      = array_merge(Helper::filterArrayByKeys($this->args, ['post_id', 'query_param', 'resource_type']), ['ignore_date' => true]);

        $totalViews         = $this->viewsModel->countViews($totalHitsArgs);
        $totalVisitors      = $this->visitorsModel->countVisitors($totalHitsArgs);

        $recentViews        = $this->viewsModel->countViews($this->args);
        $recentVisitors     = $this->visitorsModel->countVisitors($this->args);

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

        $result = [
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
                'comments'  => [
                    'total' => $totalComments,
                ]
            ]
        ];

        if (WordCountService::isActive()) {
            $totalWords = $this->postsModel->countWords($this->args);

            $result['overview']['words'] = [
                'total' => $totalWords,
            ];
        }

        return $result;
    }
}