<?php

namespace WP_Statistics\Service\Admin\Posts;

use WP_Statistics\Components\DateTime;
use WP_Statistics\MiniChart\WP_Statistics_Mini_Chart_Settings;
use WP_Statistics\Models\PostsModel;
use WP_Statistics\Service\Admin\MiniChart\MiniChartHelper;
use WP_Statistics\Service\AnalyticsQuery\AnalyticsQueryHandler;
use WP_Statistics\Service\Database\DatabaseSchema;
use WP_Statistics\Traits\ObjectCacheTrait;
use WP_Statistics\Utils\UrlBuilder;

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
     * Analytics query handler for v15 API.
     *
     * @var AnalyticsQueryHandler
     */
    private $queryHandler;

    private $initialPostDate;

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

        $postsModel            = new PostsModel();
        $initialDate           = $postsModel->getInitialPostDate();
        $this->initialPostDate = !empty($initialDate) ? DateTime::format($initialDate, ['date_format' => 'Y-m-d']) : date('Y-m-d');

        $this->miniChartHelper = new MiniChartHelper();
        $this->queryHandler    = new AnalyticsQueryHandler();
    }

    /**
     * Adds a custom column to posts/taxonomies lists for hits statistics.
     *
     * @param array $columns Columns array.
     * @param string $postType Post type.
     *
     * @return array
     *
     * @hooked action: `manage_{$type}_posts_columns` - 10
     * @hooked action: `manage_edit-{$tax}_columns` - 10
     */
    public function addHitColumn($columns, $postType = '')
    {
        if (!empty($postType) && empty(is_post_type_viewable($postType))) {
            return $columns;
        }

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
            $this->setCache('postType', get_post_type($postId));
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
            $this->setCache('postType', (($term instanceof \WP_Term) && ($term->taxonomy === 'category' || $term->taxonomy === 'post_tag')) ? $term->taxonomy : 'tax_' . $term->taxonomy);
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
            $dateCondition = 'BETWEEN "' . DateTime::getTimeAgo($this->miniChartHelper->getChartDateRange()) . '" AND "' . date('Y-m-d') . '"';
        }

        // v15 table names
        $viewsTable        = DatabaseSchema::table('views');
        $resourceUrisTable = DatabaseSchema::table('resource_uris');
        $resourcesTable    = DatabaseSchema::table('resources');
        $sessionsTable     = DatabaseSchema::table('sessions');
        $summaryTable      = DatabaseSchema::table('summary');

        // Select Field
        if ($this->miniChartHelper->getChartMetric() === 'visitors') {
            if (!empty($dateCondition)) {
                $dateCondition = "AND `v`.`viewed_at` $dateCondition";
            }

            $clauses['fields'] .= ", (SELECT COUNT(DISTINCT `s`.`visitor_id`) FROM {$viewsTable} AS `v` INNER JOIN {$resourceUrisTable} AS `ru` ON `v`.`resource_uri_id` = `ru`.`ID` INNER JOIN {$resourcesTable} AS `r` ON `ru`.`resource_id` = `r`.`ID` AND `r`.`is_deleted` = 0 INNER JOIN {$sessionsTable} AS `s` ON `v`.`session_id` = `s`.`ID` WHERE `r`.`resource_id` = {$wpdb->posts}.`ID` {$dateCondition}) AS `post_hits_sortable` ";
        } else {
            $summarySubQuery = '';
            if (!empty($dateCondition)) {
                $dateCondition = "AND `v`.`viewed_at` $dateCondition";
            } else {
                // Consider summary totals for all-time views
                $summarySubQuery = " + IFNULL((SELECT SUM(`sm`.`views`) FROM {$summaryTable} AS `sm` INNER JOIN {$resourceUrisTable} AS `sru` ON `sm`.`resource_uri_id` = `sru`.`ID` INNER JOIN {$resourcesTable} AS `sr` ON `sru`.`resource_id` = `sr`.`ID` AND `sr`.`is_deleted` = 0 WHERE `sr`.`resource_id` = {$wpdb->posts}.`ID`), 0)";
            }

            $clauses['fields'] .= ", ((SELECT COUNT(*) FROM {$viewsTable} AS `v` INNER JOIN {$resourceUrisTable} AS `ru` ON `v`.`resource_uri_id` = `ru`.`ID` INNER JOIN {$resourcesTable} AS `r` ON `ru`.`resource_id` = `r`.`ID` AND `r`.`is_deleted` = 0 WHERE `r`.`resource_id` = {$wpdb->posts}.`ID` {$dateCondition}){$summarySubQuery}) AS `post_hits_sortable` ";
        }

        // Order by `post_hits_sortable`
        $clauses['orderby'] = " COALESCE(`post_hits_sortable`, 0) $order";

        return $clauses;
    }

    /**
     * Modifies query clauses when terms are sorted by hits column.
     *
     * @param array $clauses Clauses for the query.
     * @param array $taxonomies Taxonomy WP_Statistics_names.
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
            $dateCondition = 'BETWEEN "' . DateTime::getTimeAgo($this->miniChartHelper->getChartDateRange()) . '" AND "' . date('Y-m-d') . '"';
        }

        // v15 table names
        $viewsTable        = DatabaseSchema::table('views');
        $resourceUrisTable = DatabaseSchema::table('resource_uris');
        $resourcesTable    = DatabaseSchema::table('resources');
        $sessionsTable     = DatabaseSchema::table('sessions');
        $summaryTable      = DatabaseSchema::table('summary');

        // Select Field
        if ($this->miniChartHelper->getChartMetric() === 'visitors') {
            if (!empty($dateCondition)) {
                $dateCondition = "AND `v`.`viewed_at` $dateCondition";
            }

            $clauses['fields'] .= ", (SELECT COUNT(DISTINCT `s`.`visitor_id`) FROM {$viewsTable} AS `v` INNER JOIN {$resourceUrisTable} AS `ru` ON `v`.`resource_uri_id` = `ru`.`ID` INNER JOIN {$resourcesTable} AS `r` ON `ru`.`resource_id` = `r`.`ID` AND `r`.`is_deleted` = 0 INNER JOIN {$sessionsTable} AS `s` ON `v`.`session_id` = `s`.`ID` WHERE `r`.`resource_id` = `t`.`term_id` AND `r`.`resource_type` LIKE 'tax_%' {$dateCondition}) AS `tax_hits_sortable` ";
        } else {
            $summarySubQuery = '';
            if (!empty($dateCondition)) {
                $dateCondition = "AND `v`.`viewed_at` $dateCondition";
            } else {
                // Consider summary totals for all-time views
                $summarySubQuery = " + IFNULL((SELECT SUM(`sm`.`views`) FROM {$summaryTable} AS `sm` INNER JOIN {$resourceUrisTable} AS `sru` ON `sm`.`resource_uri_id` = `sru`.`ID` INNER JOIN {$resourcesTable} AS `sr` ON `sru`.`resource_id` = `sr`.`ID` AND `sr`.`is_deleted` = 0 WHERE `sr`.`resource_id` = `t`.`term_id` AND `sr`.`resource_type` LIKE 'tax_%'), 0)";
            }

            $clauses['fields'] .= ", ((SELECT COUNT(*) FROM {$viewsTable} AS `v` INNER JOIN {$resourceUrisTable} AS `ru` ON `v`.`resource_uri_id` = `ru`.`ID` INNER JOIN {$resourcesTable} AS `r` ON `ru`.`resource_id` = `r`.`ID` AND `r`.`is_deleted` = 0 WHERE `r`.`resource_id` = `t`.`term_id` AND `r`.`resource_type` LIKE 'tax_%' {$dateCondition}){$summarySubQuery}) AS `tax_hits_sortable` ";
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

        $countDisplay = $this->miniChartHelper->getCountDisplay();
        $isVisitors   = $this->miniChartHelper->getChartMetric() === 'visitors';

        // Build the query request
        $queryRequest = [
            'sources' => [$isVisitors ? 'visitors' : 'views'],
            'filters' => [
                'resource_id' => $objectId,
                'post_type'   => $this->getCache('postType'),
            ],
            'format'  => 'flat',
        ];

        // Add date range if configured
        if ($countDisplay === 'date_range') {
            $dateRange = [
                'from' => DateTime::getTimeAgo($this->miniChartHelper->getChartDateRange()),
                'to'   => date('Y-m-d'),
            ];

            $queryRequest['date_from'] = $dateRange['from'];
            $queryRequest['date_to']   = $dateRange['to'];

            // Cache hitArgs for use in getHitColumnContent
            $this->setCache('hitArgs', ['date' => $dateRange]);
        } else {
            // For total count, we don't limit by date
            $queryRequest['date_from'] = $this->initialPostDate;
            $queryRequest['date_to']   = date('Y-m-d');

            $this->setCache('hitArgs', []);
        }

        try {
            $result   = $this->queryHandler->handle($queryRequest);
            $source   = $isVisitors ? 'visitors' : 'views';
            $hitCount = $result['data'][$source] ?? 0;
        } catch (\Exception $e) {
            $hitCount = 0;
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
            $miniChartSettings = class_exists(WP_Statistics_Mini_Chart_Settings::class) ? get_option(WP_Statistics_Mini_Chart_Settings::get_instance()->setting_name) : '';
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
            $analyticsUrl = $isTerm
                ? UrlBuilder::categoryAnalytics(intval($objectId))
                : UrlBuilder::pageAnalytics(intval($objectId));

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
            '<div class="wps-admin-column__unlock">
                        <a href="%s">
                            <span class="wps-admin-column__unlock__text">%s</span>
                            <span class="wps-admin-column__unlock__img"></span>
                        </a>
                    </div>',
            esc_url(admin_url('admin.php?page=wps_plugins_page&type=locked-mini-chart')),
            __('Unlock', 'wp-statistics')
        );
    }
}
