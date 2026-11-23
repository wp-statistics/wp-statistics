<?php

use WP_Statistics\Components\DateTime;
use WP_Statistics\Components\View;
use WP_Statistics\Service\Analytics\Referrals\SourceChannels;
use WP_Statistics\Service\Charts\ChartDataProviderFactory;
use WP_Statistics\Service\Database\Managers\TableHandler;
use WP_Statistics\Service\Database\Schema\Manager;
use WP_STATISTICS\DB;

/**
 * Top Source Categories widget: sorted by visitors with Direct at its real place, top 5 plus
 * an "Other" row (shares add to 100%), one colour per category, the widget range used for the
 * top domain, and Direct shown when it is the only category.
 */
class Test_TopSourceCategories extends WP_UnitTestCase
{
    private static $tables = ['visitor'];

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

    private function addVisitors($channel, $times, $referred = '', $date = null)
    {
        global $wpdb;

        static $n = 0;

        for ($i = 0; $i < $times; $i++) {
            $n++;
            $wpdb->insert(DB::table('visitor'), [
                'last_counter'   => $date ?: DateTime::get(),
                'referred'       => $referred,
                'agent'          => 'Chrome',
                'platform'       => 'Windows',
                'version'        => '1',
                'ip'             => '198.51.100.' . ($n % 250),
                'user_id'        => 0,
                'hits'           => 1,
                'source_channel' => $channel,
            ]);
        }
    }

    private function rows($args = [])
    {
        return ChartDataProviderFactory::topSourceCategories(array_merge(['date' => $this->range()], $args))->getData();
    }

    public function test_sorted_by_visitors_with_direct_at_its_real_place()
    {
        $this->addVisitors('search', 10, 'https://google.com/');
        $this->addVisitors('direct', 6);
        $this->addVisitors('social', 3, 'https://linkedin.com/');

        $rows = $this->rows();

        $this->assertSame(['search', 'direct', 'social'], array_column($rows, 'key'));
        $this->assertSame([10, 6, 3], array_column($rows, 'count'));
        $this->assertSame('Direct Traffic', $rows[1]['label']);
        $this->assertSame('Direct Traffic', $rows[1]['source_category']);
    }

    public function test_top_five_plus_other_row_adds_up_to_one_hundred()
    {
        $counts = ['search' => 9, 'direct' => 8, 'social' => 7, 'paid_search' => 6, 'email' => 5, 'video' => 4, 'referral' => 3];
        foreach ($counts as $channel => $times) {
            $this->addVisitors($channel, $times, $channel === 'direct' ? '' : "https://{$channel}.example/");
        }

        $rows = $this->rows();

        $this->assertCount(6, $rows);
        $this->assertSame(['search', 'direct', 'social', 'paid_search', 'email', 'other'], array_column($rows, 'key'));

        $other = end($rows);
        $this->assertTrue($other['is_other']);
        $this->assertSame(7, $other['count']);
        $this->assertSame('Other 2 categories', $other['label']);
        $this->assertStringContainsString('tab=source-categories', $other['url']);
        $this->assertSame('', $other['domain']);

        $this->assertSame(42, array_sum(array_column($rows, 'count')));
        $this->assertEqualsWithDelta(100, array_sum(array_column($rows, 'share')), 0.2);
    }

    public function test_no_other_row_with_five_categories_or_fewer()
    {
        foreach (['search', 'direct', 'social', 'paid_search', 'email'] as $channel) {
            $this->addVisitors($channel, 2, $channel === 'direct' ? '' : "https://{$channel}.example/");
        }

        $this->assertNotContains('other', array_column($this->rows(), 'key'));
    }

