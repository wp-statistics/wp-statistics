<?php

namespace WP_Statistics\Service\Tracking;

use ErrorException;
use WP_Statistics\Components\Ip;
use WP_Statistics\Globals\Option;
use WP_Statistics\Service\Resources\ResourcesFactory;
use WP_Statistics\Utils\Request;
use WP_Statistics\Utils\Signature;
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
            'resourceUriId'    => [
                'type'     => 'number',
                'required' => true,
                'nullable' => false
            ],
            'resourceUri'    => [
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
            'language'         => [
                'type'     => 'string',
                'required' => true,
                'nullable' => false
            ],
            'languageFullName' => [
                'type'     => 'string',
                'required' => true,
                'nullable' => false
            ],
            'screenWidth'      => [
                'type'     => 'string',
                'required' => true,
                'nullable' => false
            ],
            'screenHeight'     => [
                'type'     => 'string',
                'required' => true,
                'nullable' => false
            ],
            'referred'         => [
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
        if (Request::isRestApiCall() && isset($_REQUEST['page_uri'])) {
            return base64_decode($_REQUEST['page_uri']);
        }

        return sanitize_url(wp_unslash($_SERVER['REQUEST_URI']));
    }

    /**
     * Determine whether Do Not Track (DNT) is enabled in the user's browser,
     * and allowed by the plugin settings.
     *
     * Checks both the HTTP_DNT header and the DNT value from getallheaders(),
     * if available, and only if the plugin setting 'do_not_track' is enabled.
     *
     * @return bool True if DNT is enabled and respected, false otherwise.
     */
    public static function isDoNotTrackEnabled()
    {
        if (Option::getValue('do_not_track')) {
            return (isset($_SERVER['HTTP_DNT']) && $_SERVER['HTTP_DNT'] == 1) || (function_exists('getallheaders') && isset(getallheaders()['DNT']) && getallheaders()['DNT'] == 1);
        }

        return false;
    }

    /**
     * Determine if the request‑signature feature is active.
     *
     * @return bool True when enabled via the
     *              'wp_statistics_request_signature_enabled' filter.
     */
    public static function isSignatureEnabled()
    {
        return apply_filters('wp_statistics_request_signature_enabled', true);
    }

    /**
     * Build the default query parameters sent by the front‑end hit pixel.
     *
     * @return array<string,int|string>
     * @since  15.0.0
     */
    public static function getHitsDefaultParams()
    {
        $resource = ResourcesFactory::getCurrentResource();

        $params = [
            'resource_type' => $resource->getType(),
            'resource_id'   => $resource->getId(),
        ];

        // Append request signature when the feature is active.
        if (self::isSignatureEnabled()) {
            $params['signature'] = Signature::generate([
                $params['resource_type'],
                $params['resource_id'],
            ]);
        }

        return $params;
    }
}
