<?php

namespace WP_Statistics\Tests\Tracking;

use WP_UnitTestCase;
use WP_Statistics\Service\Tracking\Methods\AjaxTracker;
use WP_Statistics\Service\Tracking\Methods\RestTracker;
use WP_Statistics\Service\Tracking\Core\Tracker;
use WP_Statistics\Service\Tracking\Core\Visitor;
use Exception;

/**
 * Tests for batch tracking (processBatch in BaseTracker) and Tracker::recordEngagement().
 *
 * @since 15.0.0
 */
class Test_BatchTracking extends WP_UnitTestCase
{
    protected function tearDown(): void
    {
        remove_all_actions('wp_statistics_batch_events');
        parent::tearDown();
    }

    // ── Visitor without payload ─────────────────────────────────

    public function test_visitor_can_be_created_without_payload()
    {
        $visitor = new Visitor();
        $this->assertNull($visitor->getRequest());
    }

    public function test_visitor_without_payload_returns_null_for_client_data()
    {
        $visitor = new Visitor();

        // Client-side data requires payload — null without it
        $this->assertNull($visitor->getStorableIp());
        $this->assertNull($visitor->getReferrer());
        $this->assertFalse($visitor->isReferred());
        $this->assertInstanceOf(\WP_Statistics\Service\Analytics\Referrals\SourceDetector::class, $visitor->getSource());
        $this->assertNull($visitor->getUserId());
    }

    public function test_visitor_without_payload_resolves_server_data()
    {
        $visitor = new Visitor();

        // Server-resolved methods should still work
        $this->assertIsString($visitor->getIp());
        $this->assertIsString($visitor->getHashedIp());
        $this->assertNotEmpty($visitor->getHashedIp());
    }

    // ── Tracker::recordEngagement() ─────────────────────────────

    public function test_record_engagement_returns_false_for_no_session()
    {
        $result = (new Tracker())->recordEngagement(5000);
        $this->assertFalse($result);
    }

    public function test_record_engagement_returns_false_for_zero_ms()
    {
        $result = (new Tracker())->recordEngagement(0);
        $this->assertFalse($result);
    }

    public function test_record_engagement_returns_false_for_sub_second()
    {
        $result = (new Tracker())->recordEngagement(400);
        $this->assertFalse($result);
    }

    // ── Batch processing dispatches raw events via hook ──────────

    public function test_batch_events_hook_fires_with_raw_array()
    {
        $captured = null;

        add_action('wp_statistics_batch_events', function ($events) use (&$captured) {
            $captured = $events;
        });

        $tracker = new AjaxTracker();
        $method = new \ReflectionMethod($tracker, 'processBatch');
        $method->setAccessible(true);

        $result = $method->invoke($tracker, json_encode([
            'engagement_time' => 0,
            'events' => [
                ['type' => 'custom_event', 'data' => ['event_name' => 'a']],
                ['type' => 'custom_event', 'data' => ['event_name' => 'b']],
            ],
        ]));

        $this->assertIsArray($captured);
        $this->assertCount(2, $captured);
        $this->assertSame('custom_event', $captured[0]['type']);
        $this->assertSame(2, $result['processed']);
    }

    public function test_no_events_does_not_fire_hook()
    {
        $fired = false;

        add_action('wp_statistics_batch_events', function () use (&$fired) {
            $fired = true;
        });

        $tracker = new AjaxTracker();
        $method = new \ReflectionMethod($tracker, 'processBatch');
        $method->setAccessible(true);

        $method->invoke($tracker, json_encode([
            'engagement_time' => 0,
            'events' => [],
        ]));

        $this->assertFalse($fired);
    }

    public function test_process_throws_on_null()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Missing batch data');

        $tracker = new AjaxTracker();
        $method = new \ReflectionMethod($tracker, 'processBatch');
        $method->setAccessible(true);
        $method->invoke($tracker, null);
    }

    public function test_process_throws_on_empty_string()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Missing batch data');

        $tracker = new AjaxTracker();
        $method = new \ReflectionMethod($tracker, 'processBatch');
        $method->setAccessible(true);
        $method->invoke($tracker, '');
    }

    public function test_process_throws_on_invalid_json()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid JSON payload');

        $tracker = new AjaxTracker();
        $method = new \ReflectionMethod($tracker, 'processBatch');
        $method->setAccessible(true);
        $method->invoke($tracker, 'not-json{{{');
    }

    public function test_process_with_engagement_and_no_session()
    {
        $tracker = new AjaxTracker();
        $method = new \ReflectionMethod($tracker, 'processBatch');
        $method->setAccessible(true);

        $result = $method->invoke($tracker, json_encode([
            'engagement_time' => 5000,
            'events' => [],
        ]));

        // No session exists — engagement not counted
        $this->assertSame(0, $result['processed']);
        $this->assertEmpty($result['errors']);
    }

    public function test_process_with_engagement_and_events_combined()
    {
        $captured = null;
        add_action('wp_statistics_batch_events', function ($events) use (&$captured) {
            $captured = $events;
        });

        $tracker = new AjaxTracker();
        $method = new \ReflectionMethod($tracker, 'processBatch');
        $method->setAccessible(true);

        $result = $method->invoke($tracker, json_encode([
            'engagement_time' => 5000,
            'events' => [
                ['type' => 'custom_event', 'data' => ['event_name' => 'test']],
            ],
        ]));

        // Events dispatched (1), engagement not counted (no session)
        $this->assertSame(1, $result['processed']);
        $this->assertCount(1, $captured);
    }

    // ── AjaxTracker batch structure ─────────────────────────────

    public function test_batch_action_constant_is_batch()
    {
        $this->assertSame('batch', AjaxTracker::BATCH_ACTION);
    }

    public function test_ajax_tracker_config_has_relative_endpoints()
    {
        $tracker = new AjaxTracker();
        $config  = $tracker->getTrackerConfig();

        $this->assertArrayHasKey('hitEndpoint', $config);
        $this->assertArrayHasKey('batchEndpoint', $config);
        $this->assertStringStartsWith('?action=', $config['hitEndpoint']);
        $this->assertStringStartsWith('?action=', $config['batchEndpoint']);
        $this->assertStringContainsString('wp_statistics_batch', $config['batchEndpoint']);
    }

    public function test_ajax_register_adds_batch_hooks()
    {
        $tracker = new AjaxTracker();

        $tracker->register();

        $this->assertNotFalse(has_action('wp_ajax_wp_statistics_batch'));
        $this->assertNotFalse(has_action('wp_ajax_nopriv_wp_statistics_batch'));
    }

    public function test_rest_register_adds_batch_route()
    {
        $tracker = new RestTracker();

        $hooksBefore = $GLOBALS['wp_filter']['rest_api_init'] ?? null;
        $countBefore = 0;
        if ($hooksBefore) {
            foreach ($hooksBefore->callbacks as $callbacks) {
                $countBefore += count($callbacks);
            }
        }

        $tracker->register();

        $hooksAfter = $GLOBALS['wp_filter']['rest_api_init'] ?? null;
        $countAfter = 0;
        if ($hooksAfter) {
            foreach ($hooksAfter->callbacks as $callbacks) {
                $countAfter += count($callbacks);
            }
        }

        $this->assertGreaterThan($countBefore, $countAfter);
    }

    // ── Removed methods ─────────────────────────────────────────

    public function test_no_record_batch_on_tracker()
    {
        $this->assertFalse(
            method_exists(Tracker::class, 'recordBatch'),
            'recordBatch should not exist on Tracker'
        );
    }
}
