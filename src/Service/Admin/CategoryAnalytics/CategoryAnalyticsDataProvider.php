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

    public function getPerformanceData()
    {
        $totalPosts         = $this->postsModel->countPosts($this->args);
        $totalViews         = $this->viewsModel->countViews($this->args);
        $totalVisitors      = $this->visitorsModel->countVisitors($this->args);
        $totalWords         = $this->postsModel->countWords($this->args);
        $totalComments      = $this->postsModel->countComments($this->args);

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