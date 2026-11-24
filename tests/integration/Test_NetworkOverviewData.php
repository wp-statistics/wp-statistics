<?php

use WP_Statistics\Components\DateTime;
use WP_Statistics\Service\Database\Managers\TableHandler;
use WP_Statistics\Service\Database\Schema\Manager;
use WP_STATISTICS\DB;
use WP_STATISTICS\Network;

/**
 * Data behind Network Admin > Statistics: one row per site with visitors and views of the
 * last 7 days, the change against the 7 days before, daily views and the top page.
 */
class Test_NetworkOverviewData extends WP_UnitTestCase
{
    private static $tables = ['visitor', 'pages', 'historical'];

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        if (!class_exists(Network::class)) {
            require_once WP_STATISTICS_DIR . 'includes/admin/class-wp-statistics-admin-network.php';
        }

        foreach (self::$tables as $table) {
            TableHandler::createTable($table, Manager::getSchemaForTable($table));
        }
    }

    public function setUp(): void
    {
        parent::setUp();
        $this->emptyTables();
        delete_transient(Network::NUMBERS_TRANSIENT);
    }

    public function tearDown(): void
    {
        $this->emptyTables();
        delete_transient(Network::NUMBERS_TRANSIENT);
        parent::tearDown();
    }

    private function emptyTables()
    {
        global $wpdb;

        foreach (self::$tables as $table) {
            $wpdb->query('DELETE FROM `' . DB::table($table) . '`');
        }
    }

    private function day($daysAgo)
    {
        return DateTime::subtract(DateTime::get(), $daysAgo);
    }

    private function addVisitor($daysAgo, $hits = 1)
    {
        global $wpdb;

        static $n = 0;
        $n++;

        $day = $this->day($daysAgo);

        $wpdb->insert(DB::table('visitor'), [
            'last_counter'   => $day,
            'referred'       => '',
            'agent'          => 'Chrome',
            'platform'       => 'Windows',
            'version'        => '1',
            'ip'             => '192.0.2.' . $n,
            'user_id'        => 0,
            'hits'           => $hits,
            'location'       => 'DE',
            'source_channel' => 'direct',
            'first_view'     => $day . ' 08:00:00',
            'last_view'      => $day . ' 08:00:00',
        ]);
    }

    public function test_change_is_null_without_a_previous_period()
    {
        $this->assertNull(Network::getChange(5, 0));
        $this->assertNull(Network::getChange(null, 3));
        $this->assertSame(50.0, Network::getChange(6, 4));
        $this->assertSame(-25.0, Network::getChange(3, 4));
        $this->assertSame(0.0, Network::getChange(4, 4));
    }

    public function test_site_numbers_cover_both_weeks_daily_views_and_top_page()
    {
        global $wpdb;

        $this->addVisitor(0, 3);
        $this->addVisitor(2, 2);
        $this->addVisitor(6, 1);
        $this->addVisitor(8, 1); // previous 7 days
        $this->addVisitor(13, 1); // previous 7 days
        $this->addVisitor(20, 9); // outside both ranges

        $postId = self::factory()->post->create(['post_title' => 'Lisbon on a budget']);
        $wpdb->insert(DB::table('pages'), ['uri' => '/lisbon/', 'type' => 'post', 'date' => $this->day(1), 'count' => 4, 'id' => $postId]);
        $wpdb->insert(DB::table('pages'), ['uri' => '/lisbon/', 'type' => 'post', 'date' => $this->day(0), 'count' => 3, 'id' => $postId]);
        $wpdb->insert(DB::table('pages'), ['uri' => '/', 'type' => 'home', 'date' => $this->day(0), 'count' => 5, 'id' => 0]);

        $numbers = Network::getSiteNumbers();

        $this->assertSame(3, $numbers['visitors']);
        $this->assertSame(6, $numbers['views']);
        $this->assertSame(2, $numbers['prev_visitors']);
        $this->assertSame(50.0, $numbers['change']);
        $this->assertSame([1, 0, 0, 0, 2, 0, 3], $numbers['daily'], 'One value per day, oldest first, 0 on days without visits.');
        $this->assertSame('Lisbon on a budget', $numbers['top_page']['title']);
        $this->assertSame(7, $numbers['top_page']['views']);
    }

    public function test_site_numbers_are_cached()
    {
        $this->addVisitor(0);
        $this->assertSame(1, Network::getSiteNumbers()['visitors']);

        $this->addVisitor(0);
        $this->assertSame(1, Network::getSiteNumbers()['visitors'], 'Second call reads the transient.');

        delete_transient(Network::NUMBERS_TRANSIENT);
        $this->assertSame(2, Network::getSiteNumbers()['visitors']);
    }

    public function test_site_without_visits_has_zeroes_and_no_top_page()
    {
        $numbers = Network::getSiteNumbers();

        $this->assertSame(0, $numbers['visitors']);
        $this->assertSame(0, $numbers['views']);
        $this->assertNull($numbers['change']);
        $this->assertSame([0, 0, 0, 0, 0, 0, 0], $numbers['daily']);
        $this->assertNull($numbers['top_page']);
    }

    public function test_overview_rows_on_multisite()
    {
        if (!is_multisite()) {
            $this->markTestSkipped('Multisite only (get_blog_details()).');
        }

        $this->addVisitor(0, 2);
        $this->addVisitor(9, 1);

        $sites = Network::getSitesOverview();

        $this->assertNotEmpty($sites);
        $row = $sites[0];
        foreach (['id', 'name', 'url', 'active', 'visitors', 'views', 'prev_visitors', 'change', 'daily', 'top_page', 'overview_url', 'activate_url'] as $key) {
            $this->assertArrayHasKey($key, $row);
        }
        $this->assertTrue($row['active']);
        $this->assertStringContainsString('page=wps_overview_page', $row['overview_url']);
    }

    public function test_totals_add_up_every_site()
    {
        $totals = Network::getTotals([
            ['visitors' => 10, 'views' => 20, 'prev_visitors' => 5],
            ['visitors' => null, 'views' => null, 'prev_visitors' => null], // plugin not active
            ['visitors' => 0, 'views' => 0, 'prev_visitors' => 5],
        ]);

        $this->assertSame(10, $totals['visitors']);
        $this->assertSame(20, $totals['views']);
        $this->assertSame(10, $totals['prev_visitors']);
        $this->assertSame(0.0, $totals['change']);
    }
}
