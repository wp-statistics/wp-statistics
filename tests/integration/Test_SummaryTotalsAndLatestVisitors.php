<?php

use WP_Statistics\Components\DateTime;
use WP_Statistics\Models\VisitorsModel;
use WP_Statistics\Service\Admin\Metabox\MetaboxDataProvider;
use WP_Statistics\Service\Admin\Referrals\ReferralsDataProvider;
use WP_Statistics\Service\Charts\ChartDataProviderFactory;
use WP_Statistics\Service\Charts\DataProvider\SummaryChartDataProvider;
use WP_Statistics\Service\Database\Managers\TableHandler;
use WP_Statistics\Service\Database\Schema\Manager;
use WP_STATISTICS\DB;

/**
 * Traffic Summary totals (historical only without filters), the Referred Traffic card,
 * and latest-visitor lists in time order.
 */
class Test_SummaryTotalsAndLatestVisitors extends WP_UnitTestCase
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

    private function today()
    {
        return DateTime::get();
    }

    private function addVisitor($data = [])
    {
        global $wpdb;

        static $n = 0;
        $n++;

        $day = $data['last_counter'] ?? $this->today();

        $wpdb->insert(DB::table('visitor'), array_merge([
            'last_counter'   => $day,
            'referred'       => '',
            'agent'          => 'Chrome',
            'platform'       => 'Windows',
            'version'        => '1',
            'ip'             => '192.0.2.' . $n,
            'user_id'        => 0,
            'hits'           => 1,
            'location'       => 'DE',
            'source_channel' => 'direct',
            'first_view'     => $day . ' 08:00:00',
            'last_view'      => $day . ' 08:00:00',
        ], $data));

        return $wpdb->insert_id;
    }

    private function addHistorical()
    {
        global $wpdb;

        $wpdb->insert(DB::table('historical'), ['category' => 'visitors', 'page_id' => -1, 'uri' => '-1', 'value' => 1000]);
        $wpdb->insert(DB::table('historical'), ['category' => 'visits', 'page_id' => -2, 'uri' => '-2', 'value' => 5000]);
    }

    private function summary($args)
    {
        return ChartDataProviderFactory::summaryChart(array_merge($args, ['include_total' => true]))->getData();
    }

    private function ids($visitors)
    {
        return array_map(function ($visitor) {
            return intval($visitor->getId());
        }, $visitors);
    }

    public function test_site_wide_total_adds_historical()
    {
        $this->addHistorical();
        $this->addVisitor(['hits' => 3]);
        $this->addVisitor(['hits' => 2, 'location' => 'PL']);

        $total = $this->summary([])['total']['data']['current'];

        $this->assertSame(1002, intval($total['visitors']));
        $this->assertSame(5005, intval($total['views']));
    }

    public function test_country_total_skips_site_wide_historical()
    {
        $this->addHistorical();
        $this->addVisitor(['hits' => 3]);
        $this->addVisitor(['hits' => 2, 'location' => 'PL']);

        $total = $this->summary(['country' => 'PL'])['total']['data']['current'];

        $this->assertSame(1, intval($total['visitors']));
        $this->assertSame(2, intval($total['views']));
    }

    public function test_post_type_total_skips_site_wide_historical()
    {
        global $wpdb;

        $this->addHistorical();

        $postId    = self::factory()->post->create();
        $visitorId = $this->addVisitor();

        $wpdb->insert(DB::table('pages'), ['uri' => '/p/', 'type' => 'post', 'date' => $this->today(), 'count' => 4, 'id' => $postId]);
        $wpdb->insert(DB::table('visitor_relationships'), ['visitor_id' => $visitorId, 'page_id' => $wpdb->insert_id, 'date' => $this->today() . ' 10:00:00']);

        $total = $this->summary(['post_type' => 'post'])['total']['data']['current'];

        $this->assertSame(1, intval($total['visitors']));
        $this->assertSame(4, intval($total['views']));
    }

    public function test_historical_rule_per_filter()
    {
        $method = new ReflectionMethod(SummaryChartDataProvider::class, 'canIncludeHistorical');
        if (PHP_VERSION_ID < 80100) {
            $method->setAccessible(true);
        }

        $cases = [
            [[], true],
            [['include_total' => true, 'date' => ['from' => '2026-01-01', 'to' => '2026-01-02']], true],
            [['resource_id' => 12, 'resource_type' => ['post', 'page']], true],
            [['query_param' => '/about/', 'hide_post' => true], true],
            [['post_type' => 'post'], false],
            [['country' => 'PL'], false],
            [['referred_visitors' => true], false],
            [['resource_id' => 12, 'country' => 'PL'], false],
        ];

        foreach ($cases as [$args, $expected]) {
            $this->assertSame($expected, (bool) $method->invoke(new SummaryChartDataProvider($args)), wp_json_encode($args));
        }
    }

    public function test_referred_traffic_counts_referred_visitors_and_their_views()
    {
        $this->addHistorical();
        $yesterday = DateTime::get('-1 day');

        // Today: 2 referred (one by referrer, one by a non-direct channel), 1 direct
        $this->addVisitor(['referred' => 'https://example.org/', 'source_channel' => 'referral', 'hits' => 3]);
        $this->addVisitor(['source_channel' => 'paid_search', 'hits' => 2]);
        $this->addVisitor(['hits' => 7]);

        // Yesterday: 1 referred, 1 direct
        $this->addVisitor(['last_counter' => $yesterday, 'referred' => 'https://example.net/', 'source_channel' => 'social', 'hits' => 4]);
        $this->addVisitor(['last_counter' => $yesterday, 'hits' => 9]);

        $data = (new ReferralsDataProvider([]))->getReferralsOverview()['traffic_summary'];

        $this->assertSame(2, intval($data['today']['data']['current']['visitors']));
        $this->assertSame(5, intval($data['today']['data']['current']['views']));

        $this->assertSame(1, intval($data['yesterday']['data']['current']['visitors']));
        $this->assertSame(4, intval($data['yesterday']['data']['current']['views']));

        // Total: referred rows only, no site-wide historical numbers
        $this->assertSame(3, intval($data['total']['data']['current']['visitors']));
        $this->assertSame(9, intval($data['total']['data']['current']['views']));
    }

    public function test_count_visitors_supports_referred_visitors()
    {
        $this->addVisitor(['referred' => 'https://example.org/', 'source_channel' => 'referral']);
        $this->addVisitor();

        $model = new VisitorsModel();

        $this->assertSame(1, $model->countVisitors(['date' => ['from' => $this->today(), 'to' => $this->today()], 'referred_visitors' => true]));
        $this->assertSame(2, $model->countVisitors(['date' => ['from' => $this->today(), 'to' => $this->today()]]));
    }

    public function test_latest_visitors_follow_last_view_not_id()
    {
        $day       = $this->today();
        $yesterday = DateTime::get('-1 day');

        $old     = $this->addVisitor(['last_counter' => $yesterday, 'last_view' => $yesterday . ' 23:59:00']);
        $morning = $this->addVisitor(['last_view' => $day . ' 17:00:00']); // came at 8, back at 17:00
        $noon    = $this->addVisitor(['last_view' => $day . ' 12:00:00']);
        $tieA    = $this->addVisitor(['last_view' => $day . ' 09:00:00']);
        $tieB    = $this->addVisitor(['last_view' => $day . ' 09:00:00']);

        $ids = $this->ids((new MetaboxDataProvider())->getLatestVisitorsData(['ignore_date' => true]));

        $this->assertSame([$morning, $noon, $tieB, $tieA, $old], $ids);
    }

    public function test_latest_visitors_since_bound_keeps_the_right_rows()
    {
        $day = $this->today();

        // Twelve rows over three days; the newest ID is not the latest view
        $expected = [];
        foreach ([DateTime::get('-2 days'), DateTime::get('-1 day')] as $older) {
            $this->addVisitor(['last_counter' => $older, 'last_view' => $older . ' 22:00:00']);
        }
        for ($i = 0; $i < 10; $i++) {
            $expected[$i] = $this->addVisitor(['last_view' => sprintf('%s 10:%02d:00', $day, $i)]);
        }

        $model = new VisitorsModel();

        $this->assertSame($day, $model->getLatestVisitorsSince(10));
        $this->assertSame(DateTime::get('-1 day'), $model->getLatestVisitorsSince(11));

        $ids = $this->ids((new MetaboxDataProvider())->getLatestVisitorsData(['ignore_date' => true]));

        $this->assertSame(array_reverse($expected), $ids);
    }

    public function test_visitors_tab_and_referred_visitors_are_in_time_order()
    {
        $day = $this->today();

        $first  = $this->addVisitor(['referred' => 'https://a.example/', 'source_channel' => 'referral', 'last_view' => $day . ' 18:00:00']);
        $second = $this->addVisitor(['referred' => 'https://b.example/', 'source_channel' => 'referral', 'last_view' => $day . ' 11:00:00']);
        $third  = $this->addVisitor(['referred' => 'https://c.example/', 'source_channel' => 'referral', 'last_view' => $day . ' 14:00:00']);

        $range = ['from' => $day, 'to' => $day];

        $visitors = (new \WP_Statistics\Service\Admin\VisitorInsights\VisitorInsightsDataProvider(['date' => $range]))->getVisitorsData();
        $this->assertSame([$first, $third, $second], $this->ids($visitors['data']));

        $referred = (new ReferralsDataProvider(['date' => $range]))->getReferredVisitors();
        $this->assertSame([$first, $third, $second], $this->ids($referred['visitors']));

        $latest = (new ReferralsDataProvider(['date' => $range]))->getReferralsOverview()['visitors'];
        $this->assertSame([$first, $third, $second], $this->ids($latest));
    }

    public function test_paged_latest_list_is_bounded_to_its_days_and_keeps_the_order()
    {
        // Four days, rows inserted out of time order within each day
        $expected = [];
        foreach ([3, 2, 1, 0] as $daysAgo) {
            $day  = DateTime::get("-{$daysAgo} days");
            $rows = [];
            foreach (['09:00:00', '18:00:00', '12:00:00'] as $time) {
                $rows[$time] = $this->addVisitor(['last_counter' => $day, 'last_view' => "{$day} {$time}"]);
            }
            krsort($rows);
            $expected = array_merge(array_values($rows), $expected);
        }

        $model = new VisitorsModel();
        $range = ['from' => DateTime::get('-10 days'), 'to' => $this->today()];
        $all   = [];

        $queries = [];
        $catch   = function ($query) use (&$queries) {
            $queries[] = $query;
            return $query;
        };
        add_filter('query', $catch);

        for ($page = 1; $page <= 3; $page++) {
            $all = array_merge($all, $this->ids($model->getVisitorsData([
                'date'     => $range,
                'order_by' => VisitorsModel::LATEST_ORDER_BY,
                'order'    => 'DESC',
                'page'     => $page,
                'per_page' => 4,
            ])));
        }

        remove_filter('query', $catch);

        $this->assertSame($expected, $all, 'same order as one unbounded sort, page after page');

        // Page 1 (4 rows) only needs the last two days
        $bounded = array_values(array_filter($queries, function ($query) {
            return strpos($query, 'last_view') !== false && strpos($query, 'ORDER BY') !== false && strpos($query, 'LIMIT 4 OFFSET 0') !== false;
        }));
        $this->assertNotEmpty($bounded);
        $this->assertStringContainsString("last_counter` >= '" . DateTime::get('-1 day') . "'", str_replace('visitor.last_counter', '`last_counter`', $bounded[0]));
    }

    public function test_paged_referred_visitors_are_bounded_to_their_days_and_keep_the_order()
    {
        // Four days of referred rows inserted out of time order, plus direct and other-source rows
        // that the filters must keep out even when they are the newest of their day
        $expected = [];
        foreach ([3, 2, 1, 0] as $daysAgo) {
            $day  = DateTime::get("-{$daysAgo} days");
            $rows = [];
            foreach (['09:00:00', '18:00:00', '12:00:00'] as $time) {
                $rows[$time] = $this->addVisitor(['last_counter' => $day, 'last_view' => "{$day} {$time}", 'referred' => 'https://a.example/', 'source_channel' => 'referral', 'source_name' => 'a.example']);
            }
            $this->addVisitor(['last_counter' => $day, 'last_view' => "{$day} 23:00:00"]);
            $this->addVisitor(['last_counter' => $day, 'last_view' => "{$day} 22:00:00", 'referred' => 'https://b.example/', 'source_channel' => 'referral', 'source_name' => 'b.example']);
            krsort($rows);
            $expected = array_merge(array_values($rows), $expected);
        }

        $model = new VisitorsModel();
        $range = ['from' => DateTime::get('-10 days'), 'to' => $this->today()];
        $args  = ['date' => $range, 'order_by' => VisitorsModel::LATEST_ORDER_BY, 'source_name' => 'a.example'];

        // One full sort, no paging, so no bound
        $full = $this->ids($model->getReferredVisitors($args));
        $this->assertSame($expected, $full);

        $queries = [];
        $catch   = function ($query) use (&$queries) {
            $queries[] = $query;
            return $query;
        };
        add_filter('query', $catch);

        $paged = [];
        for ($page = 1; $page <= 3; $page++) {
            $paged = array_merge($paged, $this->ids($model->getReferredVisitors(array_merge($args, [
                'page'     => $page,
                'per_page' => 4,
            ]))));
        }

        remove_filter('query', $catch);

        $this->assertSame($full, $paged, 'paged order equals one full sort');

        // Page 1 (4 rows) only needs the last two days
        $bounded = array_values(array_filter($queries, function ($query) {
            return strpos($query, 'last_view') !== false && strpos($query, 'ORDER BY') !== false && strpos($query, 'LIMIT 4 OFFSET 0') !== false;
        }));
        $this->assertNotEmpty($bounded);
        $this->assertStringContainsString("last_counter` >= '" . DateTime::get('-1 day') . "'", str_replace('visitor.last_counter', '`last_counter`', $bounded[0]));

        // A page past the end still returns nothing, and the provider list keeps working
        $this->assertSame([], $this->ids($model->getReferredVisitors(array_merge($args, ['page' => 4, 'per_page' => 4]))));
        $this->assertSame(array_slice($expected, 0, 5), $this->ids((new ReferralsDataProvider(['date' => $range, 'source_name' => 'a.example']))->getReferralsOverview()['visitors']));
    }
}
