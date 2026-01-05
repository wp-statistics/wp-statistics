<?php

use WP_Statistics\Service\Cron\CronSchedules;

/**
 * Test CronSchedules class.
 *
 * @group cron
 */
class Test_CronSchedules extends WP_UnitTestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function test_getSchedules_returns_array()
    {
        $schedules = CronSchedules::getSchedules();

        $this->assertIsArray($schedules);
    }

    public function test_getSchedules_contains_required_intervals()
    {
        $schedules = CronSchedules::getSchedules();

        $this->assertArrayHasKey('daily', $schedules);
        $this->assertArrayHasKey('weekly', $schedules);
        $this->assertArrayHasKey('biweekly', $schedules);
        $this->assertArrayHasKey('monthly', $schedules);
    }

    public function test_schedule_has_required_keys()
    {
        $schedules = CronSchedules::getSchedules();

        foreach (['daily', 'weekly', 'biweekly', 'monthly'] as $interval) {
            $this->assertArrayHasKey('interval', $schedules[$interval]);
            $this->assertArrayHasKey('display', $schedules[$interval]);
            $this->assertArrayHasKey('next_schedule', $schedules[$interval]);
        }
    }

    public function test_daily_interval_is_correct()
    {
        $schedules = CronSchedules::getSchedules();

        $this->assertEquals(DAY_IN_SECONDS, $schedules['daily']['interval']);
    }

    public function test_weekly_interval_is_correct()
    {
        $schedules = CronSchedules::getSchedules();

        $this->assertEquals(WEEK_IN_SECONDS, $schedules['weekly']['interval']);
    }

    public function test_biweekly_interval_is_correct()
    {
        $schedules = CronSchedules::getSchedules();

        $this->assertEquals(2 * WEEK_IN_SECONDS, $schedules['biweekly']['interval']);
    }

    public function test_monthly_interval_is_correct()
    {
        $schedules = CronSchedules::getSchedules();

        $this->assertEquals(MONTH_IN_SECONDS, $schedules['monthly']['interval']);
    }

    public function test_next_schedule_is_in_future()
    {
        $schedules = CronSchedules::getSchedules();
        $now = time();

        foreach (['daily', 'weekly', 'biweekly', 'monthly'] as $interval) {
            $nextSchedule = $schedules[$interval]['next_schedule'];
            $this->assertGreaterThan($now, $nextSchedule, "Next schedule for {$interval} should be in the future");
        }
    }

    public function test_addCustomSchedules_adds_to_wordpress()
    {
        $existingSchedules = [];

        $modified = CronSchedules::addCustomSchedules($existingSchedules);

        $this->assertArrayHasKey('daily', $modified);
        $this->assertArrayHasKey('weekly', $modified);
        $this->assertArrayHasKey('biweekly', $modified);
        $this->assertArrayHasKey('monthly', $modified);
    }

    public function test_addCustomSchedules_does_not_override_existing()
    {
        $existingSchedules = [
            'daily' => [
                'interval' => 12345,
                'display'  => 'Custom Daily',
            ],
        ];

        $modified = CronSchedules::addCustomSchedules($existingSchedules);

        // Should not override existing 'daily'
        $this->assertEquals(12345, $modified['daily']['interval']);
        $this->assertEquals('Custom Daily', $modified['daily']['display']);
    }

    public function test_getNextScheduledTime_returns_false_for_unscheduled()
    {
        $result = CronSchedules::getNextScheduledTime('wp_statistics_nonexistent_hook');

        $this->assertFalse($result);
    }

    public function test_every_minute_schedule_exists()
    {
        $schedules = CronSchedules::getSchedules();

        $this->assertArrayHasKey('every_minute', $schedules);
        $this->assertEquals(60, $schedules['every_minute']['interval']);
    }

    public function test_schedules_filter_is_applied()
    {
        // Add a filter to modify schedules
        add_filter('wp_statistics_cron_schedules', function ($schedules) {
            $schedules['custom_test'] = [
                'interval' => 3600,
                'display'  => 'Custom Test',
            ];
            return $schedules;
        });

        $schedules = CronSchedules::getSchedules();

        $this->assertArrayHasKey('custom_test', $schedules);
        $this->assertEquals(3600, $schedules['custom_test']['interval']);

        // Clean up filter
        remove_all_filters('wp_statistics_cron_schedules');
    }
}
