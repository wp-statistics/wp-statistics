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

    public function getPerformanceChartData()
    {
        $result = [
            'labels'    => [],
            'views'     => [],
            'visitors'  => [],
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

            $result['labels'][]     = date_i18n(Helper::getDefaultDateFormat(false, true), strtotime($date));
            $result['views'][]      = isset($viewsData[$date]) ? intval($viewsData[$date]) : 0;
            $result['visitors'][]   = isset($visitorsData[$date]) ? intval($visitorsData[$date]) : 0;
            $result['posts'][]      = isset($postsData[$date]) ? intval($postsData[$date]) : 0;
        }

        return $result;
    }

    public function getSingleTermData()
    {
        $totalPosts         = $this->postsModel->countPosts(array_merge($this->args, ['date' => '']));
        $recentPosts        = $this->postsModel->countPosts($this->args);
        $totalViews         = $this->viewsModel->countViews($this->args);
        $totalVisitors      = $this->visitorsModel->countVisitors($this->args);
        $totalWords         = $this->postsModel->countWords($this->args);
        $totalComments      = $this->postsModel->countComments($this->args);

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
        $recentPosts        = $this->postsModel->getPostsViewsData(array_merge($this->args, ['order_by' => 'post_date']));
        $topCommentedPosts  = $this->postsModel->getPostsCommentsData($this->args);

        return [
            'overview'          => [
                'published' => [
                    'total' => $totalPosts,
                    'recent'=> $recentPosts
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
            'posts'             => [
                'top_viewing'   => $topViewingPosts,
                'recent'        => $recentPosts,
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
        $totalPosts         = $this->postsModel->countPosts(array_merge($this->args, ['date' => '']));
        $recentPostsCount   = $this->postsModel->countPosts($this->args);
        $totalViews         = $this->viewsModel->countViews($this->args);
        $totalVisitors      = $this->visitorsModel->countVisitors($this->args);
        $totalWords         = $this->postsModel->countWords($this->args);
        $totalComments      = $this->postsModel->countComments($this->args);

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
        $recentPosts        = $this->postsModel->getPostsViewsData(array_merge($this->args, ['order_by' => 'post_date']));

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
                    'recent'=> $recentPostsCount
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
            'performance'       => $performanceData,
            'visits_summary'    => array_replace_recursive($visitorsSummary, $viewsSummary),
            'posts'             => [
                'top_viewing'   => $topPostsByView,
                'top_commented' => $topPostsByComment,
                'recent'        => $recentPosts
            ]
        ];
    }

    public function getPagesData()
    {
        $args = array_merge($this->args, [
            'order'     => Request::get('order', 'DESC'),
            'order_by'  => Request::get('order_by', 'views'),
            'per_page'  => Admin_Template::$item_per_page,
            'page'      => Admin_Template::getCurrentPaged(),
        ]);

        $data = $this->taxonomyModel->getTaxonomiesData($args);

        return [
            'categories'  => $data,
            'total'       => count($data)
        ];
    }

    public function getCategoryReportData()
    {
        return [
            'terms' => $this->taxonomyModel->getTermsReportData($this->args)
        ];
    }
}