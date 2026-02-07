<?php

namespace WP_Statistics\Service\Admin\Posts;

use WP_STATISTICS\DB;
use WP_Statistics\Service\AnalyticsQuery\AnalyticsQueryHelper;
use WP_Statistics\Traits\ObjectCacheTrait;
use WP_Statistics\Utils\UrlBuilder;

/**
 * This class will add, render, modify sort and modify order by hits column in posts and taxonomies list pages.
 */
class HitColumnHandler
{
    use ObjectCacheTrait;

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
                    $cols[$this->columnName] = __('Views', 'wp-statistics');
                }
                $cols[$key] = $value;
            }
            return $cols;
        }

        $columns[$this->columnName] = __('Views', 'wp-statistics');

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
        $resourceUrisTable = DB::table('resource_uris');
        $resourcesTable    = DB::table('resources');

        // Always use views with total (all-time) — COUNT(*)
        $clauses['fields'] .= ", (
            SELECT COUNT(*)
            FROM {$viewsTable} v
            JOIN {$resourceUrisTable} ru ON v.resource_uri_id = ru.ID
            JOIN {$resourcesTable} r ON ru.resource_id = r.ID AND r.is_deleted = 0
            WHERE r.resource_id = {$wpdb->posts}.ID
        ) AS `post_hits_sortable` ";

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

        // v15 table references
        $viewsTable        = DB::table('views');
        $resourceUrisTable = DB::table('resource_uris');
        $resourcesTable    = DB::table('resources');

        // Always use views with total (all-time) — COUNT(*)
        $clauses['fields'] .= ", (
            SELECT COUNT(*)
            FROM {$viewsTable} v
            JOIN {$resourceUrisTable} ru ON v.resource_uri_id = ru.ID
            JOIN {$resourcesTable} r ON ru.resource_id = r.ID AND r.is_deleted = 0
            WHERE r.resource_id = t.term_id
        ) AS `tax_hits_sortable` ";

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

        // Get IDs from WordPress's already-fetched posts
        $postIds = [];
        if (!empty($wp_query->posts)) {
            $postIds = wp_list_pluck($wp_query->posts, 'ID');
        }

        if (empty($postIds)) {
            $this->setCache('batchResults', []);
            return;
        }

        // Determine the type for the query
        // Use context if provided, otherwise derive from the first post
        $type = $this->contextType;
        if (empty($type) && !empty($wp_query->posts[0])) {
            $type = get_post_type($wp_query->posts[0]);
        }

        $this->fetchHitCountsForIds($postIds, $type);
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

        // WP_Terms_List_Table stores terms in $this->items after prepare_items()
        $termIds = [];
        if (!empty($wp_list_table) && !empty($wp_list_table->items)) {
            $termIds = wp_list_pluck($wp_list_table->items, 'term_id');
        }

        if (empty($termIds)) {
            $this->setCache('batchResults', []);
            return;
        }

        // v15 stores raw taxonomy names (no 'tax_' prefix)
        $this->fetchHitCountsForIds($termIds, $taxonomy);
    }

    /**
     * Fetch hit counts for a set of IDs in a single optimized query.
     *
     * Uses AnalyticsQueryHelper::getResourceHitsBatch() internally to ensure
     * consistent join patterns with the analytics reports.
     *
     * @param array  $ids    Array of post or term IDs.
     * @param string $type   The resource type (e.g., 'post', 'page', 'category', 'product_cat').
     *
     * @return void
     */
    private function fetchHitCountsForIds(array $ids, $type)
    {
        // Always use views with total (all-time)
        $dateFrom = '2000-01-01';
        $dateTo   = date('Y-m-d');

        // Use AnalyticsQueryHelper for consistent query patterns
        $batchResults = AnalyticsQueryHelper::getResourceHitsBatch(
            $ids,
            $type,
            'views',
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
        $result = '';

        // Display hit count only if it's a valid number
        if (is_numeric($hitCount)) {
            $analyticsUrl = $isTerm
                ? UrlBuilder::categoryAnalytics($objectId)
                : UrlBuilder::contentAnalytics($objectId);

            // Show simple count with label
            $result .= sprintf(
                '<div><span>%s</span> <a href="%s" class="wps-admin-column__link">%s</a></div>',
                __('Views', 'wp-statistics'),
                esc_url($analyticsUrl),
                esc_html(number_format($hitCount))
            );
        }

        return $result;
    }
}
