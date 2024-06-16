<?php

namespace WP_Statistics\Service\Admin\Devices;

class DevicesManager
{
    public function __construct()
    {
        add_filter('wp_statistics_admin_menu_list', [$this, 'addMenuItem']);
    }

    /**
     * Adds menu item
     *
     * @param   array   $items
     *
     * @return  array
     */
    public function addMenuItem($items)
    {
        $newItem = [
            'devices' => [
                'sub'      => 'overview',
                'title'    => esc_html__('Devices', 'wp-statistics'),
                'page_url' => 'devices',
                'callback' => DevicesPage::class,
            ]
        ];

        array_splice($items, 9, 0, $newItem);

        return $items;
    }
}
