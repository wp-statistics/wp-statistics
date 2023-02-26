<?php

namespace WP_STATISTICS;

class License
{
    public function __construct()
    {
    }

    public static function getAddOns()
    {
        return apply_filters('wp_statistics_addons', array());
    }

    public function wp_sms_check_remote_license($addOnKey, $licenseKey)
    {
        $response = wp_remote_get(add_query_arg(array(
            'plugin-name' => $addOnKey,
            'license_key' => $licenseKey,
            'website'     => get_bloginfo('url'),
        ), 'https://wp-statistics.com/wp-json/plugins/v1/validate'));

        if (is_wp_error($response)) {
            return;
        }

        $response = json_decode($response['body']);

        if (isset($response->status) and $response->status == 200) {
            return true;
        }
    }
}
