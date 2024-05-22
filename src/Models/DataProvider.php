<?php

namespace WP_Statistics\Models;

use WP_STATISTICS\DB;

/**
 * Todo object cache, consider historical, hooks, filters, etc
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
     * @param $args
     * @param $defaults
     * @return mixed|null
     */
    protected function parseArgs($args, $defaults = [])
    {
        $args = wp_parse_args($args, $defaults);

        return apply_filters('wp_statistics_data_{child-method-name}_args', $args);
    }

    protected function execute($sql)
    {
        return $this->db->get_results($sql);
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