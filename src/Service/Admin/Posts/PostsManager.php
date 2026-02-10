<?php

namespace WP_Statistics\Service\Admin\Posts;

use WP_Statistics\Components\Assets;
use WP_STATISTICS\DB;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Meta_Box;
use WP_Statistics\Components\Option;
use WP_STATISTICS\TimeZone;
use WP_Statistics\Utils\Request;
use WP_Statistics\Utils\User;

class PostsManager
{
    public function __construct()
    {
        // Add Hits column in edit lists of all post types
        if (User::hasAccess('read') && !Option::getValue('disable_column')) {
            add_action('admin_init', [$this, 'initHitsColumn']);
        }

        // Remove post hits on post delete
        add_action('deleted_post', [$this, 'deletePostHits']);

        // Remove term hits on term delete
        add_action('delete_term', [$this, 'deleteTermHits'], 10, 3);
    }

    /**
     * Initializes hits column in edit lists.
     *
     * @return void
     *
     * @hooked action: `admin_init` - 10
     */
    public function initHitsColumn()
    {
        global $pagenow;

        $isPostQuickEdit = $pagenow === 'admin-ajax.php' && !empty($_POST['action']) && $_POST['action'] === 'inline-save';
        $isTaxQuickEdit  = $pagenow === 'admin-ajax.php' && !empty($_POST['action']) && $_POST['action'] === 'inline-save-tax';

        if ($pagenow === 'edit.php' || $isPostQuickEdit) {
            // Posts and pages and CPTs + Quick edit

            // Get post type for context-aware batch prefetching.
            // This enables HitColumnHandler to prefetch hit counts for all posts
            // on the current page in a single query rather than N+1 queries.
            $currentPage = Request::get('post_type', 'post');

            $hitColumnHandler = new HitColumnHandler(false, $currentPage);

            foreach (['posts', 'pages'] as $type) {
                add_filter("manage_{$type}_columns", [$hitColumnHandler, 'addHitColumn'], 10, 2);
                add_action("manage_{$type}_custom_column", [$hitColumnHandler, 'renderHitColumn'], 10, 2);
            }

            add_filter("manage_edit-{$currentPage}_sortable_columns", [$hitColumnHandler, 'modifySortableColumns']);

            if (!$isPostQuickEdit) {
                add_filter('posts_clauses', [$hitColumnHandler, 'handlePostOrderByHits'], 10, 2);
            }
        } else if ($pagenow === 'edit-tags.php' || $isTaxQuickEdit) {
            // Taxonomies + Quick edit

            if (!apply_filters('wp_statistics_show_taxonomy_hits', true)) {
                return;
            }

            // Get taxonomy for context-aware batch prefetching.
            // This enables HitColumnHandler to prefetch hit counts for all terms
            // on the current page in a single query rather than N+1 queries.
            $currentTax = Request::get('taxonomy', 'category');

            $hitColumnHandler = new HitColumnHandler(true, $currentTax);

            foreach (Helper::get_list_taxonomy() as $tax => $name) {
                add_filter("manage_edit-{$tax}_columns", [$hitColumnHandler, 'addHitColumn'], 10, 2);
                add_filter("manage_{$tax}_custom_column", [$hitColumnHandler, 'renderTaxHitColumn'], 10, 3);
                add_filter("manage_edit-{$tax}_sortable_columns", [$hitColumnHandler, 'modifySortableColumns']);
            }

            add_filter('terms_clauses', [$hitColumnHandler, 'handleTaxOrderByHits'], 10, 3);
        }
    }

    /**
     * Deletes all post hits when the post is deleted.
     *
     * @param int $postId
     *
     * @return void
     *
     * @hooked action: `deleted_post` - 10
     *
     * @todo Replace this method with visitor decorator call after the class is ready.
     * @todo Also delete from historical table.
     */
    public static function deletePostHits($postId)
    {
        global $wpdb;

        $wpdb->query(
            $wpdb->prepare("DELETE FROM `" . DB::table('pages') . "` WHERE `id` = %d AND (`type` = 'post' OR `type` = 'page' OR `type` = 'product');", esc_sql($postId))
        );
    }

