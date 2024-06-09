<?php

namespace WP_Statistics\Service\Analytics;

use WP_STATISTICS\Helper;
use WP_STATISTICS\Hits;
use WP_STATISTICS\User;

class AnalyticsController
{
    /**
     * Records tracker hit when Cache and "Bypass Ad Blockers" are enabled.
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

    /**
     * Keeps user online when "Bypass Ad Blockers" is enabled.
     *
     * @return  void
     */
    public function keep_online_action_callback()
    {
        if (Helper::is_request('ajax')) {
            $visitorProfile = new VisitorProfile();

            \WP_STATISTICS\UserOnline::record($visitorProfile);

            // Return response
            wp_send_json([
                'status'  => true,
                'message' => __('User is online, the data is updated successfully.', 'wp-statistics')
            ], 200);
        }

        exit;
    }
}
