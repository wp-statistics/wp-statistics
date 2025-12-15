<?php

namespace WP_Statistics\Tests;

use WP_Statistics\Service\AnalyticsQuery\AnalyticsQueryHandler;
use WP_Statistics\Service\AnalyticsQuery\Exceptions\InvalidColumnException;
use WP_UnitTestCase;

class Test_AnalyticsQueryHandler extends WP_UnitTestCase
{
    /**
     * Analytics query handler instance.
     *
     * @var AnalyticsQueryHandler
     */
    private $handler;

    /**
     * Set up test environment.
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->handler = new AnalyticsQueryHandler(false); // Disable cache for testing
    }

    /**
     * Test that queries inherit global compare: true.
     */
    public function test_batch_with_global_compare_true()
    {
        $queries = [
            [
                'id'       => 'query1',
                'sources'  => ['visitors'],
                'group_by' => [],
            ],
            [
                'id'       => 'query2',
                'sources'  => ['views'],
                'group_by' => [],
            ],
        ];

        $dateFrom      = '2024-01-01';
        $dateTo        = '2024-01-31';
        $globalFilters = [];
        $globalCompare = true;

        $result = $this->handler->handleBatch($queries, $dateFrom, $dateTo, $globalFilters, $globalCompare);

        $this->assertTrue($result['success'], 'Batch request should succeed');
        $this->assertArrayHasKey('items', $result);
        $this->assertArrayHasKey('query1', $result['items']);
        $this->assertArrayHasKey('query2', $result['items']);

        // Both queries should have comparison data
        $this->assertArrayHasKey('meta', $result['items']['query1']);
        $this->assertArrayHasKey('compare_from', $result['items']['query1']['meta'], 'Query1 should have comparison data');

        $this->assertArrayHasKey('meta', $result['items']['query2']);
        $this->assertArrayHasKey('compare_from', $result['items']['query2']['meta'], 'Query2 should have comparison data');
    }

    /**
     * Test that queries work with global compare: false.
     */
    public function test_batch_with_global_compare_false()
    {
        $queries = [
            [
                'id'       => 'query1',
                'sources'  => ['visitors'],
                'group_by' => [],
            ],
        ];

        $dateFrom      = '2024-01-01';
        $dateTo        = '2024-01-31';
        $globalFilters = [];
        $globalCompare = false;

        $result = $this->handler->handleBatch($queries, $dateFrom, $dateTo, $globalFilters, $globalCompare);

        $this->assertTrue($result['success'], 'Batch request should succeed');
        $this->assertArrayHasKey('items', $result);
        $this->assertArrayHasKey('query1', $result['items']);

        // Query should NOT have comparison data
        $this->assertArrayHasKey('meta', $result['items']['query1']);
        $this->assertArrayNotHasKey('compare_from', $result['items']['query1']['meta'], 'Query should not have comparison data when global compare is false');
    }

    /**
     * Test that individual query can override global compare.
     */
    public function test_batch_query_override_global_compare()
    {
        $queries = [
            [
                'id'       => 'with_comparison',
                'sources'  => ['visitors'],
                'group_by' => [],
                // Inherits global compare: true
            ],
            [
                'id'       => 'without_comparison',
                'sources'  => ['views'],
                'group_by' => [],
                'compare'  => false, // Override global
            ],
        ];

        $dateFrom      = '2024-01-01';
        $dateTo        = '2024-01-31';
        $globalFilters = [];
        $globalCompare = true;

        $result = $this->handler->handleBatch($queries, $dateFrom, $dateTo, $globalFilters, $globalCompare);

        $this->assertTrue($result['success'], 'Batch request should succeed');

        // First query should have comparison (inherits global)
        $this->assertArrayHasKey('compare_from', $result['items']['with_comparison']['meta'], 'Query should inherit global compare: true');

        // Second query should NOT have comparison (overrides global)
        $this->assertArrayNotHasKey('compare_from', $result['items']['without_comparison']['meta'], 'Query should override global compare with false');
    }

