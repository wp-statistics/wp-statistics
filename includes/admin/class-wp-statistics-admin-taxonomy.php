<?php

namespace WP_STATISTICS;

use WP_Statistics\Service\Admin\Posts\HitColumnHandler;

class Admin_Taxonomy
{
    /**
     * Admin_Taxonomy constructor.
     */
    public function __construct()
    {

        // Add Hits Column in All Admin Post-Type Wp_List_Table
        if (User::Access('read') and !Option::get('disable_column')) {
            add_action('admin_init', array($this, 'init'));
        }

        // Remove Term Hits when Term Id deleted
        add_action('delete_term', array($this, 'modify_delete_term'), 10, 2);
    }

    /**
     * Init Hook
     */
    public function init()
    {

        // Check Active
        if (!apply_filters('wp_statistics_show_taxonomy_hits', true)) {
            return;
        }

        $hitColumnHandler = new HitColumnHandler(true);

        // Add Column
        foreach (Helper::get_list_taxonomy() as $tax => $name) {
            add_action('manage_edit-' . $tax . '_columns', array($hitColumnHandler, 'addHitColumn'), 10, 2);
            add_filter('manage_' . $tax . '_custom_column', array($hitColumnHandler, 'renderTaxHitColumn'), 10, 3);
            add_filter('manage_edit-' . $tax . '_sortable_columns', array($hitColumnHandler, 'modifySortableColumns'));
        }
        add_filter('terms_clauses', array($this, 'modify_order_by_hits'), 10, 3);
    }

    /**
     * Sort Taxonomy By Hits
     *
     * @param $clauses
     * @param $query
     */
    public function modify_order_by_hits($clauses, $taxonomy, $query)
    {

        // Check in Admin
        if (!is_admin()) {
            return;
        }

        // If order-by.
        if (isset($query['orderby']) and $query['orderby'] == 'hits') {
            // Select Field
            $clauses['fields'] .= ", (select SUM(" . DB::table("pages") . ".count) from " . DB::table("pages") . " where (" . DB::table("pages") . ".type = 'category' OR " . DB::table("pages") . ".type = 'post_tag' OR " . DB::table("pages") . ".type = 'tax') AND t.term_id = " . DB::table("pages") . ".id) as tax_hist_sortable ";

            // And order by it.
            $clauses['orderby'] = " ORDER BY coalesce(tax_hist_sortable, 0)";
        }

        return $clauses;
    }

    /**
     * Delete All Term Hits When Term is Deleted
     *
     * @param $term
     * @param $term_id
     */
    public static function modify_delete_term($term, $term_id)
    {
        global $wpdb;
        $wpdb->query(
            $wpdb->prepare("DELETE FROM `" . DB::table('pages') . "` WHERE `id` = %d AND (`type` = 'category' OR `type` = 'post_tag' OR `type` = 'tax');", esc_sql($term_id))
        );
    }
}

new Admin_Taxonomy;
