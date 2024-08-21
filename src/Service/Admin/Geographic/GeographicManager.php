<?php

namespace WP_Statistics\Service\Admin\Geographic;

use WP_STATISTICS\Helper;

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
        $items['geographic'] = [
            'sub'      => 'overview',
            'title'    => esc_html__('Geographic', 'wp-statistics'),
            'page_url' => 'geographic',
            'callback' => GeographicPage::class,
            'priority'  => 74,
        ];

        return $items;
    }

}