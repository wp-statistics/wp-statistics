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

        $publishingData = $this->postsModel->getPostPublishOverview($args);
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
        $totalWords           = $this->postsModel->countWords($this->args);
        $totalComments        = $this->postsModel->countComments($this->args);
        $totalPosts           = $this->postsModel->countPosts($this->args);

        return [
            'authors' => [
                'total'             => $totalAuthors,
                'active'            => $activeAuthors,
                'published'         => $totalPosts,
                'avg'               => Helper::divideNumbers($totalPosts, $activeAuthors),
                'top_publishing'    => $topPublishingAuthors,
                'top_viewing'       => $topViewingAuthors,
                'top_by_comments'   => $topAuthorsByComment,
                'top_by_views'      => $topAuthorsByViews,
                'top_by_words'      => $topAuthorsByWords
            ],
            'views'   => [
                'total' => $totalViews,
                'avg'   => Helper::divideNumbers($totalViews, $totalPosts)
            ],
            'posts'   => [
                'words'     => [
                    'total' => $totalWords,
                    'avg'   => Helper::divideNumbers($totalWords, $totalPosts)
                ],
                'comments'  => [
                    'total' => $totalComments,
                    'avg'   => Helper::divideNumbers($totalComments, $totalPosts)
                ]
            ]
        ];
    }

    public function getAuthorsPagesData()
    {
        $args = array_merge(
            $this->args, 
            ['post_type' => Helper::get_list_post_type()]
        );
        $authors = $this->authorModel->getAuthorsPagesData($args);
        $total   = $this->authorModel->countAuthors($this->args);

        return [
            'authors' => $authors,
            'total'   => $total
        ];
    }


    public function getAuthorsReportData()
    {
        $authors = $this->authorModel->getAuthorsPerformanceData($this->args);
        $total   = $this->authorModel->countAuthors($this->args);

        return [
            'authors'   => $authors,
            'total'     => $total
        ];
    }

    public function getAuthorsPostsData()
    {
        $posts  = $this->postsModel->getPostsReportData($this->args);
        $total  = $this->postsModel->countPosts($this->args);

        return [
            'posts'   => $posts,
            'total'   => $total
        ];
    }

    public function getAuthorSingleData()
    {
        $totalViews         = $this->viewsModel->countViews($this->args);

        $totalWords         = $this->postsModel->countWords($this->args);
        $totalComments      = $this->postsModel->countComments($this->args);
        $totalPosts         = $this->postsModel->countPosts($this->args);
        $totalVisitors      = $this->visitorsModel->countVisitors($this->args);

        $taxonomies         = $this->taxonomyModel->countTaxonomiesPosts($this->args);
        $topPostsByView     = $this->postsModel->getPostsViewsData($this->args);
        $topPostsByComment  = $this->postsModel->getPostsCommentsData($this->args);
        $topPostsByWords    = $this->postsModel->getPostsWordsData($this->args);

        $visitorsData       = $this->visitorsModel->getParsedVisitorsData($this->args);
        $visitorsSummary    = $this->visitorsModel->getVisitorsSummary($this->args);
        $viewsSummary       = $this->viewsModel->getViewsSummary($this->args);

        $data = [
            'visit_summary' => array_replace_recursive($visitorsSummary, $viewsSummary),
            'visitors_data' => $visitorsData,
            'taxonomies'    => $taxonomies,
            'overview'      => [
                'posts'         => [
                    'total'     => $totalPosts
                ],
                'views'     => [
                    'total'     => $totalViews,
                    'avg'       => Helper::divideNumbers($totalViews, $totalPosts)
                ],
                'visitors'  => [
                    'total'     => $totalVisitors,
                    'avg'       => Helper::divideNumbers($totalVisitors, $totalPosts)
                ],
                'words'     => [
                    'total'     => $totalWords,
                    'avg'       => Helper::divideNumbers($totalWords, $totalPosts)
                ],
                'comments'  => [
                    'total'     => $totalComments,
                    'avg'       => Helper::divideNumbers($totalComments, $totalPosts)
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