<?php

namespace WP_Statistics\Models;

use WP_STATISTICS\Helper;
use WP_Statistics\Utils\Query;
use WP_Statistics\Components\BaseModel;

class TaxonomyModel extends BaseModel
{
    public function countTaxonomiesPosts($args = [], $bypassCache = false)
    {
        $args = $this->parseArgs($args, [
            'date'      => '',
            'post_type' => Helper::get_list_post_type(),
            'page'      => 1,
            'per_page'  => 5,
            'author_id' => '',
            'taxonomy'  => array_keys(Helper::get_list_taxonomy(true))
        ]);

        $result = Query::select([
                'taxonomy', 
                'terms.term_id',
                'terms.name',
                'COUNT(posts.ID) as post_count'
            ])
            ->from('term_taxonomy')
            ->join('terms', ['term_taxonomy.term_id', 'terms.term_id'])
            ->join('term_relationships', ['term_relationships.term_taxonomy_id', 'term_taxonomy.term_taxonomy_id'], [], 'LEFT')
            ->join('posts', ['posts.ID', 'term_relationships.object_id'], [['posts.post_type' , 'IN', $args['post_type']], ['posts.post_status', '=', 'publish']], 'LEFT')
            ->where('posts.post_author', '=', $args['author_id'])
            ->where('term_taxonomy.taxonomy', 'IN', $args['taxonomy'])
            ->whereDate('posts.post_date', $args['date'])
            ->groupBy(['taxonomy', 'terms.term_id','terms.name'])
            ->orderBy(['term_taxonomy.taxonomy', 'post_count'])
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
                ];
            }

            $result = $taxonomies;
        }

        return $result ? $result : [];
    }
}