<?php

namespace WP_Statistics\Models;

use WP_STATISTICS\Helper;
use WP_Statistics\Utils\Query;

class TaxonomyModel extends DataProvider
{
    public function countTaxonomiesPosts($args = [], $bypassCache = false)
    {
        $args = $this->parseArgs($args, [
            'from'      => '',
            'to'        => '',
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
            ->whereDate('posts.post_date', [$args['from'], $args['to']])
            ->groupBy(['taxonomy', 'terms.term_id','terms.name'])
            ->orderBy(['term_taxonomy.taxonomy', 'post_count'])
            ->perPage($args['page'], $args['per_page'])
            ->bypassCache($bypassCache)
            ->getAll();

        return $result;
    }
}