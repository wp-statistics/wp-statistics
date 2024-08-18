<?php

namespace WP_Statistics\Service\Admin\Posts;

use WP_Statistics\Components\Assets;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Meta_Box;
use WP_STATISTICS\Option;
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

        Assets::script('editor-sidebar', 'blocks/index.js', ['wp-plugins', 'wp-editor'], $postSummary);

        $styleFileName = is_rtl() ? 'style-index-rtl.css' : 'style-index.css';
        Assets::style('editor-sidebar', "blocks/$styleFileName");
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
        // Add meta-box to all post types
        foreach (Helper::get_list_post_type() as $screen) {
            add_meta_box(
                Meta_Box::getMetaBoxKey('post-summary'),
                Meta_Box::getList('post-summary')['name'],
                Meta_Box::LoadMetaBox('post-summary'),
                $screen,
                'side',
                'high',
                [
                    '__block_editor_compatible_meta_box' => false,
                    '__back_compat_meta_box'             => false,
                ]
            );

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

    /**
     * Returns the data needed for "Statistics - Summary" widget/panel in edit posts.
     *
     * @param   \WP_Post    $post
     *
     * @return  array|null          Keys: 
     *  - `postId`
     *  - `fromString`
     *  - `toString`
     *  - `totalVisitors`
     *  - `totalViews`
     *  - `topReferrer`
     *  - `topReferrerCount`
     *  - `thisPeriodVisitors`
     *  - `thisPeriodViews`
     *  - `thisPeriodTopReferrer`
     *  - `thisPeriodTopReferrerCount`
     *  - `postChartData`
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
        $dailyViews   = $dataProvider->getDailyViews();
        $wpDateFormat = get_option('date_format');
        foreach ($dailyViews as $dailyView) {
            if (empty($dailyView->date) || empty($dailyView->views)) {
                continue;
            }

            $chartData[] = [
                'views'     => intval($dailyView->views),
                'shortDate' => date('d M', strtotime($dailyView->date)),
                'fullDate'  => date($wpDateFormat, strtotime($dailyView->date)),
            ];
        }

        return [
            'postId'                     => $post->ID,
            'fromString'                 => $dataProvider->getFromString(),
            'toString'                   => $dataProvider->getToString(),
            'totalVisitors'              => $dataProvider->getVisitors(true),
            'totalViews'                 => $dataProvider->getViews(true),
            'topReferrer'                => $topReferrerAndCountTotal['url'],
            'topReferrerCount'           => $topReferrerAndCountTotal['count'],
            'thisPeriodVisitors'         => $dataProvider->getVisitors(),
            'thisPeriodViews'            => $dataProvider->getViews(),
            'thisPeriodTopReferrer'      => $topReferrerAndCountThisPeriod['url'],
            'thisPeriodTopReferrerCount' => $topReferrerAndCountThisPeriod['count'],
            'postChartData'              => $chartData,
            'contentAnalyticsUrl'        => $dataProvider->getContentAnalyticsUrl(),
        ];
    }
}
