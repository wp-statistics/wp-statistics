<?php

use WP_Statistics\Components\DateTime;
use WP_Statistics\Models\VisitorsModel;
use WP_Statistics\Service\Admin\Geographic\GeographicDataProvider;
use WP_Statistics\Service\Admin\Referrals\ReferralsDataProvider;
use WP_Statistics\Service\Database\Managers\TableHandler;
use WP_Statistics\Service\Database\Schema\Manager;
use WP_STATISTICS\DB;

/**
 * The "Share" columns divide each row by a total from the same query, over every row
 * (not only the rows on the current page), so the column adds up to 100%.
 */
class Test_ShareTotals extends WP_UnitTestCase
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

    private function addVisitor($data, $times = 1)
    {
        global $wpdb;

        static $n = 0;

        for ($i = 0; $i < $times; $i++) {
            $n++;
            $wpdb->insert(DB::table('visitor'), array_merge([
                'last_counter'   => DateTime::get(),
                'referred'       => '',
                'agent'          => 'Chrome',
                'platform'       => 'Windows',
                'version'        => '1',
                'ip'             => '198.51.100.' . ($n % 250),
                'user_id'        => 0,
                'hits'           => 1,
                'source_channel' => 'direct',
            ], $data));
        }
    }

    private function sumVisitors($rows)
    {
        return array_sum(array_map(function ($row) {
            return intval($row->visitors);
        }, $rows));
    }

    private function seedGeo()
    {
        $this->addVisitor(['location' => 'DE', 'continent' => 'Europe', 'region' => 'Bavaria', 'city' => 'Munich'], 3);
        $this->addVisitor(['location' => 'FR', 'continent' => 'Europe', 'region' => 'Ile-de-France', 'city' => 'Paris'], 2);
        $this->addVisitor(['location' => 'US', 'continent' => 'North America', 'region' => 'Texas', 'city' => 'Austin'], 4);
        $this->addVisitor(['location' => 'US', 'continent' => 'North America', 'region' => 'Ohio', 'city' => null], 1);
        $this->addVisitor(['location' => 'US', 'continent' => 'North America', 'region' => null, 'city' => null], 2);
        // Unknown location: still a row ("(not set)") on the Countries tab
        $this->addVisitor(['location' => null, 'continent' => null, 'region' => null, 'city' => null], 1);
        // Outside the range
        $this->addVisitor(['location' => 'DE', 'continent' => 'Europe', 'last_counter' => DateTime::get('-30 days')], 5);
    }

    public function test_countries_share_base_is_every_visitor_in_range()
    {
        $this->seedGeo();

        // One row per page: the base must not depend on the page
        $data = (new GeographicDataProvider(['date' => $this->range(), 'per_page' => 1, 'page' => 1]))->getCountriesData();
        $this->assertCount(1, $data['countries']);
        $this->assertSame(13, $data['visitors']);

        $all = (new GeographicDataProvider(['date' => $this->range()]))->getCountriesData();
        $this->assertSame($this->sumVisitors($all['countries']), $all['visitors']);
    }

    public function test_unknown_countries_are_one_row_on_one_page()
    {
        // NULL, empty and the unknown code: three spellings of "(not set)", 1 + 1 + 1 visitors
        $this->addVisitor(['location' => 'DE'], 5);
        $this->addVisitor(['location' => 'FR'], 4);
        $this->addVisitor(['location' => null]);
        $this->addVisitor(['location' => '']);
        $this->addVisitor(['location' => '000']);
        $this->addVisitor(['location' => 'IT'], 2);

        $all = (new GeographicDataProvider(['date' => $this->range()]))->getCountriesData();
        $this->assertSame(['DE', 'FR', '000', 'IT'], array_column(array_map('get_object_vars', $all['countries']), 'country'));
        $this->assertSame(3, (int)$all['countries'][2]->visitors);
        $this->assertSame(4, $all['total'], 'the row count matches the rows');

        // Page by page, "(not set)" shows exactly once and the pages add up to the total
        $seen = [];
        for ($page = 1; $page <= 2; $page++) {
            $data = (new GeographicDataProvider(['date' => $this->range(), 'per_page' => 2, 'page' => $page]))->getCountriesData();
            $this->assertSame(4, $data['total']);
            foreach ($data['countries'] as $row) {
                $seen[] = $row->country;
            }
        }
        $this->assertSame(['DE', 'FR', '000', 'IT'], $seen);
    }

    public function test_share_base_follows_the_same_filters_as_the_rows()
    {
        $this->addVisitor(['location' => 'DE', 'source_channel' => 'search'], 3);
        $this->addVisitor(['location' => 'FR', 'source_channel' => 'search'], 1);
        $this->addVisitor(['location' => 'DE', 'source_channel' => 'direct'], 6);
        $this->addVisitor(['location' => 'US', 'referred' => 'https://ref.example/', 'source_channel' => 'referral'], 2);

        $model = new VisitorsModel();

        foreach ([
            ['source_channel' => 'search'],
            ['referrer' => 'https://ref.example/'],
            ['referred_visitors' => true],
        ] as $filter) {
            $args = array_merge(['date' => $this->range()], $filter);
            $rows = $model->getVisitorsGeoData($args);

            $this->assertSame($this->sumVisitors($rows), $model->countGeoVisitors($args), json_encode($filter));
            $this->assertSame(count($rows), $model->countGeoData(array_merge($args, ['merge_unknown_country' => true])), json_encode($filter));
        }

        $this->assertSame(4, $model->countGeoVisitors(['date' => $this->range(), 'source_channel' => 'search']));
    }

    public function test_share_base_with_a_page_filter_counts_each_visitor_once()
    {
        global $wpdb;

        $post = self::factory()->post->create();
        $wpdb->insert(DB::table('pages'), ['uri' => '/a/', 'type' => 'post', 'date' => DateTime::get(), 'count' => 1, 'id' => $post]);
        $pageId = $wpdb->insert_id;

        $this->addVisitor(['location' => 'DE'], 2);
        $visitorIds = $wpdb->get_col('SELECT ID FROM `' . DB::table('visitor') . '`');
        foreach ($visitorIds as $visitorId) {
            // Two views of the same post each
            $wpdb->insert(DB::table('visitor_relationships'), ['visitor_id' => $visitorId, 'page_id' => $pageId, 'date' => DateTime::get() . ' 10:00:00']);
            $wpdb->insert(DB::table('visitor_relationships'), ['visitor_id' => $visitorId, 'page_id' => $pageId, 'date' => DateTime::get() . ' 10:05:00']);
        }
        $this->addVisitor(['location' => 'FR'], 3); // no view of the post

        $model = new VisitorsModel();
        $args  = ['date' => $this->range(), 'post_id' => $post];

        $this->assertSame(2, $this->sumVisitors($model->getVisitorsGeoData($args)));
        $this->assertSame(2, $model->countGeoVisitors($args));

        $wpdb->query('DELETE FROM `' . DB::table('visitor_relationships') . '`');
    }

    public function test_europe_share_base_is_visitors_from_europe()
    {
        $this->seedGeo();

        $data = (new GeographicDataProvider(['date' => $this->range()]))->getEuropeData();

        $this->assertSame(5, $data['visitors']);
        $this->assertSame($this->sumVisitors($data['countries']), $data['visitors']);
    }

    public function test_cities_and_us_states_share_base_match_their_rows()
    {
        $this->seedGeo();

        $provider = new GeographicDataProvider(['date' => $this->range()]);

        $cities = $provider->getCitiesData();
        $this->assertSame(9, $cities['visitors']); // visitors with a known city
        $this->assertSame($this->sumVisitors($cities['cities']), $cities['visitors']);

        $states = $provider->getUsData();
        $this->assertSame(5, $states['visitors']); // US visitors with a known state
        $this->assertSame($this->sumVisitors($states['states']), $states['visitors']);
    }

    public function test_overview_top_countries_base_is_all_visitors_not_the_five_rows()
    {
        $this->seedGeo();

        $data = (new GeographicDataProvider(['date' => $this->range()]))->getOverviewData();

        $this->assertSame(13, $data['countries_visitors']);
    }

    public function test_referrals_sum_visitors_counts_referrals_not_rows()
    {
        $this->addVisitor(['referred' => 'search.example', 'source_channel' => 'search'], 3);
        $this->addVisitor(['referred' => 'search.example', 'source_channel' => 'paid_search'], 2);
        $this->addVisitor(['referred' => 'other.example', 'source_channel' => 'referral'], 1);
        $this->addVisitor(['referred' => '', 'source_channel' => 'direct'], 4);

        $model   = new VisitorsModel();
        $grouped = ['date' => $this->range(), 'group_by' => ['visitor.referred', 'visitor.source_channel']];

        $this->assertEquals(3, $model->countReferrers($grouped));
        $this->assertEquals(6, $model->countReferrers(array_merge($grouped, ['sum_visitors' => true])));
        $this->assertEquals(1, $model->countReferrers(array_merge($grouped, ['sum_visitors' => true, 'referrer' => 'other'])));
    }

    public function test_referrer_tabs_share_base_matches_every_row()
    {
        $this->addVisitor(['referred' => 'google.com', 'source_channel' => 'search', 'source_name' => 'Google'], 5);
        $this->addVisitor(['referred' => 'bing.com', 'source_channel' => 'search', 'source_name' => 'Bing'], 2);
        $this->addVisitor(['referred' => 'google.com', 'source_channel' => 'paid_search', 'source_name' => 'Google Ads'], 1);
        // Paid click with no referrer, grouped by source name on the Search Engines tab
        $this->addVisitor(['referred' => '', 'source_channel' => 'paid_search', 'source_name' => 'Microsoft Ads'], 2);
        $this->addVisitor(['referred' => 'facebook.com', 'source_channel' => 'social', 'source_name' => 'Facebook'], 3);

        $sum = function ($rows) {
            return array_sum(array_map(function ($row) {
                return $row->getTotalReferrals(true);
            }, $rows));
        };

        $paged = new ReferralsDataProvider(['date' => $this->range(), 'per_page' => 1, 'page' => 1]);
        $all   = new ReferralsDataProvider(['date' => $this->range(), 'per_page' => 100, 'page' => 1]);

        $search = $paged->getSearchEngineReferrals();
        $this->assertCount(1, $search['referrers']);
        $this->assertSame(10, $search['referrals']);
        $this->assertSame($sum($all->getSearchEngineReferrals()['referrers']), $search['referrals']);

        $social = $all->getSocialMediaReferrals();
        $this->assertSame(3, $social['referrals']);

        // Referrers tab lists only visits with a referrer domain
        $referrers = $all->getReferrers();
        $this->assertSame(11, $referrers['referrals']);
        $this->assertSame($sum($referrers['referrers']), $referrers['referrals']);
    }
}
