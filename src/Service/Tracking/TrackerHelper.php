<?php

namespace WP_Statistics\Service\Tracking;

use WP_Statistics\Utils\Request;

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
