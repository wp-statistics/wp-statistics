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
     * Get List WP Statistics addons
     */
    public static function get_list_addons()
    {
        $response        = wp_remote_get(self::$addons);
        $response_code   = wp_remote_retrieve_response_code($response);
        $error           = null;
        $args['plugins'] = array();

        // Check response
        if (is_wp_error($response)) {
            $args['error'] = $response->get_error_message();
        } else {
            if ($response_code == '200') {
                $args['plugins'] = json_decode($response['body']);
            } else {
                $args['error'] = $response['body'];
            }
        }

        return $args;
    }

    /**
     * Show change log
     */
    public static function show_change_log()
    {

        // Get Change Log From Github Api
        $response = wp_remote_get(self::$change_log);
        if (is_wp_error($response)) {
            return;
        }
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code == '200') {

            // Json Data To Array
            $data = json_decode($response['body']);

            // Load ParseDown
            if (!class_exists('\Parsedown')) {
                include(WP_STATISTICS_DIR . "includes/libraries/Parsedown.php");
            }
            $parse = new \Parsedown();

            // convert MarkDown To Html
            echo $parse->text(nl2br($data->body)); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
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