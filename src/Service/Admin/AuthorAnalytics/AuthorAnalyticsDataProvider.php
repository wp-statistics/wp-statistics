<?php 

namespace WP_Statistics\Service\Admin\AuthorAnalytics;

use WP_STATISTICS\Helper;
use WP_Statistics\Models\AuthorsModel;
use WP_Statistics\Models\ViewsModel;
use WP_Statistics\Models\PostsModel;
use WP_Statistics\Models\TaxonomyModel;
use WP_Statistics\Models\VisitorsModel;

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

    public function getViewsPerPostsChartData()
    {
        $args               = array_merge($this->args, ['per_page' => -1]);
        $topAuthorsByViews  = $this->authorModel->getAuthorsByViewsPerPost($args);

        $data = [];
        
        if ($topAuthorsByViews) {
            foreach ($topAuthorsByViews as $author) {
                $data[] = [
                    'x'      => $author->total_views,  
                    'y'      => $author->total_posts,  
                    'img'    => esc_url(get_avatar_url($author->id)),
                    'author' => esc_html($author->name)
                ];
            }
        }

        return $data;
    }

    public function getPublishingChartData()
    {
        // Just filter by post type
        $args = Helper::filterArrayByKeys($this->args, ['post_type', 'author_id']);

        $publishingData = $this->postsModel->countDailyPosts(array_merge($args, ['date' => ['from' => date('Y-m-d', strtotime('-365 days')), 'to' => date('Y-m-d')]]));
        $publishingData = wp_list_pluck($publishingData, 'posts', 'date');

        $today  = time();
        $date   = strtotime('-365 days');

        // Get number of posts published per day during last 365 days
        while ($date <= $today) {
            $currentDate    = date('Y-m-d', $date);
            $numberOfPosts  = isset($publishingData[$currentDate]) ? intval($publishingData[$currentDate]) : 0;

            $data[] = [
                'x' => $currentDate,
                'y' => date('N', $date),
                'd' => date_i18n(get_option('date_format', 'Y-m-d'), strtotime($currentDate)),
                'v' => $numberOfPosts
            ];
    
            $date += 86400;
        }

        return $data;
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
        $topAuthorsByWords    = $this->authorModel->getAuthorsByWordsPerPost($this->args);

        // Views data
        $totalViews           = $this->viewsModel->countViews($this->args);

        // Posts data
        $recentWords    = $this->postsModel->countWords($this->args);
        $totalWords     = $this->postsModel->countWords(array_merge($this->args, ['ignore_date' => true]));

        $recentComments = $this->postsModel->countComments($this->args);
        $totalComments  = $this->postsModel->countComments(array_merge($this->args, ['ignore_date' => true]));

        $recentPosts    = $this->postsModel->countPosts($this->args);
        $totalPosts     = $this->postsModel->countPosts(array_merge($this->args, ['ignore_date' => true]));

        return [
            'authors' => [
                'total'             => $totalAuthors,
                'active'            => $activeAuthors,
                'published'         => $recentPosts,
                'avg'               => Helper::divideNumbers($recentPosts, $activeAuthors),
                'top_publishing'    => $topPublishingAuthors,
                'top_viewing'       => $topViewingAuthors,
                'top_by_comments'   => $topAuthorsByComment,
                'top_by_views'      => $topAuthorsByViews,
                'top_by_words'      => $topAuthorsByWords
            ],
            'views'   => [
                'total' => $totalViews,
                'avg'   => Helper::divideNumbers($totalViews, $recentPosts)
            ],
            'posts'   => [
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
                    'total_avg' => Helper::divideNumbers($totalComments, $totalPosts),
                ]
            ]
        ];
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
        $platformData = $this->visitorsModel->getVisitorsPlatformData($this->args);

        $data = [
            'os_chart_data'         => [
                'labels'    => wp_list_pluck($platformData['platform'], 'label'),
                'data'      => wp_list_pluck($platformData['platform'], 'visitors'),
                'icons'     => wp_list_pluck($platformData['platform'], 'icon'),
            ],
            'browser_chart_data'    => [
                'labels'    => wp_list_pluck($platformData['agent'], 'label'), 
                'data'      => wp_list_pluck($platformData['agent'], 'visitors'),
                'icons'     => wp_list_pluck($platformData['agent'], 'icon')
            ],
            'publish_chart_data'    => $this->getPublishingChartData()
        ];

        return $data;
    }

    
    public function getAuthorsChartData()
    {
        $data = [
            'publish_chart_data'         => $this->getPublishingChartData(),
            'views_per_posts_chart_data' => [
                'data'          => $this->getViewsPerPostsChartData(),
                'chartLabel'    => sprintf(esc_html__('Views/Published %s', 'wp-statistics'),Helper::getPostTypeName($this->args['post_type'])),
                'yAxisLabel'    => sprintf(esc_html__('Published %s', 'wp-statistics'), Helper::getPostTypeName($this->args['post_type'])),
                'xAxisLabel'    => sprintf(esc_html__('%s Views', 'wp-statistics'), Helper::getPostTypeName($this->args['post_type'], true))
            ]
        ];

        return $data;
    }



    public function getAuthorSingleData()
    {
        $recentViews        = $this->viewsModel->countViews($this->args);

        $recentWords        = $this->postsModel->countWords($this->args);
        $totalWords         = $this->postsModel->countWords(array_merge($this->args, ['ignore_date' => true]));

        $recentComments     = $this->postsModel->countComments($this->args);
        $totalComments      = $this->postsModel->countComments(array_merge($this->args, ['ignore_date' => true]));

        $recentPosts        = $this->postsModel->countPosts($this->args);
        $totalPosts         = $this->postsModel->countPosts(array_merge($this->args, ['ignore_date' => true]));

        $recentVisitors     = $this->visitorsModel->countVisitors($this->args);

        $taxonomies         = $this->taxonomyModel->getTaxonomiesData($this->args);
        $topPostsByView     = $this->postsModel->getPostsViewsData($this->args);
        $topPostsByComment  = $this->postsModel->getPostsCommentsData($this->args);
        $topPostsByWords    = $this->postsModel->getPostsWordsData($this->args);

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
                    'total_avg' => Helper::divideNumbers($totalComments, $totalPosts),
                ]
            ],
            'posts'         => [
                'top_views'     => $topPostsByView,
                'top_comments'  => $topPostsByComment,
                'top_words'     => $topPostsByWords
            ]
        ];

        return $data;
    }
}