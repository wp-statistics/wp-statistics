<?php

use WP_Statistics\Service\Cron\CronManager;
use WP_Statistics\Service\Cron\ScheduledEventInterface;
use WP_Statistics\Service\Cron\Events\EmailReportEvent;
use WP_Statistics\Service\Cron\Events\DatabaseMaintenanceEvent;

/**
 * Test CronManager class.
 *
 * @group cron
 */
class Test_CronManager extends WP_UnitTestCase
{
    /**
     * @var CronManager
     */
    private $manager;

    public function setUp(): void
    {
        parent::setUp();

        // Clear all scheduled hooks before testing
        CronManager::unscheduleAll();
    }

    public function tearDown(): void
    {
        CronManager::unscheduleAll();
        parent::tearDown();
    }

    public function test_getScheduledEvents_returns_array()
    {
        $events = CronManager::getScheduledEvents();

        $this->assertIsArray($events);
    }

    public function test_getScheduledEvents_contains_email_report()
    {
        $events = CronManager::getScheduledEvents();

        $this->assertArrayHasKey('wp_statistics_email_report', $events);
        $this->assertEquals('Email Report', $events['wp_statistics_email_report']['label']);
    }

    public function test_getScheduledEvents_contains_database_maintenance()
    {
        $events = CronManager::getScheduledEvents();

        $this->assertArrayHasKey('wp_statistics_dbmaint_hook', $events);
        $this->assertEquals('Database Maintenance', $events['wp_statistics_dbmaint_hook']['label']);
    }

    public function test_getScheduledEvents_contains_geoip_update()
    {
        $events = CronManager::getScheduledEvents();

        $this->assertArrayHasKey('wp_statistics_geoip_hook', $events);
        $this->assertEquals('GeoIP Database Update', $events['wp_statistics_geoip_hook']['label']);
    }

    public function test_unscheduleAll_removes_all_hooks()
    {
        // Schedule a test hook
        wp_schedule_event(time(), 'daily', 'wp_statistics_email_report');

        $this->assertNotFalse(wp_next_scheduled('wp_statistics_email_report'));

        CronManager::unscheduleAll();

        $this->assertFalse(wp_next_scheduled('wp_statistics_email_report'));
    }

    public function test_unscheduleAll_removes_legacy_hooks()
    {
        // Schedule a legacy hook
        wp_schedule_event(time(), 'daily', 'wp_statistics_report_hook');

        $this->assertNotFalse(wp_next_scheduled('wp_statistics_report_hook'));

        CronManager::unscheduleAll();

        $this->assertFalse(wp_next_scheduled('wp_statistics_report_hook'));
    }
}

/**
 * Test ScheduledEventInterface implementation.
 *
 * @group cron
 */
class Test_ScheduledEventInterface extends WP_UnitTestCase
{
    public function test_email_report_event_implements_interface()
    {
        $event = new EmailReportEvent();

        $this->assertInstanceOf(ScheduledEventInterface::class, $event);
    }

    public function test_database_maintenance_event_implements_interface()
    {
        $event = new DatabaseMaintenanceEvent();

        $this->assertInstanceOf(ScheduledEventInterface::class, $event);
    }

    public function test_event_has_required_methods()
    {
        $event = new EmailReportEvent();

        $this->assertTrue(method_exists($event, 'getHook'));
        $this->assertTrue(method_exists($event, 'getRecurrence'));
        $this->assertTrue(method_exists($event, 'shouldSchedule'));
        $this->assertTrue(method_exists($event, 'isScheduled'));
        $this->assertTrue(method_exists($event, 'execute'));
        $this->assertTrue(method_exists($event, 'getDescription'));
        $this->assertTrue(method_exists($event, 'maybeSchedule'));
        $this->assertTrue(method_exists($event, 'registerCallback'));
        $this->assertTrue(method_exists($event, 'reschedule'));
        $this->assertTrue(method_exists($event, 'unschedule'));
        $this->assertTrue(method_exists($event, 'getNextRunTime'));
        $this->assertTrue(method_exists($event, 'getInfo'));
    }
}

/**
 * Test EmailReportEvent class.
 *
 * @group cron
 * @group email
 */
class Test_EmailReportEvent extends WP_UnitTestCase
{
    /**
     * @var EmailReportEvent
     */
    private $event;

    public function setUp(): void
    {
        parent::setUp();
        $this->event = new EmailReportEvent();

        // Clear any existing schedule
        $this->event->unschedule();
    }

    public function tearDown(): void
    {
        $this->event->unschedule();
        parent::tearDown();
    }

