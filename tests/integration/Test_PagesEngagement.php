<?php

use WP_Statistics\Components\DateTime;
use WP_Statistics\Models\VisitorsModel;
use WP_Statistics\Service\Admin\Metabox\MetaboxDataProvider;
use WP_Statistics\Service\Database\Managers\TableHandler;
use WP_Statistics\Service\Database\Schema\Manager;
use WP_STATISTICS\DB;

/**
 * Visitors and bounce rate for the Top Pages card (VisitorsModel::getPagesEngagement()).
 */
class Test_PagesEngagement extends WP_UnitTestCase
{
    private static $tables = ['visitor', 'pages', 'visitor_relationships'];

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

    private function range()
    {
        return ['from' => DateTime::get('-7 days'), 'to' => DateTime::get()];
    }

    private function addPage($id, $uri, $type, $count = 1, $date = null)
    {
        global $wpdb;

        $wpdb->insert(DB::table('pages'), [
            'id'    => $id,
            'uri'   => $uri,
            'type'  => $type,
            'count' => $count,
            'date'  => $date ?: DateTime::get(),
        ]);

        return $wpdb->insert_id;
    }

    /**
     * A visitor who saw the given page rows; the first one is their landing page.
     */
    private function addVisitor(array $pageIds, $date = null)
    {
        global $wpdb;

        static $n = 0;
        $n++;
        $date = $date ?: DateTime::get();

        $wpdb->insert(DB::table('visitor'), [
            'last_counter'   => $date,
            'referred'       => '',
            'agent'          => 'Chrome',
            'platform'       => 'Windows',
            'version'        => (string)$n,
            'ip'             => '198.51.100.' . $n,
            'user_id'        => 0,
            'hits'           => count($pageIds),
            'first_page'     => $pageIds[0],
            'last_page'      => end($pageIds),
            'source_channel' => 'direct',
        ]);
        $visitorId = $wpdb->insert_id;

        foreach ($pageIds as $pageId) {
            $wpdb->insert(DB::table('visitor_relationships'), [
                'visitor_id' => $visitorId,
                'page_id'    => $pageId,
                'date'       => $date . ' 10:00:00',
            ]);
        }

        return $visitorId;
    }

    public function test_post_rows_get_visitors_and_bounce_rate()
    {
        $post  = $this->addPage(10, '/hello/', 'post', 3);
        $other = $this->addPage(11, '/other/', 'post', 2);

        $this->addVisitor([$post]);           // lands on /hello/, bounces
        $this->addVisitor([$post, $other]);   // lands on /hello/, goes on
        $this->addVisitor([$other, $post]);   // lands on /other/, sees /hello/ too
        $this->addVisitor([$post]);           // lands on /hello/, bounces

        $result = (new VisitorsModel())->getPagesEngagement([
            'date'      => $this->range(),
            'resources' => [(object)['id' => 10, 'uri' => '/hello/', 'type' => 'post'], (object)['id' => 11, 'uri' => '/other/', 'type' => 'post']],
        ]);

        $hello = $result[VisitorsModel::pageEngagementKey(10)];
        $this->assertSame(4, $hello['visitors']);
        $this->assertSame(3, $hello['entrances']);
        $this->assertSame(2, $hello['bounces']);
        $this->assertSame(66.7, $hello['bounce_rate']);

        $other = $result[VisitorsModel::pageEngagementKey(11)];
        $this->assertSame(2, $other['visitors']);
        $this->assertSame(1, $other['entrances']);
        $this->assertEquals(0, $other['bounce_rate']);
    }

    public function test_rows_with_id_zero_match_by_uri_and_type()
    {
        $home    = $this->addPage(0, '/', 'home');
        $missing = $this->addPage(0, '/missing/', '404');
        $search  = $this->addPage(0, '/?s=a%20b', 'search');

        $this->addVisitor([$home]);
        $this->addVisitor([$home, $missing]);
        $this->addVisitor([$missing]);
        $this->addVisitor([$search]);

        $result = (new VisitorsModel())->getPagesEngagement([
            'date'      => $this->range(),
            'resources' => [
                (object)['id' => 0, 'uri' => '/', 'type' => 'home'],
                (object)['id' => 0, 'uri' => '/missing/', 'type' => '404'],
                (object)['id' => 0, 'uri' => '/?s=a%20b', 'type' => 'search'],
            ],
        ]);

        $this->assertSame(2, $result[VisitorsModel::pageEngagementKey(0, '/', 'home')]['visitors']);
        $this->assertSame(50.0, $result[VisitorsModel::pageEngagementKey(0, '/', 'home')]['bounce_rate']);
        $this->assertSame(2, $result[VisitorsModel::pageEngagementKey(0, '/missing/', '404')]['visitors']);
        $this->assertSame(100.0, $result[VisitorsModel::pageEngagementKey(0, '/missing/', '404')]['bounce_rate']);
        $this->assertSame(1, $result[VisitorsModel::pageEngagementKey(0, '/?s=a%20b', 'search')]['visitors']);
    }

