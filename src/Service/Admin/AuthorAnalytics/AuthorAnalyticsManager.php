<?php

namespace WP_Statistics\Service\Admin\AuthorAnalytics;

use WP_STATISTICS\Helper;

class AuthorAnalyticsManager
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
            'author_analytics' => [
                'sub'      => 'overview',
                'pages'    => ['pages' => true],
                'title'    => esc_html__('Author Analytics', 'wp-statistics'),
                'page_url' => 'author-analytics',
                'callback' => AuthorAnalyticsPage::class
            ]
        ];

        return Helper::arraySpliceAssociative($items, 8, 0, $newItem);
    }
}
