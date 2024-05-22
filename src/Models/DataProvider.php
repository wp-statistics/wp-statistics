<?php

namespace WP_Statistics\Models;

use WP_Statistics\Utils\Query;

/**
 * Todo object cache, consider historical, hooks, filters, etc
 */
abstract class DataProvider
{
    /** @var Query $query */
    protected $query;

    public function __construct()
    {
        $this->query = Query::class;
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