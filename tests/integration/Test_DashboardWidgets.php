<?php

use WP_Statistics\Components\DateTime;
use WP_Statistics\Components\View;
use WP_Statistics\Decorators\VisitorDecorator;
use WP_Statistics\Service\Admin\Metabox\MetaboxDataProvider;
use WP_Statistics\Service\Admin\Metabox\MetaboxHelper;
use WP_Statistics\Service\Database\Managers\TableHandler;
use WP_Statistics\Service\Database\Schema\Manager;
use WP_STATISTICS\DB;

/**
 * WP Dashboard widgets, compact set: 5 rows fetched by the query (not 10 hidden),
 * Most Active grouped by user, share totals, short one-line times.
 */
class Test_DashboardWidgets extends WP_UnitTestCase
{
    private static $tables = ['visitor', 'pages', 'visitor_relationships', 'historical'];

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

    private function day($offset = 0)
    {
        return DateTime::get($offset ? "{$offset} days" : 'now');
    }

    private function range()
    {
        return ['from' => $this->day(-29), 'to' => $this->day()];
    }

    private function addVisitor($data = [])
    {
        global $wpdb;

        static $n = 0;
        $n++;

        $day  = $data['last_counter'] ?? $this->day();
        $time = $data['time'] ?? sprintf('08:%02d:00', $n % 60);
        unset($data['time']);

        $wpdb->insert(DB::table('visitor'), array_merge([
            'last_counter'   => $day,
            'referred'       => '',
            'agent'          => 'Chrome',
            'platform'       => 'Windows',
            'version'        => '1',
            'ip'             => '192.0.2.' . ($n % 250),
            'user_id'        => 0,
            'hits'           => 1,
            'location'       => 'DE',
            'source_channel' => 'direct',
            'first_view'     => "{$day} {$time}",
            'last_view'      => "{$day} {$time}",
        ], $data));

        return $wpdb->insert_id;
    }

    public function test_dashboard_rows_constant_is_five()
    {
        $this->assertSame(5, MetaboxHelper::DASHBOARD_ROWS);
    }

    public function test_latest_visitors_query_is_limited_to_five()
    {
        global $wpdb;

        for ($i = 0; $i < 8; $i++) {
            $this->addVisitor();
        }

        $provider = new MetaboxDataProvider();

        $rows = $provider->getLatestVisitorsData(['ignore_date' => true], MetaboxHelper::DASHBOARD_ROWS);
        $this->assertCount(5, $rows);
        $this->assertStringContainsString('LIMIT 5', $wpdb->last_query);

        // Default (Overview, Widgets add-on) keeps 10 rows.
        $this->assertCount(8, $provider->getLatestVisitorsData(['ignore_date' => true]));
        $this->assertStringContainsString('LIMIT 10', $wpdb->last_query);
    }

    public function test_top_pages_share_is_limited_to_five_with_total()
    {
        global $wpdb;

        $views = [50, 40, 30, 20, 10, 5, 3];
        foreach ($views as $i => $count) {
            $wpdb->insert(DB::table('pages'), ['uri' => "/p{$i}/", 'type' => 'page', 'date' => $this->day(), 'count' => $count, 'id' => 1000 + $i]);
        }

        $data = (new MetaboxDataProvider())->getTopPagesShare(['date' => $this->range()], MetaboxHelper::DASHBOARD_ROWS);

        $this->assertCount(5, $data['rows']);
        $this->assertSame([50, 40, 30, 20, 10], array_map('intval', wp_list_pluck($data['rows'], 'views')));
        $this->assertSame(array_sum($views), $data['total']);
    }

    public function test_top_pages_share_empty_range()
    {
        $data = (new MetaboxDataProvider())->getTopPagesShare(['date' => $this->range()]);

        $this->assertSame(['rows' => [], 'total' => 0], $data);
    }

    public function test_referrers_share_is_limited_to_five_with_total()
    {
        $domains = ['google.com' => 6, 'bing.com' => 5, 'facebook.com' => 4, 'linkedin.com' => 3, 'duckduckgo.com' => 2, 'yahoo.com' => 1];
        foreach ($domains as $domain => $visitors) {
            for ($i = 0; $i < $visitors; $i++) {
                $this->addVisitor(['referred' => "https://{$domain}/"]);
            }
        }

        $data = (new MetaboxDataProvider())->getReferrersShare(['date' => $this->range()], MetaboxHelper::DASHBOARD_ROWS);

        $this->assertCount(5, $data['rows']);
        $this->assertSame(21, $data['total']);
        $this->assertSame(6, $data['rows'][0]->getTotalReferrals(true));
    }

