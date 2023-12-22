<?php

namespace WP_STATISTICS\MetaBox;

class about
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
        $args = apply_filters('wp_statistics_meta_about_post_args', $args);

        include WP_STATISTICS_DIR . 'includes/admin/templates/meta-box/about.php';
    }

}