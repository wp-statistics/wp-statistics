<?php

namespace WP_Statistics\Service\Admin\TrackerDebugger;

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
        $items['tracker_debugger'] = [
            'sub'       => 'overview',
            'title'     => '',
            'page_url'  => 'tracker-debugger',
            'callback'  => TrackerDebuggerPage::class,
            'priority'  => 100,
        ];

        return $items;
    }
}