<?php
namespace WP_Statistics\Service\Admin\Exclusions;

use WP_STATISTICS\Option;

class ExclusionsManager
{
    public function __construct()
    {
        if (Option::get('record_exclusions')) {
            add_filter('wp_statistics_admin_menu_list', [$this, 'addMenuItem']);
        }
    }

    /**
     * Add menu item
     *
     * @param array $items
     * @return array
     */
    public function addMenuItem($items)
    {
        $items['exclusions'] = [
            'sub'      => 'overview',
            'title'    => esc_html__('Exclusions', 'wp-statistics'),
            'page_url' => 'exclusions',
            'callback' => ExclusionsPage::class,
            'priority' => 999
        ];

        return $items;
    }
}