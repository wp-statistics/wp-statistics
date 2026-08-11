<?php

namespace WP_Statistics\Tests;

use WP_STATISTICS\Option;
use WP_STATISTICS\Schedule;
use WP_UnitTestCase;

class Test_Schedule extends WP_UnitTestCase
{
    private const REPORT_HOOK = 'wp_statistics_report_hook';

    private const SCHEDULE_HOOKS = [
        'wp_statistics_dbmaint_hook',
        'wp_statistics_referrals_db_hook',
        self::REPORT_HOOK,
        'wp_statistics_licenses_hook',
        'wp_statistics_geoip_hook',
        'wp_statistics_check_licenses_status',
    ];

    private $originalTimeReport;

    public function setUp(): void
    {
        parent::setUp();

        $this->originalTimeReport = Option::get('time_report');
    }

    public function tearDown(): void
    {
        foreach (self::SCHEDULE_HOOKS as $hook) {
            wp_clear_scheduled_hook($hook);
        }

        Option::update('time_report', $this->originalTimeReport);

        parent::tearDown();
    }

    public function test_report_event_is_rescheduled_when_frequency_changes()
    {
        Option::update('time_report', 'weekly');
        wp_clear_scheduled_hook(self::REPORT_HOOK);

        $this->assertTrue(wp_schedule_event(time() + HOUR_IN_SECONDS, 'daily', self::REPORT_HOOK));
        $this->assertSame('daily', wp_get_schedule(self::REPORT_HOOK));

        Schedule::get_instance()->maybe_schedule_hooks();

        $this->assertSame('weekly', wp_get_schedule(self::REPORT_HOOK));
    }
}
