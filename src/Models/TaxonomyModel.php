<?php

namespace WP_Statistics\Models;

use WP_STATISTICS\Helper;
use WP_Statistics\Utils\Query;
use WP_Statistics\Abstracts\BaseModel;

class TaxonomyModel extends BaseModel
{
    public function getTaxonomiesData($args = [], $bypassCache = false)
    {
        $args = $this->parseArgs($args, [
            'post_type' => Helper::get_list_post_type(),
            'order_by'  => ['term_taxonomy.taxonomy', 'post_count'],
            'taxonomy'  => array_keys(Helper::get_list_taxonomy(true)),
            'page'      => 1,
            'per_page'  => 5,
            'date'      => '',
            'author_id' => '',
            'order'     => ''
        ]);

        $result = Query::select([
                'taxonomy', 
                'terms.term_id',
                'terms.name',
                'COUNT(posts.ID) as post_count',
                'COALESCE(pages.count, 0) as views'
            ])
            ->from('term_taxonomy')
            ->join('terms', ['term_taxonomy.term_id', 'terms.term_id'])
            ->join('term_relationships', ['term_relationships.term_taxonomy_id', 'term_taxonomy.term_taxonomy_id'], [], 'LEFT')
            ->join('posts', ['posts.ID', 'term_relationships.object_id'], [['posts.post_type' , 'IN', $args['post_type']], ['posts.post_status', '=', 'publish']], 'LEFT')
            ->join('pages', ['pages.id', 'term_taxonomy.term_taxonomy_id'], [], 'LEFT')
            ->where('posts.post_author', '=', $args['author_id'])
            ->where('term_taxonomy.taxonomy', 'IN', $args['taxonomy'])
            ->whereDate('posts.post_date', $args['date'])
            ->whereDate('pages.date', $args['date'])
            ->groupBy(['taxonomy', 'terms.term_id','terms.name'])
            ->orderBy($args['order_by'], $args['order'])
            ->perPage($args['page'], $args['per_page'])
            ->bypassCache($bypassCache)
            ->getAll();

        if (!empty($result)) {
            $taxonomies = [];

            foreach ($result as $item) {
                $taxonomies[$item->taxonomy][] = [
                    'term_id'       => $item->term_id,
                    'term_name'     => $item->name,
                    'posts_count'   => $item->post_count,
                    'views'         => $item->views
                ];
            }

            $result = $taxonomies;
        }

        return $result ? $result : [];
    }

    public function getTopPublishingTerms($args = [], $bypassCache = false)
    {
        $args = $this->parseArgs($args, [
            'order_by'  => 'posts_count',
            'order'     => '',
            'page'      => 1,
            'per_page'  => 5,
            'date'      => '',
            'taxonomy'  => ''
        ]);

        $query = Query::select([
                'terms.term_id',
                'terms.name as term_name',
                'COUNT(posts.ID) AS posts_count'
            ])
            ->from('posts')
            ->join('term_relationships', ['posts.ID', 'term_relationships.object_id'])
            ->join('term_taxonomy', ['term_relationships.term_taxonomy_id', 'term_taxonomy.term_taxonomy_id'])
            ->join('terms', ['term_taxonomy.term_id', 'terms.term_id'])
            ->where('term_taxonomy.taxonomy', 'IN', $args['taxonomy'])
            ->whereDate('posts.post_date', $args['date'])
            ->groupBy(['term_id'])
            ->orderBy($args['order_by'], $args['order'])
            ->perPage($args['page'], $args['per_page'])
            ->bypassCache($bypassCache);

        $result = $query->getAll();

        return $result;
    }
}