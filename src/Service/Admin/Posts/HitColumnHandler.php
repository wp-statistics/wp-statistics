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
use WP_Statistics\Traits\ObjectCacheTrait;
use WP_Statistics\Utils\Request;

/**
 * This class will add, render, modify sort and modify order by hits column in posts and taxonomies list pages.
 */
class HitColumnHandler
{
    use ObjectCacheTrait;

    /**
     * Mini-chart helper class.
     *
     * @var MiniChartHelper
     */
    private $miniChartHelper;

    /**
     * Hits column name.
     *
     * This will change in taxonomies lists.
     *
     * @var string
     */
    private $columnName = 'wp-statistics-post-hits';

    /**
     * Class constructor.
     *
     * @param bool $isTerm Is this instance handling a taxonomies list?
     */
    public function __construct($isTerm = false)
    {
        if ($isTerm) {
            $this->columnName = 'wp-statistics-tax-hits';
        }

        $this->miniChartHelper = new MiniChartHelper();
    }

    /**
     * Adds a custom column to posts/taxonomies lists for hits statistics.
     *
     * @param array $columns
     *
     * @return array
     *
     * @hooked action: `manage_{$type}_posts_columns` - 10
     * @hooked action: `manage_edit-{$tax}_columns` - 10
     */
    public function addHitColumn($columns)
    {
        // Handle WooCommerce sortable UI
        if (isset($columns['handle'])) {
            $cols = [];
            foreach ($columns as $key => $value) {
                if ($key == 'handle') {
                    $cols[$this->columnName] = $this->miniChartHelper->getLabel();
                }
                $cols[$key] = $value;
            }
            return $cols;
        }

        $columns[$this->columnName] = $this->miniChartHelper->getLabel();

        return $columns;
    }

    /**
     * Renders hits column in post/pages lists.
     *
     * @param string $columnName
     * @param int $postId
     *
     * @return void
     *
     * @hooked action: `manage_{$type}_posts_columns` - 10
     */
    public function renderHitColumn($columnName, $postId)
    {
        // Exit early if the column is not the one we're interested in
        if ($columnName !== $this->columnName) {
            return;
        }

        // Initialize class attributes only once (since all posts in the list have the same post type)
        if (!$this->isCacheSet('postType')) {
            $this->setCache('postType', Pages::get_post_type($postId));
        }

        $hitCount = $this->calculateHitCount($postId);
        echo $this->getHitColumnContent($hitCount, $postId);
    }

    /**
     * Renders hits column in taxonomies lists.
     *
     * @param string Column HTML output.
     * @param string $columnName
     * @param int $termId
     *
     * @return string HTML output.
     *
     * @hooked filter: `manage_{$tax}_custom_column` - 10
     */
    public function renderTaxHitColumn($output, $columnName, $termId)
    {
        // Exit early if the column is not the one we're interested in
        if ($columnName !== $this->columnName) {
            return $output;
        }

        $term = get_term($termId);

        // Initialize class attributes only once (since all terms in the list have the same taxonomy)
        if (!$this->isCacheSet('postType')) {
            $this->setCache('postType', (($term instanceof \WP_Term) && ($term->taxonomy === 'category' || $term->taxonomy === 'post_tag')) ? $term->taxonomy : 'tax');
        }

        $hitCount = $this->calculateHitCount($termId, $term);
        return $this->getHitColumnContent($hitCount, $termId, true);
    }

    /**
     * Adds hits column to sortable columns.
     *
     * @param array $columns
     *
     * @return array
     *
     * @hooked filter: `manage_edit-{$type}_sortable_columns` - 10
     * @hooked filter: `manage_edit-{$tax}_sortable_columns` - 10
     */
    public function modifySortableColumns($columns)
    {
        $columns[$this->columnName] = 'hits';

        return $columns;
    }

