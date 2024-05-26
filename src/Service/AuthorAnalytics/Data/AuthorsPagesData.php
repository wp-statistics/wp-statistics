<?php 

namespace WP_Statistics\Service\AuthorAnalytics\Data;

use WP_Statistics\Models\AuthorsModel;
use WP_Statistics\Models\PagesModel;
use WP_Statistics\Models\PostsModel;

class AuthorsPagesData
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
        return [];
    }

}