    public function test_most_active_groups_rows_of_the_same_user()
    {
        $userId = self::factory()->user->create(['display_name' => 'Admin']);

        // Same user on three days: 30 + 20 + 14 = 64 views, 3 visits; newest row last.
        $this->addVisitor(['user_id' => $userId, 'hits' => 30, 'last_counter' => $this->day(-3)]);
        $this->addVisitor(['user_id' => $userId, 'hits' => 20, 'last_counter' => $this->day(-2)]);
        $latest = $this->addVisitor(['user_id' => $userId, 'hits' => 14, 'last_counter' => $this->day(-1)]);

        // Guests: one row each, never merged.
        $this->addVisitor(['hits' => 40]);
        $this->addVisitor(['hits' => 13]);
        $this->addVisitor(['hits' => 13]);

        $rows = (new MetaboxDataProvider())->getTopVisitorsGroupedData(['date' => $this->range()], MetaboxHelper::DASHBOARD_ROWS);

        $this->assertCount(4, $rows);
        $this->assertContainsOnlyInstancesOf(VisitorDecorator::class, $rows);

        $first = $rows[0];
        $this->assertSame($latest, intval($first->getId()), 'The newest row of the user is shown.');
        $this->assertSame(64, $first->getHits(true));
        $this->assertSame(3, $first->getVisits());

        $this->assertSame(40, $rows[1]->getHits(true));
        $this->assertSame(1, $rows[1]->getVisits());

        $userRows = array_filter($rows, function ($row) use ($userId) {
            return intval($row->getUserId()) === $userId;
        });
        $this->assertCount(1, $userRows, 'The same user is listed once.');
    }

    public function test_most_active_is_limited_to_five_groups()
    {
        global $wpdb;

        $userId = self::factory()->user->create();
        for ($i = 1; $i <= 4; $i++) {
            $this->addVisitor(['user_id' => $userId, 'hits' => 2, 'last_counter' => $this->day(-$i)]);
        }
        for ($i = 0; $i < 9; $i++) {
            $this->addVisitor(['hits' => 1]);
        }

        $rows = (new MetaboxDataProvider())->getTopVisitorsGroupedData(['date' => $this->range()], 5);

        $this->assertCount(5, $rows);
        $this->assertSame(8, $rows[0]->getHits(true));
        $this->assertSame(4, $rows[0]->getVisits());
    }

    public function test_most_active_respects_the_date_range()
    {
        $this->addVisitor(['hits' => 99, 'last_counter' => $this->day(-60)]);

        $rows = (new MetaboxDataProvider())->getTopVisitorsGroupedData(['date' => $this->range()]);

        $this->assertSame([], $rows);
    }

    public function test_last_view_compact_is_time_today_and_date_before()
    {
        update_option('date_format', 'F j, Y');
        update_option('time_format', 'g:i a');

        $today = new VisitorDecorator((object) ['last_view' => $this->day() . ' 07:24:00']);
        $this->assertSame('7:24 am', $today->getLastViewCompact());

        $before   = DateTime::get('-3 days');
        $expected = date_i18n('M j', strtotime($before));
        $old      = new VisitorDecorator((object) ['last_view' => $before . ' 19:41:00']);

        if (substr($before, 0, 4) === DateTime::get('now', 'Y')) {
            $this->assertSame($expected, $old->getLastViewCompact());
        }

        $lastYear = new VisitorDecorator((object) ['last_view' => (intval(DateTime::get('now', 'Y')) - 1) . '-03-04 10:00:00']);
        $this->assertSame('Mar 4, ' . (intval(DateTime::get('now', 'Y')) - 1), $lastYear->getLastViewCompact());
    }

    public function test_compact_visitor_list_view_renders_rows_and_empty_line()
    {
        for ($i = 0; $i < 3; $i++) {
            $this->addVisitor(['hits' => 1200]);
        }

        $rows = (new MetaboxDataProvider())->getLatestVisitorsData(['ignore_date' => true], 5);
        $html = View::load('metabox/dashboard/visitors-list', ['data' => $rows, 'mode' => 'latest'], true);

        $this->assertSame(3, substr_count($html, '<tr>') - 1, 'Three body rows, no blank filler rows.');
        $this->assertStringContainsString('wps-visitor-table__last', $html, 'shared visitor table');
        $this->assertStringContainsString(number_format_i18n(1200), $html);

        $empty = View::load('metabox/dashboard/visitors-list', [
            'data'  => [],
            'mode'  => 'online',
            'empty' => ['title' => 'No one online right now', 'hint' => 'Visitors from the last 5 minutes show here.'],
        ], true);

        $this->assertStringContainsString('wps-dash-empty', $empty);
        $this->assertStringContainsString('No one online right now', $empty);
        $this->assertStringNotContainsString('<table', $empty);
    }

    public function test_rank_list_view_prints_share_of_total()
    {
        global $wpdb;

        $wpdb->insert(DB::table('pages'), ['uri' => '/a/', 'type' => 'page', 'date' => $this->day(), 'count' => 75, 'id' => 2001]);
        $wpdb->insert(DB::table('pages'), ['uri' => '/b/', 'type' => 'page', 'date' => $this->day(), 'count' => 25, 'id' => 2002]);

        $html = View::load('metabox/dashboard/rank-list', [
            'data' => (new MetaboxDataProvider())->getTopPagesShare(['date' => $this->range()]),
            'type' => 'pages',
            'date' => $this->range(),
        ], true);

        // Same share rows as Top Countries: track under each row, width = share of the total.
        $this->assertStringContainsString('wps-horizontal-bar wps-share-list', $html);
        $this->assertSame(2, substr_count($html, 'class="wps-horizontal-bar__item"'));
        $this->assertStringContainsString('style="width:75%"', $html);
        $this->assertStringContainsString('style="width:25%"', $html);
        $this->assertStringContainsString('75%', $html);
        $this->assertStringContainsString('25%', $html);
    }
}
