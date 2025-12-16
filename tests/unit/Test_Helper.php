<?php

namespace WP_Statistics\Tests;

use WP_STATISTICS\Helper;
use WP_UnitTestCase;

class Test_Helper extends WP_UnitTestCase
{
    /**
     * Test that makeAnonymousVersion works with a standard version number.
     */
    public function test_make_anonymous_version_standard()
    {
        $version  = '106.2.124.0';
        $expected = '106.0.0.0';

        $this->assertEquals($expected, Helper::makeAnonymousVersion($version), 'Failed to anonymize version with standard format.');
    }

    /**
     * Test that makeAnonymousVersion works with a version number with fewer sections.
     */
    public function test_make_anonymous_version_fewer_sections()
    {
        $version  = '106.2';
        $expected = '106.0';

        $this->assertEquals($expected, Helper::makeAnonymousVersion($version), 'Failed to anonymize version with fewer sections.');
    }

    /**
     * Test calculatePercentageChange returns null when first number is zero.
     */
    public function test_calculate_percentage_change_first_number_zero()
    {
        $result = Helper::calculatePercentageChange(0, 100);

        $this->assertNull($result, 'Should return null when first number is zero.');
    }

    /**
     * Test calculatePercentageChange returns 0 when both numbers are equal.
     */
    public function test_calculate_percentage_change_equal_numbers()
    {
        $result = Helper::calculatePercentageChange(100, 100);

        $this->assertEquals(0, $result, 'Should return 0 when both numbers are equal.');
    }

    /**
     * Test calculatePercentageChange with an increasing change.
     */
    public function test_calculate_percentage_change_increase()
    {
        // From 100 to 150 is a 50% increase
        $result = Helper::calculatePercentageChange(100, 150);

        $this->assertEquals(50.0, $result, 'Should return positive percentage for increase.');
    }

    /**
     * Test calculatePercentageChange with a decreasing change.
     */
    public function test_calculate_percentage_change_decrease()
    {
        // From 100 to 75 is a 25% decrease
        $result = Helper::calculatePercentageChange(100, 75);

        $this->assertEquals(-25.0, $result, 'Should return negative percentage for decrease.');
    }

    /**
     * Test calculatePercentageChange with 100% increase (doubling).
     */
    public function test_calculate_percentage_change_double()
    {
        // From 50 to 100 is a 100% increase
        $result = Helper::calculatePercentageChange(50, 100);

        $this->assertEquals(100.0, $result, 'Should return 100% for doubling.');
    }

    /**
     * Test calculatePercentageChange with 50% decrease (halving).
     */
    public function test_calculate_percentage_change_half()
    {
        // From 100 to 50 is a 50% decrease
        $result = Helper::calculatePercentageChange(100, 50);

        $this->assertEquals(-50.0, $result, 'Should return -50% for halving.');
    }

    /**
     * Test calculatePercentageChange with custom decimal places.
     */
    public function test_calculate_percentage_change_custom_decimals()
    {
        // From 3 to 10 is a 233.333...% increase
        $result = Helper::calculatePercentageChange(3, 10, 4);

        $this->assertEquals(233.3333, $result, 'Should round to specified decimal places.');
    }

    /**
     * Test calculatePercentageChange with abs parameter set to true.
     */
    public function test_calculate_percentage_change_abs_true()
    {
        // From 100 to 75 is a -25% change, but abs should make it 25
        $result = Helper::calculatePercentageChange(100, 75, 2, true);

        $this->assertEquals(25.0, $result, 'Should return absolute value when abs is true.');
    }

    /**
     * Test calculatePercentageChange with abs parameter on positive result.
     */
    public function test_calculate_percentage_change_abs_true_positive()
    {
        // From 100 to 150 is 50%, abs should still be 50
        $result = Helper::calculatePercentageChange(100, 150, 2, true);

        $this->assertEquals(50.0, $result, 'Should return same positive value when abs is true.');
    }

    /**
     * Test calculatePercentageChange converts float inputs to integers.
     */
    public function test_calculate_percentage_change_float_to_int()
    {
        // 100.7 becomes 100, 150.9 becomes 150
        $result = Helper::calculatePercentageChange(100.7, 150.9);

        $this->assertEquals(50.0, $result, 'Should convert floats to integers before calculation.');
    }

    /**
     * Test calculatePercentageChange with string numeric inputs.
     */
    public function test_calculate_percentage_change_string_inputs()
    {
        $result = Helper::calculatePercentageChange('100', '200');

        $this->assertEquals(100.0, $result, 'Should handle string numeric inputs.');
    }

    /**
     * Test calculatePercentageChange with second number as zero.
     */
    public function test_calculate_percentage_change_second_number_zero()
    {
        // From 100 to 0 is a -100% change
        $result = Helper::calculatePercentageChange(100, 0);

        $this->assertEquals(-100.0, $result, 'Should return -100% when decreasing to zero.');
    }

    /**
     * Test calculatePercentageChange with large numbers.
     */
    public function test_calculate_percentage_change_large_numbers()
    {
        // From 1000000 to 1500000 is a 50% increase
        $result = Helper::calculatePercentageChange(1000000, 1500000);

        $this->assertEquals(50.0, $result, 'Should handle large numbers correctly.');
    }

    /**
     * Test calculatePercentageChange with small percentage change.
     */
    public function test_calculate_percentage_change_small_change()
    {
        // From 1000 to 1001 is a 0.1% increase
        $result = Helper::calculatePercentageChange(1000, 1001);

        $this->assertEquals(0.1, $result, 'Should calculate small percentage changes correctly.');
    }

    /**
     * Test calculatePercentageChange rounding with default decimals.
     */
    public function test_calculate_percentage_change_default_rounding()
    {
        // From 3 to 4 is 33.333...% increase, should round to 33.33
        $result = Helper::calculatePercentageChange(3, 4);

        $this->assertEquals(33.33, $result, 'Should round to 2 decimal places by default.');
    }

    /**
     * Test calculatePercentageChange with zero decimals.
     */
    public function test_calculate_percentage_change_zero_decimals()
    {
        // From 3 to 4 is 33.333...%, should round to 33
        $result = Helper::calculatePercentageChange(3, 4, 0);

        $this->assertEquals(33, $result, 'Should round to whole number when decimals is 0.');
    }
}
