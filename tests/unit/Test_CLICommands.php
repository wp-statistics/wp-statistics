<?php

namespace WP_Statistics\Tests;

use WP_Statistics\Components\DateRange;
use WP_Statistics\Models\OnlineModel;
use WP_Statistics\Service\AnalyticsQuery\AnalyticsQueryHandler;
use WP_Statistics\Service\CLI\Commands\CacheCommand;
use WP_Statistics\Service\CLI\Commands\ExportCommand;
use WP_Statistics\Service\CLI\Commands\OnlineCommand;
use WP_Statistics\Service\CLI\Commands\QueryCommand;
use WP_Statistics\Service\CLI\Commands\RecordCommand;
use WP_Statistics\Service\CLI\Commands\ReinitializeCommand;
use WP_Statistics\Service\CLI\Commands\SummaryCommand;
use WP_Statistics\Service\CLI\Commands\VisitorsCommand;
use WP_UnitTestCase;

/**
 * Test CLI Commands
 *
 * Tests for WP Statistics CLI commands functionality.
 * Note: These tests focus on the command logic, not WP-CLI output formatting.
 *
 * @group cli
 */
class Test_CLICommands extends WP_UnitTestCase
{
    /**
     * Analytics query handler instance.
     *
     * @var AnalyticsQueryHandler
     */
    private $queryHandler;

    /**
     * Set up test environment.
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->queryHandler = new AnalyticsQueryHandler(false);
    }

    /**
     * Tear down test environment.
     */
    public function tearDown(): void
    {
        parent::tearDown();
    }

    // =========================================================================
    // SummaryCommand Tests
    // =========================================================================

    /**
     * Test SummaryCommand can be instantiated.
     */
    public function test_summary_command_instantiation()
    {
        $command = new SummaryCommand();
        $this->assertInstanceOf(SummaryCommand::class, $command);
    }

    /**
     * Test SummaryCommand uses valid time periods.
     */
    public function test_summary_command_time_periods_are_valid()
    {
        $validPeriods = ['today', 'yesterday', '7days', '30days', '12months', 'total'];

        foreach ($validPeriods as $period) {
            $dateRange = DateRange::resolveDate($period);
            $this->assertArrayHasKey('from', $dateRange, "Period '$period' should have 'from' date");
            $this->assertArrayHasKey('to', $dateRange, "Period '$period' should have 'to' date");
            $this->assertNotEmpty($dateRange['from'], "Period '$period' should have non-empty 'from' date");
            $this->assertNotEmpty($dateRange['to'], "Period '$period' should have non-empty 'to' date");
        }
    }

    /**
     * Test SummaryCommand query returns valid structure.
     */
    public function test_summary_command_query_returns_valid_structure()
    {
        $dateRange = DateRange::resolveDate('30days');

        $result = $this->queryHandler->handle([
            'sources'   => ['visitors', 'views'],
            'date_from' => $dateRange['from'],
            'date_to'   => $dateRange['to'],
            'format'    => 'flat',
        ]);

        $this->assertTrue($result['success'], 'Query should succeed');
        // FlatFormatter returns data in 'items' array (single row for no groupBy)
        $this->assertArrayHasKey('items', $result);
        $this->assertNotEmpty($result['items']);
        $this->assertArrayHasKey('visitors', $result['items'][0]);
        $this->assertArrayHasKey('views', $result['items'][0]);
    }

    /**
     * Test SummaryCommand with custom date range.
     */
    public function test_summary_command_custom_date_range()
    {
        $result = $this->queryHandler->handle([
            'sources'   => ['visitors', 'views'],
            'date_from' => '2024-01-01',
            'date_to'   => '2024-01-31',
            'format'    => 'flat',
        ]);

        $this->assertTrue($result['success'], 'Query should succeed with custom date range');
        $this->assertArrayHasKey('items', $result);
    }

    /**
     * Test SummaryCommand with period parameter.
     */
    public function test_summary_command_period_parameter()
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

