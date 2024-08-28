<?php

namespace WP_Statistics\Service\Admin\PageInsights;

class PageInsightsManager
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
        $items['page-insights'] = [
            'sub'       => 'overview',
            'title'     => esc_html__('Page Insights', 'wp-statistics'),
            'page_url'  => 'pages',
            'callback'  => PageInsightsPage::class,
            'priority'  => 26
        ];

        return $items;
    }
}
