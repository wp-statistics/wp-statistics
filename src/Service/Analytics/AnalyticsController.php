<?php

namespace WP_Statistics\Service\Analytics;

use WP_STATISTICS\Helper;
use WP_STATISTICS\Hits;
use WP_STATISTICS\User;

class AnalyticsController
{
    /**
     * Records tracker hit when Cache and "Bypass Ad Blockers" are enable.
     *
     * @return  void
     */
    public function hit_record_action_callback()
    {
        if (Helper::is_request('ajax') && User::Access('read')) {
            // Check Refer Ajax
            check_ajax_referer('wp_rest', 'nonce');

            // Start Record
            $exclusion = Hits::record();

            // Return response
            wp_send_json([
                'status'  => true,
                'data'    => array(
                    'exclusion' => $exclusion,
                ),
                'message' => __('Visitor Interaction Successfully Logged.', 'wp-statistics')
            ], 200);
        }

        exit;
    }
}
