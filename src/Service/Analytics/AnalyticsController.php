<?php

namespace WP_Statistics\Service\Analytics;

use Exception;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Hits;
use WP_Statistics\Utils\Request;
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
        if (!Helper::is_request('ajax')) {
            return;
        }

        try {
            $this->checkSignature();
            Helper::validateHitRequest();

            Hits::record();
            wp_send_json(['status' => true]);

        } catch (Exception $e) {
            wp_send_json(['status' => false, 'data' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * Keeps user online when "Bypass Ad Blockers" is enabled.
     *
     * @return  void
     */
    public function online_check_action_callback()
    {
        if (!Helper::is_request('ajax')) {
            return;
        }

        try {
            $this->checkSignature();
            Helper::validateHitRequest();

            Hits::recordOnline();
            wp_send_json(['status' => true]);

        } catch (Exception $e) {
            wp_send_json(['status' => false, 'data' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * @return void
     * @doc https://wp-statistics.com/resources/managing-request-signatures/
     * @throws Exception
     */
    private function checkSignature()
    {
        if (Helper::isRequestSignatureEnabled()) {
            $signature = sanitize_text_field($_REQUEST['signature']);
            $payload   = [
                sanitize_text_field($_REQUEST['source_type']),
                (int)sanitize_text_field($_REQUEST['source_id']),
            ];

            if (!Signature::check($payload, $signature)) {
                throw new Exception(__('Invalid signature', 'wp-statistics'), 403);
            }
        }
    }
}
