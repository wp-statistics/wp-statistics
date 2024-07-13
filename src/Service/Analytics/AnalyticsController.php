<?php

namespace WP_Statistics\Service\Analytics;

use WP_STATISTICS\Helper;
use WP_STATISTICS\Hits;
use WP_STATISTICS\UserOnline;
use WP_Statistics\Utils\Signature;

class AnalyticsController
{
    /**
     * Records tracker hit when Cache and "Bypass Ad Blockers" are enabled.
     *
     * @return  void
     */
    public function hit_record_action_callback()
    {
        $this->checkSignature();

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
        $this->checkSignature();

        if (Helper::is_request('ajax')) {
            UserOnline::record();

            // Return response
            wp_send_json([
                'status' => true
            ], 200);
        }

        exit;
    }

    /**
     * @return void
     * @doc https://wp-statistics.com/resources/managing-request-signatures/
     */
    private function checkSignature()
    {
        if (Helper::isRequestSignatureEnabled()) {
            $signature = sanitize_text_field($_REQUEST['signature']);
            $payload   = [
                sanitize_text_field($_REQUEST['current_page_type']),
                (int)sanitize_text_field($_REQUEST['current_page_id']),
            ];

            if (!Signature::check($payload, $signature)) {
                wp_send_json_error(__('Invalid signature', 'wp-statistics'), 403);
            }
        }
    }
}
