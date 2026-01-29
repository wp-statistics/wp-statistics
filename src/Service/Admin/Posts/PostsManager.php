<?php

namespace WP_Statistics\Service\Admin\Posts;

use WP_Statistics\Components\DateTime;
use WP_Statistics\Components\Option;
use WP_Statistics\Service\Admin\MiniChart\MiniChartHelper;
use WP_Statistics\Utils\Query;
use WP_Statistics\Utils\Request;
use WP_Statistics\Utils\Taxonomy;
use WP_Statistics\Utils\User;

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
        if (User::hasAccess('read') && !Option::getValue('disable_column')) {
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

            foreach (Taxonomy::getAll() as $tax => $name) {
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
     * @todo Also delete from historical table.
     */
    public static function deletePostHits($postId)
    {
        $postType = get_post_type($postId);

        if (empty($postType)) {
            return;
        }

        Query::delete('pages')
            ->where('id', '=', $postId)
            ->where('type', '=', $postType)
            ->execute();
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
     * @todo Also delete from historical table.
     */
    public static function deleteTermHits($term, $ttId, $taxonomy)
    {
        $types = ['category', 'post_tag', 'tax_' . $taxonomy];

        Query::delete('pages')
            ->where('id', '=', $ttId)
            ->where('type', 'IN', $types)
            ->execute();
    }

    /**
     * Returns the data needed for "Statistics - Summary" widget/panel in edit posts.
     *
     * @param int $postId
     *
     * @return array|null Keys:
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
        $miniChartHelper = new MiniChartHelper();

        try {
            $dataProvider = new PostSummaryDataProvider($postId);
        } catch (\Exception $e) {
            return null;
        }

        $publishDate  = $dataProvider->getPublishDate();
        $chartDays    = $miniChartHelper->getChartDateRange();
        $chartMetric  = $miniChartHelper->getChartMetric();

        // Compute all date ranges upfront (no mutable state)
        $periodRange = [
            'from' => DateTime::getTimeAgo(7),
            'to'   => DateTime::getTimeAgo(),
        ];
        $totalRange = [
            'from' => $publishDate,
            'to'   => date('Y-m-d'),
        ];
        $chartRange = [
            'from' => DateTime::getTimeAgo($chartDays),
            'to'   => date('Y-m-d'),
        ];

        // Fetch all data in 5 batched queries
        $data = $dataProvider->getAllData($periodRange, $totalRange, $chartRange, $chartMetric);

        // Build chart data with default 0s
        $chartData    = [];
        $wpDateFormat = get_option('date_format');

        foreach ($miniChartHelper->getChartDates() as $date) {
            $shortDate             = date('d M', strtotime($date));
            $chartData[$shortDate] = [
                'ymdDate'  => date('Y-m-d', strtotime($date)),
                'hits'     => 0,
                'fullDate' => date($wpDateFormat, strtotime($date)),
            ];
        }

        // Fill chart data with real stats
        foreach ($data['dailyHits'] as $hit) {
            if (empty($hit['date']) || empty($hit['hits'])) {
                continue;
            }

            $shortDate             = date('d M', strtotime($hit['date']));
            $chartData[$shortDate] = [
                'ymdDate'  => $hit['date'],
                'hits'     => intval($hit['hits']),
                'fullDate' => date($wpDateFormat, strtotime($hit['date'])),
            ];
        }

        // Sort chart data by date
        uasort($chartData, function ($a, $b) {
            if ($a['ymdDate'] == $b['ymdDate']) {
                return 0;
            }
            return ($a['ymdDate'] < $b['ymdDate']) ? -1 : 1;
        });

        $chartSettings = [
            'color' => $miniChartHelper->getChartColor(),
            'label' => $miniChartHelper->getLabel(),
        ];

        // Format period date strings (short format without year)
        $shortDateFormat = self::makeShortDateFormat(get_option('date_format'));

        return [
            'postId'                     => $postId,
            'fromString'                 => date($shortDateFormat, strtotime($periodRange['from'])),
            'toString'                   => date($shortDateFormat, strtotime($periodRange['to'])),
            'publishDateString'          => $publishDate,
            'totalVisitors'              => $data['totalVisitors'],
            'totalViews'                 => $data['totalViews'],
            'topReferrer'                => $data['topReferrerTotal']['url'],
            'topReferrerCount'           => $data['topReferrerTotal']['count'],
            'thisPeriodVisitors'         => $data['periodVisitors'],
            'thisPeriodViews'            => $data['periodViews'],
            'thisPeriodTopReferrer'      => $data['topReferrerPeriod']['url'],
            'thisPeriodTopReferrerCount' => $data['topReferrerPeriod']['count'],
            'postChartData'              => $chartData,
            'postChartSettings'          => $chartSettings,
            'contentAnalyticsUrl'        => $data['contentAnalyticsUrl'],
        ];
    }

    /**
     * Removes year from the given date format and makes the month shorter.
     *
     * @param string $dateFormat
     *
     * @return string
     */
    private static function makeShortDateFormat($dateFormat)
    {
        $dateFormat = str_replace(['o', 'X', 'x', 'Y', 'y'], '', $dateFormat);
        $dateFormat = trim($dateFormat, ' ,./\\-_');
        $dateFormat = str_replace('F', 'M', $dateFormat);
        $dateFormat = trim($dateFormat, ' ,./\\-_');

        return $dateFormat;
    }
}