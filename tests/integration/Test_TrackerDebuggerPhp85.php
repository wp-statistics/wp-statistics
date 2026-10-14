<?php

namespace WP_Statistics\Tests\Service\Debugger;

use WP_Statistics\Option;
use WP_Statistics\Service\Debugger\DebuggerFactory;
use WP_Statistics\Service\Debugger\Provider\TrackerProvider;
use WP_UnitTestCase;

class Test_TrackerDebuggerPhp85 extends WP_UnitTestCase
{
    /**
     * Ensure the debugger's tracker checks remain free of PHP deprecations.
     */
    public function test_tracker_checks_do_not_emit_deprecations()
    {
        $httpRequestFilter = static function () {
            return [
                'headers'  => [],
                'body'     => '{"status":true}',
                'response' => ['code' => 200, 'message' => 'OK'],
                'cookies'  => [],
                'filename' => null,
            ];
        };
        add_filter('pre_http_request', $httpRequestFilter);

        $previousOptions = Option::getOptions();
        $deprecations    = [];

        set_error_handler(static function ($severity, $message, $file, $line) use (&$deprecations) {
            if (in_array($severity, [E_DEPRECATED, E_USER_DEPRECATED], true)) {
                $deprecations[] = sprintf('%s:%d %s', $file, $line, $message);
                return true;
            }

            return false;
        });

        try {
            foreach ([false, true] as $bypassAdBlockers) {
                Option::update('bypass_ad_blockers', $bypassAdBlockers);
                (new DebuggerFactory())->getAllProviders();
            }
        } finally {
            remove_filter('pre_http_request', $httpRequestFilter);
            restore_error_handler();
            Option::save_options($previousOptions);
        }

        $this->assertSame([], $deprecations, implode("\n", $deprecations));
    }

    /**
     * Detects stale cached tracker URLs even when cache compatibility is disabled.
     */
    public function test_reports_broken_tracker_url_from_cached_frontend_html()
    {
        $previousOptions = Option::getOptions();
        $cachedTrackerUrl = home_url('/?cached_tracker=old-hash.js&ver=14.16.13');

        Option::update('use_cache_plugin', false);
        Option::update('bypass_ad_blockers', false);

        $httpRequestFilter = static function ($preempt, $args, $url) use ($cachedTrackerUrl) {
            $responseCode = 200;
            $body         = '{"status":true}';

            if ($url === home_url('/')) {
                $body = sprintf('<html><body><script src="%s" id="wp-statistics-tracker-js"></script></body></html>', esc_url($cachedTrackerUrl));
            } elseif ($url === $cachedTrackerUrl) {
                $responseCode = 404;
                $body         = 'File not found.';
            }

            return [
                'headers'  => [],
                'body'     => $body,
                'response' => ['code' => $responseCode, 'message' => $responseCode === 200 ? 'OK' : 'Not Found'],
                'cookies'  => [],
                'filename' => null,
            ];
        };

        add_filter('pre_http_request', $httpRequestFilter, 10, 3);

        try {
            $status = (new TrackerProvider())->getTrackerStatus();
        } finally {
            remove_filter('pre_http_request', $httpRequestFilter, 10);
            Option::save_options($previousOptions);
        }

        $this->assertTrue($status['frontendStatus']['checked']);
        $this->assertTrue($status['frontendStatus']['found']);
        $this->assertSame($cachedTrackerUrl, $status['frontendStatus']['url']);
        $this->assertSame(404, $status['frontendStatus']['responseCode']);
        $this->assertFalse($status['frontendStatus']['isAccessible']);
    }

    /**
     * Treats tracker request errors as inconclusive instead of successful.
     */
    public function test_frontend_tracker_request_error_is_inconclusive()
    {
        $previousOptions = Option::getOptions();
        $trackerUrl      = home_url('/tracker.js');

        $httpRequestFilter = static function ($preempt, $args, $url) use ($trackerUrl) {
            if ($url === $trackerUrl) {
                return new \WP_Error('http_request_failed', 'Connection failed.');
            }

            $body = $url === home_url('/')
                ? sprintf('<html><body><script src="%s" id="wp-statistics-tracker-js"></script></body></html>', esc_url($trackerUrl))
                : '{"status":true}';

            return [
                'headers'  => [],
                'body'     => $body,
                'response' => ['code' => 200, 'message' => 'OK'],
                'cookies'  => [],
                'filename' => null,
            ];
        };

        add_filter('pre_http_request', $httpRequestFilter, 10, 3);

        try {
            $status = (new TrackerProvider())->getTrackerStatus();
        } finally {
            remove_filter('pre_http_request', $httpRequestFilter, 10);
            Option::save_options($previousOptions);
        }

        $this->assertTrue($status['frontendStatus']['found']);
        $this->assertNull($status['frontendStatus']['responseCode']);
        $this->assertNull($status['frontendStatus']['isAccessible']);
    }

    /**
     * Resolves root-relative tracker sources against the home URL origin.
     */
    public function test_root_relative_tracker_url_uses_home_origin()
    {
        $homeUrlFilter = static function () {
            return 'https://example.com/blog/';
        };

        add_filter('home_url', $homeUrlFilter);

        try {
            $reflection = new \ReflectionClass(TrackerProvider::class);
            $provider   = $reflection->newInstanceWithoutConstructor();
            $method     = $reflection->getMethod('extractFrontendTrackerUrl');
            $method->setAccessible(true);

            $url = $method->invoke(
                $provider,
                '<script src="/blog/tracker.js?ver=14.16.17" id="wp-statistics-tracker-js"></script>'
            );
        } finally {
            remove_filter('home_url', $homeUrlFilter);
        }

        $this->assertSame('https://example.com/blog/tracker.js?ver=14.16.17', $url);
    }
}
