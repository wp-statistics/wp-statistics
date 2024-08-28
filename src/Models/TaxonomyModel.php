<?php

namespace WP_Statistics\Models;

use WP_STATISTICS\Helper;
use WP_Statistics\Utils\Query;
use WP_Statistics\Abstracts\BaseModel;

class TaxonomyModel extends BaseModel
{
    public function countTerms($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'      => '',
            'taxonomy'  => '',
        ]);

        $result = Query::select([
                'COUNT(term_id)',
            ])
            ->from('term_taxonomy')
            ->where('taxonomy', 'IN', $args['taxonomy'])
            ->getVar();

        return $result;
    }

    public function getTaxonomiesData($args = [])
    {
        $args = $this->parseArgs($args, [
            'post_type'         => Helper::get_list_post_type(),
            'order_by'          => ['term_taxonomy.taxonomy', 'post_count'],
            'taxonomy'          => array_keys(Helper::get_list_taxonomy(true)),
            'page'              => 1,
            'per_page'          => 5,
            'date'              => '',
            'author_id'         => '',
            'order'             => '',
            'count_total_posts' => false
        ]);

        $categoryViewsQuery = Query::select(['id', 'date', 'SUM(count) AS views'])
            ->from('pages')
            ->where('pages.type', '=', 'category')
            ->whereDate('date', $args['date'])
            ->groupBy('id')
            ->getQuery();

        $query = Query::select([
                'taxonomy', 
                'terms.term_id',
                'terms.name',
                'COUNT(DISTINCT posts.ID) as post_count',
                'COALESCE(category.views, 0) as term_views'
            ])
            ->from('term_taxonomy')
            ->join('terms', ['term_taxonomy.term_id', 'terms.term_id'])
            ->join('term_relationships', ['term_relationships.term_taxonomy_id', 'term_taxonomy.term_taxonomy_id'], [], 'LEFT')
            ->join('posts', ['posts.ID', 'term_relationships.object_id'], [['posts.post_type' , 'IN', $args['post_type']], ['posts.post_status', '=', 'publish']], 'LEFT')
            ->joinQuery($categoryViewsQuery, ['category.id', 'term_taxonomy.term_taxonomy_id'], 'category', 'LEFT')
            ->where('term_taxonomy.taxonomy', 'IN', $args['taxonomy'])
            ->where('posts.post_author', '=', $args['author_id'])
            ->groupBy(['taxonomy', 'terms.term_id','terms.name'])
            ->orderBy($args['order_by'], $args['order'])
            ->perPage($args['page'], $args['per_page']);

        // If total posts is not requested, filter by date
        if ($args['count_total_posts'] == false) {
            $query->whereDate('posts.post_date', $args['date']);
        }

        $result = $query->getAll();

        if (!empty($result)) {
            $taxonomies = [];

            foreach ($result as $item) {
                $taxonomies[$item->taxonomy][] = [
                    'term_id'       => $item->term_id,
                    'term_name'     => $item->name,
                    'posts_count'   => $item->post_count,
                    'views'         => $item->term_views
                ];
            }

            $result = $taxonomies;
        }

        return $result ? $result : [];
    }

    public function getTermsData($args = [])
    {
        $args = $this->parseArgs($args, [
            'order_by'  => 'views',
            'order'     => '',
            'page'      => 1,
            'per_page'  => 5,
            'date'      => '',
            'taxonomy'  => '',
            'author_id' => '',
            'post_type' => Helper::getPostTypes(),
            'date_field'=> 'pages.date'
        ]);

        $result = Query::select([
                'terms.term_id',
                'terms.name as term_name',
                'SUM(pages.count) AS views',
                'COUNT(DISTINCT posts.ID) AS posts'
            ])
            ->from('posts')
            ->join('term_relationships', ['posts.ID', 'term_relationships.object_id'])
            ->join('term_taxonomy', ['term_relationships.term_taxonomy_id', 'term_taxonomy.term_taxonomy_id'])
            ->join('terms', ['term_taxonomy.term_id', 'terms.term_id'])
            ->join('pages', ['pages.id', 'posts.ID'])
            ->where('term_taxonomy.taxonomy', 'IN', $args['taxonomy'])
            ->where('posts.post_type', 'IN', $args['post_type'])
            ->where('posts.post_author', '=', $args['author_id'])
            ->whereDate($args['date_field'], $args['date'])
            ->groupBy(['term_id'])
            ->orderBy($args['order_by'], $args['order'])
            ->perPage($args['page'], $args['per_page'])
            ->getAll();

        return $result;
    }

    public function getTermsReportData($args = [])
    {
        $args = $this->parseArgs($args, [
            'order_by'  => 'views',
            'order'     => '',
            'page'      => 1,
            'per_page'  => 5,
            'date'      => '',
            'taxonomy'  => '',
            'author_id' => '',
            'post_type' => Helper::getDefaultPostTypes(),
            'date_field'=> 'pages.date'
        ]);

        $wordsQuery = Query::select([
                'terms.term_id',
                'terms.name as term_name',
                'SUM(postmeta.meta_value) AS words',
                'COUNT(DISTINCT posts.ID) AS posts'
            ])
            ->from('postmeta')
            ->join('posts', ['postmeta.post_id', 'posts.ID'])
            ->join('term_relationships', ['posts.ID', 'term_relationships.object_id'])
            ->join('term_taxonomy', ['term_relationships.term_taxonomy_id', 'term_taxonomy.term_taxonomy_id'])
            ->join('terms', ['term_taxonomy.term_id', 'terms.term_id'])
            ->where('postmeta.meta_key', '=', 'wp_statistics_words_count')
            ->where('term_taxonomy.taxonomy', 'IN', $args['taxonomy'])
            ->where('posts.post_type', 'IN', $args['post_type'])
            ->where('posts.post_author', '=', $args['author_id'])
            ->whereDate('posts.post_date', $args['date'])
            ->groupBy(['terms.term_id'])
            ->getQuery();

        $result = Query::select([
                'terms.term_id',
                'terms.name as term_name',
                'SUM(pages.count) AS views',
                'COALESCE(postmeta.posts, 0) AS posts',
                'COALESCE(postmeta.words, 0) AS words',
                'COALESCE(SUM(pages.count) / postmeta.posts, 0) AS avg_views',
                'COALESCE(postmeta.words / postmeta.posts, 0) AS avg_words'
            ])
            ->from('posts')
            ->join('term_relationships', ['posts.ID', 'term_relationships.object_id'])
            ->join('term_taxonomy', ['term_relationships.term_taxonomy_id', 'term_taxonomy.term_taxonomy_id'])
            ->join('terms', ['term_taxonomy.term_id', 'terms.term_id'])
            ->join('pages', ['pages.id', 'posts.ID'])
            ->joinQuery($wordsQuery, ['postmeta.term_id', 'terms.term_id'], 'postmeta', 'LEFT')
            ->where('term_taxonomy.taxonomy', 'IN', $args['taxonomy'])
            ->where('posts.post_type', 'IN', $args['post_type'])
            ->where('posts.post_author', '=', $args['author_id'])
            ->whereDate($args['date_field'], $args['date'])
            ->groupBy(['term_id'])
            ->orderBy($args['order_by'], $args['order'])
            ->perPage($args['page'], $args['per_page'])
            ->getAll();

        return $result;
    }
}