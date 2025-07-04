<?php
namespace WP_Statistics\Abstracts;

use Exception;
use WP_STATISTICS\Helper;
use WP_Statistics\Utils\Signature;

/**
 * Abstract base class for WP Statistics tracking implementations.
 * Implements request signature validation and defines the structure
 * for tracking endpoint registration and routing.
 *
 * @since 15.0.0
 */
abstract class BaseTrackerController {
    /**
     * REST API endpoint slug for recording page hits.
     * Used to register the /hit endpoint that handles tracking page views.
     *
     * @var string
     */
    protected const ENDPOINT_HIT = 'hit';

    /**
     * REST API endpoint slug for tracking online users.
     * Used to register the /online endpoint that updates user online status.
     *
     * @var string
     */
    protected const ENDPOINT_ONLINE = 'online';

    /**
     * Namespace for tracking endpoints.
     *
     * @since 15.0.0
     * @var string
     */
    protected $namespace = 'wp-statistics/v2';

    /**
     * Validate request signature for tracking authenticity.
     *
     * @since 15.0.0
     * @throws Exception Invalid signature results in 403 status code
     */
    protected function checkSignature() {
        if (Helper::isRequestSignatureEnabled()) {
            $signature = ! empty($_REQUEST['signature']) ? sanitize_text_field($_REQUEST['signature']) : '';
            $payload   = [
                ! empty($_REQUEST['source_type']) ? sanitize_text_field($_REQUEST['source_type']) : '',
                ! empty($_REQUEST['source_id']) ? (int)sanitize_text_field($_REQUEST['source_id']) : 0,
            ];

            if (!Signature::check($payload, $signature)) {
                throw new Exception(__('Invalid signature', 'wp-statistics'), 403);
            }
        }
    }

    /**
     * Register tracking endpoints.
     *
     * @since 15.0.0
     */
    abstract public function register();

    /**
     * Get tracking endpoint route.
     *
     * @since 15.0.0
     * @return string|null Tracking endpoint route
     */
    abstract public function getRoute();
}
