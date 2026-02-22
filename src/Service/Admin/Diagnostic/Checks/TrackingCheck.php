<?php

namespace WP_Statistics\Service\Admin\Diagnostic\Checks;

use Exception;
use WP_Statistics\Components\RemoteRequest;
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

        // Determine if using REST or AJAX based on bypass_ad_blockers setting
        if (Option::getValue('bypass_ad_blockers', false)) {
            return $this->testAjaxEndpoint();
        }

        return $this->testRestEndpoint($trackingRoute);
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

        $request   = new RemoteRequest($url, 'GET', [], [
            'timeout' => self::TIMEOUT,
        ]);
        $startTime = microtime(true);

        try {
            $request->execute(false, false);
        } catch (Exception $e) {
            return $this->fail(
                $e->getMessage(),
                [
                    'endpoint' => 'REST API',
                    'url'      => $url,
                ]
            );
        }

        $duration = round((microtime(true) - $startTime) * 1000);
        $code     = $request->getResponseCode();

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

        $request   = new RemoteRequest($url, 'POST', [], [
            'timeout' => self::TIMEOUT,
            'body'    => [
                'action' => 'wp_statistics_tracker',
            ],
        ]);
        $startTime = microtime(true);

        try {
            $request->execute(false, false);
        } catch (Exception $e) {
            return $this->fail(
                $e->getMessage(),
                [
                    'endpoint' => 'AJAX',
                    'url'      => $url,
                ]
            );
        }

        $duration = round((microtime(true) - $startTime) * 1000);
        $code     = $request->getResponseCode();

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
