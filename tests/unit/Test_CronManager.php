<?php

use WP_Statistics\Service\Cron\CronManager;
use WP_Statistics\Service\Cron\ScheduledEventInterface;
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
        wp_schedule_event(time(), 'daily', 'wp_statistics_dbmaint_hook');

        $this->assertNotFalse(wp_next_scheduled('wp_statistics_dbmaint_hook'));

        CronManager::unscheduleAll();

        $this->assertFalse(wp_next_scheduled('wp_statistics_dbmaint_hook'));
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
    public function test_database_maintenance_event_implements_interface()
    {
        $event = new DatabaseMaintenanceEvent();

        $this->assertInstanceOf(ScheduledEventInterface::class, $event);
    }

    public function test_event_has_required_methods()
    {
        $event = new DatabaseMaintenanceEvent();

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
