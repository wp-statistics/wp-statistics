<?php 

namespace WP_Statistics\Service\AuthorAnalytics\Data;

use WP_STATISTICS\Helper;
use WP_Statistics\Models\AuthorsModel;
use WP_Statistics\Models\PagesModel;
use WP_Statistics\Models\PostsModel;

class AuthorsPerformanceData
{
    protected $args;
    
    public function __construct($args)
    {
        $this->args = $args;
    }

    public function getAuthorsOverview()
    {
        $authorModel = new AuthorsModel();

        return [
            'total' => $authorModel->count(),
            'active'=> $authorModel->count($this->args),
            'avg'   => $authorModel->averagePostsPerAuthor($this->args)
        ];
    }

    public function getViewsOverview()
    {
        $pagesModel = new PagesModel();

        return [
            'total' => $pagesModel->count($this->args),
            'avg'   => $pagesModel->averageViewsPerPost($this->args)
        ];
    }

    public function getPostsOverview()
    {
        $postsModel = new PostsModel();

        return [
            'words' => [
                'total' => $postsModel->countTotalWords($this->args),
                'avg'   => $postsModel->averageWordsPerPost($this->args)
            ],
            'comments' => [
                'total' => $postsModel->countTotalComments($this->args),
                'avg'   => $postsModel->averageCommentsPerPost($this->args)
            ],
            'publish' => [
                'total' => $postsModel->publishOverview(array_intersect_key($this->args, ['post_type']))
            ]
        ];
    }

    public function get()
    {
        return [
            'authors' => $this->getAuthorsOverview(),
            'views'   => $this->getViewsOverview(),
            'posts'   => $this->getPostsOverview(),
        ];
    }

}