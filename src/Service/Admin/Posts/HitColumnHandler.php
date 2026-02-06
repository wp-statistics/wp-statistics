<?php

namespace WP_Statistics\Service\Admin\Posts;

use WP_STATISTICS\DB;
use WP_Statistics\MiniChart\WP_Statistics_Mini_Chart_Settings;
use WP_Statistics\Components\Option;
use WP_Statistics\Service\Admin\MiniChart\MiniChartHelper;
use WP_Statistics\Service\AnalyticsQuery\AnalyticsQueryHelper;
use WP_STATISTICS\TimeZone;
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
     * Whether this handler is for taxonomies.
     *
     * @var bool
     */
    private $isTerm;

    /**
     * The context type (post_type or taxonomy slug).
     *
     * @var string|null
     */
    private $contextType;

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
     * @param bool        $isTerm      Is this instance handling a taxonomies list?
     * @param string|null $contextType The post type or taxonomy slug for context-aware queries.
     */
    public function __construct($isTerm = false, $contextType = null)
    {
        $this->isTerm      = $isTerm;
        $this->contextType = $contextType;

        if ($isTerm) {
            $this->columnName = 'wp-statistics-tax-hits';
        }

        $this->miniChartHelper = new MiniChartHelper();
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

        // Batch prefetch on first call
        if (!$this->isCacheSet('batchResults')) {
            $this->prefetchPostHitCounts();
        }

        $batchResults = $this->getCache('batchResults');
        $hitCount     = $batchResults[$postId] ?? 0;

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
        // v15 stores raw taxonomy names (no 'tax_' prefix)
        if (!$this->isCacheSet('postType')) {
            $this->setCache('postType', ($term instanceof \WP_Term) ? $term->taxonomy : '');
        }

        // Batch prefetch on first call
        if (!$this->isCacheSet('batchResults')) {
            $this->prefetchTaxHitCounts($term->taxonomy);
        }

        $batchResults = $this->getCache('batchResults');
        $hitCount     = $batchResults[$termId] ?? 0;

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
     * Note: This method uses custom SQL subqueries because WordPress's posts_clauses
     * filter requires inline SQL for ORDER BY. It cannot use AnalyticsQueryHelper,
     * but follows the same join pattern for consistency:
     * views.resource_uri_id → resource_uris.ID → resources.ID
     *
     * @see AnalyticsQueryHelper::getViewsResourceJoinPattern() for the canonical pattern.
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
            return $clauses;
        }

        // If order-by.
        if (!isset($wpQuery->query_vars['orderby']) || !isset($wpQuery->query_vars['order']) || $wpQuery->query_vars['orderby'] != 'hits') {
            return $clauses;
        }

        global $wpdb;

        // Get global Variable
        $order = $wpQuery->query_vars['order'];

        // v15 table references
        $viewsTable        = DB::table('views');
        $sessionsTable     = DB::table('sessions');
        $resourceUrisTable = DB::table('resource_uris');
        $resourcesTable    = DB::table('resources');

        // Add date condition if needed (viewed_at is a datetime column)
        $dateCondition = '';
        if ($this->miniChartHelper->getCountDisplay() === 'date_range') {
            $dateFrom = TimeZone::getTimeAgo(intval(Option::getByAddon('date_range', 'mini_chart', '14')));
            $dateTo   = date('Y-m-d');
            $dateCondition = $wpdb->prepare(
                ' AND v.viewed_at BETWEEN %s AND %s',
                $dateFrom . ' 00:00:00',
                $dateTo . ' 23:59:59'
            );
        }

        // Select Field - use v15 normalized tables
        // Join via resource_uris for consistency with AnalyticsQuery:
        // views.resource_uri_id -> resource_uris.ID -> resources.ID
        if ($this->miniChartHelper->getChartMetric() === 'visitors') {
            // For visitors: join through sessions to get visitor_id
            $clauses['fields'] .= ", (
                SELECT COUNT(DISTINCT s.visitor_id)
                FROM {$viewsTable} v
                JOIN {$sessionsTable} s ON v.session_id = s.ID
                JOIN {$resourceUrisTable} ru ON v.resource_uri_id = ru.ID
                JOIN {$resourcesTable} r ON ru.resource_id = r.ID AND r.is_deleted = 0
                WHERE r.resource_id = {$wpdb->posts}.ID{$dateCondition}
            ) AS `post_hits_sortable` ";
        } else {
            // For views: each row in views = 1 view, so COUNT(*)
            $clauses['fields'] .= ", (
                SELECT COUNT(*)
                FROM {$viewsTable} v
                JOIN {$resourceUrisTable} ru ON v.resource_uri_id = ru.ID
                JOIN {$resourcesTable} r ON ru.resource_id = r.ID AND r.is_deleted = 0
                WHERE r.resource_id = {$wpdb->posts}.ID{$dateCondition}
            ) AS `post_hits_sortable` ";
        }

        // Order by `post_hits_sortable`
        $clauses['orderby'] = " COALESCE(`post_hits_sortable`, 0) $order";

        return $clauses;
    }

    /**
     * Modifies query clauses when terms are sorted by hits column.
     *
     * Note: This method uses custom SQL subqueries because WordPress's terms_clauses
     * filter requires inline SQL for ORDER BY. It cannot use AnalyticsQueryHelper,
     * but follows the same join pattern for consistency:
     * views.resource_uri_id → resource_uris.ID → resources.ID
     *
     * @see AnalyticsQueryHelper::getViewsResourceJoinPattern() for the canonical pattern.
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
            return $clauses;
        }

        // If order-by.
        if (!isset($args['orderby']) || $args['orderby'] !== 'hits') {
            return $clauses;
        }

        global $wpdb;

        // v15 table references
        $viewsTable        = DB::table('views');
        $sessionsTable     = DB::table('sessions');
        $resourceUrisTable = DB::table('resource_uris');
        $resourcesTable    = DB::table('resources');

        // Add date condition if needed (viewed_at is a datetime column)
        $dateCondition = '';
        if ($this->miniChartHelper->getCountDisplay() === 'date_range') {
            $dateFrom = TimeZone::getTimeAgo(intval(Option::getByAddon('date_range', 'mini_chart', '14')));
            $dateTo   = date('Y-m-d');
            $dateCondition = $wpdb->prepare(
                ' AND v.viewed_at BETWEEN %s AND %s',
                $dateFrom . ' 00:00:00',
                $dateTo . ' 23:59:59'
            );
        }

        // Select Field - use v15 normalized tables
        // Join via resource_uris for consistency with AnalyticsQuery:
        // views.resource_uri_id -> resource_uris.ID -> resources.ID
        if ($this->miniChartHelper->getChartMetric() === 'visitors') {
            // For visitors: join through sessions to get visitor_id
            $clauses['fields'] .= ", (
                SELECT COUNT(DISTINCT s.visitor_id)
                FROM {$viewsTable} v
                JOIN {$sessionsTable} s ON v.session_id = s.ID
                JOIN {$resourceUrisTable} ru ON v.resource_uri_id = ru.ID
                JOIN {$resourcesTable} r ON ru.resource_id = r.ID AND r.is_deleted = 0
                WHERE r.resource_id = t.term_id{$dateCondition}
            ) AS `tax_hits_sortable` ";
        } else {
            // For views: each row in views = 1 view, so COUNT(*)
            $clauses['fields'] .= ", (
                SELECT COUNT(*)
                FROM {$viewsTable} v
                JOIN {$resourceUrisTable} ru ON v.resource_uri_id = ru.ID
                JOIN {$resourcesTable} r ON ru.resource_id = r.ID AND r.is_deleted = 0
                WHERE r.resource_id = t.term_id{$dateCondition}
            ) AS `tax_hits_sortable` ";
        }

        // Order by `tax_hits_sortable`
        $clauses['orderby'] = " ORDER BY coalesce(`tax_hits_sortable`, 0)";

        return $clauses;
    }

    /**
     * Prefetch hit counts for all posts currently displayed in the list.
     *
     * Uses the global WP_Query to get all post IDs and fetches their hit counts in a single query.
     *
     * @return void
     */
    private function prefetchPostHitCounts()
    {
        global $wp_query;

        // Don't calculate stats if `count_display` is disabled
        if ($this->miniChartHelper->getCountDisplay() === 'disabled') {
            $this->setCache('batchResults', []);
            $this->setCache('hitArgs', []);
            return;
        }

        // Get IDs from WordPress's already-fetched posts
        $postIds = [];
        if (!empty($wp_query->posts)) {
            $postIds = wp_list_pluck($wp_query->posts, 'ID');
        }

        if (empty($postIds)) {
            $this->setCache('batchResults', []);
            $this->setCache('hitArgs', []);
            return;
        }

        // Determine the type for the query
        // Use context if provided, otherwise derive from the first post
        $type = $this->contextType;
        if (empty($type) && !empty($wp_query->posts[0])) {
            $type = get_post_type($wp_query->posts[0]);
        }

        $this->fetchHitCountsForIds($postIds, $type, false);
    }

    /**
     * Prefetch hit counts for all terms currently displayed in the list.
     *
     * @param string $taxonomy The taxonomy slug.
     *
     * @return void
     */
    private function prefetchTaxHitCounts($taxonomy)
    {
        global $wp_list_table;

        // Don't calculate stats if `count_display` is disabled
        if ($this->miniChartHelper->getCountDisplay() === 'disabled') {
            $this->setCache('batchResults', []);
            $this->setCache('hitArgs', []);
            return;
        }

        // WP_Terms_List_Table stores terms in $this->items after prepare_items()
        $termIds = [];
        if (!empty($wp_list_table) && !empty($wp_list_table->items)) {
            $termIds = wp_list_pluck($wp_list_table->items, 'term_id');
        }

        if (empty($termIds)) {
            $this->setCache('batchResults', []);
            $this->setCache('hitArgs', []);
            return;
        }

        // v15 stores raw taxonomy names (no 'tax_' prefix)
        $this->fetchHitCountsForIds($termIds, $taxonomy, true);
    }

    /**
     * Fetch hit counts for a set of IDs in a single optimized query.
     *
     * Uses AnalyticsQueryHelper::getResourceHitsBatch() internally to ensure
     * consistent join patterns with the analytics reports.
     *
     * @param array  $ids    Array of post or term IDs.
     * @param string $type   The resource type (e.g., 'post', 'page', 'category', 'product_cat').
     * @param bool   $isTerm Whether these are term IDs (unused, kept for signature compatibility).
     *
     * @return void
     */
    private function fetchHitCountsForIds(array $ids, $type, $isTerm)
    {
        $countDisplay = $this->miniChartHelper->getCountDisplay();
        $isVisitors   = $this->miniChartHelper->getChartMetric() === 'visitors';

        // Calculate date range
        // Note: AnalyticsQueryHandler defaults to 30 days if no dates provided,
        // so we must pass explicit dates for both modes
        if ($countDisplay === 'date_range') {
            $dateFrom = TimeZone::getTimeAgo(intval(Option::getByAddon('date_range', 'mini_chart', '14')));
            $dateTo   = date('Y-m-d');
            $this->setCache('hitArgs', ['date' => ['from' => $dateFrom, 'to' => $dateTo]]);
        } else {
            // Total mode: use all-time (from earliest possible date to today)
            $dateFrom = '2000-01-01';
            $dateTo   = date('Y-m-d');
            $this->setCache('hitArgs', []);
        }

        // Determine metric
        $metric = $isVisitors ? 'visitors' : 'views';

        // Use AnalyticsQueryHelper for consistent query patterns
        $batchResults = AnalyticsQueryHelper::getResourceHitsBatch(
            $ids,
            $type,
            $metric,
            $dateFrom,
            $dateTo
        );

        $this->setCache('batchResults', $batchResults);
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
            // Build URL using v15 hash-based routing
            $hitArgs = $this->getCache('hitArgs');

            // Only add date params for date_range mode (not for total mode)
            $urlParams = [];
            if (!empty($hitArgs['date'])) {
                $urlParams['from'] = $hitArgs['date']['from'];
                $urlParams['to']   = $hitArgs['date']['to'];
            }

            $analyticsUrl = $isTerm
                ? UrlBuilder::categoryAnalytics($objectId, $urlParams)
                : UrlBuilder::contentAnalytics($objectId, $urlParams);

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
