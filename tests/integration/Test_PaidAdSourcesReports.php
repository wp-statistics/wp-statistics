<?php

namespace WP_Statistics\Tests\Service\Referrals;

use WP_STATISTICS\DB;
use WP_STATISTICS\Option;
use WP_Statistics\Service\Admin\Referrals\ReferralsDataProvider;
use WP_Statistics\Service\Admin\Referrals\ReferralsManager;
use WP_Statistics\Service\Analytics\Referrals\SourceDetector;
use WP_Statistics\Service\Database\Managers\TableHandler;
use WP_Statistics\Service\Database\Schema\Manager;
use WP_UnitTestCase;

/**
 * Paid clicks that come without a referrer (Microsoft Ads, OpenAI Ads) are counted under
 * last-touch attribution, and listed in the Search Engines and Social Media tables, so the
 * tables add up to the charts above them. All values are made up.
 */
class Test_PaidAdSourcesReports extends WP_UnitTestCase
{
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
    }

    private $ip = 0;

    private function addVisitor($referred, $channel, $name)
    {
        global $wpdb;

        $wpdb->insert(DB::table('visitor'), [
            'last_counter'   => gmdate('Y-m-d'),
            'last_view'      => gmdate('Y-m-d H:i:s'),
            'referred'       => $referred,
            'agent'          => 'Chrome',
            'platform'       => 'Windows',
            'version'        => '141',
            'ip'             => '10.0.0.' . (++$this->ip),
            'user_id'        => 0,
            'source_channel' => $channel,
            'source_name'    => $name,
        ]);
    }

    private function profile($referrer, $pageUrl)
    {
        $source = new SourceDetector($referrer, $pageUrl);

        return new class($referrer, $source) {
            private $referrer;
            private $source;

            public function __construct($referrer, $source)
            {
                $this->referrer = $referrer;
                $this->source   = $source;
            }

            public function isReferred()
            {
                return !empty($this->referrer);
            }

            public function getReferrer()
            {
                return $this->referrer;
            }

            public function getSource()
            {
                return $this->source;
            }
        };
    }

    public function test_last_touch_takes_a_paid_click_without_referrer()
    {
        Option::update('attribution_model', 'last-touch');
        $existing = ['referred' => 'google.com', 'source_channel' => 'search', 'source_name' => 'Google'];

        $data = (new ReferralsManager())->handleLastTouchAttributionModel($existing, $this->profile('', 'http://example.com/?msclkid=test123'));

        $this->assertSame('paid_search', $data['source_channel']);
        $this->assertSame('Microsoft Ads', $data['source_name']);
        $this->assertSame('google.com', $data['referred'], 'no new referrer to store, the old one is kept');

        $data = (new ReferralsManager())->handleLastTouchAttributionModel($existing, $this->profile('', 'http://example.com/?oppref=test123'));

        $this->assertSame('paid_other', $data['source_channel']);
        $this->assertSame('OpenAI Ads', $data['source_name']);
    }

    public function test_last_touch_without_referrer_and_without_paid_click_changes_nothing()
    {
        Option::update('attribution_model', 'last-touch');
        $existing = ['referred' => 'google.com', 'source_channel' => 'search', 'source_name' => 'Google'];
        $manager  = new ReferralsManager();

        $this->assertSame($existing, $manager->handleLastTouchAttributionModel($existing, $this->profile('', 'http://example.com/')));
        $this->assertSame($existing, $manager->handleLastTouchAttributionModel($existing, $this->profile('', 'http://example.com/?utm_source=newsletter&utm_medium=email')));
        $this->assertSame($existing, $manager->handleLastTouchAttributionModel($existing, $this->profile('', 'http://example.com/?fbclid=test123')));
    }

    public function test_last_touch_with_referrer_is_unchanged()
    {
        Option::update('attribution_model', 'last-touch');

        $data = (new ReferralsManager())->handleLastTouchAttributionModel(
            ['referred' => '', 'source_channel' => 'direct', 'source_name' => 'Direct'],
            $this->profile('https://www.bing.com/search?q=example', 'http://example.com/')
        );

        $this->assertSame('bing.com', \WP_Statistics\Utils\Url::getDomain($data['referred']));
        $this->assertSame('search', $data['source_channel']);
        $this->assertSame('Bing', $data['source_name']);
    }

    public function test_first_touch_never_changes_the_source()
    {
        Option::update('attribution_model', 'first-touch');
        $existing = ['referred' => '', 'source_channel' => 'direct', 'source_name' => 'Direct'];

        $this->assertSame($existing, (new ReferralsManager())->handleLastTouchAttributionModel($existing, $this->profile('', 'http://example.com/?msclkid=test123')));
    }

    public function test_search_engines_table_adds_up_to_the_chart()
    {
        $this->addVisitor('', 'paid_search', 'Microsoft Ads');
        $this->addVisitor('', 'paid_search', 'Microsoft Ads');
        $this->addVisitor('', 'paid_search', 'Microsoft Ads');
        $this->addVisitor('bing.com', 'search', 'Bing');
        $this->addVisitor('google.com', 'search', 'Google');
        $this->addVisitor('google.com', 'paid_search', 'Google Ads');
        $this->addVisitor('', 'paid_other', 'OpenAI Ads');

        $data  = (new ReferralsDataProvider([]))->getSearchEngineReferrals();
        $rows  = [];
        $total = 0;

        foreach ($data['referrers'] as $referrer) {
            $rows[$referrer->getRawReferrer() . '|' . $referrer->getRawSourceChannel() . '|' . $referrer->getSourceName()] = $referrer->getTotalReferrals(true);
            $total += $referrer->getTotalReferrals(true);
        }

        $this->assertSame(6, $total, 'the table adds up to what the chart counts for these channels');
        $this->assertSame(3, $rows['|paid_search|Microsoft Ads']);
        $this->assertSame(1, $rows['bing.com|search|Bing']);
        $this->assertSame(1, $rows['google.com|search|Google']);
        $this->assertSame(1, $rows['google.com|paid_search|Google Ads']);
        $this->assertCount(4, $rows);
        $this->assertSame(4, (int)$data['total'], 'the page count matches the rows');
    }

    public function test_no_referrer_clicks_with_different_names_are_separate_rows()
    {
        $this->addVisitor('', 'paid_social', 'Facebook');
        $this->addVisitor('', 'paid_social', 'Instagram');
        $this->addVisitor('l.facebook.com', 'paid_social', 'Facebook Ads');
        $this->addVisitor('l.facebook.com', 'paid_social', 'Facebook Ads');

        $data = (new ReferralsDataProvider([]))->getSocialMediaReferrals();
        $rows = [];

        foreach ($data['referrers'] as $referrer) {
            $rows[$referrer->getRawReferrer() . '|' . $referrer->getSourceName()] = $referrer->getTotalReferrals(true);
        }

        $this->assertEquals(['|Facebook' => 1, '|Instagram' => 1, 'l.facebook.com|Facebook Ads' => 2], $rows);
        $this->assertSame(3, (int)$data['total'], 'the page count matches the rows');
    }

    public function test_referrers_tab_still_lists_only_referrer_domains()
    {
        $this->addVisitor('', 'paid_search', 'Microsoft Ads');
        $this->addVisitor('bing.com', 'search', 'Bing');

        $data = (new ReferralsDataProvider([]))->getReferrers();

        $this->assertCount(1, $data['referrers']);
        $this->assertSame('bing.com', $data['referrers'][0]->getRawReferrer());
        $this->assertSame(1, (int)$data['total'], 'the page count matches the rows');
    }
}
