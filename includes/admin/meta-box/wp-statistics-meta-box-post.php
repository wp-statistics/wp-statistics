<?php

namespace WP_STATISTICS\MetaBox;

use WP_STATISTICS\Admin_Template;

class post
{
    /**
     * Returns post meta-box.
     *
     * @param array $args
     * @return array
     * @throws \Exception
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
        $args = apply_filters('wp_statistics_meta_box_post_args', $args);

        // Set Not Publish Content
        $not_publish = array('content' => __('This post is not yet published.', 'wp-statistics'));

        // Check Isset POST ID
        if (!isset($args['ID']) || $args['ID'] < 1) {
            return $not_publish;
        }

        // Get Post Information
        $post = get_post($args['ID']);

        // Check Not Publish Post
        if ($post->post_status != 'publish' && $post->post_status != 'private') {
            return $not_publish;
        }

        $response = [
            'visitors' => apply_filters(
                'wp_statistics_meta_box_post_visitors',
                Admin_Template::get_template('meta-box/pages-visitors-preview', null, true),
                $post
            ),
        ];

        return $response;
    }
}
