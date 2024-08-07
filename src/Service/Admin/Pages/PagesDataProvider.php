<?php 

namespace WP_Statistics\Service\Admin\Pages;

use WP_Statistics\Models\PostsModel;
use WP_Statistics\Models\TaxonomyModel;
use WP_Statistics\Utils\Request;

class PagesDataProvider
{
    protected $args;
    protected $postsModel;
    protected $taxonomyModel;
    
    public function __construct($args)
    {
        $this->args = $args;

        $this->postsModel       = new PostsModel();
        $this->taxonomyModel    = new TaxonomyModel();
    }

    public function getContentsData()
    {
        $args = array_merge($this->args, ['order_by' => Request::get('order_by', 'visitors')]);

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
}