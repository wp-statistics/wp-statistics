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
        if (User::Access('read') && $pagenow !== 'post-new.php') {
            if (!Option::get('disable_editor')) {
                add_action('enqueue_block_editor_assets', [$this, 'enqueueSidebarPanelAssets']);
            }

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
        // Display "Statistics - Summary" meta-box only in classic editor and only when `disable_editor` is disabled
        $displaySummary        = !Helper::is_gutenberg() && !Option::get('disable_editor');

        // Display "Statistics - Latest Visitors" meta-box only when DataPlus add-on is active and `latest_visitors_metabox` is enabled
        // Or when DataPlus add-on is not active and `disable_editor` is disabled
        $displayLatestVisitors = Helper::isAddOnActive('data-plus') ? Option::getByAddon('latest_visitors_metabox', 'data_plus', '1') === '1' : !Option::get('disable_editor');

        // Add meta-box to all post types
        foreach (Helper::get_list_post_type() as $screen) {
            if ($displaySummary) {
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
        $dataProvider    = null;
        $miniChartHelper = new MiniChartHelper();
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
            'label'  => $miniChartHelper->getTooltipLabel(),
        ];

        // Reset date range because text summary displays info for the past week
        $dataProvider->setFrom(TimeZone::getTimeAgo(7));
        $dataProvider->setTo(TimeZone::getTimeAgo());

        return [
            'postId'                     => $post->ID,
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
