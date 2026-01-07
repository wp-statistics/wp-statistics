<?php

namespace WP_Statistics\Tests\BackwardCompatibility;

use WP_UnitTestCase;
use WP_STATISTICS\Option;
use WP_STATISTICS\Helper;
use WP_STATISTICS\TimeZone;
use WP_STATISTICS\Menus;
use WP_Statistics\Components\DateTime;

/**
 * Tests backward compatibility for add-ons.
 *
 * These tests ensure that the public API used by add-ons remains functional.
 * Add-ons depend on:
 * - Legacy namespace classes (WP_STATISTICS\*)
 * - Public template functions (wp_statistics_*)
 * - The WP_Statistics() global function
 *
 * @since 15.0.0
 */
class Test_BackwardCompatibility extends WP_UnitTestCase
{
    /**
     * Test that the WP_Statistics() global function exists and returns expected object.
     */
    public function test_wpStatisticsGlobalFunctionExists()
    {
        $this->assertTrue(function_exists('WP_Statistics'), 'WP_Statistics() global function should exist');
    }

    /**
     * Test that legacy Option class exists in WP_STATISTICS namespace.
     */
    public function test_legacyOptionClassExists()
    {
        $this->assertTrue(class_exists('WP_STATISTICS\Option'), 'WP_STATISTICS\Option class should exist');
    }

    /**
     * Test Option::get method exists and returns values.
     */
    public function test_optionGetMethodExists()
    {
        $this->assertTrue(
            method_exists('WP_STATISTICS\Option', 'get'),
            'WP_STATISTICS\Option::get() method should exist'
        );
    }

    /**
     * Test Option::update method exists.
     */
    public function test_optionUpdateMethodExists()
    {
        $this->assertTrue(
            method_exists('WP_STATISTICS\Option', 'update'),
            'WP_STATISTICS\Option::update() method should exist'
        );
    }

    /**
     * Test Option::getByAddon method exists for add-on options.
     */
    public function test_optionGetByAddonMethodExists()
    {
        $this->assertTrue(
            method_exists('WP_STATISTICS\Option', 'getByAddon'),
            'WP_STATISTICS\Option::getByAddon() method should exist for add-ons'
        );
    }

    /**
     * Test Option::saveByAddon method exists for add-on options.
     */
    public function test_optionSaveByAddonMethodExists()
    {
        $this->assertTrue(
            method_exists('WP_STATISTICS\Option', 'saveByAddon'),
            'WP_STATISTICS\Option::saveByAddon() method should exist for add-ons'
        );
    }

    /**
     * Test legacy Helper class exists.
     */
    public function test_legacyHelperClassExists()
    {
        $this->assertTrue(class_exists('WP_STATISTICS\Helper'), 'WP_STATISTICS\Helper class should exist');
    }

    /**
     * Test Helper::formatNumberWithUnit method exists.
     */
    public function test_helperFormatNumberWithUnitMethodExists()
    {
        $this->assertTrue(
            method_exists('WP_STATISTICS\Helper', 'formatNumberWithUnit'),
            'WP_STATISTICS\Helper::formatNumberWithUnit() method should exist'
        );
    }

    /**
     * Test legacy TimeZone class exists.
     */
    public function test_legacyTimeZoneClassExists()
    {
        $this->assertTrue(class_exists('WP_STATISTICS\TimeZone'), 'WP_STATISTICS\TimeZone class should exist');
    }

    /**
     * Test TimeZone::getCurrentDate method exists.
     */
    public function test_timeZoneGetCurrentDateMethodExists()
    {
        $this->assertTrue(
            method_exists('WP_STATISTICS\TimeZone', 'getCurrentDate'),
            'WP_STATISTICS\TimeZone::getCurrentDate() method should exist'
        );
    }

    /**
     * Test legacy Menus class exists.
     */
    public function test_legacyMenusClassExists()
    {
        $this->assertTrue(class_exists('WP_STATISTICS\Menus'), 'WP_STATISTICS\Menus class should exist');
    }

    /**
     * Test Menus::admin_url method exists.
     */
    public function test_menusAdminUrlMethodExists()
    {
        $this->assertTrue(
            method_exists('WP_STATISTICS\Menus', 'admin_url'),
            'WP_STATISTICS\Menus::admin_url() method should exist'
        );
    }

