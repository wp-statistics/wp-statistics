<?php 

namespace WP_Statistics\Service\Admin\PageInsights;

use WP_STATISTICS\Helper;
use WP_Statistics\Models\PostsModel;
use WP_Statistics\Models\TaxonomyModel;
use WP_Statistics\Models\AuthorsModel;
use WP_Statistics\Utils\Request;

class PageInsightsDataProvider
{
    protected $args;
    protected $postsModel;
    protected $taxonomyModel;
    protected $authorsModel;
    
    public function __construct($args)
    {
        $this->args = $args;

        $this->postsModel       = new PostsModel();
        $this->authorsModel     = new AuthorsModel();
        $this->taxonomyModel    = new TaxonomyModel();
    }

    public function getContentsData()
    {
        $args = array_merge($this->args, [
            'order_by'          => Request::get('order_by', 'visitors'),
            'count_no_views'    => false
        ]);

        unset($args['taxonomy']);

        $posts  = $this->postsModel->getPostsReportData($args);
        $total  = $this->postsModel->countPosts($args);

        return [
            'posts'   => $posts,
            'total'   => $total
        ];
    }

    public function getCategoryData()
    {
        $args = array_merge($this->args, [
            'order_by'          => Request::get('order_by', 'views'),
            'count_total_posts' => true
        ]);

        $data = $this->taxonomyModel->getTaxonomiesData($args);

        return [
            'categories'  => $data,
            'total'       => count($data)
        ];
    }

    public function getAuthorsData()
    {
        $args = array_merge($this->args, [
            'post_type' => Helper::getPostTypes(),
            'order_by'  => 'page_views'
        ]);
        $authors = $this->authorsModel->getAuthorsPagesData($args);
        $total   = $this->authorsModel->countAuthors($this->args);

        return [
            'authors' => $authors,
            'total'   => $total
        ];
    }
}