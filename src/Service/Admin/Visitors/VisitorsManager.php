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
        $items['visitors'] = [
            'sub'       => 'overview',
            'title'     => esc_html__('Visitors', 'wp-statistics'),
            'page_url'  => 'visitors',
            'callback'  => VisitorsPage::class,
            'priority'  => 20,
        ];

        return $items;
    }
}
