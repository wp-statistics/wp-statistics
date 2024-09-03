<?php

namespace WP_STATISTICS;

use WP_Statistics\Service\Admin\Posts\HitColumnHandler;

class Admin_Post
{
    /**
     * Admin_Post constructor.
     */
    public function __construct()
    {
        // Add Hits column in edit lists of all post types
        if (User::Access('read') && !Option::get('disable_column')) {
            add_action('admin_init', [$this, 'init']);
        }

        // Remove post hits on post delete
        add_action('deleted_post', [$this, 'modifyDeletePost']);
    }

    /**
     * Initializes hits column.
     *
     * @return void
     *
     * @hooked action: `admin_init` - 10
     */
    public function init()
    {
        $hitColumnHandler = new HitColumnHandler();

        foreach (Helper::get_list_post_type() as $type) {
            add_action("manage_{$type}_posts_columns", [$hitColumnHandler, 'addHitColumn'], 10, 2);
            add_action("manage_{$type}_posts_custom_column", [$hitColumnHandler, 'renderHitColumn'], 10, 2);
            add_filter("manage_edit-{$type}_sortable_columns", [$hitColumnHandler, 'modifySortableColumns']);
        }

        add_filter('posts_clauses', [$hitColumnHandler, 'handleOrderByHits'], 10, 2);
    }

    /**
     * Deletes all post hits when the post is deleted.
     *
     * @param int $postId
     *
     * @return void
     */
    public static function modifyDeletePost($postId)
    {
        global $wpdb;

        $wpdb->query(
            $wpdb->prepare("DELETE FROM `" . DB::table('pages') . "` WHERE `id` = %d AND (`type` = 'post' OR `type` = 'page' OR `type` = 'product');", esc_sql($postId))
        );
    }
}

new Admin_Post;