    public function test_direct_is_shown_when_it_is_the_only_category()
    {
        $this->addVisitors('direct', 4);

        $rows = $this->rows();

        $this->assertCount(1, $rows);
        $this->assertSame('direct', $rows[0]['key']);
        $this->assertSame('Direct Traffic', $rows[0]['label']);
        $this->assertSame(100.0, (float)$rows[0]['share']);
        $this->assertSame('', $rows[0]['domain']);
        $this->assertSame('-', $rows[0]['top_domain']); // old key, as add-on templates expect
        // Direct is not a referral: it opens the visitors list filtered to Direct, in the widget range.
        $this->assertStringContainsString('page=wps_visitors_page', $rows[0]['url']);
        $this->assertStringContainsString('source_channel=direct', $rows[0]['url']);
        $this->assertStringContainsString('from=', $rows[0]['url']);
    }

    public function test_colour_and_icon_follow_the_category_not_the_rank()
    {
        $this->addVisitors('social', 5, 'https://linkedin.com/');
        $this->addVisitors('search', 2, 'https://google.com/');
        $first = array_column($this->rows(), null, 'key');

        $this->emptyTables();

        $this->addVisitors('search', 9, 'https://google.com/');
        $this->addVisitors('social', 1, 'https://linkedin.com/');
        $second = array_column($this->rows(), null, 'key');

        foreach (['search', 'social'] as $key) {
            $this->assertSame(SourceChannels::getStyle($key)['color'], $first[$key]['color']);
            $this->assertSame($first[$key]['color'], $second[$key]['color']);
            $this->assertSame($first[$key]['icon'], $second[$key]['icon']);
        }

        $this->assertSame('#4285F4', $first['search']['color']);
        $this->assertNotSame($first['search']['color'], $first['social']['color']);
    }

    public function test_every_channel_has_its_own_style()
    {
        $styles = SourceChannels::getStyles();

        foreach (array_keys(SourceChannels::getList()) as $key) {
            $this->assertArrayHasKey($key, $styles, "No style for {$key}");
            $this->assertMatchesRegularExpression('/^#[0-9A-F]{6}$/i', $styles[$key]['color']);
            $this->assertNotEmpty($styles[$key]['icon']);
        }

        $this->assertSame(count($styles), count(array_unique(array_column($styles, 'color'))), 'Colours must be unique');
        $this->assertSame(SourceChannels::getStyle('unassigned'), SourceChannels::getStyle('not_a_channel'));
        $this->assertSame(SourceChannels::getStyle('unassigned'), SourceChannels::getStyle(''));
    }

    public function test_top_domain_uses_the_selected_date_range()
    {
        // In range: a.example; older, outside the range but more visitors: b.example
        $this->addVisitors('social', 2, 'https://a.example/');
        $this->addVisitors('social', 5, 'https://b.example/', DateTime::get('-30 days'));

        $rows = $this->rows();

        $this->assertSame('social', $rows[0]['key']);
        $this->assertSame(2, $rows[0]['count']);
        $this->assertSame('a.example', $rows[0]['domain']);
        $this->assertStringContainsString('from=' . $this->range()['from'], $rows[0]['url']);
        $this->assertStringContainsString('source_channel=social', $rows[0]['url']);
    }

    public function test_widget_renders_share_rows_with_icon_and_grey_domain()
    {
        $this->addVisitors('search', 3, 'https://google.com/');
        $this->addVisitors('direct', 1);

        $html = View::load('components/tables/source-categories', ['data' => $this->rows()], true);

        $this->assertStringContainsString('wps-horizontal-bar wps-share-list', $html);
        $this->assertSame(2, substr_count($html, 'wps-horizontal-bar__item'));
        $this->assertStringContainsString('style="color:#4285F4"', $html);
        $this->assertSame(1, substr_count($html, 'wps-share-list__meta'), 'Direct has no domain line');
        $this->assertStringContainsString('dir="ltr" title="google.com">google.com</span>', $html);
        $this->assertStringNotContainsString('>-<', $html);
        $this->assertStringNotContainsString('wps-source-category__', $html);
        $this->assertStringContainsString('75%', $html);
        $this->assertStringContainsString('25%', $html);
    }

