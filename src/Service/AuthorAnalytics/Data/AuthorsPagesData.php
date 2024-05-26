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
        $authors = $this->authorModel->getAuthorsByViewsPerPost($this->args);
        $total   = $this->authorModel->countAuthors($this->args);

        return [
            'authors' => $authors,
            'total'   => $total
        ];
    }

}