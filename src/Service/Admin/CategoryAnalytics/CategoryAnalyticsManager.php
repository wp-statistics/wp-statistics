<?php

namespace WP_Statistics\Service\Admin\CategoryAnalytics;

class CategoryAnalyticsManager
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
            'category_analytics' => [
                'sub'       => 'overview',
                'pages'     => ['visits' => true],
                'title'     => esc_html__('Category Analytics', 'wp-statistics'),
                'page_url'  => 'category-analytics',
                'callback'  => CategoryAnalyticsPage::class,
            ]
        ];

        array_splice($items, 13, 0, $newItem);

        return $items;
    }
}
