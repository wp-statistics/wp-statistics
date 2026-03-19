<?php

namespace WP_Statistics\Tests\CustomEvent;

use WP_UnitTestCase;
use WP_Statistics\Service\CustomEvent\CustomEventHandler;
use WP_Statistics\Service\CustomEvent\CustomEventHelper;

/**
 * Tests for CustomEventHandler and the event registration/validation pipeline.
 *
 * @since 15.0.0
 */
class Test_CustomEventHandler extends WP_UnitTestCase
{
    protected function tearDown(): void
    {
        remove_all_filters('wp_statistics_internal_custom_events');
        remove_all_filters('wp_statistics_custom_events');
        remove_all_filters('wp_statistics_active_custom_events');
        remove_all_filters('wp_statistics_excluded_custom_events');
        remove_all_actions('wp_statistics_batch_events');
        remove_all_actions('wp_statistics_record_custom_event');
        remove_all_actions('wp_statistics_custom_event_batch');
        parent::tearDown();
    }

    // ── Hook registration ────────────────────────────────────────

    public function test_constructor_registers_record_event_action()
    {
        $beforeCount = has_action('wp_statistics_record_custom_event');

        new CustomEventHandler();

        $afterCount = has_action('wp_statistics_record_custom_event');

        $this->assertNotFalse($afterCount, 'wp_statistics_record_custom_event should have a handler');
    }

    public function test_constructor_registers_batch_events_action()
    {
        new CustomEventHandler();

        $this->assertNotFalse(
            has_action('wp_statistics_batch_events'),
            'wp_statistics_batch_events should have a handler'
        );
    }

    public function test_record_event_method_exists()
    {
        $handler = new CustomEventHandler();
        $this->assertTrue(method_exists($handler, 'recordEvent'));
    }

    // ── onBatchEvents filtering ─────────────────────────────────

    public function test_on_batch_events_ignores_non_custom_event_type()
    {
        $fired = false;
        add_action('wp_statistics_custom_event_batch', function () use (&$fired) {
            $fired = true;
        });

        $handler = new CustomEventHandler();
        $handler->onBatchEvents([
            ['type' => 'some_other_type', 'data' => ['event_name' => 'test']],
        ]);

        $this->assertFalse($fired, 'onBatchEvents should ignore non-custom_event types');
    }

    public function test_on_batch_events_ignores_empty_event_name()
    {
        $fired = false;
        add_action('wp_statistics_custom_event_batch', function () use (&$fired) {
            $fired = true;
        });

        $handler = new CustomEventHandler();
        $handler->onBatchEvents([
            ['type' => 'custom_event', 'data' => ['event_name' => '']],
        ]);

        $this->assertFalse($fired, 'onBatchEvents should skip empty event names');
    }

    public function test_on_batch_events_fires_batch_hook_for_valid_event()
    {
        $capturedNames = [];

        add_action('wp_statistics_custom_event_batch', function ($name) use (&$capturedNames) {
            $capturedNames[] = $name;
        }, 10, 2);

        $handler = new CustomEventHandler();
        $handler->onBatchEvents([
            ['type' => 'custom_event', 'data' => ['event_name' => 'event_a']],
            ['type' => 'other_type',   'data' => ['event_name' => 'skipped']],
            ['type' => 'custom_event', 'data' => ['event_name' => 'event_b']],
        ]);

        $this->assertSame(['event_a', 'event_b'], $capturedNames);
    }

    public function test_on_batch_events_decodes_json_string_event_data()
    {
        $capturedData = null;

        add_action('wp_statistics_custom_event_batch', function ($name, $data) use (&$capturedData) {
            $capturedData = $data;
        }, 10, 2);

        $handler = new CustomEventHandler();
        $handler->onBatchEvents([
            ['type' => 'custom_event', 'data' => [
                'event_name' => 'json_test',
                'event_data' => '{"nested": "value"}',
            ]],
        ]);

        $this->assertIsArray($capturedData);
        $this->assertSame('value', $capturedData['nested']);
    }

    // ── recordEvent guards ───────────────────────────────────────

