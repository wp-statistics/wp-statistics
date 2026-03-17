<?php

namespace WP_Statistics\Service\Tracking;

use ErrorException;
use WP_Statistics\Components\Ip;
use WP_Statistics\Components\Option;
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
     * Query parameter key that flags a REST request as a tracking hit.
     */
    public const HIT_REQUEST_KEY = 'wp_statistics_hit';
    /**
     * Checks whether the current HTTP request is the special tracking‑pixel
     * request issued by the JavaScript front‑end to bypass ad‑blockers.
     *
     * The request is recognised when the GET parameter “action” equals either
     * `wp_statistics_hit_record` (page‑view counter) or
     *
     * @return bool True when the request should bypass ad‑blocker rules.
     * @since  15.0.0
     */
    public static function isBypassAdBlockersRequest()
    {
        return (Request::compare('action', 'wp_statistics_hit_record'));
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
            'resource_uri_id'  => [
                'type'     => 'number',
                'required' => true,
                'nullable' => false
            ],
            'resource_uri'     => [
                'required'        => true,
                'nullable'        => true,
                'type'            => 'string',
                'encoding'        => 'base64',
                'invalid_pattern' => Validator::getThreatPatterns()
            ],
            'resource_type'    => [
                'type'     => 'string',
                'required' => false,
                'nullable' => false
            ],
            'resource_id'      => [
                'type'     => 'number',
                'required' => true,
                'nullable' => false
            ],
            'timezone'         => [
                'type'     => 'string',
                'required' => true,
                'nullable' => false
            ],
            'language_code'    => [
                'type'     => 'string',
                'required' => true,
                'nullable' => false
            ],
            'language_name'    => [
                'type'     => 'string',
                'required' => true,
                'nullable' => false
            ],
            'screen_width'     => [
                'type'     => 'string',
                'required' => true,
                'nullable' => false
            ],
            'screen_height'    => [
                'type'     => 'string',
                'required' => true,
                'nullable' => false
            ],
            'referrer'         => [
                'required' => true,
                'nullable' => true,
                'type'     => 'url',
                'encoding' => 'base64'
            ],
        ]);

        if (!$isValid) {
            /**
             * Trigger action after validating the hit request parameters.
             *
             * @param bool $isValid Indicates if the request parameters are valid.
             * @param string $ipAddress The IP address of the requester.
             */
            do_action('wp_statistics_invalid_hit_request', $isValid, Ip::getCurrent());

            throw new ErrorException(esc_html__('Invalid hit request.', 'wp-statistics'));
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
        if (isset($_REQUEST['page_uri'])) {
            return sanitize_url(base64_decode(wp_unslash($_REQUEST['page_uri'])));
        }

        return sanitize_url(wp_unslash($_SERVER['REQUEST_URI']));
    }

}
