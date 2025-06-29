<?php

namespace WP_Statistics\Service\Tracking;

use ErrorException;
use WP_STATISTICS\IP;
use WP_STATISTICS\Option;
use WP_STATISTICS\Pages;
use WP_Statistics\Utils\Request;
use WP_Statistics\Utils\Signature;
use WP_Statistics\Utils\Validator;
use WP_Statistics\Service\Integrations\WpConsentApi;

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
            'page_uri'         => [
                'required'        => true,
                'nullable'        => true,
                'type'            => 'string',
                'encoding'        => 'base64',
                'invalid_pattern' => Validator::getThreatPatterns()
            ],
            'search_query'     => [
                'required'        => true,
                'nullable'        => true,
                'type'            => 'string',
                'encoding'        => 'base64',
                'invalid_pattern' => Validator::getThreatPatterns()
            ],
            'source_id'        => [
                'type'     => 'number',
                'required' => true,
                'nullable' => false
            ],
            'resourceId'       => [
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
                'type'     => 'number',
                'required' => true,
                'nullable' => false
            ],
            'screenHeight'     => [
                'type'     => 'number',
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

        $timestamp = !empty($_SERVER['HTTP_X_WPS_TS']) ? (int)base64_decode($_SERVER['HTTP_X_WPS_TS']) : false;

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
        if (Option::get('do_not_track')) {
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
     * Returned keys:
     *  - source_type   Page‑type slug (post, page, product, …)
     *  - source_id     Related object ID (int)
     *  - search_query  Base‑64 encoded search term (empty string if none)
     *  - signature     (Optional) Anti‑tamper token when enabled
     *
     * @return array<string,int|string>
     * @since  15.0.0
     */
    public static function getHitsDefaultParams()
    {
        $page = Pages::get_page_type();

        $params = [
            'source_type'  => $page['type'],
            'source_id'    => (int)$page['id'],
            'search_query' => empty($page['search_query'])
                ? ''
                : base64_encode($page['search_query']),
        ];

        // Append request signature when the feature is active.
        if (self::isSignatureEnabled()) {
            $params['signature'] = Signature::generate([
                $params['source_type'],
                $params['source_id'],
            ]);
        }

        return $params;
    }

    /**
     * Decide whether the current visitor must be tracked anonymously.
     *
     * The request will be anonymised when all the following are true:
     *  • WP Consent API is active.
     *  • A consent‑level other than 'disabled' is configured.
     *  • The "anonymous_tracking" option is enabled.
     *  • The visitor has not granted the required consent level.
     *
     * @return bool True when tracking should mask visitor identifiers.
     * @since  15.0.0
     */
    public static function shouldTrackAnonymously()
    {
        // Ensure the Consent API integration is available.
        if (!WpConsentApi::isWpConsentApiActive()) {
            return false;
        }

        // Determine required consent level.
        $consentLevel = Option::get('consent_level_integration', 'disabled');
        if ($consentLevel === 'disabled') {
            return false;
        }

        // Anonymous tracking must be enabled in plugin settings.
        if (!Option::get('anonymous_tracking', false)) {
            return false;
        }

        // If the visitor HAS given consent, no need to anonymise.
        $hasConsent = function_exists('wp_has_consent') && wp_has_consent($consentLevel);

        return !$hasConsent;
    }
}