    /**
     * Test backward compatibility when compare not provided.
     */
    public function test_batch_without_compare()
    {
        $queries = [
            [
                'id'       => 'query1',
                'sources'  => ['visitors'],
                'group_by' => [],
            ],
        ];

        $dateFrom      = '2024-01-01';
        $dateTo        = '2024-01-31';
        $globalFilters = [];
        // No globalCompare parameter provided (defaults to false)

        $result = $this->handler->handleBatch($queries, $dateFrom, $dateTo, $globalFilters);

        $this->assertTrue($result['success'], 'Batch request should succeed');
        $this->assertArrayHasKey('items', $result);
        $this->assertArrayHasKey('query1', $result['items']);

        // Query should NOT have comparison data (backward compatible)
        $this->assertArrayHasKey('meta', $result['items']['query1']);
        $this->assertArrayNotHasKey('compare_from', $result['items']['query1']['meta'], 'Query should not have comparison when global compare is not provided');
    }

    /**
     * Test mix of global and per-query compare settings.
     */
    public function test_batch_mixed_compare_settings()
    {
        $queries = [
            [
                'id'       => 'inherit_global',
                'sources'  => ['visitors'],
                'group_by' => [],
                // Inherits global compare: true
            ],
            [
                'id'       => 'explicit_true',
                'sources'  => ['views'],
                'group_by' => [],
                'compare'  => true, // Explicitly set to true
            ],
            [
                'id'       => 'explicit_false',
                'sources'  => ['sessions'],
                'group_by' => [],
                'compare'  => false, // Explicitly set to false
            ],
        ];

        $dateFrom      = '2024-01-01';
        $dateTo        = '2024-01-31';
        $globalFilters = [];
        $globalCompare = true;

        $result = $this->handler->handleBatch($queries, $dateFrom, $dateTo, $globalFilters, $globalCompare);

        $this->assertTrue($result['success'], 'Batch request should succeed');

        // First query inherits global compare: true
        $this->assertArrayHasKey('compare_from', $result['items']['inherit_global']['meta'], 'Query should inherit global compare: true');

        // Second query explicitly sets compare: true
        $this->assertArrayHasKey('compare_from', $result['items']['explicit_true']['meta'], 'Query should have comparison when explicitly set to true');

        // Third query explicitly sets compare: false
        $this->assertArrayNotHasKey('compare_from', $result['items']['explicit_false']['meta'], 'Query should not have comparison when explicitly set to false');
    }

    /**
     * Test that columns parameter filters response to specified columns only.
     */
    public function test_columns_filter_single_query()
    {
        $request = [
            'sources'   => ['visitors', 'views', 'sessions'],
            'group_by'  => ['date'],
            'columns'   => ['date', 'visitors'],
            'date_from' => '2024-01-01',
            'date_to'   => '2024-01-31',
        ];

        $result = $this->handler->handle($request);

        $this->assertTrue($result['success'], 'Request should succeed');
        $this->assertArrayHasKey('data', $result);

        // Check totals only includes visitors (not views or sessions)
        if (isset($result['data']['totals'])) {
            $this->assertArrayHasKey('visitors', $result['data']['totals'], 'Totals should include visitors');
            $this->assertArrayNotHasKey('views', $result['data']['totals'], 'Totals should not include views');
            $this->assertArrayNotHasKey('sessions', $result['data']['totals'], 'Totals should not include sessions');
        }

        // Check rows only include date and visitors
        if (!empty($result['data']['rows'])) {
            foreach ($result['data']['rows'] as $row) {
                $this->assertArrayHasKey('date', $row, 'Row should include date');
                $this->assertArrayHasKey('visitors', $row, 'Row should include visitors');
                $this->assertArrayNotHasKey('views', $row, 'Row should not include views');
                $this->assertArrayNotHasKey('sessions', $row, 'Row should not include sessions');
            }
        }
    }

