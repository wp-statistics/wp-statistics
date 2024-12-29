<?php

namespace WP_Statistics\Service\Admin\TrackerDebugger;

use WP_STATISTICS\Option;

class TrackerDebuggerManager
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
        if (empty(Option::get('use_cache_plugin'))) {
            return $items;
        }

        $items['tracker_debugger'] = [
            'sub'       => 'settings',
            'title'    => esc_html__('Tracker Debugger', 'wp-statistics'),
            'page_url'  => 'tracker-debugger',
            'callback'  => TrackerDebuggerPage::class,
            'priority'  => 100,
        ];

        return $items;
    }
}