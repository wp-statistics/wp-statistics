<?php 

namespace WP_Statistics\Service\AuthorAnalytics\Data;

use WP_STATISTICS\Admin_Assets;
use WP_STATISTICS\Helper;
use WP_Statistics\Models\AuthorsModel;
use WP_Statistics\Models\PagesModel;
use WP_Statistics\Models\PostsModel;

class AuthorsPerformanceData
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

        $this->totalPosts   = $this->postsModel->countPosts($this->args);

        $this->localizeData();
    }

    public function localizeData()
    {
        wp_localize_script(Admin_Assets::$prefix, 'Wp_Statistics_Author_Analytics_Object', [
            'publish_overview_chart_data' => $this->generatePublishingChartData()
        ]);
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

    public function getAuthorsOverviewData()
    {
        $totalAuthors         = $this->authorModel->countAuthors();
        $activeAuthors        = $this->authorModel->countAuthors($this->args);
        $topPublishingAuthors = $this->authorModel->topPublishingAuthors($this->args);
        $topViewingAuthors    = $this->authorModel->topViewingAuthors($this->args);
        $topAuthorsByComment  = $this->authorModel->topAuthorsByComments($this->args);

        return [
            'total'             => $totalAuthors,
            'active'            => $activeAuthors,
            'avg'               => Helper::divideNumbers($this->totalPosts, $activeAuthors),
            'top_publishing'    => $topPublishingAuthors,
            'top_viewing'       => $topViewingAuthors,
            'top_with_comments' => $topAuthorsByComment,
        ];
    }

    public function getViewsOverviewData()
    {
        $totalViews = $this->pagesModel->countViews($this->args);

        return [
            'total' => $totalViews,
            'avg'   => Helper::divideNumbers($totalViews, $this->totalPosts)
        ];
    }

    public function getPostsOverviewData()
    {
        $totalWords     = $this->postsModel->countWords($this->args);
        $totalComments  = $this->postsModel->countComments($this->args);

        return [
            'words'     => [
                'total' => $totalWords,
                'avg'   => Helper::divideNumbers($totalWords, $this->totalPosts)
            ],
            'comments'  => [
                'total' => $totalComments,
                'avg'   => Helper::divideNumbers($totalComments, $this->totalPosts)
            ]
        ];
    }

    public function get()
    {
        return [
            'authors' => $this->getAuthorsOverviewData(),
            'views'   => $this->getViewsOverviewData(),
            'posts'   => $this->getPostsOverviewData()
        ];
    }

}