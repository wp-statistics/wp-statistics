<?php

namespace WP_Statistics\Service\Admin\ContentAnalytics;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_STATISTICS\Helper;

class ContentAnalyticsManager
{

    public function __construct()
    {
        add_filter('wp_statistics_admin_menu_list', [$this, 'addMenuItem']);
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