    /**
     * Test wp_statistics_useronline() function exists.
     */
    public function test_wpStatisticsUseronlineFunctionExists()
    {
        $this->assertTrue(
            function_exists('wp_statistics_useronline'),
            'wp_statistics_useronline() function should exist'
        );
    }

    /**
     * Test wp_statistics_visit() function exists.
     */
    public function test_wpStatisticsVisitFunctionExists()
    {
        $this->assertTrue(
            function_exists('wp_statistics_visit'),
            'wp_statistics_visit() function should exist'
        );
    }

    /**
     * Test wp_statistics_visitor() function exists.
     */
    public function test_wpStatisticsVisitorFunctionExists()
    {
        $this->assertTrue(
            function_exists('wp_statistics_visitor'),
            'wp_statistics_visitor() function should exist'
        );
    }

    /**
     * Test wp_statistics_pages() function exists.
     */
    public function test_wpStatisticsPagesFunctionExists()
    {
        $this->assertTrue(
            function_exists('wp_statistics_pages'),
            'wp_statistics_pages() function should exist'
        );
    }

    /**
     * Test wp_statistics_get_top_pages() function exists.
     */
    public function test_wpStatisticsGetTopPagesFunctionExists()
    {
        $this->assertTrue(
            function_exists('wp_statistics_get_top_pages'),
            'wp_statistics_get_top_pages() function should exist'
        );
    }

    /**
     * Test wp_statistics_searchengine() function exists.
     */
    public function test_wpStatisticsSearchengineFunctionExists()
    {
        $this->assertTrue(
            function_exists('wp_statistics_searchengine'),
            'wp_statistics_searchengine() function should exist'
        );
    }

    /**
     * Test wp_statistics_referrer() function exists.
     */
    public function test_wpStatisticsReferrerFunctionExists()
    {
        $this->assertTrue(
            function_exists('wp_statistics_referrer'),
            'wp_statistics_referrer() function should exist'
        );
    }

    /**
     * Test wp_statistics_query() function exists.
     */
    public function test_wpStatisticsQueryFunctionExists()
    {
        $this->assertTrue(
            function_exists('wp_statistics_query'),
            'wp_statistics_query() function should exist'
        );
    }

    /**
     * Test wp_statistics_get_user_ip() function exists.
     */
    public function test_wpStatisticsGetUserIpFunctionExists()
    {
        $this->assertTrue(
            function_exists('wp_statistics_get_user_ip'),
            'wp_statistics_get_user_ip() function should exist'
        );
    }

    /**
     * Test wp_statistics_needs_consent() function exists.
     */
    public function test_wpStatisticsNeedsConsentFunctionExists()
    {
        $this->assertTrue(
            function_exists('wp_statistics_needs_consent'),
            'wp_statistics_needs_consent() function should exist'
        );
    }

    /**
     * Test that DateTime component has isValidDate method (replacement for TimeZone::isValidDate).
     */
    public function test_dateTimeHasIsValidDateMethod()
    {
        $this->assertTrue(
            method_exists('WP_Statistics\Components\DateTime', 'isValidDate'),
            'WP_Statistics\Components\DateTime::isValidDate() should exist as replacement for TimeZone::isValidDate()'
        );
    }

    /**
     * Test DateTime::isValidDate validates dates correctly.
     */
    public function test_dateTimeIsValidDateWorksCorrectly()
    {
        $this->assertTrue(DateTime::isValidDate('2024-01-15'), 'Valid date should return true');
        $this->assertTrue(DateTime::isValidDate('2024-12-31'), 'Valid date should return true');
        $this->assertFalse(DateTime::isValidDate('invalid'), 'Invalid date string should return false');
        $this->assertFalse(DateTime::isValidDate(''), 'Empty string should return false');
    }

    /**
     * Test v15 Option class exists in Components namespace.
     */
    public function test_v15OptionClassExists()
    {
        $this->assertTrue(
            class_exists('WP_Statistics\Components\Option'),
            'WP_Statistics\Components\Option class should exist'
        );
    }

    /**
     * Test v15 Option::getValue method exists.
     */
    public function test_v15OptionGetValueMethodExists()
    {
        $this->assertTrue(
            method_exists('WP_Statistics\Components\Option', 'getValue'),
            'WP_Statistics\Components\Option::getValue() should exist'
        );
    }

