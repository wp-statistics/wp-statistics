<?php

namespace WP_Statistics\Tests\Service\Referrals;

use WP_STATISTICS\DB;
use WP_Statistics\Components\View;
use WP_Statistics\Service\Admin\Metabox\MetaboxDataProvider;
use WP_Statistics\Service\Admin\Referrals\ReferralsDataProvider;
use WP_Statistics\Service\Database\Managers\TableHandler;
use WP_Statistics\Service\Database\Schema\Manager;
use WP_UnitTestCase;

/**
 * One domain can be two rows (google.com as Google and as Google Ads). The rows must
 * differ at every width, and Overview "Top Referring" groups rows like the Referrers
 * report. All values are made up.
 */
class Test_ReferrerRowLabels extends WP_UnitTestCase
{
    private $ip = 0;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        TableHandler::createTable('visitor', Manager::getSchemaForTable('visitor'));
    }

    public static function tearDownAfterClass(): void
    {
        global $wpdb;
        $wpdb->query('DROP TABLE IF EXISTS `' . DB::table('visitor') . '`'); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

        parent::tearDownAfterClass();
    }

    public function setUp(): void
    {
        parent::setUp();

        global $wpdb;
        $wpdb->query('DELETE FROM `' . DB::table('visitor') . '`'); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $this->ip = 0;

        $this->addVisitor('google.com', 'search', 'Google', 3);
        $this->addVisitor('google.com', 'paid_search', 'Google Ads', 2);
        $this->addVisitor('bing.com', 'search', 'Bing', 1);
        $this->addVisitor('', 'paid_search', 'Microsoft Ads', 1);
    }

    private function addVisitor($referred, $channel, $name, $times = 1)
    {
        global $wpdb;

        for ($i = 0; $i < $times; $i++) {
            $wpdb->insert(DB::table('visitor'), [
                'last_counter'   => gmdate('Y-m-d'),
                'last_view'      => gmdate('Y-m-d H:i:s'),
                'referred'       => $referred,
                'agent'          => 'Chrome',
                'platform'       => 'Windows',
                'version'        => '141',
                'ip'             => '203.0.113.' . (++$this->ip),
                'user_id'        => 0,
                'source_channel' => $channel,
                'source_name'    => $name,
            ]);
        }
    }

    public function test_overview_top_referring_groups_like_the_referrers_report()
    {
        $share  = (new MetaboxDataProvider())->getReferrersShare([], 5);
        $report = (new ReferralsDataProvider([]))->getReferrers();

        $key = function ($row) {
            return $row->getRawReferrer() . '|' . $row->getRawSourceChannel() . '|' . $row->getTotalReferrals(true);
        };

        $this->assertSame(array_map($key, $report['referrers']), array_map($key, $share['rows']));
        $this->assertCount(3, $share['rows']);
    }

    public function test_same_domain_rows_get_the_source_in_their_label()
    {
        $rows   = (new MetaboxDataProvider())->getReferrersShare([], 5)['rows'];
        $labels = [];

        foreach ($rows as $row) {
            $labels[] = $row->getListLabel($rows);
        }

        $this->assertContains('google.com (Google)', $labels);
        $this->assertContains('google.com (Google Ads)', $labels);
        $this->assertContains('bing.com', $labels, 'a domain listed once keeps its plain name');
        $this->assertSame(count($labels), count(array_unique($labels)), 'no two rows read the same');
    }

    public function test_overview_card_prints_distinct_labels()
    {
        $data = (new MetaboxDataProvider())->getReferrersShare([], 5);
        $html = View::load('metabox/top-referring', ['data' => $data, 'filters' => [], 'isTodayOrFutureDate' => false], true);

        $this->assertStringContainsString('google.com (Google Ads)', $html);
        $this->assertStringContainsString('google.com (Google)', $html);
    }

    public function test_search_engines_table_labels_rows_without_referrer_and_keeps_source_at_narrow_width()
    {
        $data = (new ReferralsDataProvider([]))->getSearchEngineReferrals();
        $html = View::load('components/tables/referrers', ['referrers' => $data['referrers'], 'total' => $data['referrals']], true);

        $this->assertStringContainsString('(no referrer)', $html);
        $this->assertStringNotContainsString('<span aria-hidden="true">—</span>', $html, 'no bare dash row');
        $this->assertStringContainsString('<span class="wps-referrer-source-inline"><bdi>Google Ads</bdi></span>', $html);
        $this->assertStringContainsString('<span class="wps-referrer-source-inline"><bdi>Microsoft Ads</bdi></span>', $html);
    }
}
