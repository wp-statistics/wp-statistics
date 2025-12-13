<?php

namespace WP_Statistics\Tests;

use WP_Statistics\Service\AnalyticsQuery\AnalyticsQueryHandler;
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
}