    /**
     * Test v15 Option::updateValue method exists.
     */
    public function test_v15OptionUpdateValueMethodExists()
    {
        $this->assertTrue(
            method_exists('WP_Statistics\Components\Option', 'updateValue'),
            'WP_Statistics\Components\Option::updateValue() should exist'
        );
    }

    /**
     * Test that constants are defined.
     */
    public function test_pluginConstantsDefined()
    {
        $this->assertTrue(defined('WP_STATISTICS_VERSION'), 'WP_STATISTICS_VERSION constant should be defined');
        $this->assertTrue(defined('WP_STATISTICS_DIR'), 'WP_STATISTICS_DIR constant should be defined');
        $this->assertTrue(defined('WP_STATISTICS_URL'), 'WP_STATISTICS_URL constant should be defined');
    }

    /**
     * Test Helper::getPostTypes method exists and returns array.
     */
    public function test_helperGetPostTypesMethodExists()
    {
        $this->assertTrue(
            method_exists('WP_STATISTICS\Helper', 'getPostTypes'),
            'WP_STATISTICS\Helper::getPostTypes() method should exist'
        );

        $result = Helper::getPostTypes();
        $this->assertIsArray($result, 'getPostTypes() should return an array');
    }

    /**
     * Test Option::getAddonOptions method works for add-ons.
     */
    public function test_optionGetAddonOptionsWorks()
    {
        $result = Option::getAddonOptions('test-addon');
        // Returns false when addon options don't exist, array when they do
        $this->assertTrue($result === false || is_array($result), 'getAddonOptions() should return an array or false');
    }

    /**
     * Test legacy Option::get returns default value when key doesn't exist.
     */
    public function test_legacyOptionGetReturnsDefault()
    {
        $result = Option::get('nonexistent_key_12345', 'default_value');
        $this->assertEquals('default_value', $result, 'Option::get() should return default value for missing key');
    }

    /**
     * Test legacy Option::update and Option::get work together.
     */
    public function test_legacyOptionUpdateAndGet()
    {
        // Update a test option
        Option::update('test_backward_compat_key', 'test_value_123');

        // Read it back
        $result = Option::get('test_backward_compat_key');
        $this->assertEquals('test_value_123', $result, 'Option::get() should return value set by Option::update()');

        // Clean up
        Option::update('test_backward_compat_key', null);
    }

    /**
     * Test legacy Option::saveByAddon and Option::getByAddon work together.
     */
    public function test_legacyOptionAddonMethodsWork()
    {
        $addonSlug = 'test-addon-compat';
        $testOptions = ['setting1' => 'value1', 'setting2' => true];

        // Save addon options
        Option::saveByAddon($testOptions, $addonSlug);

        // Read them back
        $result = Option::getByAddon('setting1', $addonSlug);
        $this->assertEquals('value1', $result, 'Option::getByAddon() should return value set by Option::saveByAddon()');

        $result2 = Option::getByAddon('setting2', $addonSlug);
        $this->assertTrue($result2, 'Option::getByAddon() should return boolean value correctly');

        // Clean up
        delete_option('wpstatistics_' . str_replace('-', '_', $addonSlug) . '_settings');
    }

    /**
     * Test v15 Option::getValue returns default value when key doesn't exist.
     */
    public function test_v15OptionGetValueReturnsDefault()
    {
        $result = \WP_Statistics\Components\Option::getValue('nonexistent_v15_key_12345', 'v15_default');
        $this->assertEquals('v15_default', $result, 'v15 Option::getValue() should return default value for missing key');
    }

    /**
     * Test v15 Option::updateValue and Option::getValue work together.
     */
    public function test_v15OptionUpdateAndGetValue()
    {
        // Update a test option
        \WP_Statistics\Components\Option::updateValue('test_v15_compat_key', 'v15_test_value');

        // Read it back
        $result = \WP_Statistics\Components\Option::getValue('test_v15_compat_key');
        $this->assertEquals('v15_test_value', $result, 'v15 Option::getValue() should return value set by Option::updateValue()');

        // Clean up
        \WP_Statistics\Components\Option::updateValue('test_v15_compat_key', null);
    }

