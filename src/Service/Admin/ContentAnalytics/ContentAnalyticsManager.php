<?php

namespace WP_Statistics\Service\Admin\ContentAnalytics;

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
        $newItem = [
            'content_analytics' => [
                'sub'       => 'overview',
                'title'     => esc_html__('Content Analytics', 'wp-statistics'),
                'page_url'  => 'content-analytics',
                'callback'  => ContentAnalyticsPage::class,
            ]
        ];

        return Helper::arraySpliceAssociative($items, 7, 0, $newItem);
    }
}
