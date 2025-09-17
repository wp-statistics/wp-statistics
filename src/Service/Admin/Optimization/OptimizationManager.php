<?php
namespace WP_Statistics\Service\Admin\Optimization;

use WP_STATISTICS\Option;
use WP_STATISTICS\User;

class OptimizationManager
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
        $items['optimize'] = [
            'sub'      => 'overview',
            'title'    => esc_html__('Optimization', 'wp-statistics'),
            'cap'      => User::ExistCapability(Option::get('manage_capability', 'manage_options')),
            'page_url' => 'optimization',
            'method'   => 'optimization',
            'priority' => 110,
            'callback' => OptimizationPage::class
        ];

        return $items;
    }
}