    /**
     * Test that columns parameter works in batch queries.
     */
    public function test_columns_filter_batch_query()
    {
        $queries = [
            [
                'id'       => 'traffic_trends',
                'sources'  => ['visitors', 'views', 'sessions'],
                'group_by' => ['date'],
                'columns'  => ['date', 'visitors'],
            ],
            [
                'id'       => 'top_countries',
                'sources'  => ['visitors', 'views'],
                'group_by' => ['country'],
                'columns'  => ['country_name', 'visitors'],
            ],
        ];

        $dateFrom = '2024-01-01';
        $dateTo   = '2024-01-31';

        $result = $this->handler->handleBatch($queries, $dateFrom, $dateTo);

        $this->assertTrue($result['success'], 'Batch request should succeed');

        // Check traffic_trends query
        if (isset($result['items']['traffic_trends']['data']['totals'])) {
            $this->assertArrayHasKey('visitors', $result['items']['traffic_trends']['data']['totals']);
            $this->assertArrayNotHasKey('views', $result['items']['traffic_trends']['data']['totals']);
            $this->assertArrayNotHasKey('sessions', $result['items']['traffic_trends']['data']['totals']);
        }

        // Check top_countries query
        if (isset($result['items']['top_countries']['data']['totals'])) {
            $this->assertArrayHasKey('visitors', $result['items']['top_countries']['data']['totals']);
            $this->assertArrayNotHasKey('views', $result['items']['top_countries']['data']['totals']);
        }
    }

    /**
     * Test backward compatibility: no columns parameter returns all fields.
     */
    public function test_no_columns_returns_all_fields()
    {
        $request = [
            'sources'   => ['visitors', 'views'],
            'group_by'  => ['date'],
            'date_from' => '2024-01-01',
            'date_to'   => '2024-01-31',
        ];

        $result = $this->handler->handle($request);

        $this->assertTrue($result['success'], 'Request should succeed');

        // Check totals includes all sources
        if (isset($result['data']['totals'])) {
            $this->assertArrayHasKey('visitors', $result['data']['totals'], 'Totals should include visitors');
            $this->assertArrayHasKey('views', $result['data']['totals'], 'Totals should include views');
        }

        // Check rows include all fields
        if (!empty($result['data']['rows'])) {
            foreach ($result['data']['rows'] as $row) {
                $this->assertArrayHasKey('date', $row, 'Row should include date');
                $this->assertArrayHasKey('visitors', $row, 'Row should include visitors');
                $this->assertArrayHasKey('views', $row, 'Row should include views');
            }
        }
    }

    /**
     * Test that invalid column name throws exception.
     */
    public function test_invalid_column_throws_exception()
    {
        $this->expectException(InvalidColumnException::class);

        $request = [
            'sources'   => ['visitors', 'views'],
            'group_by'  => ['date'],
            'columns'   => ['date', 'visitors', 'invalid_column'],
            'date_from' => '2024-01-01',
            'date_to'   => '2024-01-31',
        ];

        $this->handler->handle($request);
    }

    /**
     * Test that columns must be from sources or group_by.
     */
    public function test_column_must_be_from_sources_or_group_by()
    {
        $this->expectException(InvalidColumnException::class);

        $request = [
            'sources'   => ['visitors'],
            'group_by'  => ['date'],
            'columns'   => ['date', 'visitors', 'views'], // views not in sources
            'date_from' => '2024-01-01',
            'date_to'   => '2024-01-31',
        ];

        $this->handler->handle($request);
    }

    /**
     * Test that columns order is preserved in response.
     */
    public function test_columns_order_is_preserved()
    {
        $request = [
            'sources'   => ['views', 'visitors', 'sessions'],
            'group_by'  => ['date'],
            'columns'   => ['sessions', 'date', 'visitors', 'views'], // Custom order
            'date_from' => '2024-01-01',
            'date_to'   => '2024-01-31',
        ];

        $result = $this->handler->handle($request);

        $this->assertTrue($result['success'], 'Request should succeed');

        // Check that rows have keys in the specified order
        if (!empty($result['data']['rows'])) {
            $firstRow = $result['data']['rows'][0];
            $keys = array_keys($firstRow);

            // Filter out any extra keys like 'is_other'
            $expectedOrder = ['sessions', 'date', 'visitors', 'views'];
            $actualOrder = array_values(array_intersect($keys, $expectedOrder));

            $this->assertEquals($expectedOrder, $actualOrder, 'Columns should be in the order specified');
        }
    }

