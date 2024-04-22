<?php

namespace WP_Statistics\Service\ContentAnalytics;

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
                'pages'     => array('pages' => true),
                'title'     => esc_html__('Content Analytics', 'wp-statistics'),
                'page_url'  => 'content-analytics',
                'callback'  => ContentAnalyticsPage::class,
            ]
        ];

        array_splice($items, 13, 0, $newItem);

        return $items;
    }
}
