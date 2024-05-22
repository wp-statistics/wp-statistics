<?php

namespace WP_Statistics\Models;

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
}