<?php

namespace WP_Statistics\Service\AuthorAnalytics;

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
                'sub'       => 'overview',
                'pages'     => array('pages' => true),
                'title'     => esc_html__('Author Analytics', 'wp-statistics'),
                'page_url'  => 'author-analytics',
                'callback'  => AuthorAnalyticsPage::class,
            ]
        ];

        array_splice($items, 13, 0, $newItem);

        return $items;
    }
}
