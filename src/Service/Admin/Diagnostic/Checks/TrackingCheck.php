<?php

namespace WP_Statistics\Service\Admin\Diagnostic\Checks;

use Exception;
use WP_Statistics\Components\RemoteRequest;
use WP_Statistics\Service\Admin\Diagnostic\DiagnosticResult;
use WP_Statistics\Components\Option;

/**
 * Tracking Endpoint Check.
 *
 * Tests if the AJAX tracking endpoint is accessible.
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

        return $this->testAjaxEndpoint();
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
