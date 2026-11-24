<?php

use WP_STATISTICS\DB;
use WP_STATISTICS\IP;
use WP_STATISTICS\TimeZone;
use WP_STATISTICS\Visitor;
use WP_Statistics\Service\Analytics\VisitorProfile;
use WP_Statistics\Service\Database\Managers\TableHandler;
use WP_Statistics\Service\Database\Schema\Manager;

/**
 * Owner test 14.17: one Mac/Chrome opened the site in three tabs at once and Online showed three
 * anonymous visitors. The three first hits of the day each saw yesterday's salt, each made a new
 * one, so each tab got its own IP hash and its own visitor row. Only one request may create the
 * day's salt now, and a first hit that loses the insert race is counted on the winner's row.
 */
class Test_OnlineVisitorRaces extends WP_UnitTestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        TableHandler::createTable('visitor', Manager::getSchemaForTable('visitor'));
    }

    public function setUp(): void
    {
        parent::setUp();
        global $wpdb;
        $wpdb->query('DELETE FROM `' . DB::table('visitor') . '`');
        IP::resetDailySaltCache();
    }

    public function tearDown(): void
    {
        IP::resetDailySaltCache();
        update_option('gmt_offset', 0);
        parent::tearDown();
    }

    private function storeRaw($value)
    {
        global $wpdb;
        $wpdb->update($wpdb->options, ['option_value' => maybe_serialize($value)], ['option_name' => IP::DAILY_SALT_OPTION]);
    }

    public function test_a_request_that_saw_the_stale_salt_uses_the_one_another_request_stored()
    {
        $today = TimeZone::getCurrentDate('Y-m-d');

        // This request read yesterday's salt (it is in the options cache)...
        update_option(IP::DAILY_SALT_OPTION, ['date' => '2000-01-01', 'salt' => 'yesterday']);
        $this->assertSame('yesterday', get_option(IP::DAILY_SALT_OPTION)['salt']);

        // ...while the first tab's request already stored today's salt in the database.
        $this->storeRaw(['date' => $today, 'salt' => 'first-tab']);

        $this->assertSame('first-tab', IP::getDailySalt(), 'No second salt for the same day');
        $this->assertSame('first-tab', get_option(IP::DAILY_SALT_OPTION)['salt']);
    }

    public function test_a_missing_salt_is_created_once()
    {
        global $wpdb;
        $today = TimeZone::getCurrentDate('Y-m-d');

        delete_option(IP::DAILY_SALT_OPTION);
        $this->assertFalse(get_option(IP::DAILY_SALT_OPTION)); // cached as missing

        // Another request inserted it in the meantime.
        $wpdb->insert($wpdb->options, ['option_name' => IP::DAILY_SALT_OPTION, 'option_value' => maybe_serialize(['date' => $today, 'salt' => 'other-request']), 'autoload' => 'yes']);

        $this->assertSame('other-request', IP::getDailySalt());
    }

    public function test_salt_rotates_once_per_site_day_and_hash_is_stable_within_it()
    {
        update_option('gmt_offset', 14); // far from UTC: the site day is not the UTC day for part of the day
        update_option(IP::DAILY_SALT_OPTION, ['date' => '2000-01-01', 'salt' => 'old']);

        $first = IP::hashUserIp('203.0.113.7');
        $salt  = get_option(IP::DAILY_SALT_OPTION);

        $this->assertSame(TimeZone::getCurrentDate('Y-m-d'), $salt['date'], 'Same day as the visitor row (last_counter)');
        $this->assertNotSame('old', $salt['salt']);

        // Next request (no per-request cache): same salt, same hash.
        IP::resetDailySaltCache();
        $this->assertSame($first, IP::hashUserIp('203.0.113.7'));
    }

    /**
     * A visitor profile for one Mac/Chrome hit whose "already a visitor today?" check ran before
     * the other tab's row existed.
     */
    private function profile($ip)
    {
        $agent = $this->getMockBuilder(\stdClass::class)->addMethods(['getBrowser', 'getPlatform', 'getVersion', 'getDevice', 'getModel'])->getMock();
        $agent->method('getBrowser')->willReturn('Chrome');
        $agent->method('getPlatform')->willReturn('Mac');
        $agent->method('getVersion')->willReturn('158');
        $agent->method('getDevice')->willReturn('desktop');
        $agent->method('getModel')->willReturn('');

        $source = $this->getMockBuilder(\stdClass::class)->addMethods(['getName', 'getChannel'])->getMock();
        $source->method('getName')->willReturn('');
        $source->method('getChannel')->willReturn('direct');

        $profile = $this->getMockBuilder(VisitorProfile::class)->disableOriginalConstructor()->onlyMethods([
            'getUserAgent', 'isIpActiveToday', 'getReferrer', 'getSource', 'getProcessedIPForStorage', 'getCountry',
            'getCity', 'getRegion', 'getContinent', 'getUserId', 'getHttpUserAgent', 'getCurrentPageType',
        ])->getMock();
        $profile->method('getUserAgent')->willReturn($agent);
        $profile->method('isIpActiveToday')->willReturn(false);
        $profile->method('getReferrer')->willReturn('');
        $profile->method('getSource')->willReturn($source);
        $profile->method('getProcessedIPForStorage')->willReturn($ip);
        $profile->method('getCountry')->willReturn('000');
        $profile->method('getCity')->willReturn('');
        $profile->method('getRegion')->willReturn('');
        $profile->method('getContinent')->willReturn('');
        $profile->method('getUserId')->willReturn(0);
        $profile->method('getHttpUserAgent')->willReturn('');
        $profile->method('getCurrentPageType')->willReturn(['type' => 'post', 'id' => 1]);

        return $profile;
    }

    public function test_a_first_hit_that_loses_the_insert_race_counts_on_the_same_row()
    {
        global $wpdb;
        $ip = '#hash#0123456789abcdef0123456789abcdef01234567';

        $first  = Visitor::record($this->profile($ip), ['page_id' => 10]);
        $second = Visitor::record($this->profile($ip), ['page_id' => 11]); // its "visitor today?" check said no

        $this->assertGreaterThan(0, $first);
        $this->assertSame((int) $first, (int) $second, 'The second tab gets the same visitor, not ID 0');

        $rows = $wpdb->get_results('SELECT * FROM `' . DB::table('visitor') . '`');
        $this->assertCount(1, $rows, 'One device, one visitor row');
        $this->assertSame(2, (int) $rows[0]->hits);
        $this->assertSame(11, (int) $rows[0]->last_page);
    }
}
