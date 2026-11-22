<?php

namespace WP_Statistics\Service\Admin\PageInsights;

use WP_STATISTICS\Helper;
use WP_Statistics\Models\PostsModel;
use WP_Statistics\Models\TaxonomyModel;
use WP_Statistics\Models\AuthorsModel;
use WP_Statistics\Models\VisitorsModel;
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
        $topData        = $this->postsModel->getPostsReportData(['order_by' => 'views', 'per_page' => 5]);
        $topData        = $this->attachBounceRates($topData);
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
        $args = wp_parse_args($this->args, [
            'order_by'            => 'visitors',
            'order'               => 'DESC',
            'filter_by_view_date' => true
        ]);

        unset($args['taxonomy']);

        if (! empty($args['url'])) {
            global $wpdb;

            // The query is prepared later, so only LIKE wildcards need escaping here
            $decodedUrl  = rawurldecode(wp_unslash($args['url']));
            $args['url'] = $wpdb->esc_like($decodedUrl);
        }

        $posts  = $this->postsModel->getPostsReportData($args);
        $posts  = $this->attachBounceRates($posts, $args);
        $total  = $this->postsModel->countPosts($args);

        return [
            'posts'   => $posts,
            'total'   => $total
        ];
    }

    /**
     * Adds `bounce_rate` (float, or null when nobody landed on the page) to post rows
     * from PostsModel::getPostsReportData(). Only the rows on screen are queried.
     *
     * @param array $posts
     * @param array $args
     * @return array
     */
    protected function attachBounceRates($posts, $args = [])
    {
        if (empty($posts) || !is_array($posts)) {
            return $posts;
        }

        $resources = [];
        foreach ($posts as $post) {
            $resources[] = ['id' => $post->post_id, 'uri' => '', 'type' => ''];
        }

        $engagement = (new VisitorsModel())->getPagesEngagement([
            'date'      => $args['date'] ?? '',
            'resources' => $resources,
            'id_types'  => !empty($args['post_type']) ? $args['post_type'] : Helper::get_list_post_type(),
            'fields'    => ['bounce_rate'],
        ]);

        foreach ($posts as $post) {
            $key               = VisitorsModel::pageEngagementKey($post->post_id);
            $post->bounce_rate = isset($engagement[$key]) ? $engagement[$key]['bounce_rate'] : null;
        }

        return $posts;
    }

    public function getCategoryData()
    {
        $args = wp_parse_args($this->args, [
            'taxonomy'          => 'category',
            'order_by'          => 'views',
            'order'             => 'DESC',
            'count_total_posts' => true
        ]);

        return [
            'categories'  => $this->taxonomyModel->getTaxonomiesData($args),
            'total'       => $this->taxonomyModel->countTerms($args)
        ];
    }

    public function getAuthorsData()
    {
        $args = wp_parse_args($this->args, [
            'order_by' => 'page_views',
            'order'    => 'DESC'
        ]);

        $authors = $this->authorsModel->getAuthorsPagesData($args);
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
