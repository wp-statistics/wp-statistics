<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_STATISTICS\Option;
use WP_Statistics\Decorators\VisitorDecorator;
use WP_Statistics\Service\Admin\ExportImport\Reports\ReportsExportDataTransformer;

/**
 * CSV exports follow the same privacy rule as the reports: no raw IP while
 * "Hash IP Addresses" is on. All values are made up.
 */
class Test_ReportsExportPrivacy extends WP_UnitTestCase
{
    public function tearDown(): void
    {
        Option::update('hash_ips', false);

        parent::tearDown();
    }

    private function visitor($ip)
    {
        return new VisitorDecorator((object)[
            'ID'           => 7,
            'ip'           => $ip,
            'user_id'      => 0,
            'location'     => 'DE',
            'region'       => '',
            'city'         => '',
            'platform'     => 'Windows',
            'device'       => 'desktop',
            'agent'        => 'Chrome',
            'referred'     => '',
            'source_channel' => 'direct',
            'source_name'  => 'Direct',
            'hits'         => 1,
            'last_counter' => '2026-11-20',
            'last_view'    => '2026-11-20 10:00:00',
            'first_view'   => '2026-11-20 10:00:00',
            'first_page'   => 0,
            'last_page'    => 0,
            'page_view'    => '2026-11-20 10:00:00',
        ]);
    }

    public function test_raw_ip_is_exported_when_hashing_is_off()
    {
        Option::update('hash_ips', false);

        $rows = ReportsExportDataTransformer::transformVisitorsData([$this->visitor('203.0.113.7')]);

        $this->assertSame('203.0.113.7', $rows[0]['ip']);
        $this->assertSame('', $rows[0]['hash_ip']);
    }

    public function test_raw_ip_is_never_exported_when_hashing_is_on()
    {
        Option::update('hash_ips', true);

        $visitors = ReportsExportDataTransformer::transformVisitorsData([$this->visitor('203.0.113.7')]);
        $views    = ReportsExportDataTransformer::transformViewsData([$this->visitor('203.0.113.8')]);

        $this->assertSame('', $visitors[0]['ip'], 'a row saved before hashing was on keeps its raw IP in the DB, not in the CSV');
        $this->assertSame('', $views[0]['ip']);
    }

    public function test_hashed_ip_goes_to_the_hash_column()
    {
        Option::update('hash_ips', true);

        $hash = '#hash#' . str_repeat('a', 40);
        $rows = ReportsExportDataTransformer::transformVisitorsData([$this->visitor($hash)]);

        $this->assertSame('', $rows[0]['ip']);
        $this->assertSame($hash, $rows[0]['hash_ip']);
    }
}
