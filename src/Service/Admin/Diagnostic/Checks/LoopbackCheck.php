<?php

namespace WP_Statistics\Service\Admin\Diagnostic\Checks;

use Exception;
use WP_Statistics\Components\RemoteRequest;
use WP_Statistics\Service\Admin\Diagnostic\DiagnosticResult;

/**
 * Loopback Connectivity Check.
 *
 * Tests if the server can make HTTP requests to itself by hitting wp-cron.php.
 * This is critical for scheduled events and background processes.
 *
 * Common issues that cause loopback failures:
 * - Blocked by security plugins
 * - Firewall rules blocking self-connections
 * - DNS resolution issues with the site URL
 * - SSL certificate verification failures
 *
 * @since 15.0.0
 */
class LoopbackCheck extends AbstractCheck
{
    /**
     * Timeout for loopback request in seconds.
     */
    private const TIMEOUT = 10;

    /**
     * {@inheritDoc}
     */
    public function getKey(): string
    {
        return 'loopback';
    }

    /**
     * {@inheritDoc}
     */
    public function getLabel(): string
    {
        return __('Loopback Connectivity', 'wp-statistics');
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription(): string
    {
        return __('Tests if background processes can communicate with your server.', 'wp-statistics');
    }

    /**
     * {@inheritDoc}
     */
    public function getHelpUrl(): ?string
    {
        return 'https://developer.wordpress.org/advanced-administration/wordpress/loopback/';
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
        $url = site_url('/wp-cron.php');

        $request   = new RemoteRequest($url, 'POST', [], [
            'timeout'  => self::TIMEOUT,
            'blocking' => true,
        ]);
        $startTime = microtime(true);

        try {
            $request->execute(false, false);
        } catch (Exception $e) {
            return $this->fail(
                $e->getMessage(),
                [
                    'error_message' => $e->getMessage(),
                    'url'           => $url,
                ]
            );
        }

        $duration = round((microtime(true) - $startTime) * 1000);
        $code     = $request->getResponseCode();

        if ($code >= 500) {
            return $this->fail(
                sprintf(
                    __('Server returned HTTP %d instead of 200.', 'wp-statistics'),
                    $code
                ),
                [
                    'http_code'     => $code,
                    'response_time' => $duration . 'ms',
                    'url'           => $url,
                ]
            );
        }

        if ($duration > 5000) {
            return $this->warning(
                sprintf(
                    __('Loopback request is slow (%dms). Background processes may time out.', 'wp-statistics'),
                    $duration
                ),
                [
                    'response_time' => $duration . 'ms',
                    'url'           => $url,
                ]
            );
        }

        return $this->pass(
            __('Background processes can communicate with your server.', 'wp-statistics'),
            [
                'response_time' => $duration . 'ms',
                'url'           => $url,
            ]
        );
    }
}
