<?php

namespace WP_Statistics\Tests;

use WP_Statistics\Components\View;
use WP_STATISTICS\Helper;
use WP_Statistics\Service\Charts\DataProvider\CountryChartDataProvider;
use WP_UnitTestCase;

/**
 * Shared restyle components: country share total, percent bar, change chip, empty state.
 */
class Test_ShareComponents extends WP_UnitTestCase
{
    /**
     * Rows as getVisitorsGeoData() returns them, sorted by visitors.
     */
    private function geoRows(array $counts)
    {
        $codes = ['US', 'DE', 'GB', 'FR', 'NL', 'IN', 'IT', 'ES', 'CA', 'BR'];
        $rows  = [];
        $i     = 0;

        foreach ($counts as $count) {
            $rows[] = (object)['country' => $codes[$i++], 'visitors' => (string)$count];
        }

        return $rows;
    }

    private function parse(array $rows, array $args = [])
    {
        $provider = new CountryChartDataProvider($args);
        $method   = new \ReflectionMethod($provider, 'parseData');
        if (PHP_VERSION_ID < 80100) {
            $method->setAccessible(true);
        }

        return $method->invoke($provider, $rows);
    }

    /**
     * The bug: 578 of 3,501 visitors showed as 40.4% (share of the top five). It must be 16.5%.
     */
    public function test_country_share_uses_real_total_not_top_five()
    {
        // 578 + 295 + 220 + 188 + 149 = 1,430 shown; 2,071 more spread over 5 other countries.
        $rows   = $this->geoRows([578, 295, 220, 188, 149, 600, 500, 400, 300, 271]);
        $parsed = $this->parse($rows);

        $this->assertSame(3501, $parsed['total']);
        $this->assertSame(16.5, $parsed['percentages'][0]);
        $this->assertNotSame(40.4, $parsed['percentages'][0]);
    }

    public function test_country_chart_adds_other_row_that_closes_the_total()
    {
        $rows   = $this->geoRows([578, 295, 220, 188, 149, 600, 500, 400, 300, 271]);
        $parsed = $this->parse($rows);

        $this->assertCount(6, $parsed['labels'], 'Top 5 plus one Other row.');
        $this->assertSame(2071, end($parsed['visitors']));
        $this->assertStringContainsString('5', end($parsed['labels']));
        $this->assertSame('', end($parsed['icons']));
        $this->assertSame($parsed['total'], array_sum($parsed['visitors']), 'Rows add up to the total, so a sum-based fallback is also right.');
        $this->assertSame(59.2, end($parsed['percentages']));
    }

    public function test_country_chart_without_extra_rows_has_no_other_row()
    {
        $parsed = $this->parse($this->geoRows([10, 5, 5]));

        $this->assertCount(3, $parsed['labels']);
        $this->assertSame(20, $parsed['total']);
        $this->assertSame([50.0, 25.0, 25.0], $parsed['percentages']);
    }

    public function test_country_chart_empty_data()
    {
        $parsed = $this->parse([]);

        $this->assertSame(0, $parsed['total']);
        $this->assertSame([], $parsed['labels']);
    }

    public function test_percent_bar_share_from_count_and_total()
    {
        $html = View::load('components/objects/percent-bar', ['count' => 578, 'total' => 3501], true);

        $this->assertStringContainsString('wps-percent--share', $html);
        $this->assertStringContainsString('16.5%', $html);
        $this->assertStringContainsString('aria-hidden="true"', $html);
        $this->assertStringContainsString('width:16.5%', $html);
    }

    public function test_percent_bar_ring_and_rate_variants()
    {
        $ring = View::load('components/objects/percent-bar', ['value' => 43.4, 'variant' => 'ring'], true);
        $this->assertStringContainsString('wps-percent__ring', $ring);
        $this->assertStringContainsString('43.4%', $ring);

        $rate = View::load('components/objects/percent-bar', ['value' => 40, 'variant' => 'rate'], true);
        $this->assertStringContainsString('wps-percent--rate', $rate);
        $this->assertStringContainsString('>40%<', $rate, 'No trailing .0');

        $clamped = View::load('components/objects/percent-bar', ['value' => 140], true);
        $this->assertStringContainsString('100%', $clamped);
    }

    public function test_change_chip_arrow_follows_sign_and_color_follows_polarity()
    {
        // Bounce rate went down: good, arrow down.
        $chip = View::load('components/objects/change-chip', ['change' => -0.6, 'polarity' => 'negative'], true);
        $this->assertStringContainsString('wps-change--down', $chip);
        $this->assertStringContainsString('wps-change--good', $chip);

        // Exits went up: bad, arrow up.
        $chip = View::load('components/objects/change-chip', ['change' => 8.2, 'polarity' => 'negative'], true);
        $this->assertStringContainsString('wps-change--up', $chip);
        $this->assertStringContainsString('wps-change--bad', $chip);

        // Neutral metric: grey either way.
        $chip = View::load('components/objects/change-chip', ['change' => -1.5, 'polarity' => 'neutral'], true);
        $this->assertStringContainsString('wps-change--down', $chip);
        $this->assertStringContainsString('wps-change--flat', $chip);

        $this->assertSame('', trim(View::load('components/objects/change-chip', ['change' => null], true)));
    }

    public function test_glance_card_keeps_legacy_chip_classes()
    {
        $html = View::load('components/objects/glance-card', ['metrics' => [
            ['label' => 'Bounce Rate', 'value' => '43.4%', 'change' => -0.6, 'polarity' => 'negative', 'ring' => 43.4, 'hint' => true],
        ]], true);

        $this->assertStringContainsString('wps-at-a-glance-change', $html);
        $this->assertStringContainsString('wps-glance-down', $html);
        $this->assertStringContainsString('wps-glance-positive', $html);
        $this->assertStringContainsString('wps-change--down', $html);
        $this->assertStringContainsString('wps-percent__ring', $html);
        $this->assertStringContainsString('Lower is better', $html);
    }

    public function test_empty_state_message_and_range()
    {
        $html = View::load('components/objects/empty-state', ['date' => ['from' => '2024-01-01', 'to' => '2024-01-07']], true);

        $this->assertStringContainsString('No data found for this date range.', $html);
        $this->assertStringContainsString('Jan 1 - Jan 7, 2024', $html);

        $legacy = View::load('components/objects/no-data', ['title' => 'No activity right now.'], true);
        $this->assertStringContainsString('No activity right now.', $legacy);
        $this->assertStringContainsString('wps--no-data__container', $legacy);
        $this->assertStringNotContainsString('wps-empty-state__range', $legacy);
    }

    public function test_no_data_message_coming_soon_only_when_range_starts_today()
    {
        $today = gmdate('Y-m-d');
        $past  = gmdate('Y-m-d', strtotime('-30 days'));

        $this->assertSame('No data found for this date range.', Helper::getNoDataMessage($today, $past));
        $this->assertSame('Data coming soon!', Helper::getNoDataMessage($today, $today));
        // Legacy single-argument call keeps its old meaning.
        $this->assertSame('Data coming soon!', Helper::getNoDataMessage($today));
    }
}
