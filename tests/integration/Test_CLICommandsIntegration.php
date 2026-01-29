<?php

namespace WP_Statistics\Tests;

use WP_Statistics\Components\DateRange;
use WP_Statistics\Entity\EntityFactory;
use WP_Statistics\Models\OnlineModel;
use WP_Statistics\Records\RecordFactory;
use WP_Statistics\Service\Analytics\VisitorProfile;
use WP_Statistics\Service\AnalyticsQuery\AnalyticsQueryHandler;
use WP_UnitTestCase;

/**
 * Integration Tests for CLI Commands
 *
 * These tests verify the underlying functionality used by CLI commands
 * with actual database operations.
 *
 * @group cli
 * @group integration
 */
class Test_CLICommandsIntegration extends WP_UnitTestCase
{
    /**
     * Analytics query handler instance.
     *
     * @var AnalyticsQueryHandler
     */
    private $queryHandler;

    /**
     * Online model instance.
     *
     * @var OnlineModel
     */
    private $onlineModel;

    /**
     * Set up test environment.
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->queryHandler = new AnalyticsQueryHandler(false);
        $this->onlineModel = new OnlineModel();
    }

    /**
     * Tear down test environment.
     */
    public function tearDown(): void
    {
        parent::tearDown();
    }

    // =========================================================================
    // Summary Command Integration Tests
    // =========================================================================

    /**
     * Test summary data retrieval for all time periods.
     */
    public function test_summary_all_time_periods()
    {
        $periods = [
            'today'     => 'Today',
            'yesterday' => 'Yesterday',
            '7days'     => 'Week',
            '30days'    => 'Month',
            '12months'  => 'Year',
            'total'     => 'Total',
        ];

        foreach ($periods as $period => $label) {
            $dateRange = DateRange::resolveDate($period);

            $result = $this->queryHandler->handle([
                'sources'   => ['visitors', 'views'],
                'date_from' => $dateRange['from'],
                'date_to'   => $dateRange['to'],
                'format'    => 'flat',
            ]);

            $this->assertTrue($result['success'], "Summary query for '$label' should succeed");
            // FlatFormatter returns data in 'items' array (single row for no groupBy)
            $this->assertArrayHasKey('items', $result);
            $this->assertNotEmpty($result['items'], "Should have items for '$label'");

            $data = $result['items'][0];
            $this->assertArrayHasKey('visitors', $data, "Should have visitors for '$label'");
            $this->assertArrayHasKey('views', $data, "Should have views for '$label'");

            // Values should be numeric
            $this->assertIsNumeric($data['visitors'], "Visitors for '$label' should be numeric");
            $this->assertIsNumeric($data['views'], "Views for '$label' should be numeric");
        }
    }

    /**
     * Test summary with custom date range.
     */
    public function test_summary_custom_date_range()
    {
        $result = $this->queryHandler->handle([
            'sources'   => ['visitors', 'views'],
            'date_from' => '2024-01-01',
            'date_to'   => '2024-01-31',
            'format'    => 'flat',
        ]);

        $this->assertTrue($result['success'], 'Summary query for custom date range should succeed');
        $this->assertArrayHasKey('items', $result);
    }

    /**
     * Test summary with period options.
     */
    public function test_summary_period_options()
    {
        $periods = ['today', 'yesterday', '7days', '30days', '90days', '12months', 'total'];

        foreach ($periods as $period) {
            $dateRange = DateRange::resolveDate($period);

            $result = $this->queryHandler->handle([
                'sources'   => ['visitors', 'views'],
                'date_from' => $dateRange['from'],
                'date_to'   => $dateRange['to'],
                'format'    => 'flat',
            ]);

            $this->assertTrue($result['success'], "Summary query for period '$period' should succeed");
        }
    }

    /**
     * Test online users count.
     */
    public function test_online_users_count()
    {
        $count = $this->onlineModel->countOnlines();

        $this->assertIsInt($count, 'Online count should be an integer');
        $this->assertGreaterThanOrEqual(0, $count, 'Online count should be non-negative');
    }

    // =========================================================================
    // Visitors Command Integration Tests
    // =========================================================================

