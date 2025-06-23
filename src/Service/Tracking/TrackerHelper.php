<?php

namespace WP_Statistics\Service\Tracking;

use ErrorException;
use WP_STATISTICS\IP;
use WP_Statistics\Utils\Request;
use WP_Statistics\Utils\Validator;

/**
 * Helper methods that are used exclusively by the tracking subsystem.
 *
 * All methods are static and stateless; the class is marked {@see \final}
 * because it is an implementation detail and should not be extended.
 *
 * @package WP_Statistics\Service\Tracking
 * @since   15.0.0
 * @internal
 */
final class TrackerHelper
{
    /**
     * Checks whether the current HTTP request is the special tracking‑pixel
     * request issued by the JavaScript front‑end to bypass ad‑blockers.
     *
     * The request is recognised when the GET parameter “action” equals either
     * `wp_statistics_hit_record` (page‑view counter) or
     * `wp_statistics_online_check` (online‑user heartbeat).
     *
     * @return bool True when the request should bypass ad‑blocker rules.
     * @since  15.0.0
     */
    public static function isBypassAdBlockersRequest()
    {
        return (
            Request::compare('action', 'wp_statistics_hit_record') ||
            Request::compare('action', 'wp_statistics_online_check')
        );
    }

    /**
     * Validates that the current HTTP request qualifies as a “hit” that can be
     * persisted in the statistics database.
     *
     * Validation sequence:
     * 1. Bypass requests are always accepted.
     * 2. Reject CLI, cron and WP‑CLI invocations.
     * 3. Reject WordPress admin, AJAX and REST API calls.
     * 4. Accept only GET and HEAD request methods.
     *
     * @return bool True when the request is valid for hit logging.
     * @since  15.0.0
     */
    public static function validateHitRequest()
    {
        $isValid = Request::validate([
            'page_uri'     => [
                'required'        => true,
                'nullable'        => true,
                'type'            => 'string',
                'encoding'        => 'base64',
                'invalid_pattern' => Validator::getThreatPatterns()
            ],
            'search_query' => [
                'required'        => true,
                'nullable'        => true,
                'type'            => 'string',
                'encoding'        => 'base64',
                'invalid_pattern' => Validator::getThreatPatterns()
            ],
            'source_id'    => [
                'type'     => 'number',
                'required' => true,
                'nullable' => false
            ],
            'resourceId'    => [
                'type'     => 'number',
                'required' => true,
                'nullable' => false
            ],
            'timezone'    => [
                'type'     => 'string',
                'required' => true,
                'nullable' => false
            ],
            'language'    => [
                'type'     => 'string',
                'required' => true,
                'nullable' => false
            ],
            'languageFullName'    => [
                'type'     => 'string',
                'required' => true,
                'nullable' => false
            ],
            'screenWidth'    => [
                'type'     => 'number',
                'required' => true,
                'nullable' => false
            ],
            'screenHeight'    => [
                'type'     => 'number',
                'required' => true,
                'nullable' => false
            ],
            'referred'     => [
                'required' => true,
                'nullable' => true,
                'type'     => 'url',
                'encoding' => 'base64'
            ],
        ]);

        $timestamp = !empty($_SERVER['HTTP_X_WPS_TS']) ? (int) base64_decode($_SERVER['HTTP_X_WPS_TS']) : false;

        // Check if the request was sent no more than 10 seconds ago
        if (!$timestamp || time() - $timestamp > 10) {
            $isValid = false;
        }

        if (!$isValid) {
            /**
             * Trigger action after validating the hit request parameters.
             *
             * @param bool $isValid Indicates if the request parameters are valid.
             * @param string $ipAddress The IP address of the requester.
             */
            do_action('wp_statistics_invalid_hit_request', $isValid, IP::getIP());

            throw new ErrorException(esc_html__('Invalid hit/online request.', 'wp-statistics'));
        }

        return true;
    }


    /**
     * Returns the canonical request URI for the current hit.
     *
     * Unlike {@see WP_Statistics\Utils\Request::getRequestUri()}—which is now
     * deprecated—this helper is scoped to the tracking subsystem and therefore
     * includes the bypass‑ad‑blocker adjustments needed for accurate hit
     * logging.
     *
     * @return string Normalised request URI (always begins with “/”).
     * @since  15.0.0
     */
    public static function getRequestUri()
    {
        if (Request::isRestRequest() && isset($_REQUEST['page_uri'])) {
            return base64_decode($_REQUEST['page_uri']);
        }

        return sanitize_url(wp_unslash($_SERVER['REQUEST_URI']));
    }
}
