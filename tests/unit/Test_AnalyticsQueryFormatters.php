<?php

namespace WP_Statistics\Tests;

use WP_Statistics\Service\AnalyticsQuery\AnalyticsQueryHandler;
use WP_Statistics\Service\AnalyticsQuery\Formatters\TableFormatter;
use WP_Statistics\Service\AnalyticsQuery\Formatters\FlatFormatter;
use WP_Statistics\Service\AnalyticsQuery\Formatters\ChartFormatter;
use WP_Statistics\Service\AnalyticsQuery\Formatters\ExportFormatter;
use WP_Statistics\Service\AnalyticsQuery\Exceptions\InvalidFormatException;
use WP_Statistics\Service\AnalyticsQuery\Query\Query;
use WP_UnitTestCase;

/**
 * Unit tests for analytics response formatters.
 *
 * Verifies each formatter outputs the expected structure and metadata.
 */
class Test_AnalyticsQueryFormatters extends WP_UnitTestCase
{
    /**
     * Build a Query instance tailored for formatter testing.
     *
     * @param array  $sources  Metrics to include.
     * @param array  $groupBy  Group by fields.
     * @param bool   $compare  Whether comparison is enabled.
     * @param string $format   Response format name.
     * @return Query
     */
    private function makeQuery(
        array $sources,
        array $groupBy,
        bool $compare,
        string $format
    ): Query {
        return new Query(
            $sources,                    // sources
            $groupBy,                    // groupBy
            [],                          // filters
            '2024-11-01 00:00:00',       // dateFrom
            '2024-11-03 23:59:59',       // dateTo
            null,                        // orderBy
            'DESC',                      // order
            1,                           // page
            10,                          // perPage
            $compare,                    // compare
            null,                        // previousDateFrom
            null,                        // previousDateTo
            null,                        // comparisonMode
            null,                        // dateColumn
            true,                        // aggregateOthers
            null,                        // originalPerPage
            true,                        // showTotals
            $format                      // format
        );
    }

    /**
     * Ensure table formatter returns rows, totals, and pagination meta.
     */
    public function test_table_formatter_structure()
    {
        $formatter = new TableFormatter();
        $query     = $this->makeQuery(['visitors', 'views'], ['date'], false, 'table');

        $rows = [
            ['date' => '2024-11-01', 'visitors' => 100, 'views' => 250],
            ['date' => '2024-11-02', 'visitors' => 120, 'views' => 280],
        ];

        $result   = $formatter->format($query, ['rows' => $rows, 'totals' => ['visitors' => 220, 'views' => 530], 'total' => 2]);
        $data     = $result['data'];
        $metadata = $result['meta'];

        $this->assertTrue($result['success']);
        $this->assertEquals($rows, $data['rows']);
        $this->assertEquals(['visitors' => 220, 'views' => 530], $data['totals']);
        $this->assertSame(2, $metadata['total_rows']);
        $this->assertSame(1, $metadata['page']);
        $this->assertSame(10, $metadata['per_page']);
        $this->assertEquals(1, $metadata['total_pages']);
    }

    /**
     * Ensure queries default to table format when none is provided.
     */
    public function test_default_format_is_table_when_omitted()
    {
        $query = Query::fromArray([
            'sources'  => ['visitors'],
            'group_by' => ['date'],
            // no format key
        ]);

        $this->assertSame('table', $query->getFormat());
    }

    /**
     * Ensure invalid format values throw an exception via the handler.
     */
    public function test_invalid_format_throws_exception()
    {
        $this->expectException(InvalidFormatException::class);

        Query::fromArray([
            'sources'  => ['visitors'],
            'group_by' => ['date'],
            'format'   => 'bad-format', // invalid
        ]);
    }

