<?php

namespace WP_Statistics\Tests\Utils;

use WP_Statistics\Utils\Math;
use WP_UnitTestCase;

class Test_Math extends WP_UnitTestCase
{
    public function test_percentageChange_zeroPrevious_returnsZeroByDefault()
    {
        $this->assertSame(0.0, Math::percentageChange(0, 10));
        $this->assertSame(0.0, Math::percentageChange(0, 0));
    }

    public function test_percentageChange_zeroPrevious_hundredBehavior()
    {
        $this->assertSame(100.0, Math::percentageChange(0, 10, 1, 'hundred'));
        $this->assertSame(0.0, Math::percentageChange(0, 0, 1, 'hundred'));
    }

    public function test_percentageChange_increase_and_decrease()
    {
        $this->assertSame(50.0, Math::percentageChange(50, 75, 0));
        $this->assertSame(-20.0, Math::percentageChange(100, 80, 0));
    }

    public function test_percentageChange_rounding()
    {
        $this->assertSame(33.3, Math::percentageChange(3, 4, 1));
        $this->assertSame(33.33, Math::percentageChange(3, 4, 2));
    }
}

