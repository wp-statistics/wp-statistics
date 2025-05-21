<?php

namespace WP_STATISTICS\Service\Tracking\Controllers;

use WP_Statistics\Abstracts\BaseTrackerController;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Hits;
use WP_STATISTICS\Option;
use WP_Statistics\Service\Tracking\TrackingFactory;

/**
 * AJAX-based Tracking Controller
 *
 * Implements visitor tracking through WordPress AJAX endpoints when both client-side
 * tracking and ad blocker bypass are enabled. This controller provides a more robust
 * tracking solution that can bypass ad blockers by using WordPress's admin-ajax.php
 * instead of REST API endpoints. Manages both page hit recording and online user
 * status tracking through dedicated AJAX callbacks.
 *
 * @since 15.0.0
 */
class AjaxBasedTracking extends BaseTrackerController
{
    /**
     * REST API endpoint slug for recording page hits.
     * Used to register the /hit endpoint that handles tracking page views.
     *
     * @var string
     */
    public const HIT_ACTION = 'hit_record';

    /**
     * REST API endpoint slug for recording online user.
     * Used to register the /online endpoint that handles tracking online user.
     *
     * @var string
     */
    public const ONLINE_ACTION = 'online_check';

    /**
     * Initialize the AJAX tracking controller.
     *
     * @since 15.0.0
     */
    public function __construct()
    {
        $this->register();
    }

    /**
     * Register AJAX endpoints and filters for tracking.
     *
     * Only activates when both conditions are met:
     * - Client-side tracking is enabled (use_cache_plugin)
     * - Ad blocker bypass is enabled (bypass_ad_blockers)
     *
     * @return void
     * @since 15.0.0
     */
    public function register()
    {
        if (
            !Option::get('use_cache_plugin') ||
            !Option::get('bypass_ad_blockers', false)
        ) {
            return;
        }

        add_filter('wp_statistics_ajax_list', [$this, 'registerAjaxCallbacks']);
        add_filter('wp_statistics_js_localized_arguments', [$this, 'addLocalizedArguments']);
    }

    /**
     * Add tracking configuration to the localized JavaScript object.
     *
     * @param array $args Existing localized arguments
     * @return array Modified arguments with tracking configuration
     * @since 15.0.0
     */
    public function addLocalizedArguments($args)
    {
        $hitAction    = 'wp_statistics_' . self::HIT_ACTION;
        $onlineAction = 'wp_statistics_' . self::ONLINE_ACTION;

        $args['requestUrl']   = get_site_url();
        $args['hitParams']    = array_merge($args, ['action' => $hitAction]);
        $args['onlineParams'] = array_merge($args, ['action' => $onlineAction]);

        return $args;
    }

    /**
     * Register tracking endpoints with the AJAX dispatcher.
     *
     * @param array $list Existing AJAX endpoints list
     * @return array Updated list with tracking endpoints
     * @since 15.0.0
     */
    public function registerAjaxCallbacks($list)
    {
        $list[] = [
            'class'  => $this,
            'action' => self::HIT_ACTION,
            'public' => true,
        ];

        $list[] = [
            'class'  => $this,
            'action' => self::ONLINE_ACTION,
            'public' => true,
        ];

        return $list;
    }

    /**
     * Get the base URL for AJAX requests.
     *
     * @return string WordPress site URL
     * @since 15.0.0
     */
    public function getRoute()
    {
        return get_site_url();
    }

    /**
     * Handle page hit recording via AJAX.
     *
     * @return void Sends JSON response with status and optional error message
     * @since 15.0.0
     */
    public function hit_record_action_callback()
    {
        if (!Helper::is_request('ajax')) {
            return;
        }

        try {
            $this->checkSignature();
            Helper::validateHitRequest();

            TrackingFactory::hits()->record();
            wp_send_json(['status' => true]);

        } catch (Exception $e) {
            wp_send_json(['status' => false, 'data' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * Handle online status updates via AJAX.
     *
     * @return void Sends JSON response with status and optional error message
     * @since 15.0.0
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
}
