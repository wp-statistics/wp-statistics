<?php 

namespace WP_Statistics\Service\Admin\Pages;

use WP_Statistics\Models\PostsModel;

class PagesDataProvider
{
    protected $args;
    protected $postsModel;
    
    public function __construct($args)
    {
        $this->args = $args;

        $this->postsModel = new PostsModel();
    }

    public function getContentsData()
    {
        $posts  = $this->postsModel->getPostsReportData($this->args);
        $total  = $this->postsModel->countPosts($this->args);

        return [
            'posts'   => $posts,
            'total'   => $total
        ];
    }
}