    /**
     * Test columns filtering with comparison data.
     */
    public function test_columns_filter_with_comparison()
    {
        $request = [
            'sources'   => ['visitors', 'views', 'sessions'],
            'group_by'  => ['date'],
            'columns'   => ['date', 'visitors'],
            'compare'   => true,
            'date_from' => '2024-01-01',
            'date_to'   => '2024-01-31',
        ];

        $result = $this->handler->handle($request);

        $this->assertTrue($result['success'], 'Request should succeed');
        $this->assertArrayHasKey('compare_from', $result['meta'], 'Should have comparison data');

        // Check totals only includes visitors and has filtered previous data
        if (isset($result['data']['totals'])) {
            $this->assertArrayHasKey('visitors', $result['data']['totals']);
            $this->assertArrayNotHasKey('views', $result['data']['totals']);

            if (isset($result['data']['totals']['previous'])) {
                $this->assertArrayHasKey('visitors', $result['data']['totals']['previous']);
                $this->assertArrayNotHasKey('views', $result['data']['totals']['previous']);
            }
        }
    }

    /**
     * Test that batch query handles invalid columns gracefully with error.
     */
    public function test_batch_invalid_column_returns_error()
    {
        $queries = [
            [
                'id'       => 'valid_query',
                'sources'  => ['visitors'],
                'group_by' => ['date'],
                'columns'  => ['date', 'visitors'],
            ],
            [
                'id'       => 'invalid_query',
                'sources'  => ['visitors'],
                'group_by' => ['date'],
                'columns'  => ['date', 'visitors', 'invalid_column'],
            ],
        ];

        $dateFrom = '2024-01-01';
        $dateTo   = '2024-01-31';

        $result = $this->handler->handleBatch($queries, $dateFrom, $dateTo);

        // Valid query should succeed
        $this->assertArrayHasKey('valid_query', $result['items']);

        // Invalid query should have error
        $this->assertArrayHasKey('invalid_query', $result['errors']);
        $this->assertEquals('invalid_column', $result['errors']['invalid_query']['code']);
    }

    /**
     * Test country group_by returns proper column names.
     */
    public function test_country_group_by_returns_proper_columns()
    {
        $request = [
            'sources'   => ['visitors'],
            'group_by'  => ['country'],
            'date_from' => '2024-01-01',
            'date_to'   => '2024-01-31',
        ];

        $result = $this->handler->handle($request);

        $this->assertTrue($result['success'], 'Request should succeed');

        // Check rows include proper column names
        if (!empty($result['data']['rows'])) {
            $firstRow = $result['data']['rows'][0];
            $this->assertArrayHasKey('country_name', $firstRow, 'Row should include country_name');
            $this->assertArrayHasKey('country_code', $firstRow, 'Row should include country_code');
            $this->assertArrayHasKey('country_id', $firstRow, 'Row should include country_id');
            $this->assertArrayHasKey('country_continent', $firstRow, 'Row should include country_continent');
            $this->assertArrayHasKey('country_continent_code', $firstRow, 'Row should include country_continent_code');
        }
    }

    /**
     * Test device_type group_by returns proper column names.
     */
    public function test_device_type_group_by_returns_proper_columns()
    {
        $request = [
            'sources'   => ['visitors'],
            'group_by'  => ['device_type'],
            'date_from' => '2024-01-01',
            'date_to'   => '2024-01-31',
        ];

        $result = $this->handler->handle($request);

        $this->assertTrue($result['success'], 'Request should succeed');

        // Check rows include proper column names
        if (!empty($result['data']['rows'])) {
            $firstRow = $result['data']['rows'][0];
            $this->assertArrayHasKey('device_type_name', $firstRow, 'Row should include device_type_name');
            $this->assertArrayHasKey('device_type_id', $firstRow, 'Row should include device_type_id');
        }
    }

