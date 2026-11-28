<?php

namespace WP_Statistics\Service\Admin\ContentAnalytics;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_STATISTICS\Helper;
use WP_Statistics\Components\View;
use WP_Statistics\Models\ViewsModel;

class ContentAnalyticsManager
{

    public function __construct()
    {
        add_filter('wp_statistics_admin_menu_list', [$this, 'addMenuItem']);
        add_action('wp_statistics_single_content_widgets', [$this, 'renderCampaignsLockedCard'], 10);
        add_action('wp_statistics_single_content_widgets', [$this, 'renderClicksLockedCard'], 20);
        add_action('wp_statistics_single_content_widgets', [$this, 'renderNextPagesCard'], 30);
    }

    /**
     * @param string $plugin Plugin file, e.g. "wp-statistics-marketing/wp-statistics-marketing.php".
     * @return bool
     */
    private function isPluginActive($plugin)
    {
        if (!function_exists('is_plugin_active')) require_once ABSPATH . 'wp-admin/includes/plugin.php';

        return is_plugin_active($plugin);
    }

    /**
     * Locked "Campaigns" card in place of the Marketing card, only while Marketing is not active.
     *
     * @return void
     */
    public function renderCampaignsLockedCard()
    {
        if ($this->isPluginActive('wp-statistics-marketing/wp-statistics-marketing.php')) return;

        View::load('components/lock-sections/locked-card', [
            'title'   => __('Campaigns that brought visitors here', 'wp-statistics'),
            'text'    => __('See which campaign links (UTM) brought visitors to this post.', 'wp-statistics'),
            'addon'   => 'wp-statistics-marketing',
            'surface' => 'single-content-campaigns',
            'label'   => __('Unlock with Marketing', 'wp-statistics'),
        ]);
    }

    /**
     * Locked "Clicks and downloads" card in place of the Data Plus card, only while Data Plus is not active.
     *
     * @return void
     */
    public function renderClicksLockedCard()
    {
        if ($this->isPluginActive('wp-statistics-data-plus/wp-statistics-data-plus.php')) return;

        View::load('components/lock-sections/locked-card', [
            'title'   => __('Clicks and downloads on this post', 'wp-statistics'),
            'text'    => __('See which links and files readers click on this post.', 'wp-statistics'),
            'addon'   => 'wp-statistics-data-plus',
            'surface' => 'single-content-clicks',
            'label'   => __('Unlock with Data Plus', 'wp-statistics'),
        ]);
    }

    /**
     * "Where readers go next" card on the single content report: top 3 next pages, top 10 with Data Plus.
     *
     * @param int $postId
     * @return void
     */
    public function renderNextPagesCard($postId)
    {
        $unlocked = $this->isPluginActive('wp-statistics-data-plus/wp-statistics-data-plus.php');

        $data = (new ViewsModel())->getNextPages([
            'resource_id'   => intval($postId),
            'resource_type' => Helper::getPostTypes(),
            'per_page'      => $unlocked ? 10 : 3,
        ]);

        View::load('components/tables/next-pages', array_merge($data, ['unlocked' => $unlocked]));
    }

    /**
     * Add menu item
     *
     * @param array $items
     * @return array
     */
    public function addMenuItem($items)
    {
        $items['content_analytics'] = [
            'sub'       => 'overview',
            'title'     => esc_html__('Content Analytics', 'wp-statistics'),
            'page_url'  => 'content-analytics',
            'callback'  => ContentAnalyticsPage::class,
            'priority'  => 71,
        ];

        return $items;
    }
}
