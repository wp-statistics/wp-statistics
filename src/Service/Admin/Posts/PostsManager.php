<?php

namespace WP_Statistics\Service\Admin\Posts;

use WP_Statistics\Components\Assets;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Meta_Box;
use WP_STATISTICS\Option;
use WP_Statistics\Service\Admin\MiniChart\MiniChartHelper;
use WP_STATISTICS\TimeZone;
use WP_STATISTICS\User;

class PostsManager
{
    /**
     * @var WordCountService $wordsCount
     */
    private $wordsCount;

    public function __construct()
    {
        $this->wordsCount = new WordCountService();

        add_action('save_post', [$this, 'addWordsCountCallback'], 99, 3);
        add_action('delete_post', [$this, 'removeWordsCountCallback'], 99, 2);

        // Add meta-boxes and blocks if the user has access and only in edit mode
        global $pagenow;
        if (User::Access('read') && !Option::get('disable_editor') && $pagenow !== 'post-new.php') {
            add_action('enqueue_block_editor_assets', [$this, 'enqueueSidebarPanelAssets']);
            add_action('add_meta_boxes', [$this, 'addPostMetaBoxes']);
        }
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
     * Enqueues assets for "Statistics - Summary" panel in the Gutenberg editor sidebar.
     *
     * @return	void
     *
     * @hooked	action: `enqueue_block_editor_assets` - 10
     */
    public function enqueueSidebarPanelAssets()
    {
        global $post;
        if (empty($post)) {
            return;
        }

        $postSummary = self::getPostStatisticsSummary($post);
        if (empty($postSummary)) {
            return;
        }

        Assets::script('editor-sidebar', 'blocks/post-summary/post-summary.js', ['wp-plugins', 'wp-editor'], $postSummary);

        $styleFileName = is_rtl() ? 'style-post-summary-rtl.css' : 'style-post-summary.css';
        Assets::style('editor-sidebar', "blocks/post-summary/$styleFileName");
    }

    /**
     * Adds meta-boxes for the post in the classic editor mode.
     *
     * @return	void
     *
     * @hooked	action: `add_meta_boxes` - 10
     */
    public function addPostMetaBoxes()
    {
        $isGutenberg           = Helper::is_gutenberg();
        $displayLatestVisitors = !Helper::isAddOnActive('data-plus') || Option::getByAddon('latest_visitors_metabox', 'data_plus', '1') === '1';

        // Add meta-box to all post types
        foreach (Helper::get_list_post_type() as $screen) {
            if (!$isGutenberg) {
                add_meta_box(
                    Meta_Box::getMetaBoxKey('post-summary'),
                    Meta_Box::getList('post-summary')['name'],
                    Meta_Box::LoadMetaBox('post-summary'),
                    $screen,
                    'side',
                    'high',
                    ['__back_compat_meta_box' => false]
                );
            }

            if ($displayLatestVisitors) {
                add_meta_box(
                    Meta_Box::getMetaBoxKey('post'),
                    Meta_Box::getList('post')['name'],
                    Meta_Box::LoadMetaBox('post'),
                    $screen,
                    'normal',
                    'high',
                    [
                        '__block_editor_compatible_meta_box' => true,
                        '__back_compat_meta_box'             => false,
                    ]
                );
            }
        }
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
    public static function getPostStatisticsSummary($post)
    {
        $dataProvider = null;
        try {
            $dataProvider = new PostSummaryDataProvider($post);
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
        // Also consider post's publish date so older dates get ignored
        foreach (MiniChartHelper::getChartDates(0, $publishDate) as $date) {
            $shortDate             = date('d M', strtotime($date));
            $chartData[$shortDate] = [
                'ymdDate'   => date('Y-m-d', strtotime($date)),
                'hits'      => 0,
                'fullDate'  => date($wpDateFormat, strtotime($date)),
            ];
        }

        // Set date range for charts based on MiniChart's `date_range` option
        $dataProvider->setFrom(TimeZone::getTimeAgo(Option::getByAddon('date_range', 'mini_chart', '14')));

        // Fill `$dailyHits` based on MiniChart's `metric` option
        $dailyHits = Helper::checkMiniChartOption('metric', 'visitors', 'visitors') ? $dataProvider->getDailyVisitors() : $dataProvider->getDailyViews();

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
            return $a['ymdDate'] <=> $b['ymdDate'];
        });

        // Some settings for the chart
        $chartSettings = [
            'color'  => MiniChartHelper::getChartColor(),
            'border' => MiniChartHelper::getBorderColor(),
            'label'  => MiniChartHelper::getTooltipLabel(),
        ];

        // Reset date range because text summary displays info for the past week
        $dataProvider->setFrom(TimeZone::getTimeAgo(7));

        return [
            'postId'                     => $post->ID,
            'fromString'                 => $dataProvider->getFromString(),
            'toString'                   => $dataProvider->getToString(),
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
