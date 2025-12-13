<?php

namespace WP_Statistics\Tests\AnalyticsQuery;

use WP_UnitTestCase;
use WP_Statistics\Service\AnalyticsQuery\FilterBuilder;
use WP_Statistics\Service\AnalyticsQuery\Registry\FilterRegistry;
use WP_Statistics\Service\AnalyticsQuery\Exceptions\InvalidFilterException;
use WP_Statistics\Service\AnalyticsQuery\Exceptions\InvalidOperatorException;

/**
 * Test FilterBuilder class.
 *
 * Tests the FilterBuilder's ability to convert filter objects into safe SQL WHERE clauses.
 * Verifies proper handling of various operators, data types, and edge cases.
 */
class Test_FilterBuilder extends WP_UnitTestCase
{
    protected $registry;

    public function setUp(): void
    {
        parent::setUp();
        $this->registry = FilterRegistry::getInstance();
    }

    /**
     * Test building simple equality filter.
     */
    public function test_build_simple_equality_filter()
    {
        $filters = ['country' => 'US'];

        $result = FilterBuilder::build($filters);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('conditions', $result);
        $this->assertArrayHasKey('params', $result);
        $this->assertArrayHasKey('joins', $result);

        $this->assertCount(1, $result['conditions']);
        $this->assertStringContainsString('countries.code = %s', $result['conditions'][0]);
        $this->assertContains('US', $result['params']);
    }

    /**
     * Test building filter with IN operator.
     */
    public function test_build_filter_with_array_values()
    {
        $filters = ['country' => ['US', 'GB', 'CA']];

        $result = FilterBuilder::build($filters);

        $this->assertCount(1, $result['conditions']);
        $this->assertStringContainsString('IN', $result['conditions'][0]);
        $this->assertStringContainsString('%s,%s,%s', $result['conditions'][0]);
        $this->assertEquals(['US', 'GB', 'CA'], $result['params']);
    }

    /**
     * Test building filter with 'is' operator.
     */
    public function test_build_filter_with_is_operator()
    {
        $filters = ['country' => ['is' => 'US']];

        $result = FilterBuilder::build($filters);

        $this->assertCount(1, $result['conditions']);
        $this->assertStringContainsString('countries.code = %s', $result['conditions'][0]);
        $this->assertContains('US', $result['params']);
    }

    /**
     * Test building filter with 'is_not' operator.
     */
    public function test_build_filter_with_is_not_operator()
    {
        $filters = ['country' => ['is_not' => 'US']];

        $result = FilterBuilder::build($filters);

        $this->assertCount(1, $result['conditions']);
        $this->assertStringContainsString('countries.code != %s', $result['conditions'][0]);
        $this->assertContains('US', $result['params']);
    }

    /**
     * Test building filter with 'in' operator.
     */
    public function test_build_filter_with_in_operator()
    {
        $filters = ['country' => ['in' => ['US', 'GB']]];

        $result = FilterBuilder::build($filters);

        $this->assertCount(1, $result['conditions']);
        $this->assertStringContainsString('IN', $result['conditions'][0]);
        $this->assertEquals(['US', 'GB'], $result['params']);
    }

    /**
     * Test building filter with 'not_in' operator.
     */
    public function test_build_filter_with_not_in_operator()
    {
        $filters = ['country' => ['not_in' => ['US', 'GB']]];

        $result = FilterBuilder::build($filters);

        $this->assertCount(1, $result['conditions']);
        $this->assertStringContainsString('NOT IN', $result['conditions'][0]);
        $this->assertEquals(['US', 'GB'], $result['params']);
    }

    /**
     * Test building filter with 'contains' operator.
     */
    public function test_build_filter_with_contains_operator()
    {
        $filters = ['referrer' => ['contains' => 'google']];

        $result = FilterBuilder::build($filters);

        $this->assertCount(1, $result['conditions']);
        $this->assertStringContainsString('LIKE %s', $result['conditions'][0]);
        $this->assertStringContainsString('google', $result['params'][0]);
        $this->assertStringStartsWith('%', $result['params'][0]);
        $this->assertStringEndsWith('%', $result['params'][0]);
    }

    /**
     * Test building filter with 'starts_with' operator.
     */
    public function test_build_filter_with_starts_with_operator()
    {
        $filters = ['referrer' => ['starts_with' => 'https']];

        $result = FilterBuilder::build($filters);

        $this->assertCount(1, $result['conditions']);
        $this->assertStringContainsString('LIKE %s', $result['conditions'][0]);
        $this->assertStringStartsWith('https', $result['params'][0]);
        $this->assertStringEndsWith('%', $result['params'][0]);
    }

    /**
     * Test building filter with 'ends_with' operator.
     */
    public function test_build_filter_with_ends_with_operator()
    {
        $filters = ['referrer' => ['ends_with' => '.com']];

        $result = FilterBuilder::build($filters);

        $this->assertCount(1, $result['conditions']);
        $this->assertStringContainsString('LIKE %s', $result['conditions'][0]);
        $this->assertStringStartsWith('%', $result['params'][0]);
        $this->assertStringEndsWith('.com', $result['params'][0]);
    }

    /**
     * Test building filter with 'gt' operator.
     */
    public function test_build_filter_with_gt_operator()
    {
        $filters = ['total_views' => ['gt' => 100]];

        $result = FilterBuilder::build($filters);

        $this->assertCount(1, $result['conditions']);
        $this->assertStringContainsString('> %d', $result['conditions'][0]);
        $this->assertEquals(100, $result['params'][0]);
    }

