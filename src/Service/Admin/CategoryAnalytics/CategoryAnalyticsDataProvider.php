<?php 

namespace WP_Statistics\Service\Admin\CategoryAnalytics;

use WP_STATISTICS\Helper;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Models\AuthorsModel;
use WP_Statistics\Models\PostsModel;
use WP_Statistics\Utils\Request;
use WP_Statistics\Models\TaxonomyModel;
use WP_Statistics\Models\ViewsModel;
use WP_Statistics\Models\VisitorsModel;

class CategoryAnalyticsDataProvider
{
    protected $args;
    protected $taxonomyModel;
    protected $postsModel;
    protected $visitorsModel;
    protected $viewsModel;
    protected $authorModel;
    
    public function __construct($args)
    {
        $this->args = $args;

        $this->taxonomyModel = new TaxonomyModel();
        $this->visitorsModel = new VisitorsModel();
        $this->viewsModel    = new ViewsModel();
        $this->postsModel    = new PostsModel();
        $this->authorModel   = new AuthorsModel();
    }

    public function getChartsData()
    {
        $visitorsData = $this->visitorsModel->getVisitorsPlatformData($this->args);

        return [
            'performance_chart_data'    => $this->getPerformanceChartData(),
            'search_engine_chart_data'  => $this->visitorsModel->getSearchEnginesChartData($this->args),
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

    public function getPerformanceChartData()
    {
        $result = [
            'labels'    => [],
            'visitors'  => [],
            'views'     => [],
            'posts'     => []
        ];

        $args = array_merge($this->args, ['date' => ['from' => date('Y-m-d', strtotime('-14 days')), 'to' => date('Y-m-d')]]);

        $visitorsData   = $this->visitorsModel->countDailyVisitors($args);
        $visitorsData   = wp_list_pluck($visitorsData, 'visitors', 'date');
        
        $viewsData  = $this->viewsModel->countDailyViews($args);
        $viewsData  = wp_list_pluck($viewsData, 'views', 'date');

        $postsData  = $this->postsModel->countDailyPosts($args);
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

    public function getSingleTermData()
    {
        $totalPosts         = $this->postsModel->countPosts(array_merge($this->args, ['ignore_date' => true]));
        $recentPosts        = $this->postsModel->countPosts($this->args);
        
        $recentViews         = $this->viewsModel->countViews($this->args);
        $recentVisitors      = $this->visitorsModel->countVisitors($this->args);

        $totalWords         = $this->postsModel->countWords(array_merge($this->args, ['ignore_date' => true]));
        $recentWords        = $this->postsModel->countWords($this->args);

        $totalComments      = $this->postsModel->countComments(array_merge($this->args, ['ignore_date' => true]));
        $recentComments     = $this->postsModel->countComments($this->args);

        $visitorsSummary    = $this->visitorsModel->getVisitorsSummary($this->args);
        $viewsSummary       = $this->viewsModel->getViewsSummary($this->args);

        $visitorsCountry    = $this->visitorsModel->getVisitorsGeoData(array_merge($this->args, ['per_page' => 10]));
        $referrersData      = $this->visitorsModel->getReferrers($this->args);
        
        $performanceArgs    = ['date' => ['from' => date('Y-m-d', strtotime('-14 days')), 'to' => date('Y-m-d')]];
        $performanceData    = [
            'posts'     => $this->postsModel->countPosts(array_merge($this->args, $performanceArgs)),
            'visitors'  => $this->visitorsModel->countVisitors(array_merge($this->args, $performanceArgs)),
            'views'     => $this->viewsModel->countViews(array_merge($this->args, $performanceArgs)),
        ];

        $topViewingPosts    = $this->postsModel->getPostsViewsData($this->args);
        $recentPostsData    = $this->postsModel->getPostsViewsData(array_merge($this->args, ['order_by' => 'post_date', 'show_no_views' => true]));
        $topCommentedPosts  = $this->postsModel->getPostsCommentsData($this->args);

        return [
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
                    'recent'    => $recentWords,
                    'avg'       => Helper::divideNumbers($recentWords, $recentPosts),
                    'total'     => $totalWords,
                    'total_avg' => Helper::divideNumbers($totalWords, $totalPosts)
                ],
                'comments'  => [
                    'recent'    => $recentComments,
                    'avg'       => Helper::divideNumbers($recentComments, $recentPosts),
                    'total'     => $totalComments,
                    'total_avg' => Helper::divideNumbers($totalComments, $totalPosts)
                ]
            ],
            'posts'             => [
                'top_viewing'   => $topViewingPosts,
                'recent'        => $recentPostsData,
                'top_commented' => $topCommentedPosts
            ],
            'performance'       => $performanceData,
            'referrers'         => $referrersData,
            'visitors_country'  => $visitorsCountry,
            'visits_summary'    => array_replace_recursive($visitorsSummary, $viewsSummary)
        ];
    }

    public function getPerformanceData()
    {
        $totalPosts         = $this->postsModel->countPosts(array_merge($this->args, ['ignore_date' => true]));
        $recentPosts        = $this->postsModel->countPosts($this->args);

        $recentViews        = $this->viewsModel->countViews($this->args);
        $recentVisitors     = $this->visitorsModel->countVisitors($this->args);

        $recentWords        = $this->postsModel->countWords($this->args);
        $totalWords         = $this->postsModel->countWords(array_merge($this->args, ['ignore_date' => true]));

        $recentComments     = $this->postsModel->countComments($this->args);
        $totalComments      = $this->postsModel->countComments(array_merge($this->args, ['ignore_date' => true]));

        $visitorsSummary    = $this->visitorsModel->getVisitorsSummary($this->args);
        $viewsSummary       = $this->viewsModel->getViewsSummary($this->args);

        $topPublishingAuthors = $this->authorModel->getAuthorsByPostPublishes($this->args);
        $topViewingAuthors    = $this->authorModel->getTopViewingAuthors($this->args);

        $visitorsCountry    = $this->visitorsModel->getVisitorsGeoData(array_merge($this->args, ['per_page' => 10]));
        $referrersData      = $this->visitorsModel->getReferrers($this->args);
        
        $performanceArgs = ['date' => ['from' => date('Y-m-d', strtotime('-14 days')), 'to' => date('Y-m-d')]];
        $performanceData = [
            'posts'     => $this->postsModel->countPosts(array_merge($this->args, $performanceArgs)),
            'visitors'  => $this->visitorsModel->countVisitors(array_merge($this->args, $performanceArgs)),
            'views'     => $this->viewsModel->countViews(array_merge($this->args, $performanceArgs)),
        ];

        $topPostsByView     = $this->postsModel->getPostsViewsData($this->args);
        $topPostsByComment  = $this->postsModel->getPostsCommentsData($this->args);
        $recentPostsData    = $this->postsModel->getPostsViewsData(array_merge($this->args, ['order_by' => 'post_date', 'show_no_views' => true]));

        $topViewingCategories    = $this->taxonomyModel->getTermsData($this->args);
        $topPublishingCategories = $this->taxonomyModel->getTermsData(array_merge($this->args, ['order_by' => 'posts', 'date_field' => 'posts.post_date']));

        return [
            'visitors_country'  => $visitorsCountry,
            'referrers'         => $referrersData,
            'authors'           => [
                'publishing' => $topPublishingAuthors,
                'viewing'    => $topViewingAuthors
            ],
            'categories'        => [
                'publishing' => $topPublishingCategories,
                'viewing'    => $topViewingCategories
            ],
            'overview'          => [
                'published' => [
                    'total' => $totalPosts,
                    'recent'=> $recentPosts
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
                    'recent'    => $recentWords,
                    'avg'       => Helper::divideNumbers($recentWords, $recentPosts),
                    'total'     => $totalWords,
                    'total_avg' => Helper::divideNumbers($totalWords, $totalPosts)
                ],
                'comments'  => [
                    'recent'    => $recentComments,
                    'avg'       => Helper::divideNumbers($recentComments, $recentPosts),
                    'total'     => $totalComments,
                    'total_avg' => Helper::divideNumbers($totalComments, $totalPosts)
                ]
            ],
            'performance'       => $performanceData,
            'visits_summary'    => array_replace_recursive($visitorsSummary, $viewsSummary),
            'posts'             => [
                'top_viewing'   => $topPostsByView,
                'top_commented' => $topPostsByComment,
                'recent'        => $recentPostsData
            ]
        ];
    }

    public function getCategoryReportData()
    {
        return [
            'terms' => $this->taxonomyModel->getTermsReportData($this->args)
        ];
    }
}