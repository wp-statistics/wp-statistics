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
            check_admin_referer('wps_optimization_nonce');

            foreach ($_POST['licences'] as $key => $licence) {
                $optionName            = AddOnsFactory::getSettingNameByKey($key);
                $option                = get_option($optionName);
                $option['license_key'] = sanitize_text_field($licence);

                // update license in Its option group
                update_option($optionName, $option);

                // delete transient & clear the cache
                $transientKey = AddOnsFactory::getLicenseTransientKey($key);
                delete_transient($transientKey);
            }
        }

        Admin_Template::get_template(array('plugins'), array('addOns' => AddOnsFactory::get()));
    }

}

new plugins_page;