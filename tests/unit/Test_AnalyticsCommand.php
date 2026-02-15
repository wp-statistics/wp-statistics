<?php

use WP_Statistics\Service\CLI\Commands\AnalyticsCommand;

/**
 * Test case for AnalyticsCommand class.
 *
 * Tests filter parsing, request building, and discovery subcommand output.
 *
 * @covers \WP_Statistics\Service\CLI\Commands\AnalyticsCommand
 * @group cli
 */
class Test_AnalyticsCommand extends WP_UnitTestCase
{
    /**
     * @var AnalyticsCommand
     */
    private $command;

    /**
     * @var ReflectionMethod
     */
    private $parseFiltersMethod;

    /**
     * @var ReflectionMethod
     */
    private $buildRequestMethod;

    public function setUp(): void
    {
        parent::setUp();

        $this->command = new AnalyticsCommand();

        // Expose private methods for testing
        $this->parseFiltersMethod = new ReflectionMethod(AnalyticsCommand::class, 'parseFilters');
        $this->parseFiltersMethod->setAccessible(true);

        $this->buildRequestMethod = new ReflectionMethod(AnalyticsCommand::class, 'buildRequest');
        $this->buildRequestMethod->setAccessible(true);
    }

    /**
     * Test parseFilters returns empty array when no filters provided.
     */
    public function test_parse_filters_returns_empty_when_no_filters()
    {
        $result = $this->parseFiltersMethod->invoke($this->command, []);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Test parseFilters returns empty array when filter key is empty.
     */
    public function test_parse_filters_returns_empty_for_empty_filter()
    {
        $result = $this->parseFiltersMethod->invoke($this->command, ['filter' => '']);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Test parseFilters handles shorthand key:value format.
     */
    public function test_parse_filters_shorthand_equality()
    {
        $result = $this->parseFiltersMethod->invoke($this->command, [
            'filter' => 'country:US',
        ]);

        $this->assertEquals(['country' => 'US'], $result);
    }

    /**
     * Test parseFilters handles explicit is operator.
     */
    public function test_parse_filters_explicit_is_operator()
    {
        $result = $this->parseFiltersMethod->invoke($this->command, [
            'filter' => 'country:is:US',
        ]);

        $this->assertEquals(['country' => 'US'], $result);
    }

    /**
     * Test parseFilters handles contains operator.
     */
    public function test_parse_filters_contains_operator()
    {
        $result = $this->parseFiltersMethod->invoke($this->command, [
            'filter' => 'browser:contains:Chrome',
        ]);

        $this->assertEquals(['browser' => ['contains' => 'Chrome']], $result);
    }

    /**
     * Test parseFilters handles is_not operator.
     */
    public function test_parse_filters_is_not_operator()
    {
        $result = $this->parseFiltersMethod->invoke($this->command, [
            'filter' => 'country:is_not:US',
        ]);

        $this->assertEquals(['country' => ['is_not' => 'US']], $result);
    }

    /**
     * Test parseFilters handles in operator with CSV values.
     */
    public function test_parse_filters_in_operator_splits_csv()
    {
        $result = $this->parseFiltersMethod->invoke($this->command, [
            'filter' => 'country:in:US,GB,DE',
        ]);

        $this->assertEquals(['country' => ['in' => ['US', 'GB', 'DE']]], $result);
    }

    /**
     * Test parseFilters handles not_in operator with CSV values.
     */
    public function test_parse_filters_not_in_operator_splits_csv()
    {
        $result = $this->parseFiltersMethod->invoke($this->command, [
            'filter' => 'country:not_in:CN,RU',
        ]);

        $this->assertEquals(['country' => ['not_in' => ['CN', 'RU']]], $result);
    }

    /**
     * Test parseFilters handles gt operator.
     */
    public function test_parse_filters_gt_operator()
    {
        $result = $this->parseFiltersMethod->invoke($this->command, [
            'filter' => 'total_views:gt:100',
        ]);

        $this->assertEquals(['total_views' => ['gt' => '100']], $result);
    }

    /**
     * Test parseFilters handles multiple filters as array.
     */
    public function test_parse_filters_multiple_filters()
    {
        $result = $this->parseFiltersMethod->invoke($this->command, [
            'filter' => [
                'country:is:US',
                'browser:contains:Chrome',
            ],
        ]);

        $this->assertEquals([
            'country' => 'US',
            'browser' => ['contains' => 'Chrome'],
        ], $result);
    }

    /**
     * Test parseFilters handles starts_with operator.
     */
    public function test_parse_filters_starts_with_operator()
    {
        $result = $this->parseFiltersMethod->invoke($this->command, [
            'filter' => 'referrer_domain:starts_with:google',
        ]);

        $this->assertEquals(['referrer_domain' => ['starts_with' => 'google']], $result);
    }

    /**
     * Test parseFilters handles value with colons (URL in value).
     */
    public function test_parse_filters_value_with_colons()
    {
        $result = $this->parseFiltersMethod->invoke($this->command, [
            'filter' => 'referrer:is:https://example.com',
        ]);

        $this->assertEquals(['referrer' => 'https://example.com'], $result);
    }

    /**
     * Test buildRequest produces correct defaults.
     */
    public function test_build_request_defaults()
    {
        $result = $this->buildRequestMethod->invoke($this->command, []);

        $this->assertEquals(['visitors'], $result['sources']);
        $this->assertEquals(10, $result['per_page']);
        $this->assertEquals(1, $result['page']);
        $this->assertEquals('DESC', $result['order']);
        $this->assertArrayHasKey('date_from', $result);
        $this->assertArrayHasKey('date_to', $result);
        $this->assertArrayNotHasKey('group_by', $result);
        $this->assertArrayNotHasKey('order_by', $result);
        $this->assertArrayNotHasKey('compare', $result);
        $this->assertArrayNotHasKey('filters', $result);
    }

    /**
     * Test buildRequest parses CSV sources.
     */
    public function test_build_request_parses_csv_sources()
    {
        $result = $this->buildRequestMethod->invoke($this->command, [
            'source' => 'visitors,views,bounce_rate',
        ]);

        $this->assertEquals(['visitors', 'views', 'bounce_rate'], $result['sources']);
    }

    /**
     * Test buildRequest parses CSV group-by.
     */
    public function test_build_request_parses_csv_group_by()
    {
        $result = $this->buildRequestMethod->invoke($this->command, [
            'group-by' => 'date,country',
        ]);

        $this->assertEquals(['date', 'country'], $result['group_by']);
    }

    /**
     * Test buildRequest maps pagination args.
     */
    public function test_build_request_pagination()
    {
        $result = $this->buildRequestMethod->invoke($this->command, [
            'per-page' => '25',
            'page'     => '3',
        ]);

        $this->assertEquals(25, $result['per_page']);
        $this->assertEquals(3, $result['page']);
    }

    /**
     * Test buildRequest maps order and order-by.
     */
    public function test_build_request_ordering()
    {
        $result = $this->buildRequestMethod->invoke($this->command, [
            'order-by' => 'visitors',
            'order'    => 'asc',
        ]);

        $this->assertEquals('visitors', $result['order_by']);
        $this->assertEquals('ASC', $result['order']);
    }

    /**
     * Test buildRequest maps date range.
     */
    public function test_build_request_date_range()
    {
        $result = $this->buildRequestMethod->invoke($this->command, [
            'date-from' => '2025-01-01',
            'date-to'   => '2025-01-31',
        ]);

        $this->assertEquals('2025-01-01', $result['date_from']);
        $this->assertEquals('2025-01-31', $result['date_to']);
    }

    /**
     * Test buildRequest enables compare flag.
     */
    public function test_build_request_compare_flag()
    {
        $result = $this->buildRequestMethod->invoke($this->command, [
            'compare' => true,
        ]);

        $this->assertTrue($result['compare']);
    }

    /**
     * Test buildRequest parses columns.
     */
    public function test_build_request_columns()
    {
        $result = $this->buildRequestMethod->invoke($this->command, [
            'columns' => 'visitors,country_name',
        ]);

        $this->assertEquals(['visitors', 'country_name'], $result['columns']);
    }

    /**
     * Test buildRequest includes filters.
     */
    public function test_build_request_with_filters()
    {
        $result = $this->buildRequestMethod->invoke($this->command, [
            'filter' => 'country:is:US',
        ]);

        $this->assertArrayHasKey('filters', $result);
        $this->assertEquals(['country' => 'US'], $result['filters']);
    }

    /**
     * Test buildRequest omits filters when none provided.
     */
    public function test_build_request_omits_empty_filters()
    {
        $result = $this->buildRequestMethod->invoke($this->command, []);

        $this->assertArrayNotHasKey('filters', $result);
    }

    /**
     * Test AnalyticsCommand can be instantiated.
     */
    public function test_command_instantiation()
    {
        $command = new AnalyticsCommand();
        $this->assertInstanceOf(AnalyticsCommand::class, $command);
    }

    /**
     * Test listSources method exists and is callable.
     */
    public function test_list_sources_method_exists()
    {
        $this->assertTrue(method_exists($this->command, 'listSources'));
    }

    /**
     * Test listGroups method exists and is callable.
     */
    public function test_list_groups_method_exists()
    {
        $this->assertTrue(method_exists($this->command, 'listGroups'));
    }

    /**
     * Test listFilters method exists and is callable.
     */
    public function test_list_filters_method_exists()
    {
        $this->assertTrue(method_exists($this->command, 'listFilters'));
    }

    /**
     * Test query method exists and is callable.
     */
    public function test_query_method_exists()
    {
        $this->assertTrue(method_exists($this->command, 'query'));
    }
}
