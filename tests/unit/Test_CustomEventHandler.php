<?php

namespace WP_Statistics\Tests\CustomEvent;

use WP_UnitTestCase;
use WP_Statistics\Service\CustomEvent\CustomEventHandler;
use WP_Statistics\Service\CustomEvent\CustomEventHelper;
use WP_Statistics\Service\CustomEvent\CustomEventDataParser;

/**
 * Tests for the deprecated custom event classes.
 *
 * These classes are now thin no-op proxies. The actual logic lives in
 * the premium EventTracker module (EventRegistry, EventValidator, EventRecorder).
 *
 * @since 15.1.0
 */
class Test_CustomEventHandler extends WP_UnitTestCase
{
    // ── CustomEventHandler (deprecated) ─────────────────────────

    public function test_handler_constructor_does_not_register_hooks()
    {
        remove_all_actions('wp_statistics_batch_events');
        remove_all_actions('wp_statistics_record_custom_event');

        new CustomEventHandler();

        $this->assertFalse(has_action('wp_statistics_batch_events'));
        $this->assertFalse(has_action('wp_statistics_record_custom_event'));
    }

    public function test_handler_on_batch_events_is_noop()
    {
        $fired = false;
        add_action('wp_statistics_custom_event_batch', function () use (&$fired) {
            $fired = true;
        });

        $handler = new CustomEventHandler();
        $handler->onBatchEvents([
            ['type' => 'custom_event', 'data' => ['event_name' => 'test']],
        ]);

        $this->assertFalse($fired);
    }

    public function test_handler_record_event_is_noop()
    {
        $this->expectNotToPerformAssertions();

        $handler = new CustomEventHandler();
        $handler->recordEvent('test', ['some' => 'data']);
    }

    // ── CustomEventHelper (deprecated) ──────────────────────────

    public function test_helper_get_custom_events_returns_empty()
    {
        $this->assertSame([], CustomEventHelper::getCustomEvents());
    }

    public function test_helper_get_active_returns_empty()
    {
        $this->assertSame([], CustomEventHelper::getActiveCustomEvents());
    }

    public function test_helper_is_event_active_returns_false()
    {
        $this->assertFalse(CustomEventHelper::isEventActive('anything'));
    }

    public function test_helper_find_event_returns_null()
    {
        $this->assertNull(CustomEventHelper::findEventByName('anything'));
    }

    public function test_helper_validate_name_returns_valid()
    {
        $result = CustomEventHelper::validateEventName('anything');
        $this->assertTrue($result['valid']);
    }

    public function test_helper_is_reserved_returns_false()
    {
        $this->assertFalse(CustomEventHelper::isEventNameReserved('page_view'));
    }

    public function test_helper_get_reserved_returns_empty()
    {
        $this->assertSame([], CustomEventHelper::getReservedEventNames());
    }

    // ── CustomEventDataParser (deprecated) ──────────────────────

    public function test_parser_returns_basic_structure()
    {
        $parser = new CustomEventDataParser('test_event', ['tu' => 'https://example.com']);
        $parsed = $parser->getParsedData();

        $this->assertSame('test_event', $parsed['event_name']);
        $this->assertNull($parsed['visitor_id']);
        $this->assertNull($parsed['page_id']);
        $this->assertNull($parsed['user_id']);
        $this->assertArrayHasKey('event_data', $parsed);
    }

    public function test_parser_get_default_fields()
    {
        $parser = new CustomEventDataParser('test', []);
        $this->assertSame(['visitor_id', 'user_id', 'resource_id'], $parser->getDefaultEventFields());
    }
}