    public function test_record_event_returns_early_for_empty_name()
    {
        $this->expectNotToPerformAssertions();

        $handler = new CustomEventHandler();
        $handler->recordEvent('', ['some' => 'data']);
    }

    public function test_record_event_respects_event_tracking_option()
    {
        $this->expectNotToPerformAssertions();

        // Ensure event_tracking is off
        update_option('wp_statistics', array_merge(
            get_option('wp_statistics', []),
            ['event_tracking' => false]
        ));

        $handler = new CustomEventHandler();
        $handler->recordEvent('click', ['some' => 'data']);

        // No exception thrown = option gate worked. Restore.
        update_option('wp_statistics', array_merge(
            get_option('wp_statistics', []),
            ['event_tracking' => true]
        ));
    }

    // ── CustomEventHelper — event registration ───────────────────

    public function test_get_active_custom_events_returns_array()
    {
        $events = CustomEventHelper::getActiveCustomEvents();
        $this->assertIsArray($events);
    }

    public function test_internal_events_filter_registers_events()
    {
        // Simulate what EventTracker::registerBuiltInEvents does
        add_filter('wp_statistics_internal_custom_events', function ($events) {
            $events[] = ['machine_name' => 'test_event', 'name' => 'Test', 'status' => true];
            return $events;
        });

        $this->assertTrue(
            CustomEventHelper::isEventActive('test_event'),
            'Event registered via wp_statistics_internal_custom_events should be active'
        );
    }

    public function test_inactive_event_is_not_active()
    {
        add_filter('wp_statistics_internal_custom_events', function ($events) {
            $events[] = ['machine_name' => 'inactive_event', 'name' => 'Inactive', 'status' => false];
            return $events;
        });

        $this->assertFalse(
            CustomEventHelper::isEventActive('inactive_event'),
            'Event with status=false should not be active'
        );

        remove_all_filters('wp_statistics_internal_custom_events');
    }

    public function test_unregistered_event_is_not_active()
    {
        $this->assertFalse(
            CustomEventHelper::isEventActive('completely_unknown_event'),
            'Unregistered event should not be active'
        );
    }

    public function test_custom_events_filter_registers_events()
    {
        add_filter('wp_statistics_custom_events', function ($events) {
            $events[] = ['machine_name' => 'plugin_event', 'name' => 'Plugin Event', 'status' => true];
            return $events;
        });

        $this->assertTrue(
            CustomEventHelper::isEventActive('plugin_event'),
            'Event registered via wp_statistics_custom_events should be active'
        );
    }

    public function test_active_events_filter_can_override()
    {
        add_filter('wp_statistics_internal_custom_events', function ($events) {
            $events[] = ['machine_name' => 'override_test', 'name' => 'Test', 'status' => true];
            return $events;
        });

        // Override via the active events filter to remove it
        add_filter('wp_statistics_active_custom_events', function ($activeEvents) {
            return array_filter($activeEvents, function ($name) {
                return $name !== 'override_test';
            });
        });

        $this->assertFalse(
            CustomEventHelper::isEventActive('override_test'),
            'wp_statistics_active_custom_events filter should be able to deactivate events'
        );

        remove_all_filters('wp_statistics_internal_custom_events');    }

    // ── Excluded events ──────────────────────────────────────────

    public function test_excluded_events_filter_blocks_event_names()
    {
        add_filter('wp_statistics_excluded_custom_events', function ($excluded) {
            $excluded[] = 'blocked_event';
            return $excluded;
        });

        // Even if registered, excluded events should be blocked at validation
        $excludedEvents = apply_filters('wp_statistics_excluded_custom_events', []);
        $this->assertContains('blocked_event', $excludedEvents);
    }

    // ── Built-in event types (click, file_download) ──────────────

    public function test_click_and_file_download_registered_via_internal_filter()
    {
        // Simulate the premium EventTracker registering built-in events
        add_filter('wp_statistics_internal_custom_events', function ($events) {
            $events[] = ['machine_name' => 'click', 'name' => 'Click', 'status' => true];
            $events[] = ['machine_name' => 'file_download', 'name' => 'File Download', 'status' => true];
            return $events;
        });

        $this->assertTrue(CustomEventHelper::isEventActive('click'));
        $this->assertTrue(CustomEventHelper::isEventActive('file_download'));

        remove_all_filters('wp_statistics_internal_custom_events');
    }