    /**
     * Modifies query clauses when posts are sorted by hits column.
     *
     * @param array $clauses Clauses for the query.
     * @param \WP_Query $wpQuery Current posts query.
     *
     * @return array Updated clauses for the query.
     *
     * @hooked filter: `posts_clauses` - 10
     */
    public function handlePostOrderByHits($clauses, $wpQuery)
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
                $historicalSubQuery = ' + IFNULL((SELECT SUM(`historical`.`value`) FROM ' . DB::table('historical') . ' AS `historical` WHERE `historical`.`page_id` = ' . $wpdb->posts . '.`ID` AND `historical`.`uri` LIKE CONCAT("%/", ' . $wpdb->posts . '.`post_name`, "/")), 0)';
            }

            $clauses['fields'] .= ', ((SELECT SUM(`pages`.`count`) FROM ' . DB::table('pages') . ' AS `pages` WHERE (`pages`.`type` IN ("page", "post", "product") OR `pages`.`type` LIKE "post_type_%") AND ' . $wpdb->posts . '.`ID` = `pages`.`id` ' . $dateCondition . ')' . $historicalSubQuery . ') AS `post_hits_sortable` ';
        }

        // Order by `post_hits_sortable`
        $clauses['orderby'] = " COALESCE(`post_hits_sortable`, 0) $order";

        return $clauses;
    }

    /**
     * Modifies query clauses when terms are sorted by hits column.
     *
     * @param array $clauses Clauses for the query.
     * @param array $taxonomies Taxonomy names.
     * @param array $args Term query arguments.
     *
     * @return array Updated clauses for the query.
     *
     * @hooked filter: `terms_clauses` - 10
     */
    public function handleTaxOrderByHits($clauses, $taxonomies, $args)
    {
        if (!is_admin()) {
            return;
        }

        // If order-by.
        if (!isset($args['orderby']) || $args['orderby'] !== 'hits') {
            return $clauses;
        }

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

            $clauses['fields'] .= ', (SELECT COUNT(DISTINCT `visitor_id`) FROM ' . DB::table('visitor_relationships') . ' AS `visitor_relationships` LEFT JOIN ' . DB::table('pages') . ' AS `pages` ON `visitor_relationships`.`page_id` = `pages`.`page_id` WHERE `pages`.`type` IN ("category", "post_tag", "tax") AND `t`.`term_id` = `pages`.`id` ' . $dateCondition . ') AS `tax_hits_sortable` ';
        } else {
            $historicalSubQuery = '';
            if (!empty($dateCondition)) {
                $dateCondition = "AND `pages`.`date` $dateCondition";
            } else {
                // Consider historical for total views
                $historicalSubQuery = ' + IFNULL((SELECT SUM(`historical`.`value`) FROM ' . DB::table('historical') . ' AS `historical` WHERE `historical`.`page_id` = `t`.`term_id` AND `historical`.`uri` LIKE CONCAT("%/", `t`.`slug`, "/")), 0)';
            }

            $clauses['fields'] .= ', ((SELECT SUM(`pages`.`count`) FROM ' . DB::table('pages') . ' AS `pages` WHERE `pages`.`type` IN ("category", "post_tag", "tax") AND `t`.`term_id` = `pages`.`id` ' . $dateCondition . ')' . $historicalSubQuery . ') AS `tax_hits_sortable` ';
        }

        // Order by `tax_hits_sortable`
        $clauses['orderby'] = " ORDER BY coalesce(`tax_hits_sortable`, 0)";

        return $clauses;
    }

    /**
     * Calculates hits count based on Mini-chart add-on's options.
     *
     * @param int $objectId Post or term ID.
     * @param \WP_Term $term If not empty, this method will calculate terms hits stats instead of posts hits stats.
     *
     * @return int|null
     */
    private function calculateHitCount($objectId, $term = null)
    {
        // Don't calculate stats if `count_display` is disabled
        if ($this->miniChartHelper->getCountDisplay() === 'disabled') {
            return null;
        }

        $hitArgs = [
            'post_id'       => $objectId,
            'resource_type' => Pages::checkIfPageIsHome($objectId) ? 'home' : $this->getCache('postType'),
            'date'          => [
                'from' => date('Y-m-d', 0),
                'to'   => date('Y-m-d'),
            ],
        ];

        // Change `resource_type` parameter if it's a term
        if (!empty($term)) {
            $hitArgs['resource_type'] = $this->getCache('postType');
        }

        if ($this->miniChartHelper->getCountDisplay() === 'date_range') {
            $hitArgs['date'] = [
                'from' => TimeZone::getTimeAgo(intval(Option::getByAddon('date_range', 'mini_chart', '14'))),
                'to'   => date('Y-m-d'),
            ];
        }

        // Cache hitArgs
        $this->setCache('hitArgs', $hitArgs);

        $hitCount = 0;
        if ($this->miniChartHelper->getChartMetric() === 'visitors') {
            $visitorsModel = new VisitorsModel();
            $hitCount      = $visitorsModel->countVisitors($hitArgs);
        } else {
            $viewsModel = new ViewsModel();
            $hitCount   = $viewsModel->countViewsFromPagesOnly($hitArgs);

            // Consider historical if `count_display` is equal to 'total'
            if ($this->miniChartHelper->getCountDisplay() === 'total') {
                $uri = empty($term) ? get_permalink($objectId) : get_term_link(intval($term->term_id), $term->taxonomy);
                $uri = !is_wp_error($uri) ? wp_make_link_relative($uri) : '';

                $historicalModel = new HistoricalModel();
                $hitCount       += $historicalModel->countUris(['page_id' => $objectId, 'uri' => $uri]);
            }
        }

        return $hitCount;
    }

    /**
     * Returns the content of hits column.
     *
     * @param int $hitCount
     * @param int $objectId Post or term ID.
     * @param bool $isTerm Is this column being rendered in taxonomies list?
     *
     * @return string HTML markup.
     */
    private function getHitColumnContent($hitCount, $objectId, $isTerm = false)
    {
        // Remove only the first occurrence of "post_type_" from `postType` attribute
        $actualPostType = $this->getCache('postType');
        if (strpos($actualPostType, 'post_type_') === 0) {
            $actualPostType = substr($actualPostType, strlen('post_type_'));
        }

        if (!$this->isCacheSet('isCurrentPostTypeSelected')) {
            // Check if current post type is selected in Mini-chart add-on's options
            $miniChartSettings               = class_exists(WP_Statistics_Mini_Chart_Settings::class) ? get_option(WP_Statistics_Mini_Chart_Settings::get_instance()->setting_name) : '';
            $this->setCache('isCurrentPostTypeSelected', !empty($miniChartSettings) && !empty($miniChartSettings["active_mini_chart_{$actualPostType}"]));
        }

        $result = '';
        if (!$this->miniChartHelper->isMiniChartActive() || ($isTerm || $this->getCache('isCurrentPostTypeSelected'))) {
            $hookName = !$isTerm ? "wp_statistics_before_hit_column_{$actualPostType}" : 'wp_statistics_before_hit_column';

            // If Mini-chart add-on is not active, this line will display the "Unlock!" button
            // If Mini-chart add-on is active and current post type is selected in settings (or it's a term), the chart will be displayed via the filter
            $result .= apply_filters($hookName, $this->getPreviewChartUnlockHtml(), $objectId, $this->getCache('postType'));
        }

        // Display hit count only if it's a valid number
        if (is_numeric($hitCount)) {
            $analyticsUrl = Menus::admin_url('content-analytics', [
                'post_id' => $objectId,
                'type'    => 'single',
                'from'    => Request::get('from', $this->getCache('hitArgs')['date']['from']),
                'to'      => Request::get('to', $this->getCache('hitArgs')['date']['to']),
            ]);
            if ($isTerm) {
                $analyticsUrl = Menus::admin_url('category-analytics', [
                    'term_id' => $objectId,
                    'type'    => 'single',
                    'from'    => Request::get('from', $this->getCache('hitArgs')['date']['from']),
                    'to'      => Request::get('to', $this->getCache('hitArgs')['date']['to']),
                ]);
            }

            // Add hit number below the chart
            $result .= sprintf(
                '<div class="%s"><span class="%s">%s</span> <a href="%s" class="wps-admin-column__link %s">%s</a></div>',
                $this->miniChartHelper->getCountDisplay() === 'disabled' ? 'wps-hide' : '',
                $this->miniChartHelper->isMiniChartActive() ? '' : 'wps-hide',
                $this->miniChartHelper->getLabel(),
                esc_url($analyticsUrl),
                $this->miniChartHelper->isMiniChartActive() ? '' : 'wps-admin-column__unlock-count',
                esc_html(number_format($hitCount))
            );
        }

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
