<?php

namespace WP_Statistics\Models;


/**
 * Todo object cache, consider historical, hooks, filters, etc
 */
abstract class DataProvider
{
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