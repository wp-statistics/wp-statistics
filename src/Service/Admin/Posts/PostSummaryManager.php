<?php

namespace WP_Statistics\Service\Admin\Posts;

use WP_Statistics\Components\Assets;

/**
 * This class is responsible for the "Statistics - Summary" widget/panel in edit posts
 */
class PostSummaryManager
{
    /**
     * Initializes the class.
     *
     * @param   \WP_Post    $post
     *
     * @throws  \Exception
     */
    public function __construct()
    {
        add_action('enqueue_block_editor_assets', [$this, 'enqueueSidebarPanelAssets']);
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
        global $pagenow;
        if ($pagenow === 'post-new.php') {
            return;
        }

        global $post;
        if (empty($post)) {
            return;
        }

        $postSummary = $this->getPostStatisticsSummary($post);
        if (empty($postSummary)) {
            return;
        }

        Assets::script('editor-sidebar', 'blocks/index.js', ['wp-plugins', 'wp-editor'], $postSummary);

        $styleFileName = is_rtl() ? 'style-index-rtl.css' : 'style-index.css';
        Assets::style('editor-sidebar', "blocks/$styleFileName");
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
    public function getPostStatisticsSummary($post)
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
