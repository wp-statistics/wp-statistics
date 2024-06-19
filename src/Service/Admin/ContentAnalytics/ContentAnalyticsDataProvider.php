<?php 
namespace WP_Statistics\Service\Admin\ContentAnalytics;

use WP_STATISTICS\Helper;
use WP_Statistics\Models\ViewsModel;
use WP_Statistics\Models\PostsModel;
use WP_Statistics\Models\VisitorsModel;

class ContentAnalyticsDataProvider
{
    protected $args;
    private $postsModel;
    private $viewsModel;
    private $visitorsModel;
    
    public function __construct($args)
    {
        $this->args = $args;

        $this->postsModel       = new PostsModel();
        $this->viewsModel       = new ViewsModel();
        $this->visitorsModel    = new VisitorsModel();
    }

    public function getOverviewData()
    {
        $totalPosts     = $this->postsModel->countPosts($this->args);
        $totalViews     = $this->viewsModel->countViews($this->args);
        $totalVisitors  = $this->visitorsModel->countVisitors($this->args);
        $totalWords     = $this->postsModel->countWords($this->args);
        $totalComments  = $this->postsModel->countComments($this->args);

        $data = [
            'published' => [
                'total' => $totalPosts
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
        ];

        return $data;
    }

    public function getPostTypeData()
    {
        $overviewData = $this->getOverviewData();

        return [
            'overview' => $overviewData
        ];
    }
}