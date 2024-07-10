<?php

namespace WP_Statistics\Service\Admin\CategoryAnalytics;

use WP_STATISTICS\Helper;

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
                'title'     => esc_html__('Category Analytics', 'wp-statistics'),
                'page_url'  => 'category-analytics',
                'callback'  => CategoryAnalyticsPage::class,
            ]
        ];

        return Helper::arraySpliceAssociative($items, 9, 0, $newItem);
    }
}
