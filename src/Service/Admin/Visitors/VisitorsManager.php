<?php

namespace WP_Statistics\Service\Admin\Visitors;

class VisitorsManager
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
        $items['visitors-report'] = [
            'sub'       => 'overview',
            'title'     => esc_html__('Visitors Report', 'wp-statistics'),
            'page_url'  => 'visitors-report',
            'callback'  => VisitorsPage::class,
            'priority'  => 20,
        ];

        return $items;
    }
}