    /**
     * Ensure flat formatter returns items and totals with base meta.
     */
    public function test_flat_formatter_structure()
    {
        $formatter = new FlatFormatter();
        $query     = $this->makeQuery(['visitors'], ['country'], false, 'flat');

        $rows   = [
            ['country' => 'US', 'visitors' => 100],
            ['country' => 'GB', 'visitors' => 80],
        ];
        $totals = ['visitors' => 180];

        $result = $formatter->format($query, ['rows' => $rows, 'totals' => $totals, 'total' => 2]);

        $this->assertTrue($result['success']);
        $this->assertEquals($rows, $result['items']);
        $this->assertEquals($totals, $result['totals']);
        $this->assertSame('2024-11-01 00:00:00', $result['meta']['date_from']);
        $this->assertSame('2024-11-03 23:59:59', $result['meta']['date_to']);
    }

    /**
     * Ensure chart formatter builds labels and comparison datasets.
     */
    public function test_chart_formatter_with_comparison_datasets()
    {
        $formatter = new ChartFormatter();
        $query     = $this->makeQuery(['visitors'], ['date'], true, 'chart');

        $rows = [
            ['date' => '2024-11-01', 'visitors' => 100, 'previous' => ['visitors' => 85]],
            ['date' => '2024-11-02', 'visitors' => 120, 'previous' => ['visitors' => 95]],
            ['date' => '2024-11-03', 'visitors' => 130, 'previous' => ['visitors' => 100]],
        ];

        $result = $formatter->format($query, ['rows' => $rows, 'totals' => null, 'total' => 3]);

        $this->assertTrue($result['success']);
        // Date range is Nov 1-3, so expect 3 labels (fillMissingDates generates all dates in range)
        $this->assertSame(['2024-11-01', '2024-11-02', '2024-11-03'], $result['labels']);
        $this->assertCount(2, $result['datasets']);

        $currentDataset   = $result['datasets'][0];
        $previousDataset  = $result['datasets'][1];

        $this->assertSame('Visitors', $currentDataset['label']);
        $this->assertSame([100.0, 120.0, 130.0], $currentDataset['data']);

        $this->assertSame('Visitors (Previous)', $previousDataset['label']);
        $this->assertSame([85.0, 95.0, 100.0], $previousDataset['data']);
        $this->assertTrue($previousDataset['comparison']);
    }

    /**
     * Ensure chart formatter returns an error when group_by is missing.
     */
    public function test_chart_formatter_requires_group_by()
    {
        $formatter = new ChartFormatter();
        $query     = $this->makeQuery(['visitors'], [], false, 'chart');

        $result = $formatter->format($query, ['rows' => [], 'totals' => null, 'total' => 0]);

        $this->assertFalse($result['success']);
        $this->assertEquals('chart_requires_group_by', $result['error']['code']);
    }

    /**
     * Ensure export formatter outputs headers and rows with comparison columns.
     */
    public function test_export_formatter_headers_and_rows_with_comparison()
    {
        $formatter = new ExportFormatter();
        $query     = $this->makeQuery(['visitors'], ['date'], true, 'export');

        $rows = [
            ['date' => '2024-11-01', 'visitors' => 100, 'previous' => ['visitors' => 80]],
        ];

        $result = $formatter->format($query, ['rows' => $rows, 'totals' => null, 'total' => 1]);

        $this->assertTrue($result['success']);
        $this->assertEquals(
            ['Date', 'Visitors', 'Visitors (Previous)', 'Change %'],
            $result['headers']
        );
        $this->assertSame([['2024-11-01', 100.0, 80.0, '+25%']], $result['rows']);
    }

    /**
     * Ensure export formatter omits comparison columns when compare is disabled.
     */
    public function test_export_formatter_without_comparison()
    {
        $formatter = new ExportFormatter();
        $query     = $this->makeQuery(['visitors'], ['date'], false, 'export');

        $rows = [
            ['date' => '2024-11-01', 'visitors' => 50],
        ];

        $result = $formatter->format($query, ['rows' => $rows, 'totals' => null, 'total' => 1]);

        $this->assertTrue($result['success']);
        $this->assertEquals(['Date', 'Visitors'], $result['headers']);
        $this->assertSame([['2024-11-01', 50.0]], $result['rows']);
    }
}
