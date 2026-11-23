<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_Statistics\Components\View;
use WP_Statistics\Decorators\VisitorDecorator;
use WP_Statistics\Service\Admin\VisitorInsights\VisitorJourney;

/**
 * Data in the new components (event names, page titles, domains) and English empty-state
 * sentences are isolated with dir="auto", so quotes and full stops stay in place on RTL
 * screens. Translation strings themselves are not touched. All values are made up.
 */
class Test_RtlIsolation extends WP_UnitTestCase
{
    private function journey()
    {
        $visitor = new VisitorDecorator((object)[
            'ID' => 3, 'ip' => '203.0.113.3', 'user_id' => 0, 'hits' => 2,
            'first_view' => '2026-04-19 20:05:00', 'last_view' => '2026-04-19 20:20:00', 'last_counter' => '2026-04-19',
            'agent' => 'Firefox', 'platform' => 'Windows', 'location' => 'ES', 'city' => '',
            'referred' => 'example.com', 'source_channel' => 'referral', 'source_name' => 'example.com',
        ]);
        $event = VisitorJourney::normalizeEvent([
            'visitor_id' => 3,
            'date'       => '2026-04-19 20:12:00',
            'event_name' => 'click',
            'event_data' => wp_json_encode(['element' => ['value' => 'Download on WordPress.org'], 'target_url' => 'https://wordpress.org/plugins/example/']),
        ]);
        $visit = VisitorJourney::buildVisit($visitor, [
            (object)['visitor_id' => 3, 'date' => '2026-04-19 20:05:00', 'page_id' => 0],
            (object)['visitor_id' => 3, 'date' => '2026-04-19 20:14:00', 'page_id' => 0],
        ], [$event], true);

        return (string)View::load('components/visitor-journey', ['visits' => [$visit], 'visitor' => $visitor], true);
    }

    public function test_journey_isolates_event_labels_domains_and_titles()
    {
        $html = $this->journey();

        $this->assertStringContainsString('<span dir="auto">Clicked &quot;Download on WordPress.org&quot;</span>', $html);
        $this->assertStringContainsString('<span dir="auto">wordpress.org</span>', $html);
        $this->assertStringContainsString('<span dir="auto">Unknown page</span>', $html);
        $this->assertStringContainsString('<span dir="auto">example.com</span>', $html);
    }

    public function test_journey_arrow_follows_reading_direction()
    {
        $this->assertStringContainsString('<span aria-hidden="true">→</span>', $this->journey());

        $GLOBALS['wp_locale']->text_direction = 'rtl';
        try {
            $this->assertStringContainsString('<span aria-hidden="true">←</span>', $this->journey());
        } finally {
            $GLOBALS['wp_locale']->text_direction = 'ltr';
        }
    }

    public function test_empty_state_sentences_are_isolated()
    {
        $html = (string)View::load('components/objects/empty-state', ['message' => 'No data found for this date range.', 'range' => 'Nov 1 - Nov 7'], true);

        $this->assertStringContainsString('<p class="wps-empty-state__message" dir="auto">', $html);
        $this->assertStringContainsString('<p class="wps-empty-state__range" dir="auto">', $html);
    }
}
