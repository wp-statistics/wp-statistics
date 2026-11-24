<?php

use WP_Statistics\Decorators\VisitorDecorator;
use WP_Statistics\Service\Admin\VisitorInsights\VisitorJourney;

/**
 * Single visitor journey shaping (VisitorJourney): visits, steps, time on page, events, totals.
 */
class Test_VisitorJourney extends WP_UnitTestCase
{
    private function visitor($id, $firstView, $lastView, $hits = 1)
    {
        return new VisitorDecorator((object)[
            'ID'           => $id,
            'ip'           => '203.0.113.' . $id,
            'user_id'      => 0,
            'hits'         => $hits,
            'first_view'   => $firstView,
            'last_view'    => $lastView,
            'last_counter' => substr($lastView, 0, 10),
            'agent'        => 'Firefox',
            'platform'     => 'Windows',
            'location'     => 'ES',
            'city'         => 'Málaga',
        ]);
    }

    private function view($visitorId, $date, $pageId)
    {
        return (object)['visitor_id' => $visitorId, 'date' => $date, 'page_id' => $pageId];
    }

    public function test_steps_are_newest_first_and_time_on_page_is_gap_to_next_view()
    {
        $visit = VisitorJourney::buildVisit($this->visitor(1, '2026-04-19 20:05:00', '2026-04-19 20:20:00', 3), [
            $this->view(1, '2026-04-19 20:08:00', 11),
            $this->view(1, '2026-04-19 20:05:00', 10),
            $this->view(1, '2026-04-19 20:20:00', 12),
        ], []);

        // Exit on top, entry at the bottom; the exit page has no time on page.
        $this->assertSame([12, 11, 10], array_column($visit['steps'], 'page_id'));
        $this->assertSame([null, 720, 180], array_column($visit['steps'], 'time_on_page'));
        $this->assertTrue($visit['steps'][0]['is_exit']);
        $this->assertFalse($visit['steps'][0]['is_entry']);
        $this->assertTrue($visit['steps'][2]['is_entry']);
        $this->assertFalse($visit['steps'][2]['is_exit']);
        $this->assertSame(900, $visit['duration']);
        $this->assertSame(3, $visit['views']);
    }

    public function test_single_view_visit_is_entry_only_without_duration()
    {
        $visit = VisitorJourney::buildVisit($this->visitor(1, '2026-04-01 15:05:00', '2026-04-01 15:05:00'), [
            $this->view(1, '2026-04-01 15:05:00', 10),
        ], []);

        $this->assertTrue($visit['steps'][0]['is_entry']);
        $this->assertFalse($visit['steps'][0]['is_exit']);
        $this->assertNull($visit['steps'][0]['time_on_page']);
        $this->assertNull($visit['duration']);
    }

    public function test_visit_without_recorded_views_falls_back_to_hits()
    {
        $visit = VisitorJourney::buildVisit($this->visitor(1, '2026-04-01 15:05:00', '2026-04-01 15:30:00', 4), [], []);

        $this->assertSame([], $visit['steps']);
        $this->assertSame(4, $visit['views']);
        $this->assertSame('2026-04-01 15:05:00', $visit['start']);
    }

    public function test_visit_with_only_its_newest_views_loaded_uses_real_hits()
    {
        $visit = VisitorJourney::buildVisit($this->visitor(1, '2026-04-01 15:00:00', '2026-04-01 15:30:00', 60), [
            $this->view(1, '2026-04-01 15:20:00', 10),
            $this->view(1, '2026-04-01 15:30:00', 11),
        ], []);

        $this->assertSame(60, $visit['views']);
        $this->assertSame(2, $visit['views_shown']);
        $this->assertTrue($visit['truncated']);
        // The real entry page was not loaded, so no step is marked as entry; the visit starts at first_view.
        $this->assertSame([false, false], array_column($visit['steps'], 'is_entry'));
        $this->assertSame('2026-04-01 15:00:00', $visit['start']);
    }

