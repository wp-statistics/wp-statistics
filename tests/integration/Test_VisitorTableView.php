<?php

namespace WP_Statistics\Tests\Views;

use WP_Statistics\Components\DateTime;
use WP_Statistics\Components\View;
use WP_Statistics\Decorators\VisitorDecorator;
use WP_UnitTestCase;

/**
 * Shared visitor table (views/components/tables/visitor-table.php), the Pages cell
 * (views/components/objects/visitor-pages.php) and the short-date rule
 * (VisitorDecorator::compactDateParts()). All visitors and pages are made up.
 */
class Test_VisitorTableView extends WP_UnitTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        update_option('date_format', 'F j, Y');
        update_option('time_format', 'g:i a');
    }

    public function test_short_date_rule()
    {
        $today = '2026-11-22';

        $now = VisitorDecorator::compactDateParts('2026-11-22 11:39:00', $today);
        $this->assertSame('11:39 am', $now['wide']);
        $this->assertSame('11:39 am', $now['narrow']);
        $this->assertSame('November 22, 2026, 11:39 am', $now['full']);
        $this->assertSame('2026-11-22T11:39', $now['iso']);

        $yesterday = VisitorDecorator::compactDateParts('2026-11-21 16:52:00', $today);
        $this->assertSame('Nov 21, 4:52 pm', $yesterday['wide']);
        $this->assertSame('Yesterday', $yesterday['narrow']);

        $thisYear = VisitorDecorator::compactDateParts('2026-11-19 19:41:00', $today);
        $this->assertSame('Nov 19, 7:41 pm', $thisYear['wide']);
        $this->assertSame('Nov 19', $thisYear['narrow']);

        $older = VisitorDecorator::compactDateParts('2025-11-19 19:41:00', $today);
        $this->assertSame('Nov 19, 2025, 7:41 pm', $older['wide']);
        $this->assertSame('Nov 19, 2025', $older['narrow']);

        $dateOnly = VisitorDecorator::compactDateParts('2026-11-22', $today);
        $this->assertSame('Today', $dateOnly['wide']);

        $this->assertNull(VisitorDecorator::compactDateParts('', $today));
    }

    public function test_short_date_follows_the_site_format()
    {
        update_option('date_format', 'j. F Y');
        update_option('time_format', 'H:i');

        $parts = VisitorDecorator::compactDateParts('2026-11-19 19:41:00', '2026-11-22');
        $this->assertSame('19. Nov, 19:41', $parts['wide']);
        $this->assertSame('19. Nov', $parts['narrow']);

        update_option('date_format', 'd/m/Y');
        $this->assertSame('19/11', VisitorDecorator::compactDateParts('2026-11-19 19:41:00', '2026-11-22')['narrow']);
    }

    private function page($title, $report = '')
    {
        return ['id' => 1, 'link' => '', 'title' => $title, 'query' => '', 'report' => $report ?: 'https://example.test/report/' . md5($title), 'sub_page' => ''];
    }

    public function test_pages_cell_two_lines_with_icons_only()
    {
        $html = View::load('components/objects/visitor-pages', ['entry' => $this->page('Pricing'), 'exit' => $this->page('Cart'), 'views' => 3], true);

        $this->assertSame(2, substr_count($html, 'wps-visitor-pages__line '), 'entry + exit lines');
        $this->assertStringContainsString('title="Entry page"', $html);
        $this->assertStringContainsString('<span class="screen-reader-text">Exit page</span>', $html);
        $this->assertStringNotContainsString('>In<', $html, 'no In/Out words');
        $this->assertStringNotContainsString('>Out<', $html);
        $this->assertStringContainsString('Pricing', $html);
        $this->assertStringContainsString('Cart', $html);
        $this->assertStringContainsString('wps-ellipsis-text', $html, 'each line cuts with the title-cell ellipsis');
    }

    public function test_pages_cell_same_page_once_with_one_page_tag()
    {
        $page = $this->page('Home');
        $one  = View::load('components/objects/visitor-pages', ['entry' => $page, 'exit' => $page, 'views' => 1], true);

        $this->assertSame(1, substr_count($one, 'wps-visitor-pages__line '));
        $this->assertSame(1, substr_count($one, '>Home<'));
        $this->assertStringContainsString('title="Entry and exit page"', $one);
        $this->assertStringContainsString('1 page', $one);

        $two = View::load('components/objects/visitor-pages', ['entry' => $page, 'exit' => $page, 'views' => 2], true);
        $this->assertStringNotContainsString('1 page', $two, 'several views on one page: no tag');
    }

    public function test_pages_cell_missing_exit_campaign_and_unknown()
    {
        $entry          = $this->page('Shop');
        $entry['query'] = 'utm_source=news&utm_campaign=always-on';

        $html = View::load('components/objects/visitor-pages', ['entry' => $entry, 'exit' => null, 'views' => 1], true);
        $this->assertStringContainsString('Not recorded', $html);
        $this->assertStringContainsString('wps-campaign-label', $html);
        $this->assertStringContainsString('always-on', $html);

        $online = View::load('components/objects/visitor-pages', ['entry' => $this->page('A'), 'exit' => $this->page('B'), 'exit_label' => 'Current page'], true);
        $this->assertStringContainsString('title="Current page"', $online);

        $none = View::load('components/objects/visitor-pages', ['entry' => null, 'exit' => null], true);
        $this->assertStringContainsString('Unknown', $none);
        $this->assertStringNotContainsString('wps-visitor-pages', $none);
    }

    private function visitor($row = [])
    {
        return new VisitorDecorator((object)array_merge([
            'ID'        => 7,
            'ip'        => '#hash#abc',
            'location'  => 'DE',
            'agent'     => 'Chrome',
            'platform'  => 'Windows',
            'device'    => 'desktop',
            'version'   => '150.0',
            'model'     => '',
            'region'    => '',
            'city'      => '',
            'continent' => '',
            'user_id'   => 0,
            'referred'  => 'https://www.google.com/',
            'source_channel' => 'search',
            'source_name'    => 'Google',
            'hits'      => 12480,
            'last_view' => DateTime::get('now', 'Y-m-d') . ' 09:15:00',
        ], $row));
    }

    private function headers($html)
    {
        preg_match_all('/<th scope="col"[^>]*>\s*(.*?)\s*<\/th>/s', $html, $m);

        return array_map(function ($cell) {
            return trim(wp_strip_all_tags($cell));
        }, $m[1]);
    }

    public function test_table_has_one_fixed_column_order_and_moves_the_sort()
    {
        $latest = View::load('components/tables/visitor-table', ['visitors' => [$this->visitor()], 'sort' => 'last'], true);
        $active = View::load('components/tables/visitor-table', ['visitors' => [$this->visitor()], 'sort' => 'views'], true);

        $order = ['Visitor', 'Referrer', 'Pages', 'Views', 'Last view'];
        $this->assertSame($order, $this->headers($latest));
        $this->assertSame($order, $this->headers($active), 'same order, only the sort changes');

        $this->assertMatchesRegularExpression('/wps-visitor-table__last is-sorted" aria-sort="descending">\s*<span class="wps-order">Last view/', $latest);
        $this->assertMatchesRegularExpression('/wps-visitor-table__views is-sorted" aria-sort="descending">\s*<span class="wps-order">Views/', $active);
        $this->assertStringContainsString('wps-visitor-table--sort-views', $active);
        $this->assertStringContainsString(number_format_i18n(12480), $active, 'huge numbers are formatted');
        $this->assertStringContainsString('9:15 am', $latest, 'today: time only');
        $this->assertStringNotContainsString('Latest Page', $latest);
        $this->assertStringContainsString('wps-visitor-table__ref-inline', $latest, 'referrer folds under the pill on narrow cards');
        $this->assertStringContainsString('google.com', $latest);
    }

    public function test_table_empty_state_and_add_on_args()
    {
        $empty = View::load('components/tables/visitor-table', ['visitors' => [], 'empty' => ['message' => 'Nobody yet', 'range' => false]], true);
        $this->assertStringContainsString('Nobody yet', $empty);
        $this->assertStringNotContainsString('<table', $empty);

        $this->assertSame('', trim(View::load('components/tables/visitor-table', ['visitors' => [], 'empty' => false], true)));

        // Data Plus (older add-on) loads components/tables/visitors with these args.
        $legacy = View::load('components/tables/visitors', [
            'data'                    => [$this->visitor(['page_view' => '2026-11-19 19:41:00'])],
            'single_post'             => true,
            'hide_latest_page_column' => true,
            'hide_entry_page_column'  => true,
            'open_links_in_new_tab'   => true,
        ], true);
        $this->assertSame(['Visitor', 'Referrer', 'Views', 'Page view'], $this->headers($legacy));
        $this->assertStringContainsString('target="_blank"', $legacy);
    }
}
