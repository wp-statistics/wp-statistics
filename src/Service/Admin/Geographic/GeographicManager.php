<?php

namespace WP_Statistics\Service\Admin\Geographic;

class GeographicManager
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
        $newItem = [
            'geographic' => [
                'require'  => ['geoip' => true, 'visitors' => true],
                'sub'      => 'overview',
                'title'    => esc_html__('Geographic', 'wp-statistics'),
                'page_url' => 'geographic',
                'callback' => GeographicPage::class,
            ]
        ];

        array_splice($items, 18, 0, $newItem);

        return $items;
    }

}