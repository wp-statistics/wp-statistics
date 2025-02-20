<?php

namespace WP_Statistics\Service\Admin\TrackerUsageData;

use WP_Statistics\Components\Ajax;
use WP_STATISTICS\Option;

class TrackerUsageDataActions
{
    /**
     * Registers AJAX actions for tracker usage data.
     *
     * @return void
     */
    public function register()
    {
        Ajax::register('enable_usage_tracking', [$this, 'enableUsageTracking']);
    }

    /**
     * Enables usage tracking for WP Statistics.
     *
     * @return void
     */
    public function enableUsageTracking()
    {
        check_ajax_referer('wp_rest', 'wps_nonce');

        Option::update('enable_usage_tracking', true);

        wp_send_json_success(['message' => __('Enable usage tracking success.', 'wp-statistics')]);
        exit();
    }
}