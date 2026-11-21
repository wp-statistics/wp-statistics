<?php

use WP_Statistics\Components\DateTime;
use WP_Statistics\Models\AuthorsModel;
use WP_Statistics\Models\TaxonomyModel;
use WP_Statistics\Models\VisitorsModel;
use WP_Statistics\Service\Admin\AuthorAnalytics\AuthorAnalyticsDataProvider;
use WP_Statistics\Service\Database\Managers\TableHandler;
use WP_Statistics\Service\Database\Schema\Manager;
use WP_STATISTICS\DB;

/**
 * Author, Category, Geographic and Devices reports: the numbers and totals use the same rows.
 */
class Test_AnalyticsReportTotals extends WP_UnitTestCase
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

    private function addVisitor($data = [])
    {
        global $wpdb;

        static $n = 0;
        $n++;

        $wpdb->insert(DB::table('visitor'), array_merge([
            'last_counter'   => $this->today(),
            'referred'       => '',
            'agent'          => 'Chrome',
            'platform'       => 'Windows',
            'version'        => '1',
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

    private function addView($visitorId, $pageId)
    {
        global $wpdb;

        $wpdb->insert(DB::table('visitor_relationships'), [
            'visitor_id' => $visitorId,
            'page_id'    => $pageId,
            'date'       => $this->today() . ' 10:00:00',
        ]);
    }

    public function test_author_views_ignore_front_page_rows()
    {
        $author = self::factory()->user->create(['role' => 'author']);
        $pageId = self::factory()->post->create(['post_type' => 'page', 'post_author' => $author]);

        // The same page also shown as the front page is stored with type 'home'
        $pageRow = $this->addPage(['id' => $pageId, 'type' => 'page', 'count' => 2, 'uri' => '/about/']);
        $homeRow = $this->addPage(['id' => $pageId, 'type' => 'home', 'count' => 5, 'uri' => '/']);

        $this->addView($this->addVisitor(), $pageRow);
        $this->addView($this->addVisitor(), $homeRow);

        $args  = ['date' => $this->range(), 'post_type' => 'page'];
        $model = new AuthorsModel();

        $top = $model->getTopViewingAuthors($args);
        $this->assertEquals(2, $top[0]->total_views);

        $report = $model->getAuthorsReportData($args);
        $this->assertEquals(2, $report[0]->total_views);

        $perPost = $model->getAuthorsByViewsPerPost(['ignore_date' => true, 'post_type' => 'page']);
        $this->assertEquals(2, $perPost[0]->total_views);

        $visitors = (new VisitorsModel())->countVisitors(array_merge($args, ['author_id' => $author]));
        $this->assertSame(1, $visitors);
    }

    public function test_authors_report_total_counts_listed_authors()
    {
        $recent = self::factory()->user->create(['role' => 'author']);
        $older  = self::factory()->user->create(['role' => 'author']);

        self::factory()->post->create(['post_author' => $recent]);
        self::factory()->post->create(['post_author' => $older, 'post_date' => '2015-01-01 10:00:00']);

        $provider = new AuthorAnalyticsDataProvider(['post_type' => 'post', 'date' => $this->range(), 'per_page' => 25]);
        $data     = $provider->getAuthorsReportData();

        $this->assertCount(2, $data['authors']);
        $this->assertEquals(2, $data['total']);
    }

    public function test_terms_publishing_counts_published_posts_without_views()
    {
        $term = self::factory()->category->create(['name' => 'Sample Term']);

        self::factory()->post->create(['post_category' => [$term]]);
        self::factory()->post->create(['post_category' => [$term]]);
        self::factory()->post->create(['post_category' => [$term], 'post_status' => 'draft']);

        $rows = (new TaxonomyModel())->getTermsData([
            'date'       => $this->range(),
            'taxonomy'   => 'category',
            'post_type'  => 'post',
            'order_by'   => 'posts',
            'date_field' => 'posts.post_date',
        ]);

        $this->assertCount(1, $rows);
        $this->assertEquals(2, $rows[0]->posts);
    }

    public function test_terms_views_ignore_archive_rows_with_same_id()
    {
        $term   = self::factory()->category->create(['name' => 'Sample Term']);
        $postId = self::factory()->post->create(['post_category' => [$term]]);

        $this->addPage(['id' => $postId, 'type' => 'post', 'count' => 3, 'uri' => '/post/']);
        $this->addPage(['id' => $postId, 'type' => 'category', 'count' => 40, 'uri' => '/category/z/']);

        $model = new TaxonomyModel();
        $args  = ['date' => $this->range(), 'taxonomy' => 'category', 'post_type' => 'post'];

        $rows = $model->getTermsData($args);
        $this->assertEquals(3, $rows[0]->views);

        $report = $model->getTermsReportData($args);
        $this->assertEquals(3, $report[0]->views);
    }

    public function test_terms_report_lists_terms_with_posts_but_no_views()
    {
        $viewed = self::factory()->category->create(['name' => 'Viewed Term']);
        $quiet  = self::factory()->category->create(['name' => 'Quiet Term']);

        $viewedPost = self::factory()->post->create(['post_category' => [$viewed]]);
        self::factory()->post->create(['post_category' => [$quiet]]);

        $this->addPage(['id' => $viewedPost, 'type' => 'post', 'count' => 4, 'uri' => '/viewed/']);

        $model = new TaxonomyModel();
        $args  = ['date' => $this->range(), 'taxonomy' => 'category', 'post_type' => 'post', 'per_page' => 25];
        $rows  = $model->getTermsReportData($args);

        $byName = wp_list_pluck($rows, 'posts', 'term_name');
        $this->assertEquals(1, $byName['Viewed Term']);
        $this->assertEquals(1, $byName['Quiet Term']);
        $this->assertEquals(count($rows), $model->getTermsReportDataCount($args));
    }

    public function test_cities_with_same_name_in_different_countries_stay_apart()
    {
        $this->addVisitor(['location' => 'US', 'region' => 'Illinois', 'city' => 'Springfield']);
        $this->addVisitor(['location' => 'US', 'region' => 'Illinois', 'city' => 'Springfield']);
        $this->addVisitor(['location' => 'AU', 'region' => 'Queensland', 'city' => 'Springfield']);

        $model = new VisitorsModel();
        $args  = [
            'date'         => $this->range(),
            'group_by'     => ['visitor.location', 'visitor.region', 'visitor.city'],
            'not_null'     => 'visitor.city',
            'count_field'  => 'city',
            'count_fields' => ['visitor.location', 'visitor.region', 'visitor.city'],
        ];

        $rows = $model->getVisitorsGeoData($args);
        $this->assertCount(2, $rows);
        $this->assertSame(2, $model->countGeoData($args));
    }

    public function test_countries_total_counts_unknown_location_row()
    {
        $this->addVisitor(['location' => 'US']);
        $this->addVisitor(['location' => null]);

        $model = new VisitorsModel();
        $args  = ['date' => $this->range()];

        $this->assertCount(2, $model->getVisitorsGeoData($args));
        $this->assertSame(2, $model->countGeoData(array_merge($args, ['include_null' => true])));

        // Default stays the same for other callers
        $this->assertSame(1, $model->countGeoData($args));
    }

    public function test_browser_versions_total_counts_versions()
    {
        $this->addVisitor(['agent' => 'Chrome', 'version' => '120.0']);
        $this->addVisitor(['agent' => 'Chrome', 'version' => '121.0']);
        $this->addVisitor(['agent' => 'Chrome', 'version' => '121.1']);
        $this->addVisitor(['agent' => 'Firefox', 'version' => '130.0']);

        $model = new VisitorsModel();
        $args  = ['date' => $this->range(), 'where_col' => 'agent', 'where_val' => 'Chrome'];

        $this->assertCount(2, $model->getVisitorsDevicesVersions($args));
        $this->assertSame(2, $model->countVisitorsDevicesVersions($args));
    }
}
