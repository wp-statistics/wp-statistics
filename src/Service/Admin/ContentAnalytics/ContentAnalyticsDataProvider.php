<?php
namespace WP_Statistics\Service\Admin\ContentAnalytics;

use WP_Statistics\Components\DateRange;
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
        $posts     = $this->postsModel->countPosts($this->args);
        $prevPosts = $this->postsModel->countPosts(array_merge($this->args, ['date' => DateRange::getPrevPeriod()]));

        $visitors     = $this->visitorsModel->countVisitors($this->args);
        $prevVisitors = $this->visitorsModel->countVisitors(array_merge($this->args, ['date' => DateRange::getPrevPeriod()]));

        $views     = $this->viewsModel->countViews($this->args);
        $prevViews = $this->viewsModel->countViews(array_merge($this->args, ['date' => DateRange::getPrevPeriod()]));

        $comments        = $this->postsModel->countComments($this->args);
        $prevComments    = $this->postsModel->countComments(array_merge($this->args, ['date' => DateRange::getPrevPeriod()]));
        $avgComments     = Helper::divideNumbers($comments, $posts);
        $prevAvgComments = Helper::divideNumbers($prevComments, $prevPosts);

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
            'glance' => [
                'posts' => [
                    'value'  => $posts,
                    'change' => Helper::calculatePercentageChange($prevPosts, $posts)
                ],
                'views' => [
                    'value'  => $views,
                    'change' => Helper::calculatePercentageChange($prevViews, $views)
                ],
                'visitors' => [
                    'value'  => $visitors,
                    'change' => Helper::calculatePercentageChange($prevVisitors, $visitors)
                ],
                'comments' => [
                    'value'  => $comments,
                    'change' => Helper::calculatePercentageChange($prevComments, $comments),
                ],
                'comments_avg' => [
                    'value'  => Helper::divideNumbers($comments, $posts),
                    'change' => Helper::calculatePercentageChange($prevAvgComments, $avgComments)
                ]
            ],
            'taxonomies'        => $taxonomies,
            'visits_summary'    => array_replace_recursive($visitorsSummary, $viewsSummary),
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
            $words    = $this->postsModel->countWords($this->args);
            $avgWords = Helper::divideNumbers($words, $posts);

            $result['glance']['words'] = [
                'value' => $words
            ];

            $result['glance']['words_avg'] = [
                'value' => $avgWords
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