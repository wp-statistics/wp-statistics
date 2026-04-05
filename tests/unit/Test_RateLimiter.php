<?php

namespace WP_Statistics\Tests\RateLimiter;

use WP_UnitTestCase;
use WP_Statistics\Service\Tracking\Core\RateLimiter;
use WP_Statistics\Service\Tracking\Core\Tracker;
use WP_Statistics\Service\Tracking\Methods\AjaxTracker;
use WP_Statistics\Service\Tracking\Methods\RestTracker;
use WP_Statistics\Utils\Signature;
use WP_Statistics\Components\Option;
use Exception;

/**
 * Tests for the IP-based rate limiter.
 *
 * Covers the RateLimiter class directly and its integration with
 * Tracker::record() and all transport methods (AJAX, REST, Hybrid).
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 * @since 15.1.0
 */
class Test_RateLimiter extends WP_UnitTestCase
{
    private $requestKeys = [
        'resource_uri_id', 'resource_id', 'resource_uri',
        'resource_type', 'referrer', 'timezone',
        'language_code', 'language_name', 'screen_width',
        'screen_height', 'user_id', 'signature', 'tracking_level',
    ];

    public function setUp(): void
    {
        parent::setUp();

        Option::updateValue('tracker_rate_limit', true);
        Option::updateValue('tracker_rate_limit_threshold', 5);

        wp_cache_flush();
    }

    public function tearDown(): void
    {
        Option::updateValue('tracker_rate_limit', false);
        Option::updateValue('tracker_rate_limit_threshold', 30);

        remove_all_filters('wp_statistics_tracker_rate_limit_threshold');
        remove_all_filters('wp_statistics_rate_limit_time_window');

        foreach ($this->requestKeys as $key) {
            unset($_REQUEST[$key]);
        }

        wp_cache_flush();
        parent::tearDown();
    }

    private function setValidRequest(array $overrides = []): void
    {
        if (empty($_SERVER['HTTP_USER_AGENT'])) {
            $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
        }

        $defaults = [
            'resource_uri_id' => '1',
            'resource_id'     => '1',
            'resource_uri'    => base64_encode('/test'),
            'resource_type'   => 'post',
            'referrer'        => base64_encode('https://example.com'),
            'timezone'        => 'UTC',
            'language_code'   => 'en',
            'language_name'   => 'English',
            'screen_width'    => '1920',
            'screen_height'   => '1080',
            'user_id'         => '0',
        ];

        $params       = array_merge($defaults, $overrides);
        $resourceType = $params['resource_type'] ?? '';
        $resourceId   = isset($params['resource_id']) ? (int) $params['resource_id'] : null;
        $userId       = (int) ($params['user_id'] ?? 0);

        $params['signature'] = Signature::generate([$resourceType, $resourceId, $userId]);

        foreach ($params as $key => $value) {
            $_REQUEST[$key] = $value;
        }
    }

    // ── RateLimiter::check() unit tests ─────────────────────────

    public function test_allows_requests_under_threshold(): void
    {
        for ($i = 0; $i < 5; $i++) {
            RateLimiter::check();
        }

        $this->addToAssertionCount(1);
    }

    public function test_blocks_requests_over_threshold(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionCode(429);

        for ($i = 0; $i < 6; $i++) {
            RateLimiter::check();
        }
    }

    public function test_noop_when_disabled(): void
    {
        Option::updateValue('tracker_rate_limit', false);

        for ($i = 0; $i < 100; $i++) {
            RateLimiter::check();
        }

        $this->addToAssertionCount(1);
    }

    public function test_is_enabled_returns_false_when_off(): void
    {
        Option::updateValue('tracker_rate_limit', false);
        $this->assertFalse(RateLimiter::isEnabled());
    }

    public function test_is_enabled_returns_true_when_on(): void
    {
        $this->assertTrue(RateLimiter::isEnabled());
    }

    // ── Filter tests ────────────────────────────────────────────

    public function test_threshold_filter_overrides_option(): void
    {
        add_filter('wp_statistics_tracker_rate_limit_threshold', function () {
            return 3;
        });

        $this->expectException(Exception::class);
        $this->expectExceptionCode(429);

        for ($i = 0; $i < 4; $i++) {
            RateLimiter::check();
        }
    }

    public function test_window_filter_changes_window(): void
    {
        add_filter('wp_statistics_rate_limit_time_window', function () {
            return 120;
        });

        $this->assertSame(120, RateLimiter::getTimeWindow());
    }