    /**
     * Deletes all term hits when the term is deleted.
     *
     * @param int $term Term ID.
     * @param int $ttId Term taxonomy ID.
     * @param string $taxonomy Taxonomy slug.
     *
     * @return void
     *
     * @hooked action: `delete_term` - 10
     *
     * @todo Replace this method with visitor decorator call after the class is ready.
     * @todo Also delete from historical table.
     */
    public static function deleteTermHits($term, $ttId, $taxonomy)
    {
        global $wpdb;

        $taxSlug = 'tax_' . $taxonomy;

        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM `" . DB::table('pages') . "` WHERE `id` = %d AND (`type` = 'category' OR `type` = 'post_tag' OR `type` = %s);",
                $ttId,
                $taxSlug
            )
        );
    }

    /**
     * Returns the data needed for "Statistics - Summary" widget/panel in edit posts.
     *
     * @param   \WP_Post    $post
     *
     * @return  array|null          Keys:
     *  - `postId`
     *  - `fromString`
     *  - `toString`
     *  - `publishDateString`
     *  - `totalVisitors`
     *  - `totalViews`
     *  - `topReferrer`
     *  - `topReferrerCount`
     *  - `thisPeriodVisitors`
     *  - `thisPeriodViews`
     *  - `thisPeriodTopReferrer`
     *  - `thisPeriodTopReferrerCount`
     *  - `postChartData`
     *  - `postChartSettings`
     *  - `contentAnalyticsUrl`
     */
    public static function getPostStatisticsSummary($postId)
    {
        $dataProvider = null;
        try {
            $dataProvider = new PostSummaryDataProvider($postId);
        } catch (\Exception $e) {
            return null;
        }

        $topReferrerAndCountTotal      = $dataProvider->getTopReferrerAndCount(true);
        $topReferrerAndCountThisPeriod = $dataProvider->getTopReferrerAndCount();

        // Data for the sidebar chart
        $chartData    = [];
        $wpDateFormat = get_option('date_format');
        $publishDate  = $dataProvider->getPublishDate();

        // Fill `$chartData` with default 0s for the last 14 days
        for ($i = 13; $i >= 0; $i--) {
            $date                  = TimeZone::getTimeAgo($i);
            $shortDate             = date('d M', strtotime($date));
            $chartData[$shortDate] = [
                'ymdDate'   => date('Y-m-d', strtotime($date)),
                'hits'      => 0,
                'fullDate'  => date($wpDateFormat, strtotime($date)),
            ];
        }

        // Set date range for charts: last 14 days
        $dataProvider->setFrom(TimeZone::getTimeAgo(14));
        $dataProvider->setTo(date('Y-m-d'));

        // Always use views
        $dailyHits = $dataProvider->getDailyViews();

        // Fill `$chartData` with real stats
        foreach ($dailyHits as $hit) {
            if (empty($hit->date) || empty($hit->views)) {
                continue;
            }

            $shortDate             = date('d M', strtotime($hit->date));
            $chartData[$shortDate] = [
                'ymdDate'   => $hit->date,
                'hits'      => intval($hit->views),
                'fullDate'  => date($wpDateFormat, strtotime($hit->date)),
            ];
        }

        // Sort `$chartData` by date
        uasort($chartData, function ($a, $b) {
            if ($a['ymdDate'] == $b['ymdDate']) {
                return 0;
            }
            return ($a['ymdDate'] < $b['ymdDate']) ? -1 : 1;
        });

        // Chart settings
        $chartSettings = [
            'color'  => '#7362BF',
            'label'  => __('Views', 'wp-statistics'),
        ];

        // Reset date range because text summary displays info for the past week
        $dataProvider->setFrom(TimeZone::getTimeAgo(7));
        $dataProvider->setTo(TimeZone::getTimeAgo());

        return [
            'postId'                     => $postId,
            'fromString'                 => $dataProvider->getFromString('', true),
            'toString'                   => $dataProvider->getToString('', true),
            'publishDateString'          => $publishDate,
            'totalVisitors'              => $dataProvider->getVisitors(true),
            'totalViews'                 => $dataProvider->getViews(true),
            'topReferrer'                => $topReferrerAndCountTotal['url'],
            'topReferrerCount'           => $topReferrerAndCountTotal['count'],
            'thisPeriodVisitors'         => $dataProvider->getVisitors(),
            'thisPeriodViews'            => $dataProvider->getViews(),
            'thisPeriodTopReferrer'      => $topReferrerAndCountThisPeriod['url'],
            'thisPeriodTopReferrerCount' => $topReferrerAndCountThisPeriod['count'],
            'postChartData'              => $chartData,
            'postChartSettings'          => $chartSettings,
            'contentAnalyticsUrl'        => $dataProvider->getContentAnalyticsUrl(),
        ];
    }
}