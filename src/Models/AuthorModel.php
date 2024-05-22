<?php

namespace WP_Statistics\Models;

use WP_STATISTICS\Helper;
use WP_Statistics\Utils\QueryUtils;

class AuthorModel extends DataProvider
{
    /**
     * Calculates the average number of posts per author based on the given arguments.
     *
     * @param array $args An array of arguments to filter the count.
     * @return float|int The average number of posts per author. Returns 0 if no authors are found.
     */
    public function averagePostsPerAuthor($args = [])
    {
        $args = wp_parse_args($args, [
            'from'      => '',
            'to'        => '',
            'post_type' => '',
        ]);

        $sql = "SELECT COUNT(ID) FROM {$this->db->posts}";
        $sql .= QueryUtils::whereClause([
            [
                'field' => 'post_status',
                'value' => 'publish'
            ],
            [
                'field'     => 'post_date', 
                'operator'  => 'BETWEEN',
                'value'     => [$args['from'], $args['to']]
            ],
            [
                'field'     => 'post_type', 
                'operator'  => 'IN',
                'value'     => $args['post_type']
            ]
        ]);

        $totalPosts   = $this->getVar($sql);
        $totalAuthors = $this->count();

        return $totalPosts ? $totalPosts / $totalAuthors : 0;
    }

    /**
     * Counts the authors based on the given arguments. 
     * By default, it will return total number of authors.
     *
     * @param array $args An array of arguments to filter the count.
     * @return int The total number of distinct authors. Returns 0 if no authors are found.
     */
    public function count($args = [])
    {
        $args = wp_parse_args($args, [
            'from'      => '',
            'to'        => '',
            'post_type' => Helper::get_list_post_type()
        ]);

        $conditions = [
            [
                'field' => 'post_status',
                'value' => 'publish'
            ],
            [
                'field'     => 'post_type', 
                'operator'  => 'IN',
                'value'     => $args['post_type']
            ],
            [
                'field'     => 'post_date', 
                'operator'  => 'BETWEEN',
                'value'     => [$args['from'], $args['to']]
            ]
        ];

        $sql = "SELECT COUNT(DISTINCT post_author) FROM {$this->db->posts}";
        $sql .= QueryUtils::whereClause($conditions);

        $result = $this->getVar($sql);

        return $result ? $result : 0;
    }

}