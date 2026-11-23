<?php

namespace WP_Statistics\Service\Admin\Posts;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_Statistics\Components\View;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Option;
use WP_STATISTICS\User;

/**
 * "WP Statistics" panel in the block editor's Post sidebar.
 *
 * Shows the same content as the "Statistics Summary" meta box (which then only shows in the
 * Classic Editor). Add-ons can add sections through the `WPStatisticsPostPanel` slot:
 *
 *     const { Fill } = window.wpStatistics.PostPanel;           // or wp.components.Fill with name 'WPStatisticsPostPanel'
 *     registerPlugin('my-addon', { render: () => <Fill>{ ({ postId }) => ... }</Fill> });
 *
 * Their script should depend on the {@see EditorPanel::HANDLE} handle.
 */
class EditorPanel
{
    /**
     * Script handle of the panel. Add-ons that fill the slot depend on it.
     */
    const HANDLE = 'wp-statistics-post-panel';

    /**
     * SlotFill name add-ons use to add sections to the panel.
     */
    const SLOT = 'WPStatisticsPostPanel';

    public function __construct()
    {
        add_action('enqueue_block_editor_assets', [$this, 'enqueue']);
    }

    /**
     * Whether the panel is used at all: the "Statistics Summary" editor setting is on,
     * the user can view statistics, and nobody turned the panel off by filter.
     *
     * When this is false, the meta box shows in the block editor as before.
     *
     * @return bool
     */
    public static function isAvailable()
    {
        if (Option::get('disable_editor') || !User::Access('read')) {
            return false;
        }

        /**
         * Filters whether WP Statistics uses its block editor sidebar panel.
         * Return false to keep the "Statistics Summary" meta box in the block editor instead.
         *
         * @param bool $enabled
         */
        return (bool)apply_filters('wp_statistics_editor_panel_enabled', true);
    }

    /**
     * Whether the panel shows for this post: an existing post of a tracked post type.
     *
     * @param \WP_Post|null $post
     * @return bool
     */
    public static function isAvailableForPost($post)
    {
        if (!$post instanceof \WP_Post || empty($post->ID) || $post->post_status === 'auto-draft') {
            return false;
        }

        if (!in_array($post->post_type, Helper::getPostTypes(), true)) {
            return false;
        }

        return self::isAvailable();
    }

    /**
     * Data for the panel script.
     *
     * @param \WP_Post $post
     * @return array
     */
    public static function getPanelData($post)
    {
        $summary = PostsManager::getPostStatisticsSummary($post->ID);

        $data = [
            'postId'   => intval($post->ID),
            'slot'     => self::SLOT,
            'html'     => (string)View::load('components/meta-box/post-summary', ['summary' => $summary], true),
            'chart'    => null,
            'hasData'  => !empty($summary),
            'isRtl'    => is_rtl(),
        ];

        if (!empty($summary['postChartData'])) {
            $data['chart'] = [
                'postChartData'     => $summary['postChartData'],
                'postChartSettings' => $summary['postChartSettings'],
            ];
        }

        /**
         * Filters the data passed to the block editor panel.
         *
         * @param array $data
         * @param \WP_Post $post
         */
        return apply_filters('wp_statistics_editor_panel_data', $data, $post);
    }

    /**
     * Enqueue the panel on the post editor (not the site or widget editors).
     *
     * @return void
     */
    public function enqueue()
    {
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;

        if (!$screen || $screen->base !== 'post' || !method_exists($screen, 'is_block_editor') || !$screen->is_block_editor()) {
            return;
        }

        $post = get_post();

        if (!self::isAvailableForPost($post)) {
            return;
        }

        $assetFile = WP_STATISTICS_DIR . 'assets/blocks/post-summary/post-summary.asset.php';
        if (!file_exists($assetFile)) {
            return;
        }

        $asset = include $assetFile;

        wp_enqueue_script(
            self::HANDLE,
            WP_STATISTICS_URL . 'assets/blocks/post-summary/post-summary.js',
            $asset['dependencies'] ?? [],
            $asset['version'] ?? WP_STATISTICS_VERSION,
            true
        );

        wp_add_inline_script(self::HANDLE, 'window.wpStatisticsPostPanelData = ' . wp_json_encode(self::getPanelData($post)) . ';', 'before');

        if (function_exists('wp_set_script_translations')) {
            wp_set_script_translations(self::HANDLE, 'wp-statistics', WP_STATISTICS_DIR . 'languages');
        }
    }
}