    public function test_widget_still_reads_old_row_keys()
    {
        $html = View::load('components/tables/source-categories', ['data' => [
            ['source_category' => 'Organic Search', 'top_domain' => 'google.com', 'visitors' => '1,200', 'percentage' => '60%'],
            ['source_category' => 'Direct', 'top_domain' => '-', 'visitors' => '800', 'percentage' => '40%'],
        ]], true);

        $this->assertStringContainsString('Organic Search', $html);
        $this->assertStringContainsString('dir="ltr" title="google.com">google.com</span>', $html);
        $this->assertSame(1, substr_count($html, 'wps-share-list__meta'));
        $this->assertStringContainsString('60%', $html);
        $this->assertStringContainsString('40%', $html);
    }

    public function test_empty_range_shows_the_empty_state()
    {
        $html = View::load('components/tables/source-categories', ['data' => $this->rows()], true);

        $this->assertStringNotContainsString('wps-share-list', $html);
    }

    public function test_share_list_prints_numbers_like_the_js_rows_in_any_locale()
    {
        global $wp_locale;

        $saved = $wp_locale->number_format;
        $wp_locale->number_format = ['thousands_sep' => '٬', 'decimal_point' => ','];

        try {
            $html = View::load('components/objects/share-list', ['items' => [
                ['label' => 'A', 'count' => 1234],
                ['label' => 'B', 'count' => 999],
                ['label' => 'C', 'count' => 5248],
            ], 'total' => 7481], true);
        } finally {
            $wp_locale->number_format = $saved;
        }

        // wps_js.format_percent(): "16.5%", never "16,5%"
        $this->assertStringContainsString('<span>16.5%</span>', $html);
        $this->assertStringContainsString('<span>70.2%</span>', $html);
        $this->assertStringNotContainsString(',5%', $html);
        // wps_js.formatNumber(): 1234 -> 1.2K, 999 -> 1K, 5248 -> 5.2K; exact number on hover
        $this->assertStringContainsString('<span title="1234">1.2K</span>', $html);
        $this->assertStringContainsString('<span title="999">1K</span>', $html);
        $this->assertStringContainsString('<span title="5248">5.2K</span>', $html);
    }

    public function test_share_list_small_counts_and_whole_percents()
    {
        $html = View::load('components/objects/share-list', ['items' => [
            ['label' => 'A', 'count' => 40],
            ['label' => 'B', 'count' => 60],
        ], 'total' => 100], true);

        $this->assertStringContainsString('<span title="40">40</span>', $html);
        $this->assertStringContainsString('<span>40%</span>', $html);
        $this->assertStringContainsString('<span>60%</span>', $html);
    }

    public function test_source_category_chart_lines_use_the_category_colours()
    {
        $this->addVisitors('search', 5, 'https://www.google.com');
        $this->addVisitors('direct', 3);
        $this->addVisitors('social', 2, 'https://facebook.com');

        $chart    = ChartDataProviderFactory::sourceCategoryChart(['date' => $this->range()])->getData();
        $datasets = $chart['data']['datasets'];
        $byLabel  = array_column($datasets, null, 'label');

        $this->assertSame(SourceChannels::getStyle('search')['color'], $byLabel[SourceChannels::getName('search')]['color']);
        $this->assertSame(SourceChannels::getStyle('direct')['color'], $byLabel[SourceChannels::getName('direct')]['color']);
        $this->assertSame(SourceChannels::getStyle('social')['color'], $byLabel[SourceChannels::getName('social')]['color']);
        $this->assertSame(5, array_sum($byLabel[SourceChannels::getName('search')]['data']));

        // Total keeps its slug colour, no fixed colour.
        $total = end($datasets);
        $this->assertSame('total', $total['slug']);
        $this->assertArrayNotHasKey('color', $total);
        $this->assertSame(10, array_sum($total['data']));
    }
}