    /**
     * Test visitors query returns expected structure.
     */
    public function test_visitors_query_structure()
    {
        $dateRange = DateRange::resolveDate('30days');

        $result = $this->queryHandler->handle([
            'sources'   => ['visitors'],
            'group_by'  => ['visitor'],
            'per_page'  => 15,
            'order_by'  => 'last_visit',
            'order'     => 'DESC',
            'date_from' => $dateRange['from'],
            'date_to'   => $dateRange['to'],
        ]);

        $this->assertTrue($result['success']);
        // TableFormatter (default) returns data with rows
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('rows', $result['data']);
        $this->assertArrayHasKey('meta', $result);

        // Check meta contains pagination info
        if (!empty($result['data']['rows'])) {
            $this->assertArrayHasKey('total_rows', $result['meta']);
        }
    }

    /**
     * Test visitors query with all filter combinations.
     */
    public function test_visitors_filter_combinations()
    {
        $dateRange = DateRange::resolveDate('30days');

        $filterCombinations = [
            ['country' => 'US'],
            ['browser' => 'Chrome'],
            ['os' => 'Windows'],
            ['country' => 'US', 'browser' => 'Chrome'],
            ['country' => 'DE', 'os' => 'macOS'],
            ['browser' => 'Firefox', 'os' => 'Linux'],
            ['country' => 'GB', 'browser' => 'Safari', 'os' => 'macOS'],
        ];

        foreach ($filterCombinations as $filters) {
            $result = $this->queryHandler->handle([
                'sources'   => ['visitors'],
                'group_by'  => ['visitor'],
                'per_page'  => 10,
                'date_from' => $dateRange['from'],
                'date_to'   => $dateRange['to'],
                'filters'   => $filters,
            ]);

            $filterDesc = json_encode($filters);
            $this->assertTrue($result['success'], "Query with filters $filterDesc should succeed");
        }
    }

    /**
     * Test visitors query with custom date ranges.
     */
    public function test_visitors_custom_date_ranges()
    {
        $dateRanges = [
            ['from' => date('Y-m-d'), 'to' => date('Y-m-d')], // Today
            ['from' => date('Y-m-d', strtotime('-1 day')), 'to' => date('Y-m-d', strtotime('-1 day'))], // Yesterday
            ['from' => date('Y-m-d', strtotime('-7 days')), 'to' => date('Y-m-d')], // Last 7 days
            ['from' => date('Y-01-01'), 'to' => date('Y-m-d')], // Year to date
        ];

        foreach ($dateRanges as $range) {
            $result = $this->queryHandler->handle([
                'sources'   => ['visitors'],
                'group_by'  => ['visitor'],
                'per_page'  => 10,
                'date_from' => $range['from'],
                'date_to'   => $range['to'],
            ]);

            $this->assertTrue($result['success'], "Query for date range {$range['from']} to {$range['to']} should succeed");
        }
    }

    // =========================================================================
    // Query Command Integration Tests
    // =========================================================================

    /**
     * Test all available sources.
     */
    public function test_query_all_sources()
    {
        $sources = [
            ['visitors'],
            ['views'],
            ['sessions'],
            ['visitors', 'views'],
            ['visitors', 'views', 'sessions'],
            ['bounce_rate', 'sessions'],
            ['pages_per_session', 'sessions', 'views'],
            ['avg_session_duration', 'sessions'],
        ];

        $dateRange = DateRange::resolveDate('30days');

        foreach ($sources as $sourceList) {
            $result = $this->queryHandler->handle([
                'sources'   => $sourceList,
                'date_from' => $dateRange['from'],
                'date_to'   => $dateRange['to'],
            ]);

            $sourceDesc = implode(', ', $sourceList);
            $this->assertTrue($result['success'], "Query for sources [$sourceDesc] should succeed");
        }
    }

    /**
     * Test all available group_by options.
     */
    public function test_query_all_group_by()
    {
        $groupByOptions = [
            'date',
            'country',
            'browser',
            'os',
            'device_type',
            'referrer',
            'page',
            'language',
            'city',
            'continent',
            'resolution',
        ];

        $dateRange = DateRange::resolveDate('30days');

        foreach ($groupByOptions as $groupBy) {
            $result = $this->queryHandler->handle([
                'sources'   => ['visitors'],
                'group_by'  => [$groupBy],
                'date_from' => $dateRange['from'],
                'date_to'   => $dateRange['to'],
                'per_page'  => 10,
            ]);

            $this->assertTrue($result['success'], "Query with group_by '$groupBy' should succeed");
        }
    }

