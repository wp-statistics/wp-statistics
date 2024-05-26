<?php 

namespace WP_Statistics\Service\AuthorAnalytics\Data;

use WP_Statistics\Models\AuthorsModel;

class AuthorsPagesData
{
    protected $args;
    protected $authorModel;


    
    public function __construct($args)
    {
        $this->args         = $args;
        $this->authorModel  = new AuthorsModel();
    }

   
    public function get()
    {
        $args    = array_merge($this->args, ['limit' => '']);
        $authors = $this->authorModel->getAuthorsByViewsPerPost($args);

        return ['authors' => $authors];
    }

}