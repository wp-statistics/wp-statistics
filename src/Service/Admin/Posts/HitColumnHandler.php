<?php

namespace WP_Statistics\Service\Admin\Posts;

use WP_STATISTICS\DB;
use WP_STATISTICS\Menus;
use WP_Statistics\MiniChart\WP_Statistics_Mini_Chart_Settings;
use WP_Statistics\Models\HistoricalModel;
use WP_Statistics\Models\ViewsModel;
use WP_Statistics\Models\VisitorsModel;
use WP_STATISTICS\Option;
use WP_STATISTICS\Pages;
use WP_Statistics\Service\Admin\MiniChart\MiniChartHelper;
use WP_STATISTICS\TimeZone;
use WP_Statistics\Utils\Request;

/**
 * This class will add, render, modify sort and modify order by post hits column in posts list pages.
 */
class HitColumnHandler
{
    /**
     * Mini-chart helper class.
     *
     * @var MiniChartHelper
     */
    private $miniChartHelper;

    /**
     * Arguments to use in the visitors and views models when fetching hits stats.
     *
     * @var array
     */
    private $hitArgs;

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

    public function __construct()
    {
        $this->miniChartHelper = new MiniChartHelper();
    }

    /**
     * Adds a custom column to post/pages for hit statistics.
     *
     * @param array $columns
     *
     * @return array
     *
     * @hooked action: `manage_{$type}_posts_columns` - 10
     */
    public function addHitColumn($columns)
    {
        $columns['wp-statistics-post-hits'] = $this->miniChartHelper->getLabel();

        return $columns;
    }

    /**
     * Renders the custom column on the post/pages lists.
     *
     * @param string $columnName
     * @param string $postId
     *
     * @return void
     *
     * @hooked action: `manage_{$type}_posts_columns` - 10
     */
    public function renderHitColumn($columnName, $postId)
    {
        // Exit early if the column is not the one we're interested in
        if ($columnName !== 'wp-statistics-post-hits') {
            return;
        }

        // Initialize class attributes only once (since all posts in the list have the same post type)
        if (empty($this->postType)) {
            $this->postType = Pages::get_post_type($postId);

            // Remove "post_type_" prefix from the CPT name because of incompatibility with WP Statistics MiniChart
            $this->actualPostType = $this->postType;
            if (strpos($this->actualPostType, 'post_type_') === 0) {
                $this->actualPostType = substr($this->actualPostType, strlen('post_type_'));
            }

            // Check if current post type is selected in Mini-chart add-on's options
            $miniChartSettings               = class_exists(WP_Statistics_Mini_Chart_Settings::class) ? get_option(WP_Statistics_Mini_Chart_Settings::get_instance()->setting_name) : '';
            $this->isCurrentPostTypeSelected = !empty($miniChartSettings) && !empty($miniChartSettings["active_mini_chart_{$this->actualPostType}"]);
        }

        $hitCount = $this->calculateHitCount($postId);
        echo $this->getHitColumnContent($hitCount, $postId);
    }

    /**
     * Adds hits column to sortable columns.
     *
     * @param array $columns
     *
     * @return array
     *
     * @hooked filter: `manage_edit-{$type}_sortable_columns` - 10
     */
    public function modifySortableColumns($columns)
    {
        $columns['wp-statistics-post-hits'] = 'hits';

        return $columns;
    }

