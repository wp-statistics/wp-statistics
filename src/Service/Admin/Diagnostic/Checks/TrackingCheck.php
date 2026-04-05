<?php

namespace WP_Statistics\Service\Admin\Diagnostic\Checks;

use Exception;
use WP_Statistics\Bootstrap;
use WP_Statistics\Components\RemoteRequest;
use WP_Statistics\Service\Admin\Diagnostic\DiagnosticResult;
use WP_Statistics\Components\Option;
use WP_Statistics\Service\Tracking\Methods\AjaxTracker;
use WP_Statistics\Service\Tracking\Methods\HybridMode\HybridModeHandler;
use WP_Statistics\Service\Tracking\Methods\RestTracker;

/**
 * Tracking Endpoint Check.
 *
 * Tests if the active tracking endpoint is reachable by making
 * an HTTP request and verifying the server responds (any non-5xx
 * response proves the endpoint exists and is routed correctly).
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
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function run(): DiagnosticResult
    {
        if (!Option::getValue('useronline') && !Option::getValue('visitors')) {
            return $this->pass(
                __('Tracking is disabled in settings.', 'wp-statistics'),
                ['status' => 'disabled']
            );
        }

        $trackerManager = Bootstrap::get('tracking');
        $methodType     = $trackerManager->getMethodType();

        return $this->testEndpoint($methodType);
    }

    /**
     * Test the active tracking endpoint.
     *
     * @param string $methodType The active tracking method type (ajax, rest, hybrid).
     * @return DiagnosticResult
     */
    private function testEndpoint(string $methodType): DiagnosticResult
    {
        $url = $this->getEndpointUrl($methodType);

        if (!$url) {
            return $this->fail(
                sprintf(__('Unable to determine endpoint URL for tracking method "%s".', 'wp-statistics'), $methodType),
                ['method' => $methodType]
            );
        }

        $request   = new RemoteRequest($url, 'POST', [], [
            'timeout' => self::TIMEOUT,
            'body'    => $this->getTestBody($methodType),
        ]);
        $startTime = microtime(true);

        try {
            $request->execute(false, false);
        } catch (Exception $e) {
            return $this->fail(
                $e->getMessage(),
                [
                    'method' => $methodType,
                    'url'    => $url,
                ]
            );
        }

        $duration = round((microtime(true) - $startTime) * 1000);
        $code     = $request->getResponseCode();

        // 5xx = server error, anything else means the endpoint is reachable.
        // A 400 is expected since we send an empty/minimal payload on purpose.
        if ($code >= 500) {
            return $this->fail(
                sprintf(__('Tracking endpoint returned HTTP %d.', 'wp-statistics'), $code),
                [
                    'method'        => $methodType,
                    'http_code'     => $code,
                    'response_time' => $duration . 'ms',
                    'url'           => $url,
                ]
            );
        }

        if ($duration > 3000) {
            return $this->warning(
                sprintf(
                    __('Tracking endpoint is slow (%dms). Tracking may be delayed.', 'wp-statistics'),
                    $duration
                ),
                [
                    'method'        => $methodType,
                    'http_code'     => $code,
                    'response_time' => $duration . 'ms',
                    'url'           => $url,
                ]
            );
        }

        return $this->pass(
            __('Tracking endpoint is accessible.', 'wp-statistics'),
            [
                'method'        => $methodType,
                'http_code'     => $code,
                'response_time' => $duration . 'ms',
                'url'           => $url,
            ]
        );
    }

    /**
     * Get the endpoint URL for the given tracking method.
     *
     * @param string $methodType
     * @return string|null
     */
    private function getEndpointUrl(string $methodType): ?string
    {
        switch ($methodType) {
            case 'ajax':
                return admin_url('admin-ajax.php');

            case 'rest':
                return rest_url(RestTracker::API_NAMESPACE . '/' . RestTracker::ENDPOINT_HIT);

            case 'hybrid':
                return site_url('/mu-plugins/' . HybridModeHandler::ENDPOINT_FILE);

            default:
                return null;
        }
    }

    /**
     * Get the minimal POST body for the given tracking method.
     *
     * @param string $methodType
     * @return array
     */
    private function getTestBody(string $methodType): array
    {
        if ($methodType === 'ajax') {
            return ['action' => 'wp_statistics_' . AjaxTracker::ACTION];
        }

        return [];
    }
}
