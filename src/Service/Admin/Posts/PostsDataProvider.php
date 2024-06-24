<?php 

namespace WP_Statistics\Service\Admin\Posts;

use WP_Statistics\Models\PostsModel;

class PostsDataProvider
{
    protected $args;
    protected $postsModel;
    
    public function __construct($args)
    {
        $this->args = $args;

        $this->postsModel    = new PostsModel();
    }

    public function getPostsReportData()
    {
        $posts  = $this->postsModel->getPostsReportData($this->args);
        $total  = $this->postsModel->countPosts($this->args);

        return [
            'posts'   => $posts,
            'total'   => $total
        ];
    }
}