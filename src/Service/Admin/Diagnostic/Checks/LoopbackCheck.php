<?php

namespace WP_Statistics\Service\Admin\Diagnostic\Checks;

use WP_Statistics\Components\Ajax;
use WP_Statistics\Service\Admin\Diagnostic\DiagnosticResult;

/**
 * Loopback Connectivity Check.
 *
 * Tests if the server can make HTTP requests to itself (loopback).
 * This is critical for background processes like wp-background-processing.
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
     * Test action name for loopback verification (without wp_statistics_ prefix).
     */
    private const TEST_ACTION = 'loopback_test';

    /**
     * Expected response for successful loopback.
     */
    private const EXPECTED_RESPONSE = 'loopback_ok';

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
        return false; // HTTP request is heavy
    }

    /**
     * {@inheritDoc}
     */
    public function run(): DiagnosticResult
    {
        // Register temporary handler for the test action (public for loopback)
        Ajax::register(self::TEST_ACTION, [$this, 'handleTestRequest'], true);

        $url = admin_url('admin-ajax.php');

        // Full action name with prefix for the request
        $fullAction = 'wp_statistics_' . self::TEST_ACTION;

        $startTime = microtime(true);

        $response = wp_remote_post($url, [
            'timeout'   => self::TIMEOUT,
            'blocking'  => true,
            'sslverify' => apply_filters('https_local_ssl_verify', false),
            'body'      => [
                'action' => $fullAction,
                'nonce'  => wp_create_nonce($fullAction),
            ],
        ]);

        $duration = round((microtime(true) - $startTime) * 1000); // ms

        // Check for WP_Error
        if (is_wp_error($response)) {
            return $this->fail(
                $response->get_error_message(),
                [
                    'error_code'    => $response->get_error_code(),
                    'error_message' => $response->get_error_message(),
                    'url'           => $url,
                ]
            );
        }

        // Check HTTP response code
        $code = wp_remote_retrieve_response_code($response);
        if ($code !== 200) {
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

        // Check response body
        $body = wp_remote_retrieve_body($response);
        if (strpos($body, self::EXPECTED_RESPONSE) === false) {
            return $this->fail(
                __('Loopback request succeeded but returned unexpected response.', 'wp-statistics'),
                [
                    'response_time' => $duration . 'ms',
                    'body_preview'  => substr($body, 0, 200),
                    'url'           => $url,
                ]
            );
        }

        // Check response time (warning if slow)
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

        // Success
        return $this->pass(
            __('Background processes can communicate with your server.', 'wp-statistics'),
            [
                'response_time' => $duration . 'ms',
                'url'           => $url,
            ]
        );
    }

    /**
     * Handle the test AJAX request.
     *
     * @return void
     */
    public function handleTestRequest(): void
    {
        // Verify nonce (use full action name with prefix)
        $fullAction = 'wp_statistics_' . self::TEST_ACTION;
        if (!wp_verify_nonce($_POST['nonce'] ?? '', $fullAction)) {
            wp_die('invalid_nonce');
        }

        // Return success response
        wp_die(self::EXPECTED_RESPONSE);
    }
}