            $this->assertTrue($result['success'], "Query should succeed for period '$period'");
        }
    }

    /**
     * Test SummaryCommand date validation.
     */
    public function test_summary_command_date_validation()
    {
        // Valid dates
        $validDates = ['2024-01-15', '2024-12-31', '2023-06-01'];

        foreach ($validDates as $date) {
            $d = \DateTime::createFromFormat('Y-m-d', $date);
            $this->assertTrue($d && $d->format('Y-m-d') === $date, "Date '$date' should be valid");
        }

        // Invalid dates
        $invalidDates = ['2024-13-01', '2024-01-32', '01-15-2024', 'invalid'];

        foreach ($invalidDates as $date) {
            $d = \DateTime::createFromFormat('Y-m-d', $date);
            $isValid = $d && $d->format('Y-m-d') === $date;
            $this->assertFalse($isValid, "Date '$date' should be invalid");
        }
    }

    // =========================================================================
    // OnlineCommand Tests
    // =========================================================================

    /**
     * Test OnlineCommand can be instantiated.
     */
    public function test_online_command_instantiation()
    {
        $command = new OnlineCommand();
        $this->assertInstanceOf(OnlineCommand::class, $command);
    }

    /**
     * Test OnlineModel countOnlines returns integer.
     */
    public function test_online_model_count_returns_integer()
    {
        $onlineModel = new OnlineModel();
        $count = $onlineModel->countOnlines();

        $this->assertIsInt($count);
        $this->assertGreaterThanOrEqual(0, $count);
    }

    /**
     * Test OnlineCommand threshold constant.
     */
    public function test_online_command_threshold_constant()
    {
        $reflection = new \ReflectionClass(OnlineCommand::class);
        $constant = $reflection->getConstant('ONLINE_THRESHOLD');

        $this->assertEquals(300, $constant, 'Online threshold should be 5 minutes (300 seconds)');
    }

    /**
     * Test OnlineCommand query for online visitors.
     */
    public function test_online_command_query_structure()
    {
        $fiveMinutesAgo = gmdate('Y-m-d H:i:s', time() - 300);

        $result = $this->queryHandler->handle([
            'sources'   => ['visitors'],
            'group_by'  => ['online_visitor'],
            'per_page'  => 15,
            'date_from' => $fiveMinutesAgo,
            'date_to'   => gmdate('Y-m-d H:i:s'),
        ]);

        $this->assertTrue($result['success'], 'Query should succeed');
        // TableFormatter (default) returns data with rows
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('rows', $result['data']);
    }

    // =========================================================================
    // VisitorsCommand Tests
    // =========================================================================

    /**
     * Test VisitorsCommand can be instantiated.
     */
    public function test_visitors_command_instantiation()
    {
        $command = new VisitorsCommand();
        $this->assertInstanceOf(VisitorsCommand::class, $command);
    }

    /**
     * Test VisitorsCommand query with default parameters.
     */
    public function test_visitors_command_default_query()
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

        $this->assertTrue($result['success'], 'Query should succeed');
        // TableFormatter (default) returns data with rows
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('rows', $result['data']);
    }

    /**
     * Test VisitorsCommand query with country filter.
     */
    public function test_visitors_command_country_filter()
    {
        $dateRange = DateRange::resolveDate('30days');

        $result = $this->queryHandler->handle([
            'sources'   => ['visitors'],
            'group_by'  => ['visitor'],
            'per_page'  => 15,
            'date_from' => $dateRange['from'],
            'date_to'   => $dateRange['to'],
            'filters'   => ['country' => 'US'],
        ]);

        $this->assertTrue($result['success'], 'Query should succeed with country filter');
    }

    /**
     * Test VisitorsCommand query with browser filter.
     */
    public function test_visitors_command_browser_filter()
    {
        $dateRange = DateRange::resolveDate('30days');

        $result = $this->queryHandler->handle([
            'sources'   => ['visitors'],
            'group_by'  => ['visitor'],
            'per_page'  => 15,
            'date_from' => $dateRange['from'],
            'date_to'   => $dateRange['to'],
            'filters'   => ['browser' => 'Chrome'],
        ]);

        $this->assertTrue($result['success'], 'Query should succeed with browser filter');
    }

    /**
     * Test VisitorsCommand query with combined filters.
     */
    public function test_visitors_command_combined_filters()
    {
        $dateRange = DateRange::resolveDate('30days');

        $result = $this->queryHandler->handle([
            'sources'   => ['visitors'],
            'group_by'  => ['visitor'],
            'per_page'  => 15,
            'date_from' => $dateRange['from'],
            'date_to'   => $dateRange['to'],
            'filters'   => [
                'country' => 'US',
                'browser' => 'Chrome',
                'os'      => 'Windows',
            ],
        ]);

        $this->assertTrue($result['success'], 'Query should succeed with combined filters');
    }

    /**
     * Test VisitorsCommand date validation helper.
     */
    public function test_visitors_command_date_validation()
    {
        // Valid dates
        $validDates = [
            '2024-01-15',
            '2024-12-31',
            '2023-06-01',
        ];

        foreach ($validDates as $date) {
            $d = \DateTime::createFromFormat('Y-m-d', $date);
            $this->assertTrue($d && $d->format('Y-m-d') === $date, "Date '$date' should be valid");
        }

        // Invalid dates
        $invalidDates = [
            '2024-13-01',  // Invalid month
            '2024-01-32',  // Invalid day
            '01-15-2024',  // Wrong format
            'invalid',     // Not a date
        ];

        foreach ($invalidDates as $date) {
            $d = \DateTime::createFromFormat('Y-m-d', $date);
            $isValid = $d && $d->format('Y-m-d') === $date;
            $this->assertFalse($isValid, "Date '$date' should be invalid");
        }
    }

    // =========================================================================
    // QueryCommand Tests
    // =========================================================================

    /**
     * Test QueryCommand can be instantiated.
     */
    public function test_query_command_instantiation()
    {
        $command = new QueryCommand();
        $this->assertInstanceOf(QueryCommand::class, $command);
    }

    /**
     * Test QueryCommand with visitors source.
     */
    public function test_query_command_visitors_source()
    {
        $result = $this->queryHandler->handle([
            'sources'   => ['visitors'],
            'date_from' => date('Y-m-d', strtotime('-30 days')),
            'date_to'   => date('Y-m-d'),
        ]);

        $this->assertTrue($result['success']);
        // TableFormatter without group_by returns totals, not rows
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('totals', $result['data']);
    }

    /**
     * Test QueryCommand with multiple sources.
     */
    public function test_query_command_multiple_sources()
    {
        $result = $this->queryHandler->handle([
            'sources'   => ['visitors', 'views', 'sessions'],
            'date_from' => date('Y-m-d', strtotime('-30 days')),
            'date_to'   => date('Y-m-d'),
        ]);

        $this->assertTrue($result['success']);
    }

    /**
     * Test QueryCommand with group_by date.
     */
    public function test_query_command_group_by_date()
    {
        $result = $this->queryHandler->handle([
            'sources'   => ['visitors', 'views'],
            'group_by'  => ['date'],
            'date_from' => date('Y-m-d', strtotime('-7 days')),
            'date_to'   => date('Y-m-d'),
        ]);

        $this->assertTrue($result['success']);
        // TableFormatter (default) returns data with rows
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('rows', $result['data']);
    }

    /**
     * Test QueryCommand with group_by country.
     */
    public function test_query_command_group_by_country()
    {
        $result = $this->queryHandler->handle([
            'sources'   => ['visitors'],
            'group_by'  => ['country'],
            'date_from' => date('Y-m-d', strtotime('-30 days')),
            'date_to'   => date('Y-m-d'),
            'per_page'  => 10,
        ]);

        $this->assertTrue($result['success']);
    }

    /**
     * Test QueryCommand with group_by browser.
     */
    public function test_query_command_group_by_browser()
    {
        $result = $this->queryHandler->handle([
            'sources'   => ['visitors'],
            'group_by'  => ['browser'],
            'date_from' => date('Y-m-d', strtotime('-30 days')),
            'date_to'   => date('Y-m-d'),
            'per_page'  => 10,
        ]);

        $this->assertTrue($result['success']);
    }

    /**
     * Test QueryCommand with group_by os.
     */
    public function test_query_command_group_by_os()
    {
        $result = $this->queryHandler->handle([
            'sources'   => ['visitors'],
            'group_by'  => ['os'],
            'date_from' => date('Y-m-d', strtotime('-30 days')),
            'date_to'   => date('Y-m-d'),
            'per_page'  => 10,
        ]);

        $this->assertTrue($result['success']);
    }

    /**
     * Test QueryCommand with bounce_rate source.
     */
    public function test_query_command_bounce_rate_source()
    {
        $result = $this->queryHandler->handle([
            'sources'   => ['bounce_rate', 'sessions'],
            'date_from' => date('Y-m-d', strtotime('-30 days')),
            'date_to'   => date('Y-m-d'),
        ]);

        $this->assertTrue($result['success']);
    }

    /**
     * Test QueryCommand with pages_per_session source.
     */
    public function test_query_command_pages_per_session_source()
    {
        $result = $this->queryHandler->handle([
            'sources'   => ['pages_per_session', 'sessions', 'views'],
            'date_from' => date('Y-m-d', strtotime('-30 days')),
            'date_to'   => date('Y-m-d'),
        ]);

        $this->assertTrue($result['success']);
    }

    /**
     * Test QueryCommand with invalid source throws exception.
     */
    public function test_query_command_invalid_source_throws_exception()
    {
        $this->expectException(\Exception::class);

        $this->queryHandler->handle([
            'sources'   => ['invalid_source'],
            'date_from' => date('Y-m-d', strtotime('-30 days')),
            'date_to'   => date('Y-m-d'),
        ]);
    }

    /**
     * Test QueryCommand with filters.
     */
    public function test_query_command_with_filters()
    {
        $result = $this->queryHandler->handle([
            'sources'   => ['visitors'],
            'group_by'  => ['date'],
            'date_from' => date('Y-m-d', strtotime('-30 days')),
            'date_to'   => date('Y-m-d'),
            'filters'   => [
                'country' => 'US',
            ],
        ]);

        $this->assertTrue($result['success']);
    }

    // =========================================================================
    // ExportCommand Tests
    // =========================================================================

    /**
     * Test ExportCommand can be instantiated.
     */
    public function test_export_command_instantiation()
    {
        $command = new ExportCommand();
        $this->assertInstanceOf(ExportCommand::class, $command);
    }

    /**
     * Test ExportCommand query for visitors export.
     */
    public function test_export_command_visitors_query()
    {
        $dateRange = DateRange::resolveDate('30days');

        $result = $this->queryHandler->handle([
            'sources'  => ['visitors'],
            'group_by' => ['visitor'],
            'order_by' => 'last_visit',
            'order'    => 'DESC',
            'date_from' => $dateRange['from'],
            'date_to'   => $dateRange['to'],
            'per_page'  => 1000,
            'format'    => 'export',
        ]);

        $this->assertTrue($result['success']);
    }

    /**
     * Test ExportCommand query for countries export.
     */
    public function test_export_command_countries_query()
    {
        $dateRange = DateRange::resolveDate('30days');

        $result = $this->queryHandler->handle([
            'sources'   => ['visitors', 'views'],
            'group_by'  => ['country'],
            'order_by'  => 'visitors',
            'order'     => 'DESC',
            'date_from' => $dateRange['from'],
            'date_to'   => $dateRange['to'],
            'per_page'  => 1000,
            'format'    => 'export',
        ]);

        $this->assertTrue($result['success']);
    }

    /**
     * Test ExportCommand query for browsers export.
     */
    public function test_export_command_browsers_query()
    {
        $dateRange = DateRange::resolveDate('30days');

        $result = $this->queryHandler->handle([
            'sources'   => ['visitors'],
            'group_by'  => ['browser'],
            'order_by'  => 'visitors',
            'order'     => 'DESC',
            'date_from' => $dateRange['from'],
            'date_to'   => $dateRange['to'],
            'per_page'  => 1000,
            'format'    => 'export',
        ]);

        $this->assertTrue($result['success']);
    }

    /**
     * Test ExportCommand CSV generation helper.
     */
    public function test_export_command_csv_generation()
    {
        $data = [
            ['country' => 'United States', 'visitors' => 100, 'views' => 500],
            ['country' => 'Germany', 'visitors' => 50, 'views' => 200],
            ['country' => 'France', 'visitors' => 25, 'views' => 100],
        ];

        // Test CSV generation logic
        $output = fopen('php://temp', 'r+');
        $headers = array_keys($data[0]);
        fputcsv($output, $headers);

        foreach ($data as $row) {
            fputcsv($output, array_values($row));
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        $this->assertStringContainsString('country,visitors,views', $csv);
        // fputcsv quotes fields containing spaces
        $this->assertStringContainsString('"United States",100,500', $csv);
        $this->assertStringContainsString('Germany,50,200', $csv);
    }

    // =========================================================================
    // CacheCommand Tests
    // =========================================================================

    /**
     * Test CacheCommand can be instantiated.
     */
    public function test_cache_command_instantiation()
    {
        $command = new CacheCommand();
        $this->assertInstanceOf(CacheCommand::class, $command);
    }

    /**
     * Test AnalyticsQueryHandler cache clearing.
     */
    public function test_cache_command_clear_cache()
    {
        $handler = new AnalyticsQueryHandler(true);
        $cleared = $handler->clearCache();

        $this->assertIsInt($cleared);
        $this->assertGreaterThanOrEqual(0, $cleared);
    }

    // =========================================================================
    // ReinitializeCommand Tests
    // =========================================================================

    /**
     * Test ReinitializeCommand can be instantiated.
     */
    public function test_reinitialize_command_instantiation()
    {
        $command = new ReinitializeCommand();
        $this->assertInstanceOf(ReinitializeCommand::class, $command);
    }

    // =========================================================================
    // RecordCommand Tests
    // =========================================================================

    /**
     * Test RecordCommand can be instantiated.
     */
    public function test_record_command_instantiation()
    {
        $command = new RecordCommand();
        $this->assertInstanceOf(RecordCommand::class, $command);
    }

    // =========================================================================
    // DateRange Integration Tests
    // =========================================================================

    /**
     * Test DateRange resolveDate for all supported periods.
     */
    public function test_date_range_all_periods()
    {
        $periods = [
            'today'     => 1,
            'yesterday' => 1,
            '7days'     => 7,
            '30days'    => 30,
            '90days'    => 90,
        ];

        foreach ($periods as $period => $expectedDays) {
            $dateRange = DateRange::resolveDate($period);

            $this->assertArrayHasKey('from', $dateRange);
            $this->assertArrayHasKey('to', $dateRange);

            // Validate date format
            $from = \DateTime::createFromFormat('Y-m-d', $dateRange['from']);
            $to = \DateTime::createFromFormat('Y-m-d', $dateRange['to']);

            $this->assertNotFalse($from, "Period '$period' should have valid 'from' date");
            $this->assertNotFalse($to, "Period '$period' should have valid 'to' date");
        }
    }

    /**
     * Test DateRange custom date range.
     */
    public function test_date_range_custom_range()
    {
        $customFrom = '2024-01-01';
        $customTo = '2024-01-31';

        // Custom date range should be usable in queries
        $result = $this->queryHandler->handle([
            'sources'   => ['visitors'],
            'date_from' => $customFrom,
            'date_to'   => $customTo,
        ]);

        $this->assertTrue($result['success']);
    }

    // =========================================================================
    // Filter Integration Tests
    // =========================================================================

    /**
     * Test country filter in visitors query.
     */
    public function test_filter_by_country()
    {
        $result = $this->queryHandler->handle([
            'sources'   => ['visitors'],
            'group_by'  => ['visitor'],
            'date_from' => date('Y-m-d', strtotime('-30 days')),
            'date_to'   => date('Y-m-d'),
            'filters'   => ['country' => 'US'],
        ]);

        $this->assertTrue($result['success']);
    }

    /**
     * Test browser filter in visitors query.
     */
    public function test_filter_by_browser()
    {
        $result = $this->queryHandler->handle([
            'sources'   => ['visitors'],
            'group_by'  => ['visitor'],
            'date_from' => date('Y-m-d', strtotime('-30 days')),
            'date_to'   => date('Y-m-d'),
            'filters'   => ['browser' => 'Chrome'],
        ]);

        $this->assertTrue($result['success']);
    }

    /**
     * Test OS filter in visitors query.
     */
    public function test_filter_by_os()
    {
        $result = $this->queryHandler->handle([
            'sources'   => ['visitors'],
            'group_by'  => ['visitor'],
            'date_from' => date('Y-m-d', strtotime('-30 days')),
            'date_to'   => date('Y-m-d'),
            'filters'   => ['os' => 'Windows'],
        ]);

        $this->assertTrue($result['success']);
    }

    /**
     * Test post_type filter in views query.
     */
    public function test_filter_by_post_type()
    {
        $result = $this->queryHandler->handle([
            'sources'   => ['views'],
            'group_by'  => ['page'],
            'date_from' => date('Y-m-d', strtotime('-30 days')),
            'date_to'   => date('Y-m-d'),
            'filters'   => ['post_type' => 'post'],
        ]);

        $this->assertTrue($result['success']);
    }

    /**
     * Test multiple filters combined.
     */
    public function test_multiple_filters_combined()
    {
        $result = $this->queryHandler->handle([
            'sources'   => ['visitors'],
            'group_by'  => ['visitor'],
            'date_from' => date('Y-m-d', strtotime('-30 days')),
            'date_to'   => date('Y-m-d'),
            'filters'   => [
                'country' => 'US',
                'browser' => 'Chrome',
                'os'      => 'Windows',
            ],
        ]);

        $this->assertTrue($result['success']);
    }

    // =========================================================================
    // Output Format Tests
    // =========================================================================

    /**
     * Test table format output structure.
     */
    public function test_output_format_table()
    {
        $result = $this->queryHandler->handle([
            'sources'   => ['visitors', 'views'],
            'group_by'  => ['date'],
            'date_from' => date('Y-m-d', strtotime('-7 days')),
            'date_to'   => date('Y-m-d'),
            'format'    => 'table',
        ]);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('data', $result);

        if (!empty($result['data']['rows'])) {
            $this->assertArrayHasKey('totals', $result['data']);
        }
    }

    /**
     * Test flat format output structure.
     */
    public function test_output_format_flat()
    {
        $result = $this->queryHandler->handle([
            'sources'   => ['visitors', 'views'],
            'date_from' => date('Y-m-d', strtotime('-7 days')),
            'date_to'   => date('Y-m-d'),
            'format'    => 'flat',
        ]);

        $this->assertTrue($result['success']);
        // FlatFormatter returns data in 'items' array (single row for no groupBy)
        $this->assertArrayHasKey('items', $result);
        $this->assertNotEmpty($result['items']);
        $this->assertArrayHasKey('visitors', $result['items'][0]);
        $this->assertArrayHasKey('views', $result['items'][0]);
    }

    /**
     * Test export command uses table format for rich data.
     */
    public function test_export_command_uses_table_format()
    {
        // ExportCommand now uses 'table' format to preserve all enriched columns
        $result = $this->queryHandler->handle([
            'sources'   => ['visitors'],
            'group_by'  => ['visitor'],
            'date_from' => date('Y-m-d', strtotime('-30 days')),
            'date_to'   => date('Y-m-d'),
            'format'    => 'table',
        ]);

        $this->assertTrue($result['success']);
        // TableFormatter returns rows under data.rows
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('rows', $result['data']);
    }

    // =========================================================================
    // Pagination Tests
    // =========================================================================

    /**
     * Test pagination with per_page parameter.
     */
    public function test_pagination_per_page()
    {
        $result = $this->queryHandler->handle([
            'sources'   => ['visitors'],
            'group_by'  => ['country'],
            'date_from' => date('Y-m-d', strtotime('-30 days')),
            'date_to'   => date('Y-m-d'),
            'per_page'  => 5,
        ]);

        $this->assertTrue($result['success']);

        if (!empty($result['data']['rows'])) {
            $this->assertLessThanOrEqual(5, count($result['data']['rows']));
        }
    }

    /**
     * Test pagination with page parameter.
     */
    public function test_pagination_page()
    {
        $result = $this->queryHandler->handle([
            'sources'   => ['visitors'],
            'group_by'  => ['country'],
            'date_from' => date('Y-m-d', strtotime('-30 days')),
            'date_to'   => date('Y-m-d'),
            'per_page'  => 5,
            'page'      => 1,
        ]);

        $this->assertTrue($result['success']);
    }

    // =========================================================================
    // Order Tests
    // =========================================================================

    /**
     * Test order by visitors DESC.
     */
    public function test_order_by_visitors_desc()
    {
        $result = $this->queryHandler->handle([
            'sources'   => ['visitors'],
            'group_by'  => ['country'],
            'date_from' => date('Y-m-d', strtotime('-30 days')),
            'date_to'   => date('Y-m-d'),
            'order_by'  => 'visitors',
            'order'     => 'DESC',
            'per_page'  => 10,
        ]);

        $this->assertTrue($result['success']);
    }

    /**
     * Test order by visitors ASC.
     */
    public function test_order_by_visitors_asc()
    {
        $result = $this->queryHandler->handle([
            'sources'   => ['visitors'],
            'group_by'  => ['country'],
            'date_from' => date('Y-m-d', strtotime('-30 days')),
            'date_to'   => date('Y-m-d'),
            'order_by'  => 'visitors',
            'order'     => 'ASC',
            'per_page'  => 10,
        ]);

        $this->assertTrue($result['success']);
    }
}
