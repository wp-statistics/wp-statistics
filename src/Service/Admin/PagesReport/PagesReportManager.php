<?php

namespace WP_Statistics\Service\Admin\PagesReport;

class PagesReportManager
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
        $items['pages'] = [
            'sub'       => 'overview',
            'title'     => esc_html__('Pages', 'wp-statistics'),
            'page_url'  => 'pages',
            'callback'  => PagesReportPage::class,
            'priority'  => 25
        ];

        return $items;
    }
}
