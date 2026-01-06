<?php

use WP_STATISTICS\User as LegacyUser;
use WP_STATISTICS\TimeZone as LegacyTimeZone;
use WP_Statistics\Utils\User;
use WP_Statistics\Components\DateTime;

/**
 * Tests for legacy class delegation to new classes.
 *
 * These tests ensure backward compatibility by verifying that legacy methods
 * properly delegate to the new architecture.
 *
 * @package WP_Statistics
 * @since 15.0.0
 */
class Test_LegacyDelegation extends WP_UnitTestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    // ========================================
    // Legacy User class delegation tests
    // ========================================

    /**
     * Test legacy User::is_login() delegates to new User::isLoggedIn()
     */
    public function test_legacy_user_isLoggedIn_delegation()
    {
        // Both should return the same value
        $legacyResult = LegacyUser::is_login();
        $newResult = User::isLoggedIn();

        $this->assertEquals($newResult, $legacyResult);
    }

    /**
     * Test legacy User::get_user_id() delegates to new User::getId()
     */
    public function test_legacy_user_get_user_id_delegation()
    {
        $legacyResult = LegacyUser::get_user_id();
        $newResult = User::getId();

        $this->assertEquals($newResult, $legacyResult);
    }

    /**
     * Test legacy User::exists() delegates to new User::exists()
     */
    public function test_legacy_user_exist_delegation()
    {
        // Test with non-existent user ID
        $legacyResult = LegacyUser::exists(999999);
        $newResult = User::exists(999999);

        $this->assertEquals($newResult, $legacyResult);
        $this->assertFalse($legacyResult);
    }

    /**
     * Test legacy User::get_role_list() delegates to new User::getRoles()
     */
    public function test_legacy_user_getRoles_delegation()
    {
        $legacyResult = LegacyUser::get_role_list();
        $newResult = User::getRoles();

        $this->assertEquals($newResult, $legacyResult);
    }

    /**
     * Test legacy User::get() returns expected structure
     */
    public function test_legacy_user_get_structure()
    {
        $legacyResult = LegacyUser::get();

        // When no user is logged in, should return empty or array without role/cap keys
        if (empty($legacyResult)) {
            $this->assertEmpty($legacyResult);
            return;
        }

        // When user is logged in, both legacy keys should exist for backward compatibility
        $this->assertArrayHasKey('role', $legacyResult);
        $this->assertArrayHasKey('roles', $legacyResult);
        $this->assertArrayHasKey('cap', $legacyResult);
        $this->assertArrayHasKey('caps', $legacyResult);

        // Both should have the same value (aliases)
        $this->assertEquals($legacyResult['role'], $legacyResult['roles']);
        $this->assertEquals($legacyResult['cap'], $legacyResult['caps']);
    }

    // ========================================
    // Legacy TimeZone class delegation tests
    // ========================================

    /**
     * Test legacy TimeZone::getCurrentTimestamp() delegates to DateTime
     */
    public function test_legacy_timezone_getCurrentTimestamp_delegation()
    {
        $legacyResult = LegacyTimeZone::getCurrentTimestamp();
        $newResult = DateTime::getCurrentTimestamp();

        // Results should be equal or very close (within 1 second)
        $this->assertEqualsWithDelta($newResult, $legacyResult, 1);
    }

    /**
     * Test legacy TimeZone::set_timezone() delegates to DateTime::getUtcOffset()
     */
    public function test_legacy_timezone_set_timezone_delegation()
    {
        $legacyResult = LegacyTimeZone::set_timezone();
        $newResult = DateTime::getUtcOffset();

        $this->assertEquals($newResult, $legacyResult);
    }

    /**
     * Test legacy TimeZone::isValidDate() delegates to DateTime::isValidDate()
     */
    public function test_legacy_timezone_isValidDate_delegation()
    {
        // Valid dates
        $this->assertEquals(
            DateTime::isValidDate('2024-01-01'),
            LegacyTimeZone::isValidDate('2024-01-01')
        );

        // Invalid dates
        $this->assertEquals(
            DateTime::isValidDate('invalid'),
            LegacyTimeZone::isValidDate('invalid')
        );

        $this->assertEquals(
            DateTime::isValidDate(''),
            LegacyTimeZone::isValidDate('')
        );
    }

    /**
     * Test legacy TimeZone::getTimeAgo() delegates to DateTime::getTimeAgo()
     */
    public function test_legacy_timezone_getTimeAgo_delegation()
    {
        $legacyResult = LegacyTimeZone::getTimeAgo(1);
        $newResult = DateTime::getTimeAgo(1);

        $this->assertEquals($newResult, $legacyResult);

        // Test with custom format
        $legacyResult = LegacyTimeZone::getTimeAgo(7, 'Y-m-d H:i:s');
        $newResult = DateTime::getTimeAgo(7, 'Y-m-d H:i:s');

        $this->assertEquals($newResult, $legacyResult);
    }

    /**
     * Test legacy TimeZone::getNumberDayBetween() delegates to DateTime
     */
    public function test_legacy_timezone_getNumberDayBetween_delegation()
    {
        $legacyResult = LegacyTimeZone::getNumberDayBetween('2024-01-01', '2024-01-10');
        $newResult = DateTime::getNumberDayBetween('2024-01-01', '2024-01-10');

        $this->assertEquals($newResult, $legacyResult);
    }

    /**
     * Test legacy TimeZone::getListDays() delegates to DateTime::getListDays()
     */
    public function test_legacy_timezone_getListDays_delegation()
    {
        $args = [
            'from' => '2024-01-01',
            'to' => '2024-01-05',
            'format' => 'j M'
        ];

        $legacyResult = LegacyTimeZone::getListDays($args);
        $newResult = DateTime::getListDays($args);

        $this->assertEquals($newResult, $legacyResult);
    }

    /**
     * Test legacy TimeZone::getCountry() delegates to DateTime::getCountryFromTimezone()
     */
    public function test_legacy_timezone_getCountry_delegation()
    {
        // Valid timezone
        $legacyResult = LegacyTimeZone::getCountry('Europe/London');
        $newResult = DateTime::getCountryFromTimezone('Europe/London');

        $this->assertEquals($newResult, $legacyResult);

        // Invalid timezone
        $legacyResult = LegacyTimeZone::getCountry('Invalid/Timezone');
        $newResult = DateTime::getCountryFromTimezone('Invalid/Timezone');

        $this->assertEquals($newResult, $legacyResult);
    }

    /**
     * Test legacy TimeZone::getElapsedTime() delegates to DateTime::getElapsedTime()
     */
    public function test_legacy_timezone_getElapsedTime_delegation()
    {
        $currentDate = new \DateTime();
        $visitDate = clone $currentDate;
        $visitDate->modify('-5 minutes');
        $originalDate = 'Jan 01, 2024';

        $legacyResult = LegacyTimeZone::getElapsedTime($currentDate, $visitDate, $originalDate);
        $newResult = DateTime::getElapsedTime($currentDate, $visitDate, $originalDate);

        $this->assertEquals($newResult, $legacyResult);
    }

    /**
     * Test legacy TimeZone::getDateFilters() returns valid structure
     */
    public function test_legacy_timezone_getDateFilters_structure()
    {
        $filters = LegacyTimeZone::getDateFilters();

        $this->assertIsArray($filters);
        $this->assertArrayHasKey('today', $filters);
        $this->assertArrayHasKey('yesterday', $filters);
        $this->assertArrayHasKey('7days', $filters);
        $this->assertArrayHasKey('30days', $filters);
        $this->assertArrayHasKey('this_month', $filters);
        $this->assertArrayHasKey('last_month', $filters);
        $this->assertArrayHasKey('this_year', $filters);

        // Each filter should have from and to keys
        foreach ($filters as $name => $filter) {
            $this->assertArrayHasKey('from', $filter, "Filter '{$name}' missing 'from' key");
            $this->assertArrayHasKey('to', $filter, "Filter '{$name}' missing 'to' key");
        }
    }

    /**
     * Test legacy TimeZone::calculateDateFilter() returns valid structure
     */
    public function test_legacy_timezone_calculateDateFilter()
    {
        // Test with valid filter
        $filter = LegacyTimeZone::calculateDateFilter('30days');
        $this->assertArrayHasKey('from', $filter);
        $this->assertArrayHasKey('to', $filter);

        // Test with invalid filter (should return 30days default)
        $filter = LegacyTimeZone::calculateDateFilter('invalid');
        $this->assertArrayHasKey('from', $filter);
        $this->assertArrayHasKey('to', $filter);
    }
}
