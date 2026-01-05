<?php

namespace WP_Statistics\Tests;

use WP_Statistics\Service\AnalyticsQuery\Sources\OnlineVisitorsSource;
use WP_UnitTestCase;

/**
 * Unit tests for OnlineVisitorsSource.
 *
 * @covers \WP_Statistics\Service\AnalyticsQuery\Sources\OnlineVisitorsSource
 *
 * @since 15.0.0
 */
class Test_OnlineVisitorsSource extends WP_UnitTestCase
{
    /**
     * @var OnlineVisitorsSource
     */
    private $source;

    /**
     * Set up test environment.
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->source = new OnlineVisitorsSource();
    }

    /**
     * Test source name is correct.
     */
    public function test_source_name()
    {
        $this->assertEquals('online_visitors', $this->source->getName());
    }

    /**
     * Test source expression counts distinct visitors.
     */
    public function test_source_expression()
    {
        $expression = $this->source->getExpression();
        $this->assertStringContainsString('COUNT', $expression);
        $this->assertStringContainsString('DISTINCT', $expression);
        $this->assertStringContainsString('visitor_id', $expression);
    }

    /**
     * Test source uses sessions table.
     */
    public function test_source_table()
    {
        $this->assertEquals('sessions', $this->source->getTable());
    }

    /**
     * Test source type is integer.
     */
    public function test_source_type()
    {
        $this->assertEquals('integer', $this->source->getType());
    }

    /**
     * Test source format is number.
     */
    public function test_source_format()
    {
        $this->assertEquals('number', $this->source->getFormat());
    }

    /**
     * Test source does not support summary table.
     */
    public function test_source_does_not_support_summary_table()
    {
        $this->assertFalse($this->source->supportsSummaryTable());
    }

    /**
     * Test source uses ended_at for date filter.
     */
    public function test_source_uses_ended_at_for_date_filter()
    {
        $this->assertTrue($this->source->usesEndedAtForDateFilter());
    }

    /**
     * Test online threshold returns valid timestamp.
     */
    public function test_online_threshold_returns_valid_timestamp()
    {
        $threshold = OnlineVisitorsSource::getOnlineThreshold();

        // Should be a valid datetime string
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $threshold);

        // Should be approximately 5 minutes ago (300 seconds)
        $now = time();
        $thresholdTime = strtotime($threshold);
        $diff = $now - $thresholdTime;

        // Allow 1 second tolerance for test execution time
        $this->assertGreaterThanOrEqual(299, $diff);
        $this->assertLessThanOrEqual(301, $diff);
    }

    /**
     * Test online threshold constant is 300 seconds.
     */
    public function test_online_threshold_constant()
    {
        $this->assertEquals(300, OnlineVisitorsSource::ONLINE_THRESHOLD);
    }

    /**
     * Test getExpressionWithAlias includes alias.
     */
    public function test_expression_with_alias()
    {
        $expression = $this->source->getExpressionWithAlias();

        $this->assertStringContainsString(' AS ', $expression);
        $this->assertStringContainsString('online_visitors', $expression);
    }
}
