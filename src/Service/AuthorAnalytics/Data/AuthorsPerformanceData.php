<?php 

namespace WP_Statistics\Service\AuthorAnalytics\Data;

use WP_Statistics\Models\AuthorsModel;
use WP_Statistics\Models\PagesModel;
use WP_Statistics\Models\PostsModel;

class AuthorsPerformanceData
{
    protected $args;
    protected $authorModel;
    protected $pagesModel;
    protected $postsModel;

    
    public function __construct($args)
    {
        $this->args = $args;

        $this->authorModel  = new AuthorsModel();
        $this->pagesModel   = new PagesModel();
        $this->postsModel   = new PostsModel();
    }

    public function get()
    {
        $totalAuthors    = $this->authorModel->countAuthors();
        $activeAuthors   = $this->authorModel->countAuthors($this->args);
        $totalPosts      = $this->postsModel->countPosts($this->args);
        $totalWords      = $this->postsModel->countWords($this->args);
        $totalComments   = $this->postsModel->countComments($this->args);
        $totalViews      = $this->pagesModel->countViews($this->args);
        $publishOverview = $this->postsModel->publishOverview(array_intersect_key($this->args, ['post_type']));

        return [
            'authors' => [
                'total' => $totalAuthors,
                'active'=> $activeAuthors,
                'avg'   => $totalPosts / $activeAuthors
            ],
            'views'   => [
                'total' => $totalViews,
                'avg'   => $totalViews / $totalPosts
            ],
            'posts'   => [
                'words'     => [
                    'total' => $totalWords,
                    'avg'   => $totalWords / $totalPosts
                ],
                'comments'  => [
                    'total' => $totalComments,
                    'avg'   => $totalComments / $totalPosts
                ],
                'publish'   => $publishOverview
            ]
        ];
    }

}