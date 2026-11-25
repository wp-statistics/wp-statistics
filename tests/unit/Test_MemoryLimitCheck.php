<?php

use WP_STATISTICS\Helper;

/**
 * The "memory limit is too low" notice must not show when PHP has no memory limit.
 */
class Test_MemoryLimitCheck extends WP_UnitTestCase
{
    private $original;

    public function setUp(): void
    {
        parent::setUp();
        $this->original = ini_get('memory_limit');
    }

    public function tearDown(): void
    {
        ini_set('memory_limit', $this->original);
        parent::tearDown();
    }

    public function test_unlimited_memory_is_not_too_low()
    {
        ini_set('memory_limit', '-1');
        $this->assertFalse(Helper::checkMemoryLimit());
    }

    public function test_limit_above_usage_is_not_too_low()
    {
        ini_set('memory_limit', '4096M');
        $this->assertFalse(Helper::checkMemoryLimit());
    }
}
