<?php

namespace WP_Statistics\Tests;

use WP_Statistics\Service\Admin\ReactApp\Abstracts\AbstractAnalyticsPage;
use WP_Statistics\Components\DateRange;
use WP_UnitTestCase;

/**
 * Concrete test class to access protected methods of AbstractAnalyticsPage.
 */
class TestAnalyticsPageImpl extends AbstractAnalyticsPage
{
    public function getEndpointName()
    {
        return 'test_analytics';
    }

    public function handle()
    {
        return $this->executeQueryFromRequest();
    }

    /**
     * Expose protected resolvePeriodDates method for testing.
     *
     * @param array $data Request data.
     * @return array Resolved data with dates.
     */
    public function testResolvePeriodDates(array $data): array
    {
        return $this->resolvePeriodDates($data);
    }
}

/**
 * Test class for AbstractAnalyticsPage period resolution functionality.
 *
 * Tests that period identifiers (like 'last_month', '30days') are correctly
 * resolved to actual date ranges at query time, ensuring dates are always
 * current relative to today rather than stored as hardcoded values.
 *
 * @since 15.0.0
 */
class Test_AbstractAnalyticsPagePeriodResolution extends WP_UnitTestCase
{
    /**
     * Test analytics page instance.
     *
     * @var TestAnalyticsPageImpl
     */
    private $page;

    /**
     * Set up test environment.
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->page = new TestAnalyticsPageImpl();
    }

    /**
     * Test that 'last_month' period resolves to correct dates.
     */
    public function test_last_month_period_resolves_correctly()
    {
        $input = [
            'period'  => 'last_month',
            'sources' => ['visitors'],
        ];

        $result = $this->page->testResolvePeriodDates($input);

        // Get expected dates from DateRange
        $expected = DateRange::get('last_month');

        $this->assertEquals($expected['from'], $result['date_from'], 'date_from should match last_month start');
        $this->assertEquals($expected['to'], $result['date_to'], 'date_to should match last_month end');
    }

    /**
     * Test that previous period dates are resolved for comparison.
     */
    public function test_previous_period_dates_are_resolved()
    {
        $input = [
            'period'  => 'last_month',
            'sources' => ['visitors'],
        ];

        $result = $this->page->testResolvePeriodDates($input);

        // Get expected previous dates from DateRange
        $expectedPrev = DateRange::getPrevPeriod('last_month');

        $this->assertArrayHasKey('previous_date_from', $result, 'Should include previous_date_from');
        $this->assertArrayHasKey('previous_date_to', $result, 'Should include previous_date_to');
        $this->assertEquals($expectedPrev['from'], $result['previous_date_from'], 'previous_date_from should match');
        $this->assertEquals($expectedPrev['to'], $result['previous_date_to'], 'previous_date_to should match');
    }

    /**
     * Test that 'custom' period preserves provided dates.
     */
    public function test_custom_period_preserves_dates()
    {
        $input = [
            'period'    => 'custom',
            'date_from' => '2025-06-01',
            'date_to'   => '2025-06-30',
            'sources'   => ['visitors'],
        ];

        $result = $this->page->testResolvePeriodDates($input);

        $this->assertEquals('2025-06-01', $result['date_from'], 'date_from should be preserved');
        $this->assertEquals('2025-06-30', $result['date_to'], 'date_to should be preserved');
    }

    /**
     * Test that missing period passes through data unchanged.
     */
    public function test_no_period_passes_through_unchanged()
    {
        $input = [
            'date_from' => '2025-07-01',
            'date_to'   => '2025-07-31',
            'sources'   => ['visitors'],
        ];

        $result = $this->page->testResolvePeriodDates($input);

        $this->assertEquals('2025-07-01', $result['date_from'], 'date_from should be unchanged');
        $this->assertEquals('2025-07-31', $result['date_to'], 'date_to should be unchanged');
    }

    /**
     * Test that invalid period passes through data unchanged.
     */
    public function test_invalid_period_passes_through_unchanged()
    {
        $input = [
            'period'    => 'invalid_period_name',
            'date_from' => '2025-08-01',
            'date_to'   => '2025-08-31',
            'sources'   => ['visitors'],
        ];

        $result = $this->page->testResolvePeriodDates($input);

        $this->assertEquals('2025-08-01', $result['date_from'], 'date_from should be unchanged for invalid period');
        $this->assertEquals('2025-08-31', $result['date_to'], 'date_to should be unchanged for invalid period');
    }

