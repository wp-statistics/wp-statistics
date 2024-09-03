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

        // Add Hits Column in All Admin Post-Type Wp_List_Table
        if (User::Access('read') and !Option::get('disable_column')) {
            add_action('admin_init', array($this, 'init'));
        }

        // Remove Post Hits when Post Id deleted
        add_action('deleted_post', array($this, 'modify_delete_post'));
    }

    /**
     * Init Hook
     */
    public function init()
    {
        $hitColumnHandler = new HitColumnHandler();

        foreach (Helper::get_list_post_type() as $type) {
            add_action('manage_' . $type . '_posts_columns', array($hitColumnHandler, 'addHitColumn'), 10, 2);
            add_action('manage_' . $type . '_posts_custom_column', array($hitColumnHandler, 'renderHitColumn'), 10, 2);
            add_filter('manage_edit-' . $type . '_sortable_columns', array($hitColumnHandler, 'modifySortableColumns'));
        }
        add_filter('posts_clauses', array($hitColumnHandler, 'handleOrderByHits'), 10, 2);
    }

    /**
     * Delete All Post Hits When Post is Deleted
     *
     * @param $post_id
     */
    public static function modify_delete_post($post_id)
    {
        global $wpdb;
        $wpdb->query(
            $wpdb->prepare("DELETE FROM `" . DB::table('pages') . "` WHERE `id` = %d AND (`type` = 'post' OR `type` = 'page' OR `type` = 'product');", esc_sql($post_id))
        );
    }
}

new Admin_Post;
