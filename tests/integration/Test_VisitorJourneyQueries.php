<?php

use WP_Statistics\Models\EventsModel;
use WP_Statistics\Models\VisitorsModel;
use WP_Statistics\Service\Database\Managers\TableHandler;
use WP_Statistics\Service\Database\Schema\Manager;
use WP_STATISTICS\DB;

/**
 * Queries behind the single visitor journey: views of several visits, and their events.
 */
class Test_VisitorJourneyQueries extends WP_UnitTestCase
{
    private static $tables = ['visitor_relationships', 'events'];

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        foreach (self::$tables as $table) {
            TableHandler::createTable($table, Manager::getSchemaForTable($table));
        }
    }

    public function setUp(): void
    {
        parent::setUp();
        $this->emptyTables();
    }

    public function tearDown(): void
    {
        $this->emptyTables();
        parent::tearDown();
    }

    private function emptyTables()
    {
        global $wpdb;

        foreach (self::$tables as $table) {
            $wpdb->query('DELETE FROM `' . DB::table($table) . '`');
        }
    }

    private function addView($visitorId, $pageId, $date)
    {
        global $wpdb;
        $wpdb->insert(DB::table('visitor_relationships'), ['visitor_id' => $visitorId, 'page_id' => $pageId, 'date' => $date]);
    }

    private function addEvent($visitorId, $name, $date)
    {
        global $wpdb;
        $wpdb->insert(DB::table('events'), ['visitor_id' => $visitorId, 'page_id' => 1, 'event_name' => $name, 'event_data' => '{}', 'date' => $date]);
    }

    public function test_journeys_of_several_visits_oldest_first()
    {
        $this->addView(1, 10, '2026-04-19 20:08:00');
        $this->addView(1, 11, '2026-04-19 20:05:00');
        $this->addView(2, 12, '2026-04-16 10:43:00');
        $this->addView(3, 13, '2026-04-16 10:44:00');

        $rows = (new VisitorsModel())->getVisitorsJourneys(['visitor_ids' => [1, 2, '2', 0]]);

        $this->assertSame(['12', '11', '10'], array_map(function ($row) {
            return (string)$row->page_id;
        }, $rows));
        $this->assertSame([], (new VisitorsModel())->getVisitorsJourneys(['visitor_ids' => []]));
    }

    public function test_journey_limit_keeps_the_newest_views()
    {
        $this->addView(1, 10, '2026-04-19 20:00:00');
        $this->addView(1, 11, '2026-04-19 20:01:00');
        $this->addView(1, 12, '2026-04-19 20:02:00');

        $rows = (new VisitorsModel())->getVisitorsJourneys(['visitor_ids' => [1], 'limit' => 2]);

        $this->assertSame(['11', '12'], array_map(function ($row) {
            return (string)$row->page_id;
        }, $rows));
    }

    public function test_visitor_events_filter_by_visit_and_name()
    {
        $this->addEvent(1, 'click', '2026-04-19 20:12:00');
        $this->addEvent(1, 'file_download', '2026-04-19 20:10:00');
        $this->addEvent(1, 'mouseup', '2026-04-19 20:11:00');
        $this->addEvent(2, 'click', '2026-04-19 20:13:00');

        $rows = (new EventsModel())->getVisitorEvents(['visitor_ids' => [1]]);

        $this->assertSame(['file_download', 'click'], array_column(array_map('get_object_vars', $rows), 'event_name'));
        $this->assertSame([], (new EventsModel())->getVisitorEvents(['visitor_ids' => []]));
    }
}