    /**
     * Test that '30days' period resolves correctly.
     */
    public function test_30days_period_resolves_correctly()
    {
        $input = [
            'period'  => '30days',
            'sources' => ['visitors'],
        ];

        $result = $this->page->testResolvePeriodDates($input);

        $expected = DateRange::get('30days');

        $this->assertEquals($expected['from'], $result['date_from'], 'date_from should match 30days start');
        $this->assertEquals($expected['to'], $result['date_to'], 'date_to should match 30days end');
    }

    /**
     * Test that '7days' period resolves correctly.
     */
    public function test_7days_period_resolves_correctly()
    {
        $input = [
            'period'  => '7days',
            'sources' => ['visitors'],
        ];

        $result = $this->page->testResolvePeriodDates($input);

        $expected = DateRange::get('7days');

        $this->assertEquals($expected['from'], $result['date_from'], 'date_from should match 7days start');
        $this->assertEquals($expected['to'], $result['date_to'], 'date_to should match 7days end');
    }

    /**
     * Test that 'today' period resolves correctly.
     */
    public function test_today_period_resolves_correctly()
    {
        $input = [
            'period'  => 'today',
            'sources' => ['visitors'],
        ];

        $result = $this->page->testResolvePeriodDates($input);

        $expected = DateRange::get('today');
        $today = date('Y-m-d');

        $this->assertEquals($expected['from'], $result['date_from'], 'date_from should match today');
        $this->assertEquals($expected['to'], $result['date_to'], 'date_to should match today');
        $this->assertEquals($today, $result['date_from'], 'date_from should be today\'s date');
    }

    /**
     * Test that 'yesterday' period resolves correctly.
     */
    public function test_yesterday_period_resolves_correctly()
    {
        $input = [
            'period'  => 'yesterday',
            'sources' => ['visitors'],
        ];

        $result = $this->page->testResolvePeriodDates($input);

        $expected = DateRange::get('yesterday');
        $yesterday = date('Y-m-d', strtotime('-1 day'));

        $this->assertEquals($expected['from'], $result['date_from'], 'date_from should match yesterday');
        $this->assertEquals($expected['to'], $result['date_to'], 'date_to should match yesterday');
        $this->assertEquals($yesterday, $result['date_from'], 'date_from should be yesterday\'s date');
    }

    /**
     * Test that 'this_month' period resolves correctly.
     */
    public function test_this_month_period_resolves_correctly()
    {
        $input = [
            'period'  => 'this_month',
            'sources' => ['visitors'],
        ];

        $result = $this->page->testResolvePeriodDates($input);

        $expected = DateRange::get('this_month');

        $this->assertEquals($expected['from'], $result['date_from'], 'date_from should match this_month start');
        $this->assertEquals($expected['to'], $result['date_to'], 'date_to should match this_month end');
    }

    /**
     * Test that 'this_year' period resolves correctly.
     */
    public function test_this_year_period_resolves_correctly()
    {
        $input = [
            'period'  => 'this_year',
            'sources' => ['visitors'],
        ];

        $result = $this->page->testResolvePeriodDates($input);

        $expected = DateRange::get('this_year');
        $currentYear = date('Y');

        $this->assertEquals($expected['from'], $result['date_from'], 'date_from should match this_year start');
        $this->assertEquals($expected['to'], $result['date_to'], 'date_to should match this_year end');
        $this->assertStringStartsWith($currentYear, $result['date_from'], 'date_from should start with current year');
    }

    /**
     * Test that empty period string passes through data unchanged.
     */
    public function test_empty_period_passes_through_unchanged()
    {
        $input = [
            'period'    => '',
            'date_from' => '2025-09-01',
            'date_to'   => '2025-09-30',
            'sources'   => ['visitors'],
        ];

        $result = $this->page->testResolvePeriodDates($input);

        $this->assertEquals('2025-09-01', $result['date_from'], 'date_from should be unchanged');
        $this->assertEquals('2025-09-30', $result['date_to'], 'date_to should be unchanged');
    }

