<?php

namespace WP_STATISTICS;

use WP_Statistics\Service\Admin\AddOnsFactory;

class plugins_page
{
    /**
     * plugins_page constructor.
     */
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
        if (isset($_POST['update-licence']) and $_POST['update-licence']) {
            // check the nonce
            //check_admin_referer($_GET['plugin']);

            foreach ($_POST['licences'] as $key => $licence) {
                $optionName            = AddOnsFactory::getSettingNameByKey($key);
                $option                = get_option($optionName);
                $option['license_key'] = sanitize_text_field($licence);

                update_option($optionName, $option);
            }

            Helper::wp_admin_notice(__('License updated', 'wp-statistics'), "success");

            wp_safe_redirect(admin_url('admin.php?page=wps_plugins_page'));
            exit();
        }

        Admin_Template::get_template(array('plugins'), array('addOns' => AddOnsFactory::get()));
    }

}

new plugins_page;