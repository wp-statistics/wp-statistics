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

    public function getPerformanceChartData()
    {
        $result = [
            'labels'    => [],
            'views'     => [],
            'visitors'  => []
        ];

        for ($i = 14; $i >= 0; $i--) {
            $date       = date('Y-m-d', strtotime("-$i days"));
            $dateFilter = ['date' => ['from' => $date, 'to' => $date]];

            $result['labels'][]     = date_i18n('j M', strtotime($date));
            $result['visitors'][]   = $this->visitorsModel->countVisitors(array_merge($this->args, $dateFilter));
            $result['views'][]      = $this->viewsModel->countViews(array_merge($this->args, $dateFilter));
            $result['posts'][]      = $this->postsModel->countPosts(array_merge($this->args, $dateFilter));
        }

        return $result;
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

    public function getVisitorsData()
    {
        return $this->visitorsModel->getParsedVisitorsData($this->args);
    }

    public function getPostTypeData()
    {
        $overviewData       = $this->getOverviewData();
        $visitorsData       = $this->getVisitorsData();
        
        $visitorsSummary    = $this->visitorsModel->getVisitorsSummary($this->args);
        $viewsSummary       = $this->viewsModel->getViewsSummary($this->args);

        $performanceData    = [
            'posts'     => $this->postsModel->countPosts(array_merge($this->args, ['date' => ['from' => date('Y-m-d', strtotime('-15 days')), 'to' => date('Y-m-d')]])),
            'visitors'  => $this->visitorsModel->countVisitors(array_merge($this->args, ['date' => ['from' => date('Y-m-d', strtotime('-15 days')), 'to' => date('Y-m-d')]])),
            'views'     => $this->viewsModel->countViews(array_merge($this->args, ['date' => ['from' => date('Y-m-d', strtotime('-15 days')), 'to' => date('Y-m-d')]]))
        ];

        $topPostsByView     = $this->postsModel->getPostsViewsData($this->args);
        $topPostsByComment  = $this->postsModel->getPostsCommentsData($this->args);
        $recentPosts        = $this->postsModel->getPostsViewsData(array_merge($this->args, ['date' => '', 'date_field' => 'post_date', 'order_by' => 'post_date']));

        return [
            'visits_summary'    => array_replace_recursive($visitorsSummary, $viewsSummary),
            'overview'          => $overviewData,
            'visitors_data'     => $visitorsData,
            'performance'       => $performanceData,
            'posts'             => [
                'top_viewing'   => $topPostsByView,
                'top_commented' => $topPostsByComment,
                'recent'        => $recentPosts
            ],
        ];
    }
}