    public function test_events_sit_above_the_page_they_happened_on_and_do_not_change_time_on_page()
    {
        $event = VisitorJourney::normalizeEvent([
            'visitor_id' => 1,
            'date'       => '2026-04-19 20:12:00',
            'event_name' => 'click',
            'event_data' => wp_json_encode(['element' => ['value' => 'Share on X'], 'target_url' => 'https://www.twitter.com/intent/tweet']),
        ]);

        $visit = VisitorJourney::buildVisit($this->visitor(1, '2026-04-19 20:05:00', '2026-04-19 20:20:00'), [
            $this->view(1, '2026-04-19 20:05:00', 10),
            $this->view(1, '2026-04-19 20:11:00', 11),
            $this->view(1, '2026-04-19 20:14:00', 12),
        ], [$event]);

        // Newest first: page 12, the click made on page 11, page 11, page 10.
        $this->assertSame(['page', 'event', 'page', 'page'], array_column($visit['steps'], 'type'));
        $this->assertSame([12, 11, 10], array_values(array_filter(array_column($visit['steps'], 'page_id'))));
        $this->assertSame('Clicked "Share on X"', $visit['steps'][1]['label']);
        $this->assertSame('twitter.com', $visit['steps'][1]['host']);
        $this->assertSame(180, $visit['steps'][2]['time_on_page']);
        $this->assertSame(360, $visit['steps'][3]['time_on_page']);
        $this->assertSame(1, $visit['events']);
    }

    public function test_download_event_label_uses_file_name()
    {
        $event = VisitorJourney::normalizeEvent((object)[
            'visitor_id' => 2,
            'date'       => '2026-11-20 19:26:40',
            'event_name' => 'file_download',
            'event_data' => '{"file_details":{"file_name":"user-guide","file_extension":"pdf"},"target_url":"http:\/\/example.test\/user-guide.pdf"}',
        ]);

        $this->assertSame('Downloaded user-guide.pdf', $event['label']);
        $this->assertSame('download', $event['kind']);
        $this->assertNull(VisitorJourney::normalizeEvent(['date' => '', 'event_name' => 'click']));
    }

    public function test_visits_are_newest_first_and_flag_current_and_latest()
    {
        $old     = $this->visitor(5, '2026-01-02 17:27:00', '2026-01-02 17:49:00');
        $current = $this->visitor(9, '2026-04-16 10:43:00', '2026-04-16 11:20:00');
        $newest  = $this->visitor(7, '2026-04-19 20:05:00', '2026-04-19 20:20:00');

        $visits = VisitorJourney::buildVisits([$current, $old, $newest], [], [], 9);

        $this->assertSame([7, 9, 5], array_column($visits, 'id'));
        $this->assertTrue($visits[0]['is_latest']);
        $this->assertTrue($visits[1]['is_current']);
        $this->assertFalse($visits[0]['is_current']);
    }

    public function test_page_resolver_is_called_per_view()
    {
        $visit = VisitorJourney::buildVisit($this->visitor(1, '2026-04-19 20:05:00', '2026-04-19 20:05:00'), [
            $this->view(1, '2026-04-19 20:05:00', 42),
        ], [], true, function ($pageId) {
            return ['title' => "Page $pageId", 'link' => '', 'report' => ''];
        });

        $this->assertSame('Page 42', $visit['steps'][0]['page']['title']);
        $this->assertTrue($visit['is_current']);
    }

    public function test_summary_counts_visits_views_and_first_last_seen()
    {
        $summary = VisitorJourney::summarize([
            $this->visitor(1, '2026-04-19 20:05:00', '2026-04-19 20:20:00', 6),
            $this->visitor(2, '2025-01-02 17:27:00', '2025-01-02 17:49:00', 8),
            'not a visitor',
        ]);

        $this->assertSame(2, $summary['visits']);
        $this->assertSame(14, $summary['views']);
        $this->assertSame('2025-01-02 17:27:00', $summary['first_seen']);
        $this->assertSame('2026-04-19 20:20:00', $summary['last_seen']);
    }

    public function test_format_duration()
    {
        $this->assertSame('', VisitorJourney::formatDuration(null));
        $this->assertSame('15 s', VisitorJourney::formatDuration(15));
        $this->assertSame('2 min 39 s', VisitorJourney::formatDuration(159));
        $this->assertSame('3 min', VisitorJourney::formatDuration(180));
        $this->assertSame('1 h 5 min', VisitorJourney::formatDuration(3900));
    }

    public function test_short_last_view_is_one_line_with_short_month()
    {
        update_option('date_format', 'F j, Y');
        update_option('time_format', 'g:i a');

        $thisYear = gmdate('Y');
        $visitor  = $this->visitor(1, $thisYear . '-11-22 10:15:00', $thisYear . '-11-22 10:15:00');
        $old      = $this->visitor(2, '2020-03-05 09:07:00', '2020-03-05 09:07:00');

        $this->assertSame('Nov 22, 10:15 am', $visitor->getLastViewShort());
        $this->assertSame('Mar 5, 2020, 9:07 am', $old->getLastViewShort());
    }
}
