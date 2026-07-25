<?php

namespace WP_Statistics\Service\Analytics;

use Exception;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Hits;
use WP_Statistics\Components\TrackingResponse;
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

        TrackingResponse::sendHeaders();

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
     * @return void
     * @doc https://wp-statistics.com/resources/managing-request-signatures/
     * @throws Exception
     */
    private function checkSignature()
    {
        // Verify the signature against the same request data that is recorded,
        // so a request cannot be authenticated with one identity while a
        // different identity is stored.
        if (!Helper::verifyHitSignature()) {
            throw new Exception(esc_html__('Invalid signature', 'wp-statistics'), 403);
        }
    }
}
