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

    public function test_many_long_visits_each_keep_their_newest_views()
    {
        global $wpdb;

        // 51 visits x 60 views: the old 2000-row cap dropped the oldest visits entirely
        $values = [];
        for ($visit = 1; $visit <= 51; $visit++) {
            for ($view = 0; $view < 60; $view++) {
                $values[] = $wpdb->prepare('(%d, %d, %s)', $visit, 100 + $view, gmdate('Y-m-d H:i:s', strtotime('2026-01-01 00:00:00') + $visit * DAY_IN_SECONDS + $view * 10));
            }
        }
        $wpdb->query('INSERT INTO `' . DB::table('visitor_relationships') . '` (visitor_id, page_id, date) VALUES ' . implode(',', $values));

        $rows = (new VisitorsModel())->getVisitorsJourneys(['visitor_ids' => range(1, 51), 'per_visit' => 50]);

        $byVisit = [];
        foreach ($rows as $row) {
            $byVisit[(int)$row->visitor_id][] = (int)$row->page_id;
        }

        $this->assertCount(51, $byVisit, 'every visit has views, the oldest one too');
        foreach ($byVisit as $pages) {
            $this->assertCount(50, $pages);
            $this->assertSame(range(110, 159), $pages, 'newest 50 views, oldest first');
        }

        // Oldest first over all visits
        $this->assertSame(1, (int)$rows[0]->visitor_id);
        $this->assertSame(51, (int)end($rows)->visitor_id);

        // Default cap keeps all 60 views of a visit
        $this->assertCount(60, (new VisitorsModel())->getVisitorsJourneys(['visitor_ids' => [1]]));
    }

    public function test_same_page_twice_in_one_second_is_kept()
    {
        $this->addView(1, 10, '2026-04-19 20:00:00');
        $this->addView(1, 10, '2026-04-19 20:00:00');
        $this->addView(2, 10, '2026-04-19 20:00:00');

        $this->assertCount(3, (new VisitorsModel())->getVisitorsJourneys(['visitor_ids' => [1, 2]]));
    }

    public function test_events_are_capped_per_visit()
    {
        for ($i = 0; $i < 5; $i++) {
            $this->addEvent(1, 'click', '2026-04-19 20:1' . $i . ':00');
        }
        $this->addEvent(2, 'click', '2026-04-10 10:00:00');

        $rows = (new EventsModel())->getVisitorEvents(['visitor_ids' => [1, 2], 'per_visit' => 3]);

        $this->assertSame(['2', '1', '1', '1'], array_map(function ($row) {
            return (string)$row->visitor_id;
        }, $rows));
        $this->assertSame('2026-04-19 20:12:00', $rows[1]->date, 'the newest 3 of visit 1');
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
