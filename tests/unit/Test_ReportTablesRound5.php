<?php

use WP_STATISTICS\Country;
use WP_Statistics\Components\View;
use WP_Statistics\Service\Charts\DataProvider\CountryChartDataProvider;

/**
 * 14.17 final QA (round 5, group 3): country share base includes visitors without a country,
 * tab-specific share labels, phone column priorities in the report tables.
 */
class Test_ReportTablesRound5 extends WP_UnitTestCase
{
    private function row($country, $visitors)
    {
        return (object)['country' => $country, 'visitors' => $visitors];
    }

    public function test_visitors_without_a_country_are_one_not_set_row()
    {
        $rows = CountryChartDataProvider::mergeUnknownCountries([
            $this->row('US', 50),
            $this->row(null, 4),
            $this->row('', 2),
            $this->row(Country::$unknown_location, 3),
            $this->row('DE', 8),
        ]);

        $this->assertSame(['US', Country::$unknown_location, 'DE'], array_map(function ($r) {
            return $r->country;
        }, $rows));
        $this->assertSame(9, $rows[1]->visitors);
        // The share base is every visitor.
        $this->assertSame(67, array_sum(array_map(function ($r) {
            return (int)$r->visitors;
        }, $rows)));
    }

    public function test_no_unknown_row_when_every_visitor_has_a_country()
    {
        $rows = CountryChartDataProvider::mergeUnknownCountries([$this->row('FR', 3), $this->row('IT', 5)]);

        // Unchanged, in the query's order.
        $this->assertSame(['FR', 'IT'], array_map(function ($r) {
            return $r->country;
        }, $rows));
    }

    public function test_referrer_share_cells_name_their_own_base()
    {
        $referral = new \WP_Statistics\Decorators\ReferralDecorator((object)[
            'referred'       => 'https://www.google.com/',
            'visitors'       => 4,
            'source_channel' => 'search',
            'source_name'    => 'Google',
        ]);

        $html = View::load('components/tables/referrers', ['referrers' => [$referral], 'total' => 8, 'share_label' => 'Share of search engine referrals'], true);
        $this->assertStringContainsString('Share of search engine referrals', $html);
        $this->assertStringNotContainsString('Share of all referrals', $html);

        $html = View::load('components/tables/referrers', ['referrers' => [$referral], 'total' => 8], true);
        $this->assertStringContainsString('Share of all referrals', $html);
    }

    public function test_secondary_columns_are_marked_for_phones()
    {
        $referral = new \WP_Statistics\Decorators\ReferralDecorator((object)['referred' => 'https://example.com/', 'visitors' => 1, 'source_channel' => 'referral', 'source_name' => 'Example']);
        $html     = View::load('components/tables/referrers', ['referrers' => [$referral], 'total' => 1], true);

        // Source Name goes first on narrow cards; the Share column stays.
        $this->assertSame(2, substr_count($html, 'wps-col-low'));
        $this->assertStringContainsString('wps-col-share', $html);
    }
}
