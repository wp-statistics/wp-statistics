<?php

namespace WP_Statistics\Service\Admin\VisitorInsights;

class VisitorInsightsManager
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
        $items['visitor-insights'] = [
            'sub'       => 'overview',
            'title'     => esc_html__('Visitor Insights', 'wp-statistics'),
            'page_url'  => 'visitors',
            'callback'  => VisitorInsightsPage::class,
            'priority'  => 25,
        ];

        return $items;
    }
}