    /**
     * Modifies query clauses when posts are sorted by hits column.
     *
     * @param array $args Posts query clauses.
     * @param \WP_Query $wpQuery Current posts query.
     *
     * @return array Updated posts query clauses.
     *
     * @hooked filter: `posts_clauses` - 10
     */
    public function handleOrderByHits($clauses, $wpQuery)
    {
        if (!is_admin()) {
            return;
        }

        // If order-by.
        if (!isset($wpQuery->query_vars['orderby']) || !isset($wpQuery->query_vars['order']) || $wpQuery->query_vars['orderby'] != 'hits') {
            return $clauses;
        }

        global $wpdb;

        // Get global Variable
        $order = $wpQuery->query_vars['order'];

        // Add date condition if needed
        $dateCondition = '';
        if ($this->miniChartHelper->getCountDisplay() === 'date_range') {
            $dateCondition = 'BETWEEN "' . TimeZone::getTimeAgo(intval(Option::getByAddon('date_range', 'mini_chart', '14'))) . '" AND "' . date('Y-m-d') . '"';
        }

        // Select Field
        if ($this->miniChartHelper->getChartMetric() === 'visitors') {
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

        // Order by `post_hits_sortable`
        $clauses['orderby'] = " COALESCE(`post_hits_sortable`, 0) $order";

        return $clauses;
    }

    /**
     * Calculates hit count based on Mini-chart add-on's options.
     *
     * @param string $postId
     *
     * @return int|null
     */
    private function calculateHitCount($postId)
    {
        // Don't calculate stats if `count_display` is disabled
        if ($this->miniChartHelper->getCountDisplay() === 'disabled') {
            return null;
        }

        $this->hitArgs = [
            'post_id'       => $postId,
            'resource_type' => Pages::checkIfPageIsHome($postId) ? 'home' : $this->postType,
            'date'          => [
                'from' => date('Y-m-d', 0),
                'to'   => date('Y-m-d'),
            ],
        ];

        if ($this->miniChartHelper->getCountDisplay() === 'date_range') {
            $this->hitArgs['date'] = [
                'from' => TimeZone::getTimeAgo(intval(Option::getByAddon('date_range', 'mini_chart', '14'))),
                'to'   => date('Y-m-d'),
            ];
        }

        $hitCount = 0;
        if ($this->miniChartHelper->getChartMetric() === 'visitors') {
            $visitorsModel = new VisitorsModel();
            $hitCount      = $visitorsModel->countVisitors($this->hitArgs);
        } else {
            $viewsModel = new ViewsModel();
            $hitCount   = $viewsModel->countViewsFromPagesOnly($this->hitArgs);

            // Consider historical if `count_display` is equal to 'total'
            if ($this->miniChartHelper->getCountDisplay() === 'total') {
                $historicalModel = new HistoricalModel();
                $hitCount       += intval($historicalModel->countUris(['page_id' => $postId, 'uri' => wp_make_link_relative(get_permalink($postId))]));
            }
        }

        return $hitCount;
    }

    /**
     * Returns the content of the hit column.
     *
     * @param int $hitCount
     * @param string $postId
     *
     * @return string HTML markup.
     */
    private function getHitColumnContent($hitCount, $postId)
    {
        // If hit count is not a valid number, don't display anything
        if (!is_numeric($hitCount)) {
            return '';
        }

        $result = '';
        if (!$this->miniChartHelper->isMiniChartActive() || $this->isCurrentPostTypeSelected) {
            // If add-on is not active, this line will display the "Unlock!" button
            // If add-on is active but current post type is not selected in the settings, nothing will be displayed
            $result .= apply_filters("wp_statistics_before_hit_column_{$this->actualPostType}", $this->getPreviewChartUnlockHtml(), $postId, $this->postType);
        }

        $result .= sprintf(
            // translators: 1 & 2: CSS class - 3: Either "Visitors" or "Views" - 4: Link to content analytics page - 5: CSS class - 6: Hits count.
            '<div class="%s"><span class="%s">%s</span> <a href="%s" class="wps-admin-column__link %s">%s</a></div>',
            $this->miniChartHelper->getCountDisplay() === 'disabled' ? 'wps-hide' : '',
            $this->miniChartHelper->isMiniChartActive() ? '' : 'wps-hide',
            $this->miniChartHelper->getLabel(),
            esc_url(Menus::admin_url('content-analytics', ['post_id' => $postId, 'type' => 'single', 'from' => Request::get('from', $this->hitArgs['date']['from']), 'to' => Request::get('to', $this->hitArgs['date']['to'])])),
            $this->miniChartHelper->isMiniChartActive() ? '' : 'wps-admin-column__unlock-count',
            esc_html(number_format($hitCount))
        );

        return $result;
    }

    /**
     * Returns HTML markup for a static "Unlock!" button that will be displayed when Mini-chart add-on is not active.
     *
     * @return string
     */
    private function getPreviewChartUnlockHtml()
    {
        return sprintf(
            '<div class="wps-admin-column__unlock"><a href="%s" target="_blank"><span class="wps-admin-column__unlock__text">%s</span>
                <span class="wps-admin-column__unlock__lock"></span>
                <span class="wps-admin-column__unlock__img"></span></a></div>',
            'https://wp-statistics.com/product/wp-statistics-mini-chart?utm_source=wp-statistics&utm_medium=link&utm_campaign=mini-chart',
            __('Unlock', 'wp-statistics')
        );
    }
}