    /**
     * Test that period resolution handles all predefined periods.
     */
    public function test_all_predefined_periods_are_resolvable()
    {
        $periods = DateRange::getPeriods();

        foreach (array_keys($periods) as $periodKey) {
            $input = [
                'period'  => $periodKey,
                'sources' => ['visitors'],
            ];

            $result = $this->page->testResolvePeriodDates($input);

            $this->assertArrayHasKey('date_from', $result, "Period '$periodKey' should resolve date_from");
            $this->assertArrayHasKey('date_to', $result, "Period '$periodKey' should resolve date_to");
            $this->assertNotEmpty($result['date_from'], "Period '$periodKey' date_from should not be empty");
            $this->assertNotEmpty($result['date_to'], "Period '$periodKey' date_to should not be empty");
        }
    }

    /**
     * Test that resolved dates are in correct format.
     */
    public function test_resolved_dates_are_in_correct_format()
    {
        $input = [
            'period'  => '30days',
            'sources' => ['visitors'],
        ];

        $result = $this->page->testResolvePeriodDates($input);

        // Dates should be in Y-m-d format
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $result['date_from'], 'date_from should be in Y-m-d format');
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $result['date_to'], 'date_to should be in Y-m-d format');
    }

    /**
     * Test that date_from is before or equal to date_to.
     */
    public function test_date_from_is_before_date_to()
    {
        $periods = ['7days', '30days', 'this_month', 'last_month', 'this_year'];

        foreach ($periods as $period) {
            $input = [
                'period'  => $period,
                'sources' => ['visitors'],
            ];

            $result = $this->page->testResolvePeriodDates($input);

            $this->assertLessThanOrEqual(
                strtotime($result['date_to']),
                strtotime($result['date_from']),
                "Period '$period': date_from should be before or equal to date_to"
            );
        }
    }

    /**
     * Test that period XSS attempts are sanitized.
     */
    public function test_period_is_sanitized()
    {
        $input = [
            'period'    => '<script>alert("xss")</script>',
            'date_from' => '2025-10-01',
            'date_to'   => '2025-10-31',
            'sources'   => ['visitors'],
        ];

        $result = $this->page->testResolvePeriodDates($input);

        // Since it's invalid, dates should be unchanged
        $this->assertEquals('2025-10-01', $result['date_from'], 'date_from should be unchanged for sanitized invalid period');
        $this->assertEquals('2025-10-31', $result['date_to'], 'date_to should be unchanged for sanitized invalid period');
    }

    /**
     * Test that other data properties are preserved after resolution.
     */
    public function test_other_properties_are_preserved()
    {
        $input = [
            'period'   => '30days',
            'sources'  => ['visitors', 'views'],
            'group_by' => ['date'],
            'filters'  => ['country' => 'US'],
            'compare'  => true,
            'format'   => 'chart',
        ];

        $result = $this->page->testResolvePeriodDates($input);

        $this->assertEquals(['visitors', 'views'], $result['sources'], 'sources should be preserved');
        $this->assertEquals(['date'], $result['group_by'], 'group_by should be preserved');
        $this->assertEquals(['country' => 'US'], $result['filters'], 'filters should be preserved');
        $this->assertTrue($result['compare'], 'compare should be preserved');
        $this->assertEquals('chart', $result['format'], 'format should be preserved');
    }

    /**
     * Test that period overrides existing date_from/date_to.
     */
    public function test_period_overrides_existing_dates()
    {
        $input = [
            'period'    => '7days',
            'date_from' => '2020-01-01', // Old date that should be overwritten
            'date_to'   => '2020-01-31', // Old date that should be overwritten
            'sources'   => ['visitors'],
        ];

        $result = $this->page->testResolvePeriodDates($input);

        $expected = DateRange::get('7days');

        // Period should override the old dates
        $this->assertEquals($expected['from'], $result['date_from'], 'date_from should be overridden by period');
        $this->assertEquals($expected['to'], $result['date_to'], 'date_to should be overridden by period');
        $this->assertNotEquals('2020-01-01', $result['date_from'], 'Old date_from should not be preserved');
    }

    /**
     * Test getActionName returns correct format.
     */
    public function test_get_action_name_format()
    {
        $actionName = TestAnalyticsPageImpl::getActionName();

        $this->assertStringStartsWith('wp_statistics_', $actionName, 'Action name should start with wp_statistics_');
        $this->assertEquals('wp_statistics_test_analytics', $actionName, 'Action name should be wp_statistics_test_analytics');
    }
}