    /**
     * Test os group_by returns proper column names.
     */
    public function test_os_group_by_returns_proper_columns()
    {
        $request = [
            'sources'   => ['visitors'],
            'group_by'  => ['os'],
            'date_from' => '2024-01-01',
            'date_to'   => '2024-01-31',
        ];

        $result = $this->handler->handle($request);

        $this->assertTrue($result['success'], 'Request should succeed');

        // Check rows include proper column names
        if (!empty($result['data']['rows'])) {
            $firstRow = $result['data']['rows'][0];
            $this->assertArrayHasKey('os_name', $firstRow, 'Row should include os_name');
            $this->assertArrayHasKey('os_id', $firstRow, 'Row should include os_id');
        }
    }

    /**
     * Test browser group_by returns proper column names.
     */
    public function test_browser_group_by_returns_proper_columns()
    {
        $request = [
            'sources'   => ['visitors'],
            'group_by'  => ['browser'],
            'date_from' => '2024-01-01',
            'date_to'   => '2024-01-31',
        ];

        $result = $this->handler->handle($request);

        $this->assertTrue($result['success'], 'Request should succeed');

        // Check rows include proper column names
        if (!empty($result['data']['rows'])) {
            $firstRow = $result['data']['rows'][0];
            $this->assertArrayHasKey('browser_name', $firstRow, 'Row should include browser_name');
            $this->assertArrayHasKey('browser_id', $firstRow, 'Row should include browser_id');
            $this->assertArrayHasKey('browser_version', $firstRow, 'Row should include browser_version');
        }
    }

    /**
     * Test referrer group_by returns proper column names.
     */
    public function test_referrer_group_by_returns_proper_columns()
    {
        $request = [
            'sources'   => ['visitors'],
            'group_by'  => ['referrer'],
            'date_from' => '2024-01-01',
            'date_to'   => '2024-01-31',
        ];

        $result = $this->handler->handle($request);

        $this->assertTrue($result['success'], 'Request should succeed');

        // Check rows include proper column names
        if (!empty($result['data']['rows'])) {
            $firstRow = $result['data']['rows'][0];
            $this->assertArrayHasKey('referrer_domain', $firstRow, 'Row should include referrer_domain');
            $this->assertArrayHasKey('referrer_id', $firstRow, 'Row should include referrer_id');
            $this->assertArrayHasKey('referrer_channel', $firstRow, 'Row should include referrer_channel');
            $this->assertArrayHasKey('referrer_name', $firstRow, 'Row should include referrer_name');
        }
    }

    /**
     * Test language group_by returns proper column names.
     */
    public function test_language_group_by_returns_proper_columns()
    {
        $request = [
            'sources'   => ['visitors'],
            'group_by'  => ['language'],
            'date_from' => '2024-01-01',
            'date_to'   => '2024-01-31',
        ];

        $result = $this->handler->handle($request);

        $this->assertTrue($result['success'], 'Request should succeed');

        // Check rows include proper column names
        if (!empty($result['data']['rows'])) {
            $firstRow = $result['data']['rows'][0];
            $this->assertArrayHasKey('language_name', $firstRow, 'Row should include language_name');
            $this->assertArrayHasKey('language_id', $firstRow, 'Row should include language_id');
            $this->assertArrayHasKey('language_code', $firstRow, 'Row should include language_code');
        }
    }

    /**
     * Test city group_by returns proper column names.
     */
    public function test_city_group_by_returns_proper_columns()
    {
        $request = [
            'sources'   => ['visitors'],
            'group_by'  => ['city'],
            'date_from' => '2024-01-01',
            'date_to'   => '2024-01-31',
        ];

        $result = $this->handler->handle($request);

        $this->assertTrue($result['success'], 'Request should succeed');

        // Check rows include proper column names
        if (!empty($result['data']['rows'])) {
            $firstRow = $result['data']['rows'][0];
            $this->assertArrayHasKey('city_name', $firstRow, 'Row should include city_name');
            $this->assertArrayHasKey('city_id', $firstRow, 'Row should include city_id');
            $this->assertArrayHasKey('city_region_name', $firstRow, 'Row should include city_region_name');
            $this->assertArrayHasKey('country_code', $firstRow, 'Row should include country_code');
        }
    }

