<?php

namespace WP_Statistics\Service\Admin;

class AddOnsFactory
{
    private static $optionMap = [
        'wp-statistics-advanced-reporting' => 'wpstatistics_advanced_reporting_settings',
        'wp-statistics-customization'      => 'wpstatistics_customization_settings',
        'wp-statistics-widgets'            => 'wpstatistics_widgets_settings',
        'wp-statistics-realtime-stats'     => 'wpstatistics_realtime_stats_settings',
        'wp-statistics-mini-chart'         => 'wpstatistics_mini_chart_settings',
        'wp-statistics-rest-api'           => 'wpstatistics_rest_api_settings',
        'wp-statistics-data-plus'          => 'wpstatistics_data_plus_settings',
    ];

    public static function get()
    {
        $licenseDecorator = [];

        foreach (self::getFromRemote() as $addOn) {
            $licenseDecorator[] = new AddOnDecorator($addOn);
        }

        return $licenseDecorator;
    }

    private static function getFromRemote()
    {
        $addOnsRemoteUrl = WP_STATISTICS_SITE . '/wp-json/plugin/addons';
        $response        = wp_remote_get($addOnsRemoteUrl, ['timeout' => 35]);

        if (is_wp_error($response)) {
            return [];
        }

        if (200 != wp_remote_retrieve_response_code($response)) {
            return [];
        }

        $response = json_decode($response['body']);

        if (isset($response->items)) {
            return $response->items;
        }
    }

    public static function getSettingNameByKey($key)
    {
        if (self::$optionMap[$key]) {
            return self::$optionMap[$key];
        }
    }

    public static function getLicenseTransientKey($key)
    {
        return $key . '_license_response';
    }
}