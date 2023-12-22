<?php

namespace WP_STATISTICS\MetaBox;

class quickstats
{
    /**
     * Get Quick States Meta Box Data
     *
     * @param array $args
     * @return array
     */
    public static function get($args = array())
    {
        /**
         * Filters the args used from metabox for query stats
         *
         * @param array $args The args passed to query stats
         * @since 14.2.1
         *
         */
        $args = apply_filters('wp_statistics_meta_box_quickstats_args', $args);

        return summary::getSummaryHits(array('user-online', 'visitors', 'visits', 'hit-chart'));
    }

}