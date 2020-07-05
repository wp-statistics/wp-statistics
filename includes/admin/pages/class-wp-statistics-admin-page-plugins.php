<?php

namespace WP_STATISTICS;

class plugins_page
{

    public function __construct()
    {

        if (Menus::in_page('plugins')) {
            add_filter('screen_options_show_screen', '__return_false');
        }
    }

    /**
     * This function displays the HTML for the page.
     */
    public static function view()
    {

        // Activate or deactivate the selected plugin
        if (isset($_GET['action'])) {

            if ($_GET['action'] == 'activate') {
                $result = activate_plugin($_GET['plugin'] . '/' . $_GET['plugin'] . '.php');
                if (is_wp_error($result)) {
                    Helper::wp_admin_notice($result->get_error_message(), "error");
                } else {
                    Helper::wp_admin_notice(__('Add-On activated.', 'wp-statistics'), "success");
                }

            }

            if ($_GET['action'] == 'deactivate') {
                $result = deactivate_plugins($_GET['plugin'] . '/' . $_GET['plugin'] . '.php');
                if (is_wp_error($result)) {
                    Helper::wp_admin_notice($result->get_error_message(), "error");
                } else {
                    Helper::wp_admin_notice(__('Add-On deactivated.', 'wp-statistics'), "success");
                }
            }
        }

        Admin_Template::get_template(array('plugins'), Welcome::get_list_addons());
    }

}

new plugins_page;