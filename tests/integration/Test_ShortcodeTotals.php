<?php

use WP_STATISTICS\DB;
use WP_Statistics\Service\Database\Managers\TableHandler;
use WP_Statistics\Service\Database\Schema\Manager;

/**
 * [wpstatistics stat=referrer] counts referring domains (stored without a scheme), and
 * stat=visits time=total includes historical views like stat=visitors time=total includes
 * historical visitors. All values are made up.
 */
class Test_ShortcodeTotals extends WP_UnitTestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        TableHandler::createTable('visitor', Manager::getSchemaForTable('visitor'));
        TableHandler::createTable('historical', Manager::getSchemaForTable('historical'));
    }

    public static function tearDownAfterClass(): void
    {
        global $wpdb;
        $wpdb->query('DROP TABLE IF EXISTS `' . DB::table('visitor') . '`'); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $wpdb->query('DROP TABLE IF EXISTS `' . DB::table('historical') . '`'); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

        parent::tearDownAfterClass();
    }

    public function setUp(): void
    {
        parent::setUp();

        global $wpdb;
        $wpdb->query('DELETE FROM `' . DB::table('visitor') . '`'); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $wpdb->query('DELETE FROM `' . DB::table('historical') . '`'); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    }

    private function addVisitor($i, $referred, $hits = 1)
    {
        global $wpdb;

        $wpdb->insert(DB::table('visitor'), [
            'last_counter' => current_time('Y-m-d'),
            'last_view'    => current_time('mysql'),
            'referred'     => $referred,
            'agent'        => 'Chrome',
            'platform'     => 'Windows',
            'version'      => '141',
            'ip'           => '203.0.113.' . $i,
            'user_id'      => 0,
            'hits'         => $hits,
        ]);
    }

    public function test_referrer_counts_scheme_less_domains()
    {
        $this->addVisitor(1, 'google.com');
        $this->addVisitor(2, 'google.com');
        $this->addVisitor(3, 'www.bing.com');
        $this->addVisitor(4, 'https://example.net/page');
        $this->addVisitor(5, wp_parse_url(home_url(), PHP_URL_HOST));
        $this->addVisitor(6, '');

        $this->assertSame(3, wp_statistics_referrer('today'));
        $this->assertSame('3', trim(do_shortcode('[wpstatistics stat=referrer time=today]')));
    }

    public function test_visits_total_includes_historical_views_like_visitors_total()
    {
        global $wpdb;

        $this->addVisitor(1, '', 4);
        $this->addVisitor(2, '', 6);
        $wpdb->insert(DB::table('historical'), ['category' => 'visits', 'page_id' => -1, 'uri' => '-1', 'value' => 100]);
        $wpdb->insert(DB::table('historical'), ['category' => 'visitors', 'page_id' => -2, 'uri' => '-2', 'value' => 50]);

        $this->assertSame(110, (int)wp_statistics_visit('total'));
        $this->assertSame('110', trim(do_shortcode('[wpstatistics stat=visits time=total]')));
        $this->assertSame('52', trim(do_shortcode('[wpstatistics stat=visitors time=total]')));
        $this->assertSame('10', trim(do_shortcode('[wpstatistics stat=visits time=today]')), 'other ranges stay without history');
    }
}
