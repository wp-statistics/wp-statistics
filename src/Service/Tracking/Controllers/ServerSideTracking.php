<?php

namespace WP_STATISTICS\Service\Tracking\Controllers;

use WP_Statistics\Abstracts\BaseTrackerController;
use WP_STATISTICS\Option;
use WP_STATISTICS\Helper;
use WP_Statistics\Service\Integrations\WpConsentApi;
use WP_STATISTICS\Hits;
use WP_Statistics\Traits\ErrorLoggerTrait;

/**
 * Server-Side Tracking Controller
 *
 * Handles server-side visitor tracking in WP Statistics. This controller is responsible for
 * recording hits when client-side tracking (use_cache_plugin) is disabled. It integrates with
 * WordPress's core hooks to track page views and follows the plugin's privacy and consent settings.
 *
 * @deprecated This class will be deprecated in a future release. Consider using client-side tracking methods.
 */
class ServerSideTracking extends BaseTrackerController
{
    use ErrorLoggerTrait;

    /**
     * Initialize the server-side tracking controller.
     *
     * @since 15.0.0
     */
    public function __construct()
    {
        $this->register();
    }

    /**
     * Register tracking hooks if server-side tracking is enabled.
     *
     * Only registers hooks when client-side tracking (use_cache_plugin) is disabled.
     * Uses WordPress 'wp' hook to ensure all necessary data is available for tracking.
     *
     * @since 15.0.0
     */
    public function register()
    {
        if (!Option::get('use_cache_plugin')) {
            add_action('wp', [$this, 'trackServerSideCallback']);
        }
    }

    /**
     * Callback for server-side tracking.
     *
     * Handles the actual tracking process including:
     * - Checking if tracking should be skipped
     * - Validating consent requirements
     * - Recording hits
     * - Error handling
     *
     * @since 15.0.0
     */
    public function trackServerSideCallback()
    {
        try {
            if ($this->shouldSkipTracking()) {
                return;
            }

            $consentLevel = Option::get('consent_level_integration', 'disabled');

            if ($consentLevel == 'disabled' || Helper::shouldTrackAnonymously() || !WpConsentApi::isWpConsentApiActive() || !\function_exists('wp_has_consent') || \wp_has_consent($consentLevel)) {
                Hits::record();
            }
        } catch (\Exception $e) {
            self::errorListener();
        }
    }

    /**
     * Determine if tracking should be skipped.
     *
     * Checks various conditions where tracking should not occur:
     * - Favicon requests
     * - Admin pages
     * - Preview pages
     * - When client-side tracking is enabled
     * - When Do Not Track is enabled and respected
     *
     * @return bool True if tracking should be skipped, false otherwise.
     * @since 15.0.0
     */
    protected function shouldSkipTracking()
    {
        return is_favicon() || is_admin() || is_preview() || Option::get('use_cache_plugin') || Helper::dntEnabled();
    }

    /**
     * Get the route for this tracking controller.
     *
     * Server-side tracking doesn't use routes as it operates through WordPress hooks.
     *
     * @return string Empty string as no route is needed.
     * @since 15.0.0
     */
    public function getRoute()
    {
        return '';
    }
}