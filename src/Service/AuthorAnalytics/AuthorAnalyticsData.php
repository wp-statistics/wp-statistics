<?php 

namespace WP_Statistics\Service\AuthorAnalytics;

use WP_STATISTICS\Admin_Assets;
use WP_STATISTICS\Helper;
use WP_Statistics\Models\AuthorsModel;
use WP_Statistics\Models\PagesModel;
use WP_Statistics\Models\PostsModel;

class AuthorAnalyticsData
{
    protected $args;
    protected $authorModel;
    protected $pagesModel;
    protected $postsModel;
    protected $totalPosts;

    
    public function __construct($args)
    {
        $this->args = $args;

        $this->authorModel  = new AuthorsModel();
        $this->pagesModel   = new PagesModel();
        $this->postsModel   = new PostsModel();

        $this->localizeData();
    }

    public function localizeData()
    {
        $data = [];

        // Data for performance tab
        if (isset($this->args['tab']) && $this->args['tab'] == 'performance') {
            $data = [
                'publish_overview_chart_data'   => $this->generatePublishingChartData(),
                'views_per_posts_chart_data'    => $this->generateViewsPerPostsChartData()
            ];
        }

        wp_localize_script(Admin_Assets::$prefix, 'Wp_Statistics_Author_Analytics_Object', $data);
    }

    protected function generateViewsPerPostsChartData()
    {
        $args               = array_merge($this->args, ['per_page' => -1]);
        $topAuthorsByViews  = $this->authorModel->getAuthorsByViewsPerPost($args);

        $data = [];
        
        if ($topAuthorsByViews) {
            foreach ($topAuthorsByViews as $author) {
                $data[] = [
                    'x'     => $author->total_views,  
                    'y'     => $author->total_posts,  
                    'img'   => esc_url(get_avatar_url($author->id))
                ];
            }
        }

        return $data;
    }

    protected function generatePublishingChartData()
    {
        // Just filter by post type
        $args = Helper::filterArrayByKeys($this->args, ['post_type']);

        $publishingData = $this->postsModel->publishOverview($args);
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
                'd' => $currentDate,
                'v' => $numberOfPosts
            ];
    
            $date += 86400;
        }

        return $data;
    }


    public function authorsPerformanceData()
    {
        // Authors data
        $totalAuthors         = $this->authorModel->countAuthors();
        $activeAuthors        = $this->authorModel->countAuthors($this->args);
        $topPublishingAuthors = $this->authorModel->topPublishingAuthors($this->args);
        $topViewingAuthors    = $this->authorModel->topViewingAuthors($this->args);
        $topAuthorsByComment  = $this->authorModel->getAuthorsByCommentsPerPost($this->args);
        $topAuthorsByViews    = $this->authorModel->getAuthorsByViewsPerPost($this->args);
        $topAuthorsByWords    = $this->authorModel->getAuthorsByWordsPerPost($this->args);

        // Views data
        $totalViews           = $this->pagesModel->countViews($this->args);

        // Posts data
        $totalWords           = $this->postsModel->countWords($this->args);
        $totalComments        = $this->postsModel->countComments($this->args);
        $totalPosts           = $this->postsModel->countPosts($this->args);

        return [
            'authors' => [
                'total'             => $totalAuthors,
                'active'            => $activeAuthors,
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

    public function authorsPagesData()
    {
        $authors = $this->authorModel->getAuthorsByViewsPerPost($this->args);
        $total   = $this->authorModel->countAuthors($this->args);

        return [
            'authors' => $authors,
            'total'   => $total
        ];
    }


    public function authorsReportData()
    {
        $authors = $this->authorModel->getAuthorsPerformanceData($this->args);
        $total   = $this->authorModel->countAuthors($this->args);

        return [
            'authors'   => $authors,
            'total'     => $total
        ];
    }

    public function authorsPostsData()
    {
        $posts  = $this->postsModel->getPostsReportData($this->args);
        $total  = $this->postsModel->countPosts($this->args);

        return [
            'posts'   => $posts,
            'total'   => $total
        ];
    }

    public function authorSingleData()
    {
        // Views data
        $totalViews           = $this->pagesModel->countViews($this->args);

        // Posts data
        $totalWords           = $this->postsModel->countWords($this->args);
        $totalComments        = $this->postsModel->countComments($this->args);
        $totalPosts           = $this->postsModel->countPosts($this->args);

        return [
            'authors' => [
                'total'             => $totalAuthors,
                'active'            => $activeAuthors,
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
}