    /**
     * Test Helper::formatNumberWithUnit formats numbers correctly.
     */
    public function test_helperFormatNumberWithUnitFormatsCorrectly()
    {
        $result = Helper::formatNumberWithUnit(1234567);
        $this->assertIsString($result, 'formatNumberWithUnit() should return a string');
        $this->assertNotEmpty($result, 'formatNumberWithUnit() should return non-empty string');
    }

    /**
     * Test TimeZone::getCurrentDate returns a valid datetime string.
     */
    public function test_timeZoneGetCurrentDateReturnsValidDate()
    {
        $result = TimeZone::getCurrentDate();
        $this->assertIsString($result, 'getCurrentDate() should return a string');
        // Default format is 'Y-m-d H:i:s' (datetime)
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}/', $result, 'getCurrentDate() should return date starting with Y-m-d format');
    }

    /**
     * Test TimeZone::getCurrentDate with custom format.
     */
    public function test_timeZoneGetCurrentDateWithFormat()
    {
        $result = TimeZone::getCurrentDate('Y/m/d');
        $this->assertMatchesRegularExpression('/^\d{4}\/\d{2}\/\d{2}$/', $result, 'getCurrentDate() should respect custom format');
    }

    /**
     * Test Menus::admin_url generates valid admin URL.
     */
    public function test_menusAdminUrlGeneratesValidUrl()
    {
        $result = Menus::admin_url('overview');
        $this->assertIsString($result, 'admin_url() should return a string');
        $this->assertStringContainsString('admin.php', $result, 'admin_url() should contain admin.php');
        $this->assertStringContainsString('page=', $result, 'admin_url() should contain page parameter');
    }

    /**
     * Test wp_statistics_useronline returns numeric value.
     */
    public function test_wpStatisticsUseronlineReturnsNumeric()
    {
        $result = wp_statistics_useronline();
        $this->assertTrue(is_numeric($result) || $result === 0, 'wp_statistics_useronline() should return a numeric value');
    }

    /**
     * Test wp_statistics_visit returns numeric value.
     */
    public function test_wpStatisticsVisitReturnsNumeric()
    {
        $result = wp_statistics_visit('today');
        $this->assertTrue(is_numeric($result) || $result === 0, 'wp_statistics_visit() should return a numeric value');
    }

    /**
     * Test wp_statistics_visitor returns numeric value.
     */
    public function test_wpStatisticsVisitorReturnsNumeric()
    {
        $result = wp_statistics_visitor('today');
        $this->assertTrue(is_numeric($result) || $result === 0, 'wp_statistics_visitor() should return a numeric value');
    }

    /**
     * Test wp_statistics_pages returns numeric value.
     */
    public function test_wpStatisticsPagesReturnsNumeric()
    {
        $result = wp_statistics_pages('today', '/');
        $this->assertTrue(is_numeric($result) || $result === 0, 'wp_statistics_pages() should return a numeric value');
    }

    /**
     * Test wp_statistics_get_user_ip returns valid IP or hash.
     */
    public function test_wpStatisticsGetUserIpReturnsValue()
    {
        $result = wp_statistics_get_user_ip();
        $this->assertIsString($result, 'wp_statistics_get_user_ip() should return a string');
    }

    /**
     * Test wp_statistics_needs_consent returns boolean.
     */
    public function test_wpStatisticsNeedsConsentReturnsBoolean()
    {
        $result = wp_statistics_needs_consent();
        $this->assertIsBool($result, 'wp_statistics_needs_consent() should return a boolean');
    }

    /**
     * Test legacy and v15 Option classes return same values.
     */
    public function test_legacyAndV15OptionClassesAreConsistent()
    {
        // Set a value using legacy class
        Option::update('test_consistency_key', 'consistency_value');

        // Read using v15 class (Components namespace)
        $v15Result = \WP_Statistics\Components\Option::getValue('test_consistency_key');
        $this->assertEquals('consistency_value', $v15Result, 'v15 Option should read values set by legacy Option');

        // Set a value using v15 class
        \WP_Statistics\Components\Option::updateValue('test_consistency_key_2', 'v15_set_value');

        // Read using legacy class
        $legacyResult = Option::get('test_consistency_key_2');
        $this->assertEquals('v15_set_value', $legacyResult, 'Legacy Option should read values set by v15 Option');

        // Clean up
        Option::update('test_consistency_key', null);
        Option::update('test_consistency_key_2', null);
    }
}
