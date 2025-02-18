<?php

use WP_STATISTICS\Helper;

class Test_NumberFormatter extends WP_UnitTestCase
{

    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Test that non‑numeric input returns 0.
     */
    public function test_non_numeric_input()
    {
        $this->assertSame(0, Helper::formatNumberWithUnit("abc"));
        $this->assertSame(0, Helper::formatNumberWithUnit(null));
        $this->assertSame("500", Helper::formatNumberWithUnit("500"));
        $this->assertSame("1.24K", Helper::formatNumberWithUnit("1242"));
    }

    /**
     * Test numbers below 1,000.
     */
    public function test_numbers_below_1000()
    {
        $this->assertSame(0, Helper::formatNumberWithUnit(0));
        $this->assertSame(500, Helper::formatNumberWithUnit(500));
        $this->assertSame(999, Helper::formatNumberWithUnit(999));
        $this->assertSame(500.1, Helper::formatNumberWithUnit(500.123, 1));
        $this->assertSame(500.12, Helper::formatNumberWithUnit(500.123, 2));
    }

    /**
     * Test numbers between 1,000 and 9,999.
     */
    public function test_numbers_between_1000_and_9999()
    {
        $this->assertSame("1.24K", Helper::formatNumberWithUnit(1242));
        $this->assertSame("9.99K", Helper::formatNumberWithUnit(9999));
        $this->assertSame("1K", Helper::formatNumberWithUnit(1000));
    }

    /**
     * Test numbers 10,000 and above.
     */
    public function test_numbers_10000_and_above()
    {
        $this->assertSame("18.7K", Helper::formatNumberWithUnit(18754));
        $this->assertSame("1.5M", Helper::formatNumberWithUnit(1500000));
    }

    /**
     * Test formatting of negative numbers.
     *
     * Adjust these expectations based on how negative numbers should be handled.
     */
    public function test_negative_numbers(): void
    {
        $this->assertSame(-500, Helper::formatNumberWithUnit(-500));
        $this->assertSame(round(-500.123, 1), Helper::formatNumberWithUnit(-500.123, 1));
        $this->assertSame(-1242, Helper::formatNumberWithUnit(-1242));
    }

    /**
     * Test custom precision parameter handling.
     *
     * For numbers below 1000, the passed precision is applied.
     * For numbers 1000 and above, the method uses its fixed rounding logic.
     */
    public function test_custom_precision_parameter(): void
    {
        // For a number between 1000 and 9999, the fixed rounding applies:
        $this->assertSame('1.24K', Helper::formatNumberWithUnit(1242, 1));
        $this->assertSame('1.24K', Helper::formatNumberWithUnit(1242, 2));
        $this->assertSame('12.4M', Helper::formatNumberWithUnit(12423535, 2));
        $this->assertSame('12.4M', Helper::formatNumberWithUnit(12483535, 2));

        // For a number below 1000, the custom precision is applied.
        $this->assertSame(500.123, Helper::formatNumberWithUnit(500.123, 3));
        $this->assertSame(500.12, Helper::formatNumberWithUnit(500.124, 2));
        $this->assertSame(500.13, Helper::formatNumberWithUnit(500.128, 2));
    }
}