    public function test_click_not_active_without_registration()
    {
        // Without the premium module registering events, click should not be active
        remove_all_filters('wp_statistics_internal_custom_events');
        remove_all_filters('wp_statistics_custom_events');

        $this->assertFalse(
            CustomEventHelper::isEventActive('click'),
            'click should not be active without premium module registration'
        );
    }

    // ── CustomEventDataParser ────────────────────────────────────

    public function test_data_parser_rejects_inactive_event()
    {
        remove_all_filters('wp_statistics_internal_custom_events');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('does not exist');

        $parser = new \WP_Statistics\Service\CustomEvent\CustomEventDataParser(
            'nonexistent_event',
            ['some' => 'data']
        );
        $parser->getParsedData();
    }

    public function test_data_parser_rejects_empty_event_data()
    {
        add_filter('wp_statistics_internal_custom_events', function ($events) {
            $events[] = ['machine_name' => 'empty_data_test', 'name' => 'Test', 'status' => true];
            return $events;
        });

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('event data is not valid');

        $parser = new \WP_Statistics\Service\CustomEvent\CustomEventDataParser(
            'empty_data_test',
            []
        );
        $parser->getParsedData();

        remove_all_filters('wp_statistics_internal_custom_events');
    }

    public function test_data_parser_rejects_nested_arrays_in_event_data()
    {
        add_filter('wp_statistics_internal_custom_events', function ($events) {
            $events[] = ['machine_name' => 'array_test', 'name' => 'Test', 'status' => true];
            return $events;
        });

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('is an array');

        $parser = new \WP_Statistics\Service\CustomEvent\CustomEventDataParser(
            'array_test',
            ['nested' => ['not' => 'allowed']]
        );
        $parser->getParsedData();

        remove_all_filters('wp_statistics_internal_custom_events');
    }

    public function test_data_parser_returns_expected_structure()
    {
        add_filter('wp_statistics_internal_custom_events', function ($events) {
            $events[] = ['machine_name' => 'struct_test', 'name' => 'Test', 'status' => true];
            return $events;
        });

        $parser = new \WP_Statistics\Service\CustomEvent\CustomEventDataParser(
            'struct_test',
            ['tu' => 'https://example.com', 'ev' => 'click text']
        );
        $parsed = $parser->getParsedData();

        $this->assertSame('struct_test', $parsed['event_name']);
        $this->assertArrayHasKey('event_data', $parsed);
        $this->assertArrayHasKey('visitor_id', $parsed);
        $this->assertArrayHasKey('page_id', $parsed);
        $this->assertArrayHasKey('user_id', $parsed);

        // Custom fields should be in event_data, not stripped
        $this->assertArrayHasKey('tu', $parsed['event_data']);
        $this->assertArrayHasKey('ev', $parsed['event_data']);

        remove_all_filters('wp_statistics_internal_custom_events');
    }

    public function test_data_parser_strips_default_fields_from_event_data()
    {
        add_filter('wp_statistics_internal_custom_events', function ($events) {
            $events[] = ['machine_name' => 'strip_test', 'name' => 'Test', 'status' => true];
            return $events;
        });

        $parser = new \WP_Statistics\Service\CustomEvent\CustomEventDataParser(
            'strip_test',
            ['visitor_id' => 99, 'user_id' => 1, 'resource_id' => 5, 'tu' => 'https://test.com']
        );
        $parsed = $parser->getParsedData();

        // Default fields should be extracted to top level, not in event_data
        $this->assertArrayNotHasKey('visitor_id', $parsed['event_data']);
        $this->assertArrayNotHasKey('user_id', $parsed['event_data']);
        $this->assertArrayNotHasKey('resource_id', $parsed['event_data']);

        // Custom field should remain in event_data
        $this->assertArrayHasKey('tu', $parsed['event_data']);

        remove_all_filters('wp_statistics_internal_custom_events');
    }
}
