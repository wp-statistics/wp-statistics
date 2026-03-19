<?php

namespace WP_Statistics\Tests\Tracking;

use WP_UnitTestCase;
use WP_Statistics\Service\Tracking\Methods\BatchTracking;
use Exception;

/**
 * Tests for BatchTracking batch processing logic.
 *
 * Covers input validation, event processing, and engagement parsing.
 *
 * @since 15.0.0
 */
class Test_BatchTracking extends WP_UnitTestCase
{
    // ── parseAndProcess validation ───────────────────────────────

    public function test_parse_throws_on_null_data()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Missing batch data');
        BatchTracking::parseAndProcess(null);
    }

    public function test_parse_throws_on_empty_string()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Missing batch data');
        BatchTracking::parseAndProcess('');
    }

    public function test_parse_throws_on_invalid_json()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid JSON payload');
        BatchTracking::parseAndProcess('not-valid-json{{{');
    }

    public function test_parse_throws_with_400_status_code()
    {
        try {
            BatchTracking::parseAndProcess(null);
            $this->fail('Expected exception was not thrown');
        } catch (Exception $e) {
            $this->assertSame(400, $e->getCode());
        }
    }

    // ── Processing with no matching session ──────────────────────

    public function test_zero_engagement_returns_zero_processed()
    {
        $result = BatchTracking::parseAndProcess(
            json_encode(['engagement_time' => 0, 'events' => []])
        );

        $this->assertSame(0, $result['processed']);
        $this->assertEmpty($result['errors']);
    }

    public function test_no_events_and_no_engagement_returns_zero()
    {
        $result = BatchTracking::parseAndProcess(
            json_encode(['engagement_time' => 0])
        );

        $this->assertSame(0, $result['processed']);
        $this->assertEmpty($result['errors']);
    }

    public function test_engagement_with_no_session_returns_zero_processed()
    {
        // No visitor/session exists for this request — engagement lookup finds nothing
        $result = BatchTracking::parseAndProcess(
            json_encode(['engagement_time' => 5000, 'events' => []])
        );

        $this->assertSame(0, $result['processed']);
        // No error — just no matching session
        $this->assertEmpty($result['errors']);
    }

    // ── Event processing ─────────────────────────────────────────

    public function test_custom_event_fires_action_hooks()
    {
        $firedBatch = false;
        $firedRecord = false;
        $capturedName = '';
        $capturedData = [];

        add_action('wp_statistics_custom_event_batch', function ($name, $data) use (&$firedBatch, &$capturedName) {
            $firedBatch = true;
            $capturedName = $name;
        }, 10, 2);

        add_action('wp_statistics_record_custom_event', function ($name, $data) use (&$firedRecord, &$capturedData) {
            $firedRecord = true;
            $capturedData = $data;
        }, 10, 2);

        $result = BatchTracking::parseAndProcess(
            json_encode([
                'engagement_time' => 0,
                'events' => [
                    [
                        'type' => 'custom_event',
                        'data' => [
                            'event_name' => 'test_event',
                            'event_data' => json_encode(['key' => 'value']),
                        ],
                    ],
                ],
            ])
        );

        $this->assertSame(1, $result['processed']);
        $this->assertEmpty($result['errors']);
        $this->assertTrue($firedBatch, 'wp_statistics_custom_event_batch should fire');
        $this->assertTrue($firedRecord, 'wp_statistics_record_custom_event should fire');
        $this->assertSame('test_event', $capturedName);
        $this->assertArrayHasKey('key', $capturedData);

        remove_all_actions('wp_statistics_custom_event_batch');
        remove_all_actions('wp_statistics_record_custom_event');
    }

    public function test_event_with_missing_type_produces_error()
    {
        $result = BatchTracking::parseAndProcess(
            json_encode([
                'engagement_time' => 0,
                'events' => [
                    ['data' => ['event_name' => 'no_type']],
                ],
            ])
        );

        $this->assertSame(0, $result['processed']);
        $this->assertCount(1, $result['errors']);
        $this->assertStringContainsString('Missing event type', $result['errors'][0]);
    }

    public function test_event_with_empty_event_name_is_silently_skipped()
    {
        $fired = false;
        add_action('wp_statistics_record_custom_event', function () use (&$fired) {
            $fired = true;
        });

        $result = BatchTracking::parseAndProcess(
            json_encode([
                'engagement_time' => 0,
                'events' => [
                    [
                        'type' => 'custom_event',
                        'data' => ['event_name' => ''],
                    ],
                ],
            ])
        );

        // Event processed without error but action not fired (empty name guard)
        $this->assertSame(1, $result['processed']);
        $this->assertFalse($fired, 'Action should not fire for empty event name');

        remove_all_actions('wp_statistics_record_custom_event');
    }

    public function test_multiple_events_processed_independently()
    {
        $eventNames = [];
        add_action('wp_statistics_record_custom_event', function ($name) use (&$eventNames) {
            $eventNames[] = $name;
        }, 10, 2);

        $result = BatchTracking::parseAndProcess(
            json_encode([
                'engagement_time' => 0,
                'events' => [
                    [
                        'type' => 'custom_event',
                        'data' => ['event_name' => 'event_a'],
                    ],
                    [
                        'type' => 'custom_event',
                        'data' => ['event_name' => 'event_b'],
                    ],
                    [
                        'type' => 'custom_event',
                        'data' => ['event_name' => 'event_c'],
                    ],
                ],
            ])
        );

        $this->assertSame(3, $result['processed']);
        $this->assertEmpty($result['errors']);
        $this->assertSame(['event_a', 'event_b', 'event_c'], $eventNames);

        remove_all_actions('wp_statistics_record_custom_event');
    }

    public function test_event_data_as_json_string_is_decoded()
    {
        $capturedData = null;
        add_action('wp_statistics_record_custom_event', function ($name, $data) use (&$capturedData) {
            $capturedData = $data;
        }, 10, 2);

        BatchTracking::parseAndProcess(
            json_encode([
                'engagement_time' => 0,
                'events' => [
                    [
                        'type' => 'custom_event',
                        'data' => [
                            'event_name' => 'json_test',
                            'event_data' => '{"nested": "value"}',
                        ],
                    ],
                ],
            ])
        );

        $this->assertIsArray($capturedData);
        $this->assertSame('value', $capturedData['nested']);

        remove_all_actions('wp_statistics_record_custom_event');
    }

    // ── BatchTracking class structure ────────────────────────────

    public function test_batch_action_constant_is_batch()
    {
        $this->assertSame('batch', BatchTracking::BATCH_ACTION);
    }

    public function test_get_batch_endpoint_returns_absolute_ajax_url()
    {
        $batch = new BatchTracking();
        $endpoint = $batch->getBatchEndpoint();

        $this->assertStringContainsString('admin-ajax.php', $endpoint);
        $this->assertStringContainsString('action=wp_statistics_batch', $endpoint);
        // Should be an absolute URL
        $this->assertMatchesRegularExpression('/^https?:\/\//', $endpoint);
    }

    public function test_register_adds_ajax_hooks()
    {
        $batch = new BatchTracking();

        $beforeAjax = has_action('wp_ajax_wp_statistics_batch');
        $beforeNopriv = has_action('wp_ajax_nopriv_wp_statistics_batch');

        $batch->register();

        $afterAjax = has_action('wp_ajax_wp_statistics_batch');
        $afterNopriv = has_action('wp_ajax_nopriv_wp_statistics_batch');

        // Ajax::register adds both logged-in and logged-out hooks
        $this->assertNotFalse($afterAjax, 'wp_ajax_wp_statistics_batch should be registered');
        $this->assertNotFalse($afterNopriv, 'wp_ajax_nopriv_wp_statistics_batch should be registered');
    }

    public function test_register_adds_rest_api_init_hook()
    {
        $batch = new BatchTracking();

        // Remove all existing rest_api_init hooks to get a clean count
        $hooksBefore = $GLOBALS['wp_filter']['rest_api_init'] ?? null;
        $callbackCountBefore = 0;
        if ($hooksBefore) {
            foreach ($hooksBefore->callbacks as $priority => $callbacks) {
                $callbackCountBefore += count($callbacks);
            }
        }

        $batch->register();

        $hooksAfter = $GLOBALS['wp_filter']['rest_api_init'] ?? null;
        $callbackCountAfter = 0;
        if ($hooksAfter) {
            foreach ($hooksAfter->callbacks as $priority => $callbacks) {
                $callbackCountAfter += count($callbacks);
            }
        }

        $this->assertGreaterThan(
            $callbackCountBefore,
            $callbackCountAfter,
            'rest_api_init should have a new callback after register()'
        );
    }
}
