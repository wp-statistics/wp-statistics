<?php

namespace WP_Statistics\Service\Admin\AuthorAnalytics;

use WP_STATISTICS\Helper;
use WP_Statistics\Models\AuthorsModel;
use WP_Statistics\Models\ViewsModel;
use WP_Statistics\Models\PostsModel;
use WP_Statistics\Models\TaxonomyModel;
use WP_Statistics\Models\VisitorsModel;
use WP_Statistics\Service\Admin\Posts\WordCountService;
use WP_Statistics\Service\Charts\ChartDataProviderFactory;

class AuthorAnalyticsDataProvider
{
    protected $args;
    protected $authorModel;
    protected $viewsModel;
    protected $postsModel;
    protected $visitorsModel;
    protected $taxonomyModel;


    public function __construct($args)
    {
        $this->args = $args;

        $this->authorModel   = new AuthorsModel();
        $this->viewsModel    = new ViewsModel();
        $this->postsModel    = new PostsModel();
        $this->visitorsModel = new VisitorsModel();
        $this->taxonomyModel = new TaxonomyModel();
    }

    public function getAuthorsPerformanceData()
    {
        // Authors data
        $totalAuthors         = $this->authorModel->countAuthors();
        $activeAuthors        = $this->authorModel->countAuthors($this->args);
        $topPublishingAuthors = $this->authorModel->getAuthorsByPostPublishes($this->args);
        $topViewingAuthors    = $this->authorModel->getTopViewingAuthors($this->args);
        $topAuthorsByComment  = $this->authorModel->getAuthorsByCommentsPerPost($this->args);
        $topAuthorsByViews    = $this->authorModel->getAuthorsByViewsPerPost($this->args);

        // Views data
        $totalViews           = $this->viewsModel->countViews($this->args);

        // Posts data
        $recentComments = $this->postsModel->countComments($this->args);
        $totalComments  = $this->postsModel->countComments(array_merge($this->args, ['ignore_date' => true]));

        $recentPosts    = $this->postsModel->countPosts($this->args);
        $totalPosts     = $this->postsModel->countPosts(array_merge($this->args, ['ignore_date' => true]));

        $result = [
            'authors' => [
                'total'             => $totalAuthors,
                'active'            => $activeAuthors,
                'published'         => $recentPosts,
                'avg'               => Helper::divideNumbers($recentPosts, $activeAuthors),
                'top_publishing'    => $topPublishingAuthors,
                'top_viewing'       => $topViewingAuthors,
                'top_by_comments'   => $topAuthorsByComment,
                'top_by_views'      => $topAuthorsByViews,
            ],
            'views'   => [
                'total' => $totalViews,
                'avg'   => Helper::divideNumbers($totalViews, $recentPosts)
            ],
            'posts'   => [
                'comments'  => [
                    'total'     => $totalComments,
                    'recent'    => $recentComments,
                    'avg'       => Helper::divideNumbers($recentComments, $recentPosts),
                    'total_avg' => Helper::divideNumbers($totalComments, $totalPosts),
                ]
            ]
        ];

        if (WordCountService::isActive()) {
            $topAuthorsByWords  = $this->authorModel->getAuthorsByWordsPerPost($this->args);
            $recentWords        = $this->postsModel->countWords($this->args);
            $totalWords         = $this->postsModel->countWords(array_merge($this->args, ['ignore_date' => true]));

            $result['authors']['top_by_words'] = $topAuthorsByWords;

            $result['posts']['words'] = [
                'total'     => $totalWords,
                'recent'    => $recentWords,
                'avg'       => Helper::divideNumbers($recentWords, $recentPosts),
                'total_avg' => Helper::divideNumbers($totalWords, $totalPosts)
            ];
        }

        return $result;
    }

    public function getAuthorsReportData()
    {
        $authors = $this->authorModel->getAuthorsReportData($this->args);
        $total   = $this->authorModel->countAuthors($this->args);

        return [
            'authors'   => $authors,
            'total'     => $total
        ];
    }

    public function getAuthorSingleChartData()
    {
        $platformDataProvider           = ChartDataProviderFactory::platformCharts($this->args);
        $publishOverviewDataProvider    = ChartDataProviderFactory::publishOverview(
            Helper::filterArrayByKeys($this->args, ['post_type', 'author_id'])
        );

        $data = [
            'os_chart_data'         => $platformDataProvider->getOsData(),
            'browser_chart_data'    => $platformDataProvider->getBrowserData(),
            'publish_chart_data'    => $publishOverviewDataProvider->getData()
        ];

        return $data;
    }

    public function getAuthorsChartData()
    {
        $authorsPostViewsDataProvider   = ChartDataProviderFactory::authorsPostViews(array_merge($this->args, ['per_page' => -1]));
        $publishOverviewDataProvider    = ChartDataProviderFactory::publishOverview(
            Helper::filterArrayByKeys($this->args, ['post_type', 'author_id'])
        );

        $data = [
            'publish_chart_data'         => $publishOverviewDataProvider->getData(),
            'views_per_posts_chart_data' => $authorsPostViewsDataProvider->getData()
        ];

        return $data;
    }

    public function getAuthorSingleData()
    {
        $recentViews        = $this->viewsModel->countViews($this->args);

        $recentComments     = $this->postsModel->countComments($this->args);
        $totalComments      = $this->postsModel->countComments(array_merge($this->args, ['ignore_date' => true]));

        $recentPosts        = $this->postsModel->countPosts($this->args);
        $totalPosts         = $this->postsModel->countPosts(array_merge($this->args, ['ignore_date' => true]));

        $recentVisitors     = $this->visitorsModel->countVisitors($this->args);

        $taxonomies         = $this->taxonomyModel->getTaxonomiesData($this->args);
        $topPostsByView     = $this->postsModel->getPostsViewsData($this->args);
        $topPostsByComment  = $this->postsModel->getPostsCommentsData($this->args);

        $visitorsSummary    = $this->visitorsModel->getVisitorsSummary($this->args);
        $viewsSummary       = $this->viewsModel->getViewsSummary($this->args);

        $visitorsCountry    = $this->visitorsModel->getVisitorsGeoData(array_merge($this->args, ['per_page' => 10]));

        $data = [
            'visit_summary'     => array_replace_recursive($visitorsSummary, $viewsSummary),
            'visitors_country'  => $visitorsCountry,
            'taxonomies'        => $taxonomies,
            'overview'          => [
                'posts'     => [
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
                    'total_avg' => Helper::divideNumbers($totalComments, $totalPosts),
                ]
            ],
            'posts'         => [
                'top_views'     => $topPostsByView,
                'top_comments'  => $topPostsByComment,
            ]
        ];

        if (WordCountService::isActive()) {
            $recentWords     = $this->postsModel->countWords($this->args);
            $totalWords      = $this->postsModel->countWords(array_merge($this->args, ['ignore_date' => true]));
            $topPostsByWords = $this->postsModel->getPostsWordsData($this->args);

            $data['overview']['words'] = [
                'total'     => $totalWords,
                'recent'    => $recentWords,
                'avg'       => Helper::divideNumbers($recentWords, $recentPosts),
                'total_avg' => Helper::divideNumbers($totalWords, $totalPosts)
            ];

            $data['posts']['top_words'] = $topPostsByWords;
        }

        return $data;
    }
}