<?php

namespace WP_Statistics\Tests;

use WP_STATISTICS\Option;
use WP_STATISTICS\Schedule;
use WP_UnitTestCase;

class Test_Schedule extends WP_UnitTestCase
{
    private const REPORT_HOOK = 'wp_statistics_report_hook';

    public function tearDown(): void
    {
        wp_clear_scheduled_hook(self::REPORT_HOOK);
        Option::update('time_report', '0');

        parent::tearDown();
    }

    public function test_report_event_is_rescheduled_when_frequency_changes()
    {
        Option::update('time_report', 'weekly');
        wp_schedule_event(time() + HOUR_IN_SECONDS, 'daily', self::REPORT_HOOK);

        Schedule::get_instance()->maybe_schedule_hooks();

        $this->assertSame('weekly', wp_get_schedule(self::REPORT_HOOK));
    }
}