    /**
     * Test continent group_by returns proper column names.
     */
    public function test_continent_group_by_returns_proper_columns()
    {
        $request = [
            'sources'   => ['visitors'],
            'group_by'  => ['continent'],
            'date_from' => '2024-01-01',
            'date_to'   => '2024-01-31',
        ];

        $result = $this->handler->handle($request);

        $this->assertTrue($result['success'], 'Request should succeed');

        // Check rows include proper column names
        if (!empty($result['data']['rows'])) {
            $firstRow = $result['data']['rows'][0];
            $this->assertArrayHasKey('continent_name', $firstRow, 'Row should include continent_name');
            $this->assertArrayHasKey('continent_code', $firstRow, 'Row should include continent_code');
        }
    }

    /**
     * Test resolution group_by returns proper column names.
     */
    public function test_resolution_group_by_returns_proper_columns()
    {
        $request = [
            'sources'   => ['visitors'],
            'group_by'  => ['resolution'],
            'date_from' => '2024-01-01',
            'date_to'   => '2024-01-31',
        ];

        $result = $this->handler->handle($request);

        $this->assertTrue($result['success'], 'Request should succeed');

        // Check rows include proper column names
        if (!empty($result['data']['rows'])) {
            $firstRow = $result['data']['rows'][0];
            $this->assertArrayHasKey('resolution', $firstRow, 'Row should include resolution');
            $this->assertArrayHasKey('resolution_id', $firstRow, 'Row should include resolution_id');
            $this->assertArrayHasKey('resolution_width', $firstRow, 'Row should include resolution_width');
            $this->assertArrayHasKey('resolution_height', $firstRow, 'Row should include resolution_height');
        }
    }

    /**
     * Test selecting specific columns with new naming.
     */
    public function test_columns_filter_with_new_column_names()
    {
        $request = [
            'sources'   => ['visitors'],
            'group_by'  => ['country'],
            'columns'   => ['country_name', 'country_code', 'visitors'],
            'date_from' => '2024-01-01',
            'date_to'   => '2024-01-31',
        ];

        $result = $this->handler->handle($request);

        $this->assertTrue($result['success'], 'Request should succeed');

        // Check rows only include specified columns
        if (!empty($result['data']['rows'])) {
            $firstRow = $result['data']['rows'][0];
            $this->assertArrayHasKey('country_name', $firstRow, 'Row should include country_name');
            $this->assertArrayHasKey('country_code', $firstRow, 'Row should include country_code');
            $this->assertArrayHasKey('visitors', $firstRow, 'Row should include visitors');
            // Should not include other country columns
            $this->assertArrayNotHasKey('country_id', $firstRow, 'Row should not include country_id');
            $this->assertArrayNotHasKey('country_continent', $firstRow, 'Row should not include country_continent');
        }
    }

    /**
     * Test batch query with multiple group_by using new column names.
     */
    public function test_batch_with_multiple_group_by_new_columns()
    {
        $queries = [
            [
                'id'       => 'top_countries',
                'sources'  => ['visitors'],
                'group_by' => ['country'],
                'columns'  => ['country_name', 'country_code', 'visitors'],
                'per_page' => 5,
            ],
            [
                'id'       => 'top_devices',
                'sources'  => ['visitors'],
                'group_by' => ['device_type'],
                'columns'  => ['device_type_name', 'visitors'],
                'per_page' => 5,
            ],
            [
                'id'       => 'top_os',
                'sources'  => ['visitors'],
                'group_by' => ['os'],
                'columns'  => ['os_name', 'visitors'],
                'per_page' => 5,
            ],
        ];

        $dateFrom = '2024-01-01';
        $dateTo   = '2024-01-31';

        $result = $this->handler->handleBatch($queries, $dateFrom, $dateTo);

        $this->assertTrue($result['success'], 'Batch request should succeed');
        $this->assertArrayHasKey('top_countries', $result['items']);
        $this->assertArrayHasKey('top_devices', $result['items']);
        $this->assertArrayHasKey('top_os', $result['items']);
    }
}
