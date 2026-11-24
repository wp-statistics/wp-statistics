<?php

use WP_Statistics\Components\DateTime;
use WP_Statistics\Exception\SystemErrorException;
use WP_Statistics\Models\HistoricalModel;
use WP_Statistics\Models\PostsModel;
use WP_Statistics\Models\VisitorsModel;
use WP_Statistics\Service\Admin\VisitorInsights\VisitorInsightsDataProvider;
use WP_Statistics\Service\Database\Managers\TableHandler;
use WP_Statistics\Service\Database\Schema\Manager;
use WP_STATISTICS\DB;

/**
 * Report lists and their totals must use the same filters.
 */
class Test_ReportTotals extends WP_UnitTestCase
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

    private function range()
    {
        return ['from' => DateTime::get('-7 days'), 'to' => $this->today()];
    }

    private function addVisitor($data)
    {
        global $wpdb;

        static $n = 0;
        $n++;

        $wpdb->insert(DB::table('visitor'), array_merge([
            'last_counter'   => $this->today(),
            'referred'       => '',
            'agent'          => 'Chrome',
            'platform'       => 'Windows',
            'version'        => (string)$n,
            'ip'             => '192.0.2.' . $n,
            'user_id'        => 0,
            'hits'           => 1,
            'source_channel' => 'direct',
        ], $data));

        return $wpdb->insert_id;
    }

    private function addPage($data)
    {
        global $wpdb;

        $wpdb->insert(DB::table('pages'), array_merge([
            'uri'   => '/',
            'type'  => 'post',
            'date'  => $this->today(),
            'count' => 1,
            'id'    => 0,
        ], $data));

        return $wpdb->insert_id;
    }

    public function test_visitors_total_uses_source_channel_filter()
    {
        $this->addVisitor(['source_channel' => 'direct']);
        $this->addVisitor(['source_channel' => 'direct']);
        $this->addVisitor(['source_channel' => 'search']);

        $model = new VisitorsModel();
        $args  = ['date' => $this->range(), 'source_channel' => 'search'];

        $this->assertCount(1, $model->getVisitorsData($args));
        $this->assertSame(1, $model->countVisitors($args));
    }

    public function test_unassigned_source_channel_lists_visitors_without_channel()
    {
        $this->addVisitor(['source_channel' => null]);
        $this->addVisitor(['source_channel' => null]);
        $this->addVisitor(['source_channel' => 'search']);

        $model = new VisitorsModel();
        $args  = ['date' => $this->range(), 'source_channel' => 'unassigned'];

        $this->assertCount(2, $model->getVisitorsData($args));
        $this->assertSame(2, $model->countVisitors($args));
    }

    public function test_unassigned_source_channel_stays_inside_date_range()
    {
        $this->addVisitor(['source_channel' => null]);
        $this->addVisitor(['source_channel' => '']);
        // Outside the range: must not be counted, with NULL or empty channel
        $this->addVisitor(['source_channel' => null, 'last_counter' => DateTime::get('-30 days')]);
        $this->addVisitor(['source_channel' => '', 'last_counter' => DateTime::get('-30 days')]);

        $model = new VisitorsModel();
        $args  = ['date' => $this->range(), 'source_channel' => 'unassigned'];

        $this->assertCount(2, $model->getVisitorsData($args));
        $this->assertSame(2, $model->countVisitors($args));
    }

    public function test_visitors_total_uses_partial_ip_like_the_list()
    {
        $this->addVisitor(['ip' => '198.51.100.1']);
        $this->addVisitor(['ip' => '198.51.100.12']);
        $this->addVisitor(['ip' => '203.0.113.5']);

        $model = new VisitorsModel();
        $args  = ['date' => $this->range(), 'ip' => '198.51.100'];

        $this->assertCount(2, $model->getVisitorsData($args));
        $this->assertSame(2, $model->countVisitors($args));
    }

    public function test_exact_ip_does_not_match_longer_ips()
    {
        $this->addVisitor(['ip' => '198.51.100.1']);
        $this->addVisitor(['ip' => '198.51.100.12']);

        $model = new VisitorsModel();
        $rows  = $model->getVisitorsData(['ignore_date' => true, 'ip' => '198.51.100.1', 'exact_ip' => true]);

        $this->assertCount(1, $rows);
    }

    public function test_exact_ip_list_and_total_agree()
    {
        $this->addVisitor(['ip' => '1.2.3.4']);
        $this->addVisitor(['ip' => '11.2.3.4']);
        $this->addVisitor(['ip' => '1.2.3.45']);

        $model = new VisitorsModel();
        $exact = ['date' => $this->range(), 'ip' => '1.2.3.4', 'exact_ip' => true];

        $rows = $model->getVisitorsData($exact);
        $this->assertCount(1, $rows);
        $this->assertSame(1, $model->countVisitors($exact));

        // Without exact_ip the same value matches every IP containing it
        $partial = ['date' => $this->range(), 'ip' => '1.2.3.4'];
        $this->assertCount(3, $model->getVisitorsData($partial));
        $this->assertSame(3, $model->countVisitors($partial));
    }

    public function test_visitors_tab_reads_exact_ip_from_the_link()
    {
        $this->addVisitor(['ip' => '1.2.3.4']);
        $this->addVisitor(['ip' => '11.2.3.4']);

        $_REQUEST['ip']       = $_GET['ip'] = '1.2.3.4';
        $_REQUEST['exact_ip'] = $_GET['exact_ip'] = '1';

        try {
            $view = new \WP_Statistics\Service\Admin\VisitorInsights\Views\TabsView();
            $data = $view->getVisitorsData();
        } finally {
            unset($_REQUEST['ip'], $_GET['ip'], $_REQUEST['exact_ip'], $_GET['exact_ip']);
        }

        $this->assertCount(1, $data['data']);
        $this->assertSame(1, $data['total']);
    }

    public function test_single_visitor_data_is_empty_for_missing_visitor()
    {
        $provider = new VisitorInsightsDataProvider(['visitor_id' => 99999999]);
        $data     = $provider->getVisitorData();

        $this->assertNull($data['visitor']);
    }

    public function test_user_role_filter_uses_site_table_prefix()
    {
        $editor = self::factory()->user->create(['role' => 'editor']);
        $author = self::factory()->user->create(['role' => 'author']);

        $this->addVisitor(['user_id' => $editor]);
        $this->addVisitor(['user_id' => $author]);

        $model = new VisitorsModel();
        $args  = ['date' => $this->range(), 'logged_in' => true, 'user_role' => 'editor'];

        $this->assertSame(1, $model->countVisitors($args));
        $this->assertCount(1, $model->getVisitorsData($args));
    }

    public function test_referrers_total_counts_referrer_and_channel_pairs()
    {
        $this->addVisitor(['referred' => 'search.example', 'source_channel' => 'search']);
        $this->addVisitor(['referred' => 'search.example', 'source_channel' => 'paid_search']);
        $this->addVisitor(['referred' => 'other.example', 'source_channel' => 'referral']);

        $model   = new VisitorsModel();
        $args    = ['date' => $this->range()];
        $grouped = array_merge($args, ['group_by' => ['visitor.referred', 'visitor.source_channel']]);

        $this->assertCount(3, $model->getReferrers(array_merge($grouped, ['per_page' => 25])));
        $this->assertEquals(3, $model->countReferrers($grouped));

        // Without grouping it still counts distinct referrers
        $this->assertEquals(2, $model->countReferrers($args));

        // The referrer search applies to the total too
        $this->assertEquals(1, $model->countReferrers(array_merge($args, ['referrer' => 'other'])));
    }

    public function test_entry_pages_total_uses_source_channel_filter()
    {
        $postA = self::factory()->post->create();
        $postB = self::factory()->post->create();
        $pageA = $this->addPage(['id' => $postA, 'uri' => '/a/']);
        $pageB = $this->addPage(['id' => $postB, 'uri' => '/b/']);

        $this->addVisitor(['first_page' => $pageA, 'source_channel' => 'search']);
        $this->addVisitor(['first_page' => $pageB, 'source_channel' => 'direct']);

        $model = new VisitorsModel();
        $args  = ['date' => $this->range(), 'resource_type' => 'post', 'source_channel' => 'search'];

        $this->assertCount(1, $model->getEntryPages($args));
        $this->assertSame(1, $model->countEntryPages($args));
    }

    public function test_historical_views_for_a_url_ignore_site_wide_baseline()
    {
        global $wpdb;

        // The site's first post sets the start of the "All time" range
        self::factory()->post->create(['post_date' => '2010-01-01 00:00:00']);

        $wpdb->insert(DB::table('historical'), ['category' => 'visits', 'page_id' => -2, 'uri' => '-2', 'value' => 5000]);
        $wpdb->insert(DB::table('historical'), ['category' => 'uri', 'page_id' => 0, 'uri' => '/sample/', 'value' => 7]);

        $model = new HistoricalModel();
        $views = $model->getViews([
            'query_param' => '/sample/',
            'date'        => ['from' => '2000-01-01', 'to' => $this->today()],
        ]);

        $this->assertEquals(7, $views);
    }

    public function test_historical_views_read_query_param_when_uri_is_empty()
    {
        global $wpdb;

        self::factory()->post->create(['post_date' => '2010-01-01 00:00:00']);

        $wpdb->insert(DB::table('historical'), ['category' => 'uri', 'page_id' => 0, 'uri' => '/sample/', 'value' => 7]);

        $model = new HistoricalModel();
        $args  = [
            'uri'         => '',
            'query_param' => '/sample/',
            'date'        => ['from' => '2000-01-01', 'to' => $this->today()],
        ];

        $this->assertTrue($model->isPostInAllTimeRange($args));
        $this->assertEquals(7, $model->getViews($args));
    }

    public function test_post_views_ignore_archive_rows_with_same_id()
    {
        $postId  = self::factory()->post->create();
        $otherId = self::factory()->post->create();

        $this->addPage(['id' => $postId, 'type' => 'post', 'count' => 3, 'uri' => '/post/']);
        $this->addPage(['id' => $postId, 'type' => 'author', 'count' => 50, 'uri' => '/author/x/']);

        // A category archive whose term ID equals another post's ID; that post has no views of its own
        $this->addPage(['id' => $otherId, 'type' => 'category', 'count' => 9, 'uri' => '/category/y/']);

        $model = new PostsModel();
        $rows  = $model->getPostsViewsData(['date' => $this->range(), 'post_type' => 'post']);

        $this->assertCount(1, $rows);
        $this->assertEquals(3, $rows[0]->views);

        $this->assertEquals(1, $model->countPosts(['date' => $this->range(), 'filter_by_view_date' => true]));
    }

    public function test_system_error_exception_accepts_previous()
    {
        $previous  = new RuntimeException('inner');
        $exception = new SystemErrorException('outer', 0, $previous);

        $this->assertSame($previous, $exception->getPrevious());
        $this->assertNull((new SystemErrorException('outer'))->getPrevious());
    }
}