    // ── Integration: Tracker::record() ──────────────────────────

    public function test_tracker_record_throws_429_when_rate_limited(): void
    {
        $this->setValidRequest();

        // Exhaust the rate limit.
        for ($i = 0; $i < 5; $i++) {
            RateLimiter::check();
        }

        // The next call through Tracker::record() should throw 429
        // before reaching Payload::parse().
        $this->expectException(Exception::class);
        $this->expectExceptionCode(429);

        (new Tracker())->record();
    }

    public function test_tracker_record_passes_when_under_limit(): void
    {
        $this->setValidRequest();

        // One hit through the rate limiter, then record() should proceed
        // past the rate check (may still throw from other pipeline stages,
        // but NOT with code 429).
        try {
            (new Tracker())->record();
            $this->addToAssertionCount(1);
        } catch (Exception $e) {
            $this->assertNotEquals(429, $e->getCode(), 'Should not be rate-limited');
        }
    }

    public function test_tracker_record_skips_rate_limit_when_disabled(): void
    {
        Option::updateValue('tracker_rate_limit', false);
        $this->setValidRequest();

        // Exhaust what would be the limit.
        for ($i = 0; $i < 100; $i++) {
            wp_cache_set('wp_statistics_rl_' . md5('127.0.0.1'), $i + 1, 'wp_statistics_rate_limit', 60);
        }

        // record() should NOT throw 429 since rate limiting is disabled.
        try {
            (new Tracker())->record();
            $this->addToAssertionCount(1);
        } catch (Exception $e) {
            $this->assertNotEquals(429, $e->getCode(), 'Should not be rate-limited when disabled');
        }
    }

    // ── Integration: AJAX transport ─────────────────────────────
    //
    // AjaxTracker::hitCallback() calls wp_send_json() which invokes
    // wp_die() — hard to capture the HTTP status code in unit tests.
    // Since hitCallback() delegates to (new Tracker())->record(), and
    // we already test that path above, we verify the AJAX-specific
    // guard: hitCallback() returns early when not in an AJAX context.

    public function test_ajax_tracker_requires_ajax_context(): void
    {
        $this->setValidRequest();

        // Without AJAX context, hitCallback returns without calling record().
        $ajax = new AjaxTracker();

        ob_start();
        $ajax->hitCallback();
        $output = ob_get_clean();

        // No JSON output means it returned early (the Request::isFrom('ajax') check).
        $this->assertEmpty($output);
    }

    // ── Integration: REST transport ─────────────────────────────

    public function test_rest_tracker_returns_429_when_rate_limited(): void
    {
        $this->setValidRequest();

        // Exhaust the rate limit.
        for ($i = 0; $i < 5; $i++) {
            RateLimiter::check();
        }

        $rest    = new RestTracker();
        $request = new \WP_REST_Request('POST', '/wp-statistics/v2/hit');

        $response = $rest->recordHit($request);

        $this->assertSame(429, $response->get_status());
        $this->assertArrayHasKey('Retry-After', $response->get_headers());
    }

    public function test_rest_tracker_passes_when_under_limit(): void
    {
        $this->setValidRequest();

        $rest    = new RestTracker();
        $request = new \WP_REST_Request('POST', '/wp-statistics/v2/hit');

        $response = $rest->recordHit($request);

        $this->assertNotEquals(429, $response->get_status());
    }

    public function test_rest_tracker_retry_after_matches_window(): void
    {
        add_filter('wp_statistics_rate_limit_time_window', function () {
            return 120;
        });

        $this->setValidRequest();

        // Exhaust the rate limit.
        for ($i = 0; $i < 5; $i++) {
            RateLimiter::check();
        }

        $rest    = new RestTracker();
        $request = new \WP_REST_Request('POST', '/wp-statistics/v2/hit');

        $response = $rest->recordHit($request);
        $headers  = $response->get_headers();

        $this->assertSame('120', $headers['Retry-After']);
    }

    // ── Counter isolation ───────────────────────────────────────

    public function test_different_ips_have_independent_counters(): void
    {
        // Fill counter for IP "1.2.3.4".
        $_SERVER['REMOTE_ADDR'] = '1.2.3.4';
        for ($i = 0; $i < 5; $i++) {
            RateLimiter::check();
        }

        // Switch to a different IP — should NOT be rate-limited.
        $_SERVER['REMOTE_ADDR'] = '5.6.7.8';

        // Reset the static IP cache if any.
        RateLimiter::check();
        $this->addToAssertionCount(1);
    }
}
