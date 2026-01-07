<?php

use WP_Statistics\Components\DateTime;

/**
 * Tests for the DateTime component.
 *
 * @package WP_Statistics
 * @since 15.0.0
 */
class Test_DateTime extends WP_UnitTestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Test isValidDate with valid dates
     */
    public function test_isValidDate_with_valid_dates()
    {
        $this->assertTrue(DateTime::isValidDate('2024-01-01'));
        $this->assertTrue(DateTime::isValidDate('2024-12-31'));
        $this->assertTrue(DateTime::isValidDate('2023-06-15'));
    }

    /**
     * Test isValidDate with invalid dates
     */
    public function test_isValidDate_with_invalid_dates()
    {
        $this->assertFalse(DateTime::isValidDate(''));
        $this->assertFalse(DateTime::isValidDate('invalid'));
        $this->assertFalse(DateTime::isValidDate('2024-13-01')); // Invalid month
        $this->assertFalse(DateTime::isValidDate('2024-01-32')); // Invalid day
        $this->assertFalse(DateTime::isValidDate('01-01-2024')); // Wrong format
    }

    /**
     * Test getTimeAgo returns correct date
     */
    public function test_getTimeAgo_returns_correct_date()
    {
        // Test 1 day ago
        $oneDayAgo = DateTime::getTimeAgo(1);
        $expected = date('Y-m-d', strtotime('-1 day'));
        $this->assertEquals($expected, $oneDayAgo);

        // Test 0 days ago (today)
        $today = DateTime::getTimeAgo(0);
        $this->assertEquals(date('Y-m-d'), $today);

        // Test 7 days ago
        $weekAgo = DateTime::getTimeAgo(7);
        $expected = date('Y-m-d', strtotime('-7 days'));
        $this->assertEquals($expected, $weekAgo);
    }

    /**
     * Test getTimeAgo with custom format
     */
    public function test_getTimeAgo_with_custom_format()
    {
        $oneDayAgo = DateTime::getTimeAgo(1, 'Y-m-d H:i:s');
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $oneDayAgo);
    }

    /**
     * Test getNumberDayBetween with two dates
     */
    public function test_getNumberDayBetween_with_two_dates()
    {
        $days = DateTime::getNumberDayBetween('2024-01-01', '2024-01-10');
        $this->assertEquals(9, $days);

        $days = DateTime::getNumberDayBetween('2024-01-01', '2024-01-01');
        $this->assertEquals(0, $days);

        $days = DateTime::getNumberDayBetween('2024-01-01', '2024-02-01');
        $this->assertEquals(31, $days);
    }

    /**
     * Test getNumberDayBetween with only from date (uses current timestamp)
     */
    public function test_getNumberDayBetween_with_only_from_date()
    {
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $days = DateTime::getNumberDayBetween($yesterday);
        $this->assertGreaterThanOrEqual(1, $days);
    }

    /**
     * Test getListDays returns correct array structure
     */
    public function test_getListDays_returns_correct_structure()
    {
        $list = DateTime::getListDays([
            'from' => '2024-01-01',
            'to' => '2024-01-05'
        ]);

        $this->assertIsArray($list);
        $this->assertCount(5, $list);
        $this->assertArrayHasKey('2024-01-01', $list);
        $this->assertArrayHasKey('2024-01-05', $list);

        // Check structure of each day
        foreach ($list as $date => $info) {
            $this->assertArrayHasKey('timestamp', $info);
            $this->assertArrayHasKey('format', $info);
            $this->assertIsNumeric($info['timestamp']);
        }
    }

    /**
     * Test getListDays with custom format
     */
    public function test_getListDays_with_custom_format()
    {
        $list = DateTime::getListDays([
            'from' => '2024-01-01',
            'to' => '2024-01-03',
            'format' => 'M d'
        ]);

        $this->assertEquals('Jan 01', $list['2024-01-01']['format']);
        $this->assertEquals('Jan 02', $list['2024-01-02']['format']);
        $this->assertEquals('Jan 03', $list['2024-01-03']['format']);
    }

    /**
     * Test getUtcOffset returns integer
     */
    public function test_getUtcOffset_returns_integer()
    {
        $offset = DateTime::getUtcOffset();
        $this->assertIsInt($offset);
    }

    /**
     * Test getCurrentTimestamp returns numeric value
     */
    public function test_getCurrentTimestamp_returns_numeric()
    {
        $timestamp = DateTime::getCurrentTimestamp();
        $this->assertIsNumeric($timestamp);
        $this->assertGreaterThan(0, $timestamp);
    }

    /**
     * Test get method with default format
     */
    public function test_get_with_default_format()
    {
        $date = DateTime::get('now');
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $date);
    }

    /**
     * Test get method with custom format
     */
    public function test_get_with_custom_format()
    {
        $date = DateTime::get('now', 'Y-m-d H:i:s');
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $date);
    }

    /**
     * Test getCountryFromTimezone with valid timezone
     */
    public function test_getCountryFromTimezone_with_valid_timezone()
    {
        $country = DateTime::getCountryFromTimezone('Europe/London');
        $this->assertEquals('GB', $country);

        $country = DateTime::getCountryFromTimezone('America/New_York');
        $this->assertEquals('US', $country);

        $country = DateTime::getCountryFromTimezone('Asia/Tokyo');
        $this->assertEquals('JP', $country);
    }

    /**
     * Test getCountryFromTimezone with invalid timezone
     */
    public function test_getCountryFromTimezone_with_invalid_timezone()
    {
        $country = DateTime::getCountryFromTimezone('Invalid/Timezone');
        $this->assertFalse($country);
    }

    /**
     * Test getElapsedTime returns "Now" for very recent time
     */
    public function test_getElapsedTime_returns_now_for_recent()
    {
        $currentDate = new \DateTime();
        $visitDate = clone $currentDate;
        // Use 25 seconds - rounds down to 0 minutes (< 1 min threshold)
        // Note: 30 seconds rounds to 1 minute due to round() in implementation
        $visitDate->modify('-25 seconds');

        $result = DateTime::getElapsedTime($currentDate, $visitDate, 'Jan 01');
        $this->assertEquals('Now', $result);
    }

    /**
     * Test getElapsedTime returns minutes ago
     */
    public function test_getElapsedTime_returns_minutes_ago()
    {
        $currentDate = new \DateTime();
        $visitDate = clone $currentDate;
        $visitDate->modify('-5 minutes');

        $result = DateTime::getElapsedTime($currentDate, $visitDate, 'Jan 01');
        $this->assertStringContainsString('5', $result);
        $this->assertStringContainsString('minute', $result);
    }

    /**
     * Test getElapsedTime returns hours ago
     */
    public function test_getElapsedTime_returns_hours_ago()
    {
        $currentDate = new \DateTime();
        $visitDate = clone $currentDate;
        $visitDate->modify('-2 hours');

        $result = DateTime::getElapsedTime($currentDate, $visitDate, 'Jan 01');
        $this->assertStringContainsString('2', $result);
        $this->assertStringContainsString('hour', $result);
    }

    /**
     * Test getElapsedTime returns original date for old visits
     */
    public function test_getElapsedTime_returns_original_date_for_old()
    {
        $currentDate = new \DateTime();
        $visitDate = clone $currentDate;
        $visitDate->modify('-2 days');

        $result = DateTime::getElapsedTime($currentDate, $visitDate, 'Jan 01, 2024');
        $this->assertEquals('Jan 01, 2024', $result);
    }

    /**
     * Test isTodayOrFutureDate with valid dates
     */
    public function test_isTodayOrFutureDate()
    {
        $today = date('Y-m-d');
        $this->assertTrue(DateTime::isTodayOrFutureDate($today));

        $tomorrow = date('Y-m-d', strtotime('+1 day'));
        $this->assertTrue(DateTime::isTodayOrFutureDate($tomorrow));

        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $this->assertFalse(DateTime::isTodayOrFutureDate($yesterday));
    }

    /**
     * Test format method with various options
     */
    public function test_format_with_options()
    {
        $date = '2024-06-15 14:30:00';

        // Test include_time
        $formatted = DateTime::format($date, ['include_time' => true]);
        $this->assertNotEmpty($formatted);

        // Test with timestamp
        $timestamp = strtotime($date);
        $formatted = DateTime::format($timestamp);
        $this->assertNotEmpty($formatted);
    }

    /**
     * Test subtract method
     */
    public function test_subtract()
    {
        $result = DateTime::subtract('2024-01-10', 5);
        $this->assertEquals('2024-01-05', $result);

        $result = DateTime::subtract('2024-01-10', 10);
        $this->assertEquals('2023-12-31', $result);
    }
}
