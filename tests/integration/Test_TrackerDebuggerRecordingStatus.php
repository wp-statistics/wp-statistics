<?php

namespace WP_Statistics\Tests\Service\Debugger;

use WP_STATISTICS\DB;
use WP_Statistics\Service\Database\Managers\TableHandler;
use WP_Statistics\Service\Database\Schema\Manager;
use WP_Statistics\Service\Debugger\Provider\VisitorProvider;
use WP_STATISTICS\TimeZone;
use WP_UnitTestCase;

/**
 * The Tracker Debugger shows when the last visit was recorded, so a site where
 * visits stopped arriving is visible at a glance, whatever stopped them (#1178).
 */
class Test_TrackerDebuggerRecordingStatus extends WP_UnitTestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        TableHandler::createTable('visitor', Manager::getSchemaForTable('visitor'));
    }

    public function setUp(): void
    {
        parent::setUp();

        global $wpdb;
        $wpdb->query('DELETE FROM `' . DB::table('visitor') . '`'); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    }

    private function addVisitor($lastView, $ip)
    {
        global $wpdb;

        $wpdb->insert(DB::table('visitor'), [
            'last_counter' => substr($lastView, 0, 10),
            'last_view'    => $lastView,
            'referred'     => '',
            'agent'        => 'Chrome',
            'platform'     => 'Windows',
            'version'      => '141',
            'ip'           => $ip,
            'user_id'      => 0,
        ]);
    }

    public function test_no_visits_yet()
    {
        $status = (new VisitorProvider())->getRecordingStatus();

        $this->assertNull($status['last_visit']);
        $this->assertNull($status['seconds_since']);
        $this->assertSame(0, $status['visitors_24h']);
    }

    public function test_recent_visits_are_counted()
    {
        $this->addVisitor(TimeZone::getCurrentDate('Y-m-d H:i:s', null), '10.0.0.1');
        $this->addVisitor(TimeZone::getCurrentDate('Y-m-d H:i:s', -3), '10.0.0.2');

        $status = (new VisitorProvider())->getRecordingStatus();

        $this->assertSame(1, $status['visitors_24h']);
        $this->assertLessThan(MINUTE_IN_SECONDS, $status['seconds_since']);
    }

    public function test_last_visit_older_than_the_recent_range_is_still_found()
    {
        $eightDaysAgo = TimeZone::getCurrentDate('Y-m-d H:i:s', -8);
        $this->addVisitor($eightDaysAgo, '10.0.0.3');

        $status = (new VisitorProvider())->getRecordingStatus();

        $this->assertSame($eightDaysAgo, $status['last_visit']);
        $this->assertSame(0, $status['visitors_24h']);
        $this->assertGreaterThan(7 * DAY_IN_SECONDS, $status['seconds_since']);
    }
}
