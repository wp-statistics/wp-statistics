<?php

namespace WP_STATISTICS;

use WP_Statistics\MiniChart\WP_Statistics_Mini_Chart_Settings;
use WP_Statistics\Models\HistoricalModel;
use WP_Statistics\Models\ViewsModel;
use WP_Statistics\Models\VisitorsModel;
use WP_Statistics\Utils\Request;

class Admin_Post
{
    /**
     * Hits Chart Post/page Meta Box
     *
     * @var string
     */
    public static $hits_chart_post_meta_box = 'post';

    /**
     * Admin_Post constructor.
     */
    public function __construct()
    {

        // Add Hits Column in All Admin Post-Type Wp_List_Table
        if (User::Access('read') and !Option::get('disable_column')) {
            add_action('admin_init', array($this, 'init'));
        }

        // Add WordPress Post/Page Hit Chart Meta Box in edit Page
        if (User::Access('read') and !Option::get('disable_editor')) {
            add_action('add_meta_boxes', array($this, 'define_post_meta_box'));
        }

        // Add Post Hit Number in Publish Meta Box in WordPress Edit a post/page
        add_action('post_submitbox_misc_actions', array($this, 'post_hit_misc'));

        // Remove Post Hits when Post Id deleted
        add_action('deleted_post', array($this, 'modify_delete_post'));
    }

    /**
     * Init Hook
     */
    public function init()
    {
        foreach (Helper::get_list_post_type() as $type) {
            add_action('manage_' . $type . '_posts_columns', array($this, 'add_hit_column'), 10, 2);
            add_action('manage_' . $type . '_posts_custom_column', array($this, 'render_hit_column'), 10, 2);
            add_filter('manage_edit-' . $type . '_sortable_columns', array($this, 'modify_sortable_columns'));
        }
        add_filter('posts_clauses', array($this, 'modify_order_by_hits'), 10, 2);
    }

    /**
     * Add a custom column to post/pages for hit statistics.
     *
     * @param array $columns Columns
     * @return array Columns
     */
    public function add_hit_column($columns)
    {
        $columns['wp-statistics-post-hits'] = Helper::checkMiniChartOption('metric', 'visitors', 'visitors') ? __('Visitors', 'wp-statistics') : __('Views', 'wp-statistics');
        return $columns;
    }

    /**
     * Render the custom column on the post/pages lists.
     *
     * @param string $column_name Column Name
     * @param string $post_id Post ID
     */
    public function render_hit_column($column_name, $post_id)
    {
        if ($column_name == 'wp-statistics-post-hits') {
            $post_type   = Pages::get_post_type($post_id);
            $hitPostType = Pages::checkIfPageIsHome($post_id) ? 'home' : $post_type;
            $args        = ['post_id' => $post_id, 'resource_type' => $hitPostType];
            $from        = date('Y-m-d', 0);
            $to          = date('Y-m-d');

            if (Helper::checkMiniChartOption('count_display', 'date_range', 'total')) {
                $from         = TimeZone::getTimeAgo(intval(Option::getByAddon('date_range', 'mini_chart', '14')));
                $args['date'] = ['from' => $from, 'to' => date('Y-m-d')];
            }

            if (Helper::checkMiniChartOption('metric', 'visitors', 'visitors')) {
                $visitorsModel = new VisitorsModel();
                $hitCount      = $visitorsModel->countVisitors($args);
            } else {
                $viewsModel = new ViewsModel();
                $hitCount   = $viewsModel->countViews($args);

                $historicalModel = new HistoricalModel();
                $hitCount       += $historicalModel->countUris(['page_id' => $post_id, 'uri' => wp_make_link_relative(get_permalink($post_id))]);
            }

            if (is_numeric($hitCount)) {
                $preview_chart_unlock_html = sprintf('<div class="wps-admin-column__unlock"><a href="%s" target="_blank"><span class="wps-admin-column__unlock__text">%s</span><img class="wps-admin-column__unlock__lock" src="%s"/><img class="wps-admin-column__unlock__img" src="%s"/></a></div>',
                    'https://wp-statistics.com/product/wp-statistics-mini-chart?utm_source=wp-statistics&utm_medium=link&utm_campaign=mini-chart',
                    __('Unlock This Feature!', 'wp-statistics'),
                    WP_STATISTICS_URL . 'assets/images/mini-chart-posts-lock.svg',
                    WP_STATISTICS_URL . 'assets/images/mini-chart-posts-preview.svg'
                );

                // Remove post_type_ from prefix of custom post type because of incompatibility with WP Statistics MiniChart
                $actual_post_type = $post_type;
                if (strpos($actual_post_type, "post_type_") === 0) {
                    $actual_post_type = substr($actual_post_type, strlen("post_type_"));
                }

                $setting = class_exists(WP_Statistics_Mini_Chart_Settings::class) ? get_option(WP_Statistics_Mini_Chart_Settings::get_instance()->setting_name) : '';
                if (
                    !Helper::isAddOnActive('mini-chart') ||
                    (!empty($setting) && !empty($setting['active_mini_chart_' . $actual_post_type]))
                ) {
                    // If add-on is not active, this line will display the "Unlock This Feature!" button
                    // If add-on is active but current post type is not selected in the settings, nothing will be displayed
                    echo apply_filters("wp_statistics_before_hit_column_{$actual_post_type}", $preview_chart_unlock_html, $post_id, $post_type); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                }

                echo sprintf('<div class="%s"><span class="%s">%s</span> <a href="%s" class="wps-admin-column__link %s">%s</a></div>',  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                    Helper::isAddOnActive('mini-chart') && Option::getByAddon('count_display', 'mini_chart', 'total') === 'disabled' ? 'wps-hide' : '',
                    Helper::isAddOnActive('mini-chart') ? '' : 'wps-hide',
                    Helper::checkMiniChartOption('metric', 'visitors', 'visitors') ? esc_html__('Visitors:', 'wp-statistics') : esc_html__('Views:', 'wp-statistics'),
                    esc_url(Menus::admin_url('content-analytics', ['post_id' => $post_id, 'type' => 'single', 'from' => Request::get('from', $from), 'to' => Request::get('to', $to)])),
                    Helper::isAddOnActive('mini-chart') ? '' : 'wps-admin-column__unlock-count',
                    esc_html(number_format($hitCount)) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                );
            }

        }
    }

