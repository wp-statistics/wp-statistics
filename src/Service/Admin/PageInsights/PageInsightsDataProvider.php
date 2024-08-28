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
            'order_by'              => Request::get('order_by', 'visitors'),
            'filter_by_view_date'   => true
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

        return [
            'categories'  => $this->taxonomyModel->getTaxonomiesData($args),
            'total'       => $this->taxonomyModel->countTerms($this->args)
        ];
    }

    public function getAuthorsData()
    {
        $authors = $this->authorsModel->getAuthorsPagesData(array_merge($this->args, ['order_by' => Request::get('order_by', 'page_views')]));
        $total   = $this->authorsModel->countAuthors(array_merge($this->args, ['ignore_date' => true]));

        return [
            'authors' => $authors,
            'total'   => $total
        ];
    }
}