<?php

namespace WP_Statistics\Service\Analytics;

use WP_STATISTICS\Helper;
use WP_STATISTICS\Hits;
use WP_STATISTICS\UserOnline;

class AnalyticsController
{
    /**
     * Records tracker hit when Cache and "Bypass Ad Blockers" are enabled.
     *
     * @return  void
     */
    public function hit_record_action_callback()
    {
        if (Helper::is_request('ajax')) {
            // Start Record
            $exclusion    = Hits::record();
            $responseData = [
                'status' => $exclusion['exclusion_match'] == false,
            ];

            if ($exclusion['exclusion_match']) {
                $responseData['data'] = $exclusion;
            }

            // Return response
            wp_send_json($responseData, 200);
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
            UserOnline::record();

            // Return response
            wp_send_json([
                'status' => true
            ], 200);
        }

        exit;
    }
}