    public function test_page_nobody_landed_on_has_null_bounce_rate()
    {
        $landing = $this->addPage(20, '/landing/', 'page');
        $inner   = $this->addPage(21, '/inner/', 'page');

        $this->addVisitor([$landing, $inner]);

        $rows = (new VisitorsModel())->attachPagesEngagement(
            [(object)['id' => 21, 'uri' => '/inner/', 'type' => 'page', 'views' => 1], (object)['id' => 99, 'uri' => '/none/', 'type' => 'page', 'views' => 0]],
            ['date' => $this->range()]
        );

        $this->assertSame(1, $rows[0]->visitors);
        $this->assertNull($rows[0]->bounce_rate);
        $this->assertSame(0, $rows[1]->visitors);
        $this->assertNull($rows[1]->bounce_rate);
    }

    public function test_id_types_keep_term_rows_with_the_same_id_out()
    {
        $post = $this->addPage(30, '/post-30/', 'post');
        $term = $this->addPage(30, '/category/news/', 'category');

        $this->addVisitor([$post]);
        $this->addVisitor([$term]);

        $model = new VisitorsModel();
        $args  = ['date' => $this->range(), 'resources' => [['id' => 30]]];

        $this->assertSame(2, $model->getPagesEngagement($args)[VisitorsModel::pageEngagementKey(30)]['visitors']);
        $this->assertSame(1, $model->getPagesEngagement($args + ['id_types' => ['post']])[VisitorsModel::pageEngagementKey(30)]['visitors']);
    }

    public function test_visits_outside_the_range_are_left_out()
    {
        $post = $this->addPage(40, '/old/', 'post', 1, DateTime::get('-40 days'));
        $this->addVisitor([$post], DateTime::get('-40 days'));

        $result = (new VisitorsModel())->getPagesEngagement([
            'date'      => $this->range(),
            'resources' => [['id' => 40]],
        ]);

        $this->assertSame([], $result);
    }

    public function test_one_landing_page_split_over_several_page_rows_is_summed()
    {
        // Same post on three days = three page rows; landings on each add up under one key.
        $day1 = $this->addPage(60, '/split/', 'post', 1, DateTime::get('-2 days'));
        $day2 = $this->addPage(60, '/split/', 'post', 1, DateTime::get('-1 days'));
        $day3 = $this->addPage(60, '/split/', 'post', 1);
        $next = $this->addPage(61, '/next/', 'post', 1);

        $this->addVisitor([$day1], DateTime::get('-2 days'));
        $this->addVisitor([$day2, $next], DateTime::get('-1 days'));
        $this->addVisitor([$day3]);
        $this->addVisitor([$day3, $next]);

        $result = (new VisitorsModel())->getPagesEngagement([
            'date'      => $this->range(),
            'resources' => [['id' => 60], ['id' => 0, 'uri' => '/nobody/', 'type' => '404']],
            'fields'    => ['bounce_rate'],
        ]);

        $this->assertSame(['visitors' => 0, 'entrances' => 4, 'bounces' => 2, 'bounce_rate' => 50.0], $result[VisitorsModel::pageEngagementKey(60)]);
        $this->assertCount(1, $result);
    }

    public function test_top_pages_card_rows_carry_visitors_and_bounce_rate()
    {
        $home = $this->addPage(0, '/', 'home', 5);
        $post = $this->addPage(50, '/post-50/', 'post', 2);

        $this->addVisitor([$home]);
        $this->addVisitor([$home, $post]);

        $rows = (new MetaboxDataProvider())->getTopPagesWithEngagement(['date' => $this->range()]);

        $this->assertCount(2, $rows);
        $this->assertSame('/', $rows[0]->uri);
        $this->assertSame(2, $rows[0]->visitors);
        $this->assertSame(50.0, $rows[0]->bounce_rate);
        $this->assertSame(1, $rows[1]->visitors);
        $this->assertNull($rows[1]->bounce_rate);
    }

    public function test_empty_resources_run_no_query()
    {
        $this->assertSame([], (new VisitorsModel())->getPagesEngagement(['resources' => []]));
    }
}
