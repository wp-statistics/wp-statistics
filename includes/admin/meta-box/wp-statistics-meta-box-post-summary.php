<?php

namespace WP_STATISTICS\MetaBox;

use WP_Statistics\Components\View;
use WP_Statistics\Service\Admin\Posts\PostsManager;

class post_summary
{
    /**
     * Returns post summary meta-box.
     *
     * @param   array   $args
     *
     * @return  array
     *
     * @throws  \Exception
     */
    public static function get($args = [])
    {
        /**
         * Filters the args used from metabox for stats query.
         *
         * @param   array   $args   The args passed to stats query.
         *
         * @since   14.10
         */
        $args = apply_filters('wp_statistics_meta_box_post_summary_args', $args);

        // Check if post ID is set
        if (empty($args['ID']) || intval($args['ID']) < 1) {
            return ['content' => __('This post is not yet published.', 'wp-statistics')];
        }

        // Get post information
        $post = get_post($args['ID']);
        if (empty($post)) {
            return ['content' => __('Invalid post!', 'wp-statistics')];
        }

        if ($post->post_status != 'publish' && $post->post_status != 'private') {
            return ['content' => __('This post is not yet published.', 'wp-statistics')];
        }

        $postSummary = PostsManager::getPostStatisticsSummary($post);
        if (empty($postSummary)) {
            return ['content' => __('Invalid post summary!', 'wp-statistics')];
        }

        // Basic Chart Data
        $response = [
            'summary' => $postSummary,
        ];

        $response['output'] = apply_filters(
            'wp_statistics_meta_box_post_summary',
            View::load('components/meta-box/post-summary', ['summary' => $postSummary], true),
            $post
        );

        return $response;
    }
}
