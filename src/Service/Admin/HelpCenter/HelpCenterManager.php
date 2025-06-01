<?php
namespace WP_Statistics\Service\Admin\HelpCenter;
use WP_STATISTICS\Option;

class HelpCenterManager
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
        $items['help_center'] = [
            'sub'      => 'overview',
            'title'    => esc_html__('Help Center', 'wp-statistics'),
            'page_url' => 'help-center',
            'callback' => HelpCenterPage::class,
            'priority' => 999
        ];

        return $items;
    }
}