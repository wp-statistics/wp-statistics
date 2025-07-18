<?php

namespace WP_Statistics\Service\Admin\Posts;

use WP_Statistics\Components\Assets;
use WP_STATISTICS\DB;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Meta_Box;
use WP_STATISTICS\Option;
use WP_Statistics\Service\Admin\MiniChart\MiniChartHelper;
use WP_STATISTICS\TimeZone;
use WP_STATISTICS\User;
use WP_Statistics\Utils\Request;

class PostsManager
{
    /**
     * @var WordCountService $wordsCount
     */
    private $wordsCount;

    public function __construct()
    {
        $this->wordsCount = new WordCountService();

        if ($this->wordsCount->isActive()) {
            add_action('save_post', [$this, 'addWordsCountCallback'], 99, 3);
            add_action('delete_post', [$this, 'removeWordsCountCallback'], 99, 2);
        }

        // Add Hits column in edit lists of all post types
        if (User::Access('read') && !Option::get('disable_column')) {
            add_action('admin_init', [$this, 'initHitsColumn']);
        }

        // Remove post hits on post delete
        add_action('deleted_post', [$this, 'deletePostHits']);

        // Remove term hits on term delete
        add_action('delete_term', [$this, 'deleteTermHits'], 10, 3);
    }

    /**
     * Count the number of words in a post and store it as a meta value
     *
     * @param $postId
     * @param \WP_Post $post
     */
    public function addWordsCountCallback($postId, $post)
    {
        $this->wordsCount->handleSavePost($postId, $post);
    }

    /**
     * Remove wps_words_count meta when the post is deleted
     *
     * @param $postId
     * @param \WP_Post $post
     */
    public function removeWordsCountCallback($postId, $post)
    {
        $this->wordsCount->removeWordsCountMeta($postId, $post);
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

            $hitColumnHandler = new HitColumnHandler();

            foreach (['posts', 'pages'] as $type) {
                add_filter("manage_{$type}_columns", [$hitColumnHandler, 'addHitColumn'], 10, 2);
                add_action("manage_{$type}_custom_column", [$hitColumnHandler, 'renderHitColumn'], 10, 2);
            }

            $currentPage = Request::get('post_type', 'post');

            add_filter("manage_edit-{$currentPage}_sortable_columns", [$hitColumnHandler, 'modifySortableColumns']);

            if (!$isPostQuickEdit) {
                add_filter('posts_clauses', [$hitColumnHandler, 'handlePostOrderByHits'], 10, 2);
            }
        } else if ($pagenow === 'edit-tags.php' || $isTaxQuickEdit) {
            // Taxonomies + Quick edit

            if (!apply_filters('wp_statistics_show_taxonomy_hits', true)) {
                return;
            }

            $hitColumnHandler = new HitColumnHandler(true);

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
        $dataProvider    = null;
        $miniChartHelper = new MiniChartHelper();
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

        // Fill `$chartData` with default 0s
        // Use a short date format for indexes and `chartDates` for values
        // Short date format will be displayed below summary charts
        foreach ($miniChartHelper->getChartDates() as $date) {
            $shortDate             = date('d M', strtotime($date));
            $chartData[$shortDate] = [
                'ymdDate'   => date('Y-m-d', strtotime($date)),
                'hits'      => 0,
                'fullDate'  => date($wpDateFormat, strtotime($date)),
            ];
        }

        // Set date range for charts based on MiniChart's `date_range` option
        // Also change `to_date` to include today's stats in charts too
        $dataProvider->setFrom(TimeZone::getTimeAgo($miniChartHelper->isMiniChartActive() ? Option::getByAddon('date_range', 'mini_chart', '14') : 14));
        $dataProvider->setTo(date('Y-m-d'));

        // Fill `$dailyHits` based on MiniChart's `metric` option
        $dailyHits = Helper::checkMiniChartOption('metric', 'views', 'visitors') ? $dataProvider->getDailyViews() : $dataProvider->getDailyVisitors();

        // Fill `$chartData` with real stats
        foreach ($dailyHits as $hit) {
            if (empty($hit->date) || (empty($hit->visitors) && empty($hit->views))) {
                continue;
            }

            $shortDate             = date('d M', strtotime($hit->date));
            $chartData[$shortDate] = [
                'ymdDate'   => $hit->date,
                'hits'      => !empty($hit->visitors) ? intval($hit->visitors) : intval($hit->views),
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

        // Some settings for the chart
        $chartSettings = [
            'color'  => $miniChartHelper->getChartColor(),
            'label'  => $miniChartHelper->getLabel(),
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