    /**
     * Added Sortable Params
     *
     * @param $columns
     * @return mixed
     */
    public function modify_sortable_columns($columns)
    {
        $columns['wp-statistics-post-hits'] = 'hits';
        return $columns;
    }

    /**
     * Sort Post By Hits
     *
     * @param $clauses
     * @param $query
     */
    public function modify_order_by_hits($clauses, $query)
    {
        global $wpdb;

        // Check in Admin
        if (!is_admin()) {
            return;
        }

        // If order-by.
        if (isset($query->query_vars['orderby']) and isset($query->query_vars['order']) and $query->query_vars['orderby'] == 'hits') {
            // Get global Variable
            $order = $query->query_vars['order'];

            // Add date condition if needed
            $dateCondition = '';
            if (Helper::checkMiniChartOption('count_display', 'date_range', 'total')) {
                $dateCondition = 'BETWEEN "' . TimeZone::getTimeAgo(intval(Option::getByAddon('date_range', 'mini_chart', '14'))) . '" AND "' . date('Y-m-d') . '"';
            }

            // Select Field
            if (Helper::checkMiniChartOption('metric', 'visitors', 'visitors')) {
                if (!empty($dateCondition)) {
                    $dateCondition = "AND `visitor_relationships`.`date` $dateCondition";
                }

                $clauses['fields'] .= ', (SELECT COUNT(DISTINCT `visitor_id`) FROM ' . DB::table('visitor_relationships') . ' AS `visitor_relationships` LEFT JOIN ' . DB::table('pages') . ' AS `pages` ON `visitor_relationships`.`page_id` = `pages`.`page_id` WHERE (`pages`.`type` IN ("page", "post", "product") OR `pages`.`type` LIKE "post_type_%") AND ' . $wpdb->posts . '.`ID` = `pages`.`id` ' . $dateCondition . ') AS `post_hits_sortable` ';
            } else {
                if (!empty($dateCondition)) {
                    $dateCondition = "AND `pages`.`date` $dateCondition";
                }

                $clauses['fields'] .= ', (SELECT SUM(`pages`.`count`) FROM ' . DB::table('pages') . ' AS `pages` WHERE (`pages`.`type` IN ("page", "post", "product") OR `pages`.`type` LIKE "post_type_%") AND ' . $wpdb->posts . '.`ID` = `pages`.`id` ' . $dateCondition . ') AS `post_hits_sortable` ';
            }

            // And order by it.
            $clauses['orderby'] = " COALESCE(`post_hits_sortable`, 0) $order";
        }

        return $clauses;
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

    /**
     * Add Post Hit Number in Publish Meta Box in WordPress Edit a post/page
     */
    public function post_hit_misc()
    {
        global $post;

        $hitCount = 0;
        if (Helper::checkMiniChartOption('metric', 'visitors', 'visitors')) {
            $visitorsModel = new VisitorsModel();
            $hitCount      = $visitorsModel->countVisitors(['post_id' => $post->ID]);
        } else {
            $viewsModel = new ViewsModel();
            $hitCount   = $viewsModel->countViews(['post_id' => $post->ID]);
        }

        if ($post->post_status == 'publish') {
            echo sprintf('<div class="misc-pub-section misc-pub-hits">%s <a href="%s">%s</a></div>',
                Helper::checkMiniChartOption('metric', 'visitors', 'visitors') ? esc_html__('Visitors:', 'wp-statistics') : esc_html__('Views:', 'wp-statistics'),
                esc_url(Menus::admin_url('content-analytics', ['post_id' => $post->ID, 'type' => 'single', 'from' => Request::get('from', date('Y-m-d', 0)), 'to' => Request::get('to', date('Y-m-d'))])),
                esc_html(number_format($hitCount))
            );
        }
    }

    /**
     * Define Hit Chart Meta Box
     */
    public function define_post_meta_box()
    {

        // Get MetaBox information
        $metaBox = Meta_Box::getList(self::$hits_chart_post_meta_box);

        // Add MEtaBox To all Post Type
        foreach (Helper::get_list_post_type() as $screen) {
            add_meta_box(Meta_Box::getMetaBoxKey(self::$hits_chart_post_meta_box), $metaBox['name'], Meta_Box::LoadMetaBox(self::$hits_chart_post_meta_box), $screen, 'normal', 'high', array('__block_editor_compatible_meta_box' => true, '__back_compat_meta_box' => false));
        }
    }

}

new Admin_Post;
