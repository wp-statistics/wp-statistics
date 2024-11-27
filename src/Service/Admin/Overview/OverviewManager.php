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
        $items['overview-new'] = [
            'sub'      => 'overview',
            'title'    => esc_html__('Overview (NEW)', 'wp-statistics'),
            'page_url' => 'overview-new',
            'callback' => OverviewPage::class,
            'priority' => 21,
        ];

        return $items;
    }
}