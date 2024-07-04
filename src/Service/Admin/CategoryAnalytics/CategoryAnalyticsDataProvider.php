<?php 

namespace WP_Statistics\Service\Admin\CategoryAnalytics;

use WP_STATISTICS\Helper;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Models\PostsModel;
use WP_Statistics\Utils\Request;
use WP_Statistics\Models\TaxonomyModel;
use WP_Statistics\Models\ViewsModel;
use WP_Statistics\Models\VisitorsModel;

class CategoryAnalyticsDataProvider
{
    protected $args;
    protected $taxonomyModel;
    protected $postsModel;
    protected $visitorsModel;
    protected $viewsModel;
    
    public function __construct($args)
    {
        $this->args = $args;

        $this->taxonomyModel = new TaxonomyModel();
        $this->visitorsModel = new VisitorsModel();
        $this->viewsModel    = new ViewsModel();
        $this->postsModel    = new PostsModel();
    }

    public function getChartsData()
    {
        $visitorsData = $this->visitorsModel->getVisitorsPlatformData($this->args);

        return [
            'performance_chart_data' => $this->getPerformanceChartData(),
            'os_chart_data'             => [
                'labels'    => array_keys($visitorsData['platform']), 
                'data'      => array_values($visitorsData['platform'])
            ],
            'browser_chart_data'        => [
                'labels'    => array_keys($visitorsData['agent']), 
                'data'      => array_values($visitorsData['agent'])
            ],
            'device_chart_data'         => [
                'labels'    => array_keys($visitorsData['device']), 
                'data'      => array_values($visitorsData['device'])
            ],
            'model_chart_data'          => [
                'labels'    => array_keys($visitorsData['model']), 
                'data'      => array_values($visitorsData['model'])
            ],
        ];
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

            $result['labels'][]     = date_i18n(Helper::getDefaultDateFormat(false, true), strtotime($date));
            $result['visitors'][]   = $this->visitorsModel->countVisitors(array_merge($this->args, $dateFilter));
            $result['views'][]      = $this->viewsModel->countViews(array_merge($this->args, $dateFilter));
            $result['posts'][]      = $this->postsModel->countPosts(array_merge($this->args, $dateFilter));
        }

        return $result;
    }

    public function getPerformanceData()
    {
        $totalPosts         = $this->postsModel->countPosts($this->args);
        $totalViews         = $this->viewsModel->countViews($this->args);
        $totalVisitors      = $this->visitorsModel->countVisitors($this->args);
        $totalWords         = $this->postsModel->countWords($this->args);
        $totalComments      = $this->postsModel->countComments($this->args);

        $performanceArgs = ['date' => ['from' => date('Y-m-d', strtotime('-14 days')), 'to' => date('Y-m-d')]];
        $performanceData = [
            'posts'     => $this->postsModel->countPosts(array_merge($this->args, $performanceArgs)),
            'visitors'  => $this->visitorsModel->countVisitors(array_merge($this->args, $performanceArgs)),
            'views'     => $this->viewsModel->countViews(array_merge($this->args, $performanceArgs)),
        ];

        $topPostsByView     = $this->postsModel->getPostsViewsData($this->args);
        $topPostsByComment  = $this->postsModel->getPostsCommentsData($this->args);
        $recentPosts        = $this->postsModel->getPostsViewsData(array_merge($this->args, ['date' => '', 'date_field' => 'post_date', 'order_by' => 'post_date']));

        return [
            'overview'          => [
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
            ],
            'performance'       => $performanceData,
            'posts'             => [
                'top_viewing'   => $topPostsByView,
                'top_commented' => $topPostsByComment,
                'recent'        => $recentPosts
            ]
        ];
    }

    public function getPagesData()
    {
        $args = array_merge($this->args, [
            'order'     => Request::get('order', 'DESC'),
            'order_by'  => Request::get('order_by', 'views'),
            'per_page'  => Admin_Template::$item_per_page,
            'page'      => Admin_Template::getCurrentPaged(),
        ]);

        $data = $this->taxonomyModel->getTaxonomiesData($args);

        return [
            'categories'  => $data,
            'total'       => count($data)
        ];
    }
}