    public function test_getHook_returns_correct_hook()
    {
        $this->assertEquals('wp_statistics_email_report', $this->event->getHook());
    }

    public function test_getDescription_returns_string()
    {
        $description = $this->event->getDescription();

        $this->assertIsString($description);
        $this->assertEquals('Email Report', $description);
    }

    public function test_getRecurrence_returns_valid_frequency()
    {
        $recurrence = $this->event->getRecurrence();

        $this->assertContains($recurrence, ['daily', 'weekly', 'biweekly', 'monthly']);
    }

    public function test_getRecurrence_defaults_to_weekly()
    {
        // Without time_report option set
        delete_option('wp_statistics_settings');

        $recurrence = $this->event->getRecurrence();

        $this->assertEquals('weekly', $recurrence);
    }

    public function test_shouldSchedule_returns_false_when_disabled()
    {
        // Disable email reports
        update_option('wp_statistics_settings', ['time_report' => '0']);

        $this->assertFalse($this->event->shouldSchedule());
    }

    public function test_shouldSchedule_returns_true_when_enabled()
    {
        // Enable email reports
        update_option('wp_statistics_settings', ['time_report' => 'weekly']);

        $this->assertTrue($this->event->shouldSchedule());
    }

    public function test_isScheduled_returns_false_initially()
    {
        $this->assertFalse($this->event->isScheduled());
    }

    public function test_maybeSchedule_schedules_when_enabled()
    {
        // Enable email reports
        update_option('wp_statistics_settings', ['time_report' => 'weekly']);

        $this->event->maybeSchedule();

        $this->assertTrue($this->event->isScheduled());
    }

    public function test_maybeSchedule_does_not_schedule_when_disabled()
    {
        // Disable email reports
        update_option('wp_statistics_settings', ['time_report' => '0']);

        $this->event->maybeSchedule();

        $this->assertFalse($this->event->isScheduled());
    }

    public function test_unschedule_removes_scheduled_event()
    {
        // Enable and schedule
        update_option('wp_statistics_settings', ['time_report' => 'weekly']);
        $this->event->maybeSchedule();

        $this->assertTrue($this->event->isScheduled());

        $this->event->unschedule();

        $this->assertFalse($this->event->isScheduled());
    }

    public function test_reschedule_updates_schedule()
    {
        // Enable and schedule
        update_option('wp_statistics_settings', ['time_report' => 'weekly']);
        $this->event->maybeSchedule();

        $this->assertTrue($this->event->isScheduled());

        // Change frequency
        update_option('wp_statistics_settings', ['time_report' => 'daily']);
        $this->event->reschedule();

        $this->assertTrue($this->event->isScheduled());
    }

    public function test_getNextRunTime_returns_timestamp_when_scheduled()
    {
        // Enable and schedule
        update_option('wp_statistics_settings', ['time_report' => 'weekly']);
        $this->event->maybeSchedule();

        $nextRun = $this->event->getNextRunTime();

        $this->assertIsInt($nextRun);
        $this->assertGreaterThan(time(), $nextRun);
    }

    public function test_getNextRunTime_returns_false_when_not_scheduled()
    {
        $nextRun = $this->event->getNextRunTime();

        $this->assertFalse($nextRun);
    }

    public function test_getInfo_returns_complete_array()
    {
        $info = $this->event->getInfo();

        $this->assertArrayHasKey('hook', $info);
        $this->assertArrayHasKey('description', $info);
        $this->assertArrayHasKey('recurrence', $info);
        $this->assertArrayHasKey('enabled', $info);
        $this->assertArrayHasKey('scheduled', $info);
        $this->assertArrayHasKey('next_run', $info);
        $this->assertArrayHasKey('next_run_formatted', $info);
    }

    public function test_getInfo_includes_email_specific_fields()
    {
        $info = $this->event->getInfo();

        $this->assertArrayHasKey('recipients', $info);
        $this->assertArrayHasKey('frequency', $info);
        $this->assertArrayHasKey('last_sent', $info);
    }

    public function test_registerCallback_adds_action_hook()
    {
        $this->event->registerCallback();

        $this->assertNotFalse(has_action('wp_statistics_email_report'));
    }

    public function test_needsReschedule_returns_true_when_not_scheduled_but_enabled()
    {
        update_option('wp_statistics_settings', ['time_report' => 'weekly']);

        $this->assertTrue($this->event->needsReschedule());
    }

    public function test_needsReschedule_returns_false_when_disabled()
    {
        update_option('wp_statistics_settings', ['time_report' => '0']);

        $this->assertFalse($this->event->needsReschedule());
    }
}
