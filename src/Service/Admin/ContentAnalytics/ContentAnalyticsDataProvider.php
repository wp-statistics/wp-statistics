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

    public function getVisitSummary()
    {
        return [
            'today'     => [
                'label'     => esc_html__('Today', 'wp-statistics'),
                'visitors'  => $this->visitorsModel->countVisitors(array_merge($this->args, ['date' => 'today'])),
                'views'     => $this->viewsModel->countViews(array_merge($this->args, ['date' => 'today'])),
            ],
            'yesterday' => [
                'label'     => esc_html__('Yesterday', 'wp-statistics'),
                'visitors'  => $this->visitorsModel->countVisitors(array_merge($this->args, ['date' => 'yesterday'])),
                'views'     => $this->viewsModel->countViews(array_merge($this->args, ['date' => 'yesterday'])),
            ],
            '7days'     => [
                'label'     => esc_html__('Last 7 days', 'wp-statistics'),
                'visitors'  => $this->visitorsModel->countVisitors(array_merge($this->args, ['date' => '7days'])),
                'views'     => $this->viewsModel->countViews(array_merge($this->args, ['date' => '7days'])),
            ],
            '30days'    => [
                'label'     => esc_html__('Last 30 days', 'wp-statistics'),
                'visitors'  => $this->visitorsModel->countVisitors(array_merge($this->args, ['date' => '30days'])),
                'views'     => $this->viewsModel->countViews(array_merge($this->args, ['date' => '30days'])),
            ],
            '60days'    => [
                'label'     => esc_html__('Last 60 days', 'wp-statistics'),
                'visitors'  => $this->visitorsModel->countVisitors(array_merge($this->args, ['date' => '60days'])),
                'views'     => $this->viewsModel->countViews(array_merge($this->args, ['date' => '60days'])),
            ],
            '120days'   => [
                'label'     => esc_html__('Last 120 days', 'wp-statistics'),
                'visitors'  => $this->visitorsModel->countVisitors(array_merge($this->args, ['date' => '120days'])),
                'views'     => $this->viewsModel->countViews(array_merge($this->args, ['date' => '120days'])),
            ],
            'year'      => [
                'label'     => esc_html__('Last 12 months', 'wp-statistics'),
                'visitors'  => $this->visitorsModel->countVisitors(array_merge($this->args, ['date' => 'year'])),
                'views'     => $this->viewsModel->countViews(array_merge($this->args, ['date' => 'year'])),
            ],
            'this_year' => [
                'label'     => esc_html__('This year (Jan - Today)', 'wp-statistics'),
                'visitors'  => $this->visitorsModel->countVisitors(array_merge($this->args, ['date' => 'this_year'])),
                'views'     => $this->viewsModel->countViews(array_merge($this->args, ['date' => 'this_year'])),
            ],
            'last_year' => [
                'label'     => esc_html__('Last Year', 'wp-statistics'),
                'visitors'  => $this->visitorsModel->countVisitors(array_merge($this->args, ['date' => 'last_year'])),
                'views'     => $this->viewsModel->countViews(array_merge($this->args, ['date' => 'last_year'])),
            ],
        ];
    }

    public function getPostTypeData()
    {
        $overviewData   = $this->getOverviewData();
        $visitsSummary  = $this->getVisitSummary();
        $visitorsData   = $this->visitorsModel->getParsedVisitorsData($this->args);

        return [
            'overview'      => $overviewData,
            'visits_summary'=> $visitsSummary,
            'visitors_data' => $visitorsData
        ];
    }
}