    /**
     * Test building filter with 'gte' operator.
     */
    public function test_build_filter_with_gte_operator()
    {
        $filters = ['total_views' => ['gte' => 100]];

        $result = FilterBuilder::build($filters);

        $this->assertCount(1, $result['conditions']);
        $this->assertStringContainsString('>= %d', $result['conditions'][0]);
        $this->assertEquals(100, $result['params'][0]);
    }

    /**
     * Test building filter with 'lt' operator.
     */
    public function test_build_filter_with_lt_operator()
    {
        $filters = ['total_views' => ['lt' => 50]];

        $result = FilterBuilder::build($filters);

        $this->assertCount(1, $result['conditions']);
        $this->assertStringContainsString('< %d', $result['conditions'][0]);
        $this->assertEquals(50, $result['params'][0]);
    }

    /**
     * Test building filter with 'lte' operator.
     */
    public function test_build_filter_with_lte_operator()
    {
        $filters = ['total_views' => ['lte' => 50]];

        $result = FilterBuilder::build($filters);

        $this->assertCount(1, $result['conditions']);
        $this->assertStringContainsString('<= %d', $result['conditions'][0]);
        $this->assertEquals(50, $result['params'][0]);
    }

    /**
     * Test building boolean filter (true).
     */
    public function test_build_boolean_filter_true()
    {
        $filters = ['logged_in' => true];

        $result = FilterBuilder::build($filters);

        $this->assertCount(1, $result['conditions']);
        $this->assertStringContainsString('IS NOT NULL', $result['conditions'][0]);
        $this->assertEmpty($result['params']);
    }

    /**
     * Test building boolean filter (false).
     */
    public function test_build_boolean_filter_false()
    {
        $filters = ['logged_in' => false];

        $result = FilterBuilder::build($filters);

        $this->assertCount(1, $result['conditions']);
        $this->assertStringContainsString('IS NULL', $result['conditions'][0]);
        $this->assertEmpty($result['params']);
    }

    /**
     * Test building multiple filters.
     */
    public function test_build_multiple_filters()
    {
        $filters = [
            'country' => 'US',
            'browser' => 'Chrome'
        ];

        $result = FilterBuilder::build($filters);

        $this->assertCount(2, $result['conditions']);
        $this->assertCount(2, $result['params']);
    }

    /**
     * Test that invalid filter throws exception.
     */
    public function test_invalid_filter_throws_exception()
    {
        $this->expectException(InvalidFilterException::class);

        $filters = ['invalid_filter_key' => 'value'];
        FilterBuilder::build($filters);
    }

    /**
     * Test that invalid operator throws exception.
     */
    public function test_invalid_operator_throws_exception()
    {
        $this->expectException(InvalidOperatorException::class);

        $filters = ['country' => ['invalid_operator' => 'US']];
        FilterBuilder::build($filters);
    }

    /**
     * Test isAllowed method.
     */
    public function test_is_allowed_method()
    {
        $this->assertTrue(FilterBuilder::isAllowed('country'));
        $this->assertFalse(FilterBuilder::isAllowed('invalid_filter'));
    }

    /**
     * Test getConfig method returns filter configuration.
     */
    public function test_get_config_method()
    {
        $config = FilterBuilder::getConfig('country');

        $this->assertIsArray($config);
        $this->assertArrayHasKey('name', $config);
        $this->assertArrayHasKey('column', $config);
        $this->assertArrayHasKey('type', $config);
        $this->assertEquals('country', $config['name']);
    }

    /**
     * Test getConfig method returns null for invalid filter.
     */
    public function test_get_config_method_returns_null_for_invalid_filter()
    {
        $config = FilterBuilder::getConfig('invalid_filter');

        $this->assertNull($config);
    }

    /**
     * Test that joins are properly collected from filters.
     */
    public function test_joins_are_collected()
    {
        $filters = ['country' => 'US'];

        $result = FilterBuilder::build($filters);

        $this->assertIsArray($result['joins']);
        $this->assertNotEmpty($result['joins']);
        $this->assertArrayHasKey('countries', $result['joins']);
    }

    /**
     * Test sanitization of string values.
     */
    public function test_string_sanitization()
    {
        $filters = ['country' => '<script>alert("xss")</script>US'];

        $result = FilterBuilder::build($filters);

        // Verify sanitize_text_field is applied
        $this->assertNotContains('<script>', $result['params']);
        $this->assertNotContains('</script>', $result['params']);
    }

    /**
     * Test sanitization of integer values.
     */
    public function test_integer_sanitization()
    {
        $filters = ['total_views' => '123abc'];

        $result = FilterBuilder::build($filters);

        $this->assertEquals(123, $result['params'][0]);
        $this->assertIsInt($result['params'][0]);
    }

    /**
     * Test empty filters array.
     */
    public function test_empty_filters_array()
    {
        $filters = [];

        $result = FilterBuilder::build($filters);

        $this->assertIsArray($result);
        $this->assertEmpty($result['conditions']);
        $this->assertEmpty($result['params']);
        $this->assertEmpty($result['joins']);
    }

    /**
     * Test 'in' operator with single value converts to array.
     */
    public function test_in_operator_with_single_value()
    {
        $filters = ['country' => ['in' => 'US']];

        $result = FilterBuilder::build($filters);

        $this->assertCount(1, $result['conditions']);
        $this->assertStringContainsString('IN', $result['conditions'][0]);
        $this->assertEquals(['US'], $result['params']);
    }

    /**
     * Test 'not_in' operator with single value converts to array.
     */
    public function test_not_in_operator_with_single_value()
    {
        $filters = ['country' => ['not_in' => 'US']];

        $result = FilterBuilder::build($filters);

        $this->assertCount(1, $result['conditions']);
        $this->assertStringContainsString('NOT IN', $result['conditions'][0]);
        $this->assertEquals(['US'], $result['params']);
    }
}
