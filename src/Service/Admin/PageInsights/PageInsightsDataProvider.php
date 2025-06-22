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

    public function getOverviewData()
    {
        $topData        = $this->postsModel->getPostsReportData(['order_by' => 'visitors', 'per_page' => 5]);
        $recentData     = $this->postsModel->getPostsReportData(['order_by' => 'date', 'per_page' => 5]);
        $notFoundData   = $this->postsModel->get404Data(['per_page' => 5]);
        $authorsData    = $this->authorsModel->getAuthorsPagesData(['order_by' => 'page_views', 'per_page' => 5]);

        return [
            'top'       => $topData,
            'recent'    => $recentData,
            '404'       => $notFoundData,
            'author'    => $authorsData
        ];
    }

    public function getTopData()
    {
        $args = array_merge($this->args, [
            'order_by'              => Request::get('order_by', 'visitors'),
            'filter_by_view_date'   => true
        ]);

        unset($args['taxonomy']);

        if (! empty($args['url'])) {
            $decodedUrl  = rawurldecode($args['url']);
            $args['url'] = esc_sql($decodedUrl);;
        }

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

    public function get404Data()
    {
        return [
            'data'  => $this->postsModel->get404Data($this->args),
            'total' => $this->postsModel->count404Data($this->args)
        ];
    }
}
