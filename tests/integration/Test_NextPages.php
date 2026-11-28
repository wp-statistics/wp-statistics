<?php

use WP_Statistics\Models\ViewsModel;
use WP_Statistics\Service\Database\Managers\TableHandler;
use WP_Statistics\Service\Database\Schema\Manager;
use WP_STATISTICS\DB;

/**
 * "Where readers go next" card: ViewsModel::getNextPages().
 */
class Test_NextPages extends WP_UnitTestCase
{
    private static $tables = ['pages', 'visitor_relationships'];

    private $range = ['from' => '2026-05-01', 'to' => '2026-05-31'];

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

    private function addPage($id, $uri, $type = 'post', $date = '2026-05-10')
    {
        global $wpdb;
        $wpdb->insert(DB::table('pages'), ['id' => $id, 'uri' => $uri, 'type' => $type, 'count' => 1, 'date' => $date]);

        return $wpdb->insert_id;
    }

    private function addView($visitorId, $pageId, $date)
    {
        global $wpdb;
        $wpdb->insert(DB::table('visitor_relationships'), ['visitor_id' => $visitorId, 'page_id' => $pageId, 'date' => $date]);
    }

    private function nextPages($args = [])
    {
        return (new ViewsModel())->getNextPages(array_merge([
            'resource_id'   => 100,
            'resource_type' => ['post', 'page'],
            'date'          => $this->range,
        ], $args));
    }

    public function test_counts_visitors_per_next_page_and_readers_who_left()
    {
        $post    = $this->addPage(100, '/post/');
        $postDay = $this->addPage(100, '/post/', 'post', '2026-05-11'); // same post, another day row
        $a       = $this->addPage(200, '/a/');
        $b       = $this->addPage(300, '/b/', 'page');
        $home    = $this->addPage(0, '/', 'home');

        // 1: post -> A
        $this->addView(1, $post, '2026-05-10 10:00:00');
        $this->addView(1, $a, '2026-05-10 10:01:00');
        // 2: B -> post -> A (only the view after the post counts)
        $this->addView(2, $b, '2026-05-10 11:00:00');
        $this->addView(2, $postDay, '2026-05-11 11:01:00');
        $this->addView(2, $a, '2026-05-11 11:02:00');
        // 3: post -> post (reload) -> B
        $this->addView(3, $post, '2026-05-10 12:00:00');
        $this->addView(3, $post, '2026-05-10 12:00:30');
        $this->addView(3, $b, '2026-05-10 12:01:00');
        // 4: post, then nothing: left
        $this->addView(4, $post, '2026-05-10 13:00:00');
        // 5: post -> home -> post, then nothing: left after this page
        $this->addView(5, $post, '2026-05-10 14:00:00');
        $this->addView(5, $home, '2026-05-10 14:01:00');
        $this->addView(5, $post, '2026-05-10 14:02:00');
        // 6: post -> A twice in one visit counts once
        $this->addView(6, $post, '2026-05-10 15:00:00');
        $this->addView(6, $a, '2026-05-10 15:01:00');
        $this->addView(6, $post, '2026-05-10 15:02:00');
        $this->addView(6, $a, '2026-05-10 15:03:00');
        // 7: never viewed the post
        $this->addView(7, $b, '2026-05-10 16:00:00');
        $this->addView(7, $a, '2026-05-10 16:01:00');
        // 8: outside the date range
        $this->addView(8, $post, '2026-06-02 10:00:00');
        $this->addView(8, $b, '2026-06-02 10:01:00');

        $result = $this->nextPages();

        $this->assertSame(6, $result['readers']);
        $this->assertSame(2, $result['left']);
        $this->assertSame(33.3, $result['left_rate']);

        $rows = array_map(function ($row) {
            return [$row->page_id, $row->visitors];
        }, $result['pages']);

        $this->assertSame([[$a, 3], [$b, 1], [$home, 1]], $rows);
    }

    public function test_per_page_limits_the_list()
    {
        $post = $this->addPage(100, '/post/');

        for ($i = 1; $i <= 5; $i++) {
            $next = $this->addPage(200 + $i, '/p' . $i . '/');
            // Page i is opened next by i visitors
            for ($v = 1; $v <= $i; $v++) {
                $visitor = $i * 10 + $v;
                $this->addView($visitor, $post, '2026-05-10 10:00:00');
                $this->addView($visitor, $next, '2026-05-10 10:01:00');
            }
        }

        $top3 = $this->nextPages(['per_page' => 3]);
        $this->assertCount(3, $top3['pages']);
        $this->assertSame([5, 4, 3], array_map(function ($row) {
            return $row->visitors;
        }, $top3['pages']));

        $this->assertCount(5, $this->nextPages(['per_page' => 10])['pages']);
    }

    public function test_no_views_gives_empty_result()
    {
        $this->addPage(100, '/post/');

        $result = $this->nextPages();

        $this->assertSame(0, $result['readers']);
        $this->assertSame([], $result['pages']);
        $this->assertSame([], $this->nextPages(['resource_id' => 0])['pages']);
    }

    public function test_locked_cards_show_while_add_ons_are_not_active()
    {
        $manager = new \WP_Statistics\Service\Admin\ContentAnalytics\ContentAnalyticsManager();

        ob_start();
        $manager->renderCampaignsLockedCard();
        $manager->renderClicksLockedCard();
        $html = ob_get_clean();

        $this->assertStringContainsString('Campaigns that brought visitors here', $html);
        $this->assertStringContainsString('data-target="wp-statistics-marketing" data-surface="single-content-campaigns"', $html);
        $this->assertStringContainsString('Clicks and downloads on this post', $html);
        $this->assertStringContainsString('data-target="wp-statistics-data-plus" data-surface="single-content-clicks"', $html);

        $this->assertSame(10, has_action('wp_statistics_single_content_widgets', [$manager, 'renderCampaignsLockedCard']));
        $this->assertSame(20, has_action('wp_statistics_single_content_widgets', [$manager, 'renderClicksLockedCard']));
        $this->assertSame(30, has_action('wp_statistics_single_content_widgets', [$manager, 'renderNextPagesCard']));
    }

    public function test_free_card_shows_top_three_and_the_lock_line()
    {
        $post = $this->addPage(100, '/post/');
        $next = $this->addPage(200, '/a/');
        $this->addView(1, $post, '2026-05-10 10:00:00');
        $this->addView(1, $next, '2026-05-10 10:01:00');
        $this->addView(2, $post, '2026-05-10 11:00:00');

        ob_start();
        \WP_Statistics\Components\View::load('components/tables/next-pages', array_merge($this->nextPages(), ['unlocked' => false]));
        $html = ob_get_clean();

        $this->assertStringContainsString('Where readers go next', $html);
        $this->assertStringContainsString('of readers left after this page', $html);
        $this->assertStringContainsString('50.0%', $html);
        $this->assertStringContainsString('data-surface="next-pages"', $html);
    }
}