    /**
     * Test query with ordering.
     */
    public function test_query_ordering()
    {
        $dateRange = DateRange::resolveDate('30days');

        $orderOptions = [
            ['order_by' => 'visitors', 'order' => 'DESC'],
            ['order_by' => 'visitors', 'order' => 'ASC'],
            ['order_by' => 'views', 'order' => 'DESC'],
            ['order_by' => 'date', 'order' => 'DESC'],
        ];

        foreach ($orderOptions as $options) {
            $result = $this->queryHandler->handle([
                'sources'   => ['visitors', 'views'],
                'group_by'  => ['date'],
                'date_from' => $dateRange['from'],
                'date_to'   => $dateRange['to'],
                'order_by'  => $options['order_by'],
                'order'     => $options['order'],
                'per_page'  => 10,
            ]);

            $this->assertTrue($result['success'], "Query with order_by '{$options['order_by']}' {$options['order']} should succeed");
        }
    }

    /**
     * Test query with comparison enabled.
     */
    public function test_query_with_comparison()
    {
        $dateRange = DateRange::resolveDate('30days');

        $result = $this->queryHandler->handle([
            'sources'   => ['visitors', 'views'],
            'date_from' => $dateRange['from'],
            'date_to'   => $dateRange['to'],
            'compare'   => true,
        ]);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('meta', $result);
        $this->assertArrayHasKey('compare_from', $result['meta'], 'Should have comparison period start');
        $this->assertArrayHasKey('compare_to', $result['meta'], 'Should have comparison period end');
    }

    // =========================================================================
    // Export Command Integration Tests
    // =========================================================================

    /**
     * Test export query for all export types.
     * ExportCommand uses 'table' format to preserve all enriched columns.
     */
    public function test_export_all_types()
    {
        $exportTypes = [
            'visitors'  => ['sources' => ['visitors'], 'group_by' => ['visitor']],
            'views'     => ['sources' => ['views'], 'group_by' => ['date']],
            'pages'     => ['sources' => ['views', 'visitors'], 'group_by' => ['page']],
            'countries' => ['sources' => ['visitors', 'views'], 'group_by' => ['country']],
            'browsers'  => ['sources' => ['visitors'], 'group_by' => ['browser']],
            'referrers' => ['sources' => ['visitors', 'views'], 'group_by' => ['referrer']],
        ];

        $dateRange = DateRange::resolveDate('30days');

        foreach ($exportTypes as $type => $config) {
            $result = $this->queryHandler->handle([
                'sources'   => $config['sources'],
                'group_by'  => $config['group_by'],
                'date_from' => $dateRange['from'],
                'date_to'   => $dateRange['to'],
                'per_page'  => 1000,
                'format'    => 'table',
            ]);

            $this->assertTrue($result['success'], "Export query for '$type' should succeed");
            // TableFormatter returns rows under data.rows
            $this->assertArrayHasKey('data', $result);
            $this->assertArrayHasKey('rows', $result['data']);
        }
    }

    /**
     * Test export with limit.
     * ExportCommand uses 'table' format to preserve all enriched columns.
     */
    public function test_export_with_limit()
    {
        $dateRange = DateRange::resolveDate('30days');
        $limits = [10, 100, 500, 1000];

        foreach ($limits as $limit) {
            $result = $this->queryHandler->handle([
                'sources'   => ['visitors'],
                'group_by'  => ['country'],
                'date_from' => $dateRange['from'],
                'date_to'   => $dateRange['to'],
                'per_page'  => $limit,
                'format'    => 'table',
            ]);

            $this->assertTrue($result['success'], "Export with limit $limit should succeed");

            if (!empty($result['data']['rows'])) {
                $this->assertLessThanOrEqual($limit, count($result['data']['rows']), "Should not exceed limit $limit");
            }
        }
    }

    // =========================================================================
    // Cache Command Integration Tests
    // =========================================================================

    /**
     * Test cache clear functionality.
     */
    public function test_cache_clear()
    {
        // Create a handler with cache enabled
        $handler = new AnalyticsQueryHandler(true);

        // Execute a query to populate cache
        $handler->handle([
            'sources'   => ['visitors'],
            'date_from' => date('Y-m-d', strtotime('-7 days')),
            'date_to'   => date('Y-m-d'),
        ]);

        // Clear cache
        $cleared = $handler->clearCache();

        $this->assertIsInt($cleared);
        $this->assertGreaterThanOrEqual(0, $cleared);
    }

    // =========================================================================
    // Record Command Integration Tests
    // =========================================================================

