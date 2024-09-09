<?php

namespace WP_Statistics\Service\Admin\LicenseManager;

class LicenseManagerManager
{

    public function __construct()
    {
        add_filter('wp_statistics_admin_menu_list', [$this, 'addMenuItem']);
    }

    /**
     * Adds menu item.
     *
     * @param array $items
     *
     * @return array
     *
     * @hooked filter: `wp_statistics_admin_menu_list` - 10
     */
    public function addMenuItem($items)
    {
        $items['license_manager'] = [
            'sub'      => 'overview',
            'title'    => esc_html__('License Manager', 'wp-statistics'),
            'name'     => '<span class="wps-text-warning">' . esc_html__('License Manager', 'wp-statistics') . '</span>',
            'page_url' => 'license_manager',
            'callback' => LicenseManagerPage::class,
            'priority' => 90,
            'break'    => true,
        ];

        return $items;
    }
}
