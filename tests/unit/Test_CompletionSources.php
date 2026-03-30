<?php

namespace WP_Statistics\Tests;

use WP_Statistics\Service\AnalyticsQuery\Sources\EventCompletionsSource;
use WP_Statistics\Service\AnalyticsQuery\Sources\ViewCompletionsSource;
use WP_Statistics\Service\AnalyticsQuery\Registry\SourceRegistry;
use WP_UnitTestCase;

/**
 * Tests for EventCompletionsSource, ViewCompletionsSource, and their registry.
 *
 * @since 15.1.0
 */
class Test_CompletionSources extends WP_UnitTestCase
{
    // ── EventCompletionsSource ───────────────────────────────────

    public function test_event_completions_name()
    {
        $source = new EventCompletionsSource();
        $this->assertSame('event_completions', $source->getName());
    }

    public function test_event_completions_expression_uses_distinct_session()
    {
        $source     = new EventCompletionsSource();
        $expression = $source->getExpression();

        $this->assertStringContainsString('COUNT', $expression);
        $this->assertStringContainsString('DISTINCT', $expression);
        $this->assertStringContainsString('events.session_id', $expression);
    }

    public function test_event_completions_table_is_events()
    {
        $source = new EventCompletionsSource();
        $this->assertSame('events', $source->getTable());
    }

    public function test_event_completions_type_is_integer()
    {
        $source = new EventCompletionsSource();
        $this->assertSame('integer', $source->getType());
    }

    public function test_event_completions_does_not_support_summary_table()
    {
        $source = new EventCompletionsSource();
        $this->assertFalse($source->supportsSummaryTable());
    }

    // ── ViewCompletionsSource ───────────────────────────────────

    public function test_view_completions_name()
    {
        $source = new ViewCompletionsSource();
        $this->assertSame('view_completions', $source->getName());
    }

    public function test_view_completions_expression_uses_distinct_session()
    {
        $source     = new ViewCompletionsSource();
        $expression = $source->getExpression();

        $this->assertStringContainsString('COUNT', $expression);
        $this->assertStringContainsString('DISTINCT', $expression);
        $this->assertStringContainsString('views.session_id', $expression);
    }

    public function test_view_completions_table_is_views()
    {
        $source = new ViewCompletionsSource();
        $this->assertSame('views', $source->getTable());
    }

    public function test_view_completions_type_is_integer()
    {
        $source = new ViewCompletionsSource();
        $this->assertSame('integer', $source->getType());
    }

    public function test_view_completions_does_not_support_summary_table()
    {
        $source = new ViewCompletionsSource();
        $this->assertFalse($source->supportsSummaryTable());
    }

    // ── SourceRegistry ──────────────────────────────────────────

    public function test_event_completions_registered_in_registry()
    {
        $registry = SourceRegistry::getInstance();
        $this->assertTrue($registry->has('event_completions'));
    }

    public function test_view_completions_registered_in_registry()
    {
        $registry = SourceRegistry::getInstance();
        $this->assertTrue($registry->has('view_completions'));
    }

    public function test_event_completions_resolves_from_registry()
    {
        $registry = SourceRegistry::getInstance();
        $source   = $registry->get('event_completions');

        $this->assertInstanceOf(EventCompletionsSource::class, $source);
    }

    public function test_view_completions_resolves_from_registry()
    {
        $registry = SourceRegistry::getInstance();
        $source   = $registry->get('view_completions');

        $this->assertInstanceOf(ViewCompletionsSource::class, $source);
    }

    public function test_registry_returns_events_table_for_event_completions()
    {
        $registry = SourceRegistry::getInstance();
        $this->assertSame('events', $registry->getTable('event_completions'));
    }

    public function test_registry_returns_views_table_for_view_completions()
    {
        $registry = SourceRegistry::getInstance();
        $this->assertSame('views', $registry->getTable('view_completions'));
    }
}