    /**
     * Test visitor profile creation.
     */
    public function test_visitor_profile_creation()
    {
        // Set up server variables
        $_SERVER['REMOTE_ADDR'] = '192.168.1.100';
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36';

        $profile = new VisitorProfile();

        // Check that profile can retrieve IP
        $ip = $profile->getIp();
        $this->assertNotEmpty($ip, 'Profile should have IP address');

        // Check that profile can retrieve user agent
        $ua = $profile->getHttpUserAgent();
        $this->assertNotEmpty($ua, 'Profile should have user agent');

        // Cleanup
        unset($_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']);
    }

    /**
     * Test ResourceUri record creation.
     */
    public function test_resource_uri_creation()
    {
        $uri = '/test-page-' . time() . '/';

        // Check if record can be created
        $recordFactory = RecordFactory::resourceUri();
        $this->assertNotNull($recordFactory);
    }

    // =========================================================================
    // Batch Query Integration Tests
    // =========================================================================

    /**
     * Test batch query execution.
     */
    public function test_batch_query()
    {
        $queries = [
            [
                'id'       => 'total_visitors',
                'sources'  => ['visitors'],
                'group_by' => [],
            ],
            [
                'id'       => 'total_views',
                'sources'  => ['views'],
                'group_by' => [],
            ],
            [
                'id'       => 'top_countries',
                'sources'  => ['visitors'],
                'group_by' => ['country'],
                'per_page' => 5,
            ],
            [
                'id'       => 'top_browsers',
                'sources'  => ['visitors'],
                'group_by' => ['browser'],
                'per_page' => 5,
            ],
        ];

        $dateFrom = date('Y-m-d', strtotime('-30 days'));
        $dateTo = date('Y-m-d');

        $result = $this->queryHandler->handleBatch($queries, $dateFrom, $dateTo);

        $this->assertTrue($result['success'], 'Batch query should succeed');
        $this->assertArrayHasKey('items', $result);
        $this->assertArrayHasKey('total_visitors', $result['items']);
        $this->assertArrayHasKey('total_views', $result['items']);
        $this->assertArrayHasKey('top_countries', $result['items']);
        $this->assertArrayHasKey('top_browsers', $result['items']);
    }

    /**
     * Test batch query with global filters.
     */
    public function test_batch_query_with_global_filters()
    {
        $queries = [
            [
                'id'       => 'visitors',
                'sources'  => ['visitors'],
                'group_by' => [],
            ],
            [
                'id'       => 'views',
                'sources'  => ['views'],
                'group_by' => [],
            ],
        ];

        $dateFrom = date('Y-m-d', strtotime('-30 days'));
        $dateTo = date('Y-m-d');
        $globalFilters = ['country' => 'US'];

        $result = $this->queryHandler->handleBatch($queries, $dateFrom, $dateTo, $globalFilters);

        $this->assertTrue($result['success'], 'Batch query with global filters should succeed');
    }

    // =========================================================================
    // Error Handling Integration Tests
    // =========================================================================

    /**
     * Test invalid source handling.
     */
    public function test_invalid_source_error()
    {
        $this->expectException(\Exception::class);

        $this->queryHandler->handle([
            'sources'   => ['invalid_source'],
            'date_from' => date('Y-m-d', strtotime('-7 days')),
            'date_to'   => date('Y-m-d'),
        ]);
    }

    /**
     * Test invalid group_by handling.
     */
    public function test_invalid_group_by_error()
    {
        $this->expectException(\Exception::class);

        $this->queryHandler->handle([
            'sources'   => ['visitors'],
            'group_by'  => ['invalid_group_by'],
            'date_from' => date('Y-m-d', strtotime('-7 days')),
            'date_to'   => date('Y-m-d'),
        ]);
    }

    /**
     * Test invalid date range handling.
     */
    public function test_invalid_date_range_error()
    {
        $this->expectException(\Exception::class);

        $this->queryHandler->handle([
            'sources'   => ['visitors'],
            'date_from' => '2024-01-31',
            'date_to'   => '2024-01-01', // to is before from
        ]);
    }

    /**
     * Test empty sources error.
     */
    public function test_empty_sources_error()
    {
        $this->expectException(\Exception::class);

        $this->queryHandler->handle([
            'sources'   => [],
            'date_from' => date('Y-m-d', strtotime('-7 days')),
            'date_to'   => date('Y-m-d'),
        ]);
    }
}
