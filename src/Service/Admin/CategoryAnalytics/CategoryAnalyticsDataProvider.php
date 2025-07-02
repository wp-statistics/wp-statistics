<?php

namespace WP_Statistics\Service\Admin\CategoryAnalytics;

use WP_STATISTICS\Helper;
use WP_Statistics\Models\AuthorsModel;
use WP_Statistics\Models\PostsModel;
use WP_Statistics\Models\TaxonomyModel;
use WP_Statistics\Models\ViewsModel;
use WP_Statistics\Models\VisitorsModel;
use WP_Statistics\Service\Charts\ChartDataProviderFactory;

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
        $performanceDataProvider    = ChartDataProviderFactory::performanceChart($this->args);
        $searchEngineDataProvider   = ChartDataProviderFactory::searchEngineChart($this->args);
        $platformDataProvider       = ChartDataProviderFactory::platformCharts($this->args);

        return [
            'performance_chart_data'    => $performanceDataProvider->getData(),
            'search_engine_chart_data'  => $searchEngineDataProvider->getData(),
            'os_chart_data'             => $platformDataProvider->getOsData(),
            'browser_chart_data'        => $platformDataProvider->getBrowserData(),
            'device_chart_data'         => $platformDataProvider->getDeviceData(),
            'model_chart_data'          => $platformDataProvider->getModelData()
        ];
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

        $performanceData    = [
            'posts'     => $this->postsModel->countPosts($this->args),
            'visitors'  => $this->visitorsModel->countVisitors($this->args),
            'views'     => $this->viewsModel->countViews($this->args),
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

        $performanceData = [
            'posts'     => $this->postsModel->countPosts($this->args),
            'visitors'  => $this->visitorsModel->countVisitors($this->args),
            'views'     => $this->viewsModel->countViews($this->args),
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
                    'recent' => $recentPosts
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
