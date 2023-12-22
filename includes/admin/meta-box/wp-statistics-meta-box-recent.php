<?php

namespace WP_STATISTICS\MetaBox;

use WP_STATISTICS\Visitor;

class recent
{

    public static function get($args = array())
    {
        /**
         * Filters the args used from metabox for query stats
         *
         * @param array $args The args passed to query stats
         * @since 14.2.1
         *
         */
        $args = apply_filters('wp_statistics_meta_box_recent_args', $args);

        // Prepare Response
        try {
            $response = Visitor::get($args);
        } catch (\Exception $e) {
            \WP_Statistics::log($e->getMessage());
            $response = array();
        }

        // Check For No Data Meta Box
        if (count($response) < 1) {
            $response['no_data'] = 1;
        }

        // Response
        return $response;
    }

}