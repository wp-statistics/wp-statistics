<?php
namespace WP_Statistics\Service\Admin\Overview;

use WP_Statistics\Service\Admin\Overview\OverviewPage;

class OverviewManager
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
        // Top level parent menu item
        $items['parent'] = [
            'title'    => esc_html__('Statistics', 'wp-statistics'),
            'page_url' => 'overview',
            'icon'     => 'dashicons-chart-pie',
            'callback' => OverviewPage::class,
            'priority' => 10
        ];

        // Sub menu overview page
        $items['overview'] = [
            'sub'      => 'overview',
            'title'    => esc_html__('Overview', 'wp-statistics'),
            'page_url' => 'overview',
            'callback' => OverviewPage::class,
            'priority' => 15
        ];

        return $items;
    }
}