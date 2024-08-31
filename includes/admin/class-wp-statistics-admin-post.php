<?php

namespace WP_STATISTICS;

use WP_Statistics\MiniChart\WP_Statistics_Mini_Chart_Settings;
use WP_Statistics\Models\HistoricalModel;
use WP_Statistics\Models\ViewsModel;
use WP_Statistics\Models\VisitorsModel;
use WP_Statistics\Service\Admin\MiniChart\MiniChartHelper;
use WP_Statistics\Utils\Request;

class Admin_Post
{
    /**
     * Mini-chart helper class.
     *
     * @var MiniChartHelper
     */
    private $miniChartHelper;

    /**
     * Post type of posts retrieved from `Pages::get_post_type()`.
     *
     * @var string
     */
    private $postType;

    /**
     * Actual post type of posts (without the "post_type_" prefix).
     *
     * @var string
     */
    private $actualPostType;

    /**
     * Is this post type selected in Mini-chart add-on's options?
     *
     * @var bool
     */
    private $isCurrentPostTypeSelected;

    /**
     * A static "Unlock!" button that will be displayed when Mini-chart add-on is not active.
     *
     * @var string
     */
    private $previewChartUnlockHtml;

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
        $this->miniChartHelper = new MiniChartHelper();

        $this->previewChartUnlockHtml = sprintf(
            '<div class="wps-admin-column__unlock"><a href="%s" target="_blank"><span class="wps-admin-column__unlock__text">%s</span>
                <span class="wps-admin-column__unlock__lock"></span>
                <span class="wps-admin-column__unlock__img"></span></a></div>',
            'https://wp-statistics.com/product/wp-statistics-mini-chart?utm_source=wp-statistics&utm_medium=link&utm_campaign=mini-chart',
            __('Unlock', 'wp-statistics')
        );

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
        $columns['wp-statistics-post-hits'] = $this->miniChartHelper->getLabel();
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
        if ($column_name !== 'wp-statistics-post-hits') {
            return;
        }

        // Initialize class attributes only once (since all posts in the list have the same post type)
        if (empty($this->postType)) {
            $this->postType = Pages::get_post_type($post_id);

            // Remove "post_type_" prefix from the CPT name because of incompatibility with WP Statistics MiniChart
            $this->actualPostType = $this->postType;
            if (strpos($this->actualPostType, 'post_type_') === 0) {
                $this->actualPostType = substr($this->actualPostType, strlen('post_type_'));
            }

            // Check if current post type is selected in Mini-chart add-on's options
            $miniChartSettings               = class_exists(WP_Statistics_Mini_Chart_Settings::class) ? get_option(WP_Statistics_Mini_Chart_Settings::get_instance()->setting_name) : '';
            $this->isCurrentPostTypeSelected = !empty($miniChartSettings) && !empty($miniChartSettings["active_mini_chart_{$this->actualPostType}"]);
        }

        // Calculate stats if `count_display` is not disabled
        $hitCount = 0;
        if ($this->miniChartHelper->getCountDisplay() !== 'disabled')
        {
            $from = date('Y-m-d', 0);
            $to   = date('Y-m-d');
            $args = [
                'post_id'       => $post_id,
                'resource_type' => Pages::checkIfPageIsHome($post_id) ? 'home' : $this->postType,
                'date'          => ['from' => $from, 'to' => $to],
            ];

            if ($this->miniChartHelper->getCountDisplay() === 'date_range') {
                $from         = TimeZone::getTimeAgo(intval(Option::getByAddon('date_range', 'mini_chart', '14')));
                $args['date'] = ['from' => $from, 'to' => $to];
            }

            if ($this->miniChartHelper->getChartMetric() === 'visitors') {
                $visitorsModel = new VisitorsModel();
                $hitCount      = $visitorsModel->countVisitors($args);
            } else {
                $viewsModel = new ViewsModel();
                $hitCount   = $viewsModel->countViewsFromPagesOnly($args);

                // Consider historical if `count_display` is equal to 'total'
                if ($this->miniChartHelper->getCountDisplay() === 'total') {
                    $historicalModel = new HistoricalModel();
                    $hitCount       += intval($historicalModel->countUris(['page_id' => $post_id, 'uri' => wp_make_link_relative(get_permalink($post_id))]));
                }
            }
        }

        if (is_numeric($hitCount)) {
            if (!$this->miniChartHelper->isMiniChartActive() || $this->isCurrentPostTypeSelected) {
                // If add-on is not active, this line will display the "Unlock!" button
                // If add-on is active but current post type is not selected in the settings, nothing will be displayed
                echo apply_filters("wp_statistics_before_hit_column_{$this->actualPostType}", $this->previewChartUnlockHtml, $post_id, $this->postType); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            }

            echo sprintf(
                // translators: 1 & 2: CSS class - 3: Either "Visitors" or "Views" - 4: Link to content analytics page - 5: CSS class - 6: Hits count.
                '<div class="%s"><span class="%s">%s</span> <a href="%s" class="wps-admin-column__link %s">%s</a></div>',
                $this->miniChartHelper->getCountDisplay() === 'disabled' ? 'wps-hide' : '',
                $this->miniChartHelper->isMiniChartActive() ? '' : 'wps-hide',
                $this->miniChartHelper->getLabel(),
                esc_url(Menus::admin_url('content-analytics', ['post_id' => $post_id, 'type' => 'single', 'from' => Request::get('from', $from), 'to' => Request::get('to', $to)])),
                $this->miniChartHelper->isMiniChartActive() ? '' : 'wps-admin-column__unlock-count',
                esc_html(number_format($hitCount))
            );
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
                $historicalSubQuery = '';
                if (!empty($dateCondition)) {
                    $dateCondition = "AND `pages`.`date` $dateCondition";
                } else {
                    // Consider historical for total views
                    $historicalSubQuery = ' + IFNULL((SELECT SUM(`historical`.`value`) FROM ' . DB::table('historical') . ' AS `historical` WHERE `historical`.`page_id` = ' . $wpdb->posts . '.`ID` AND `historical`.`uri` LIKE CONCAT("%", ' . $wpdb->posts . '.`post_name`, "/")), 0)';
                }

                $clauses['fields'] .= ', ((SELECT SUM(`pages`.`count`) FROM ' . DB::table('pages') . ' AS `pages` WHERE (`pages`.`type` IN ("page", "post", "product") OR `pages`.`type` LIKE "post_type_%") AND ' . $wpdb->posts . '.`ID` = `pages`.`id` ' . $dateCondition . ')' . $historicalSubQuery . ') AS `post_hits_sortable` ';
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
}

new Admin_Post;
