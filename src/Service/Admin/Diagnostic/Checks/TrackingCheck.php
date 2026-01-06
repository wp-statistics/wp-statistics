<?php

namespace WP_Statistics\Service\Admin\Diagnostic\Checks;

use WP_Statistics\Service\Admin\Diagnostic\DiagnosticResult;
use WP_Statistics\Service\Tracking\TrackerControllerFactory;
use WP_Statistics\Components\Option;

/**
 * Tracking Endpoint Check.
 *
 * Tests if the tracking endpoint (REST or AJAX) is accessible.
 *
 * @since 15.0.0
 */
class TrackingCheck extends AbstractCheck
{
    /**
     * Timeout for tracking endpoint test in seconds.
     */
    private const TIMEOUT = 10;

    /**
     * {@inheritDoc}
     */
    public function getKey(): string
    {
        return 'tracking';
    }

    /**
     * {@inheritDoc}
     */
    public function getLabel(): string
    {
        return __('Tracking Endpoint', 'wp-statistics');
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription(): string
    {
        return __('Tests if the visitor tracking endpoint is accessible.', 'wp-statistics');
    }

    /**
     * {@inheritDoc}
     */
    public function getHelpUrl(): ?string
    {
        return 'https://wp-statistics.com/resources/tracking-issues/';
    }

    /**
     * {@inheritDoc}
     */
    public function isLightweight(): bool
    {
        return false; // HTTP request is heavy
    }

    /**
     * {@inheritDoc}
     */
    public function run(): DiagnosticResult
    {
        // Check if tracking is disabled
        if (!Option::getValue('useronline') && !Option::getValue('visitors')) {
            return $this->pass(
                __('Tracking is disabled in settings.', 'wp-statistics'),
                ['status' => 'disabled']
            );
        }

        // Get the tracking route/endpoint
        $trackingRoute = TrackerControllerFactory::getTrackingRoute();

        if (empty($trackingRoute)) {
            return $this->fail(
                __('Could not determine tracking endpoint.', 'wp-statistics')
            );
        }

        // Determine if using REST or AJAX
        $isRestApi = Option::getValue('use_cache_plugin');

        if ($isRestApi) {
            return $this->testRestEndpoint($trackingRoute);
        }

        return $this->testAjaxEndpoint();
    }

    /**
     * Test REST API tracking endpoint.
     *
     * @param string $route The REST route.
     * @return DiagnosticResult
     */
    private function testRestEndpoint(string $route): DiagnosticResult
    {
        $url = rest_url($route);

        $startTime = microtime(true);

        $response = wp_remote_get($url, [
            'timeout'   => self::TIMEOUT,
            'sslverify' => apply_filters('https_local_ssl_verify', false),
        ]);

        $duration = round((microtime(true) - $startTime) * 1000);

        if (is_wp_error($response)) {
            return $this->fail(
                $response->get_error_message(),
                [
                    'endpoint'   => 'REST API',
                    'url'        => $url,
                    'error_code' => $response->get_error_code(),
                ]
            );
        }

        $code = wp_remote_retrieve_response_code($response);

        // REST endpoint should return 200 or 400 (missing params is OK)
        if (!in_array($code, [200, 400], true)) {
            return $this->fail(
                sprintf(__('REST endpoint returned HTTP %d.', 'wp-statistics'), $code),
                [
                    'endpoint'      => 'REST API',
                    'http_code'     => $code,
                    'response_time' => $duration . 'ms',
                    'url'           => $url,
                ]
            );
        }

        // Check response time
        if ($duration > 3000) {
            return $this->warning(
                sprintf(
                    __('REST endpoint is slow (%dms). Tracking may be delayed.', 'wp-statistics'),
                    $duration
                ),
                [
                    'endpoint'      => 'REST API',
                    'response_time' => $duration . 'ms',
                    'url'           => $url,
                ]
            );
        }

        return $this->pass(
            __('REST API tracking endpoint is accessible.', 'wp-statistics'),
            [
                'endpoint'      => 'REST API',
                'response_time' => $duration . 'ms',
                'url'           => $url,
            ]
        );
    }

    /**
     * Test AJAX tracking endpoint.
     *
     * @return DiagnosticResult
     */
    private function testAjaxEndpoint(): DiagnosticResult
    {
        $url = admin_url('admin-ajax.php');

        $startTime = microtime(true);

        $response = wp_remote_post($url, [
            'timeout'   => self::TIMEOUT,
            'sslverify' => apply_filters('https_local_ssl_verify', false),
            'body'      => [
                'action' => 'wp_statistics_tracker',
            ],
        ]);

        $duration = round((microtime(true) - $startTime) * 1000);

        if (is_wp_error($response)) {
            return $this->fail(
                $response->get_error_message(),
                [
                    'endpoint'   => 'AJAX',
                    'url'        => $url,
                    'error_code' => $response->get_error_code(),
                ]
            );
        }

        $code = wp_remote_retrieve_response_code($response);

        if ($code !== 200) {
            return $this->fail(
                sprintf(__('AJAX endpoint returned HTTP %d.', 'wp-statistics'), $code),
                [
                    'endpoint'      => 'AJAX',
                    'http_code'     => $code,
                    'response_time' => $duration . 'ms',
                    'url'           => $url,
                ]
            );
        }

        // Check response time
        if ($duration > 3000) {
            return $this->warning(
                sprintf(
                    __('AJAX endpoint is slow (%dms). Tracking may be delayed.', 'wp-statistics'),
                    $duration
                ),
                [
                    'endpoint'      => 'AJAX',
                    'response_time' => $duration . 'ms',
                    'url'           => $url,
                ]
            );
        }

        return $this->pass(
            __('AJAX tracking endpoint is accessible.', 'wp-statistics'),
            [
                'endpoint'      => 'AJAX',
                'response_time' => $duration . 'ms',
                'url'           => $url,
            ]
        );
    }
}
