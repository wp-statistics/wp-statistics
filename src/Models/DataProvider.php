<?php

namespace WP_Statistics\Models;

use WP_STATISTICS\DB;
use WP_STATISTICS\Helper;

/**
 * Todo object cache, hooks, filters, etc
 */
abstract class DataProvider
{

    /** @var wpdb $db */
    protected $db;

    public function __construct()
    {
        global $wpdb;

        $this->db = $wpdb;
    }

    /**
     * Generates SQL conditions based on the given arguments.
     *
     * @param array $args An array of arguments to generate the SQL conditions.
     * @return string The generated SQL conditions.
     */
    protected function generateSqlConditions($args)
    {
        $sql = '';

        // Post type condition
        if (!empty($args['post_type'])) {
            $sql .= $this->db->prepare(" AND post_type = '%s'", $args['post_type']);
        } else {
            $postTypes = "'" . implode("', '", Helper::get_list_post_type()) . "'";
            $sql       .= " AND post_type IN ($postTypes)";
        }

        // Date condition
        if (!empty($args['from']) && !empty($args['to'])) {
            $sql .= $this->db->prepare(' AND (Date(post_date) BETWEEN %s AND %s)', $args['from'], $args['to']);
        }

        return $sql;
    }

    protected function execute($sql)
    {
        return $this->db->query($sql);
    }

    protected function getVar($sql)
    {
        return $this->db->get_var($sql);
    }

    protected function lastQuery()
    {
        return $this->db->last_query;
    }

    protected function visitorTable()
    {
        return DB::table('visitor');
    }

    protected function visitTable()
    {
        return DB::table('visit');
    }
}