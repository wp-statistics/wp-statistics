<?php

namespace WP_Statistics\Tests\ImportExport;

use WP_UnitTestCase;
use WP_Statistics\Service\ImportExport\DataRetentionService;

/**
 * Tests for DataRetentionService.
 */
class Test_DataRetentionService extends WP_UnitTestCase
{
    public function test_get_policy()
    {
        $service = new DataRetentionService();
        $policy  = $service->getPolicy();

        $this->assertArrayHasKey('mode', $policy);
        $this->assertArrayHasKey('days', $policy);
        $this->assertIsString($policy['mode']);
        $this->assertIsInt($policy['days']);
    }

    public function test_default_policy_is_forever()
    {
        $service = new DataRetentionService();
        $policy  = $service->getPolicy();

        $this->assertEquals('forever', $policy['mode']);
    }

    public function test_default_days_is_180()
    {
        $service = new DataRetentionService();
        $policy  = $service->getPolicy();

        $this->assertEquals(180, $policy['days']);
    }

    public function test_throws_for_forever_mode()
    {
        $this->expectException(\InvalidArgumentException::class);

        $service = new DataRetentionService();
        $service->purgeWithPolicy('forever', 180);
    }

    public function test_throws_for_invalid_days()
    {
        $this->expectException(\InvalidArgumentException::class);

        $service = new DataRetentionService();
        $service->purgeWithPolicy('delete', 0);
    }

    public function test_throws_for_negative_days()
    {
        $this->expectException(\InvalidArgumentException::class);

        $service = new DataRetentionService();
        $service->purgeWithPolicy('delete', -10);
    }
}
