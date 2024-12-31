<?php

namespace WP_STATISTICS;

class Admin_Dashboard
{
    /**
     * Admin_Dashboard constructor.
     */
    public function __construct()
    {
        // Add plugin's global class name
        add_action('admin_body_class', array($this, 'add_plugin_body_class'));
    }

    public function add_plugin_body_class($classes)
    {
        // Add class for the admin body only for plugin's pages
        if (isset($_GET['page']) && strpos($_GET['page'], 'wps_') === 0) {
            $classes .= ' wps_page';
        }

        return $classes;
    }
}

new Admin_Dashboard;