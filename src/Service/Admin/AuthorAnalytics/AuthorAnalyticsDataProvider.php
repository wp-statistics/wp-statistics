<?php

namespace WP_Statistics\Service\Admin\AuthorAnalytics;

use WP_STATISTICS\Helper;
use WP_Statistics\Components\DateRange;
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
        $posts     = $this->postsModel->countPosts($this->args);
        $prevPosts = $this->postsModel->countPosts(array_merge($this->args, ['date' => DateRange::getPrevPeriod()]));

        $authors     = $this->authorModel->countAuthors($this->args);
        $prevAuthors = $this->authorModel->countAuthors(array_merge($this->args, ['date' => DateRange::getPrevPeriod()]));

        $visitors     = $this->visitorsModel->countVisitors($this->args);
        $prevVisitors = $this->visitorsModel->countVisitors(array_merge($this->args, ['date' => DateRange::getPrevPeriod()]));

        $views     = $this->viewsModel->countViews($this->args);
        $prevViews = $this->viewsModel->countViews(array_merge($this->args, ['date' => DateRange::getPrevPeriod()]));

        $comments        = $this->postsModel->countComments($this->args);
        $prevComments    = $this->postsModel->countComments(array_merge($this->args, ['date' => DateRange::getPrevPeriod()]));
        $avgComments     = Helper::divideNumbers($comments, $posts);
        $prevAvgComments = Helper::divideNumbers($prevComments, $prevPosts);

        $topPublishingAuthors = $this->authorModel->getAuthorsByPostPublishes($this->args);
        $topViewingAuthors    = $this->authorModel->getTopViewingAuthors($this->args);
        $topAuthorsByComment  = $this->authorModel->getAuthorsByCommentsPerPost($this->args);
        $topAuthorsByViews    = $this->authorModel->getAuthorsByViewsPerPost($this->args);

        $result = [
            'glance'  => [
                'authors' => [
                    'value'  => $authors,
                    'change' => Helper::calculatePercentageChange($prevAuthors, $authors)
                ],
                'posts' => [
                    'value'  => $posts,
                    'change' => Helper::calculatePercentageChange($prevPosts, $posts)
                ],
                'visitors' => [
                    'value'  => $visitors,
                    'change' => Helper::calculatePercentageChange($prevVisitors, $visitors)
                ],
                'views' => [
                    'value'  => $views,
                    'change' => Helper::calculatePercentageChange($prevViews, $views)
                ],
                'comments'  => [
                    'value'  => $comments,
                    'change' => Helper::calculatePercentageChange($prevComments, $comments)
                ],
                'comments_avg' => [
                    'value'  => $avgComments,
                    'change' => Helper::calculatePercentageChange($prevAvgComments, $avgComments)
                ]
            ],
            'top_publishing'    => $topPublishingAuthors,
            'top_viewing'       => $topViewingAuthors,
            'top_by_comments'   => $topAuthorsByComment,
            'top_by_views'      => $topAuthorsByViews
        ];

        if (WordCountService::isActive()) {
            $words    = $this->postsModel->countWords($this->args);
            $avgWords = Helper::divideNumbers($words, $posts);

            $topAuthorsByWords = $this->authorModel->getAuthorsByWordsPerPost($this->args);

            $result['top_by_words']        = $topAuthorsByWords;
            $result['glance']['words']     = ['value' => $words];
            $result['glance']['words_avg'] = ['value' => $avgWords];
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
        $views     = $this->viewsModel->countViews($this->args);
        $prevViews = $this->viewsModel->countViews(array_merge($this->args, ['date' => DateRange::getPrevPeriod()]));

        $posts     = $this->postsModel->countPosts($this->args);
        $prevPosts = $this->postsModel->countPosts(array_merge($this->args, ['date' => DateRange::getPrevPeriod()]));

        $comments        = $this->postsModel->countComments($this->args);
        $prevComments    = $this->postsModel->countComments(array_merge($this->args, ['date' => DateRange::getPrevPeriod()]));
        $avgComments     = Helper::divideNumbers($comments, $posts);
        $prevAvgComments = Helper::divideNumbers($prevComments, $prevPosts);

        $visitors     = $this->visitorsModel->countVisitors($this->args);
        $prevVisitors = $this->visitorsModel->countVisitors(array_merge($this->args, ['date' => DateRange::getPrevPeriod()]));

        $taxonomies        = $this->taxonomyModel->getTaxonomiesData($this->args);
        $topPostsByView    = $this->postsModel->getPostsViewsData($this->args);
        $topPostsByComment = $this->postsModel->getPostsCommentsData($this->args);

        $visitorsSummary = $this->visitorsModel->getVisitorsSummary($this->args);
        $viewsSummary    = $this->viewsModel->getViewsSummary($this->args);

        $visitorsCountry = $this->visitorsModel->getVisitorsGeoData(array_merge($this->args, ['per_page' => 10]));

        $data = [
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
                'comments'  => [
                    'value'  => $comments,
                    'change' => Helper::calculatePercentageChange($prevComments, $comments)
                ],
                'comments_avg' => [
                    'value'  => $avgComments,
                    'change' => Helper::calculatePercentageChange($prevAvgComments, $avgComments)
                ]
            ],
            'posts' => [
                'top_views'    => $topPostsByView,
                'top_comments' => $topPostsByComment,
            ],
            'visit_summary'    => array_replace_recursive($visitorsSummary, $viewsSummary),
            'visitors_country' => $visitorsCountry,
            'taxonomies'       => $taxonomies
        ];

        if (WordCountService::isActive()) {
            $words    = $this->postsModel->countWords($this->args);
            $avgWords = Helper::divideNumbers($words, $posts);

            $topPostsByWords = $this->postsModel->getPostsWordsData($this->args);

            $data['glance']['words']     = ['value' => $words];
            $data['glance']['words_avg'] = ['value' => $avgWords];
            $data['posts']['top_words']  = $topPostsByWords;
        }

        return $data;
    }
}