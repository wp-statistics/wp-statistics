<?php

use WP_STATISTICS\Menus;
use WP_Statistics\Service\Admin\PageHeader\PageHeader;
use WP_Statistics\Service\Admin\PageHeader\SingleHeaders;

/**
 * Shared single-report header (PageHeader): crumbs from legacy args, referrer crumb, normalization.
 */
class Test_PageHeader extends WP_UnitTestCase
{
    public function test_referrer_from_a_matching_list_replaces_the_last_crumb_and_its_label()
    {
        $crumbs = [
            ['label' => 'Visitor Insights', 'url' => Menus::admin_url('visitors'), 'from' => [['tab' => '']]],
            ['label' => 'Visitors', 'url' => Menus::admin_url('visitors', ['tab' => 'visitors']), 'from' => [['tab' => 'visitors'], ['tab' => 'top-visitors']], 'labels' => ['top-visitors' => 'Top Visitors']],
        ];
        $referrer = Menus::admin_url('visitors', ['tab' => 'top-visitors', 'order_by' => 'hits']);

        $result = PageHeader::applyReferrer($crumbs, $referrer);

        $this->assertSame($referrer, $result[1]['url']);
        $this->assertSame('Top Visitors', $result[1]['label']);
        $this->assertSame($crumbs[0]['url'], $result[0]['url']);
    }

    public function test_referrer_overview_goes_to_section_crumb()
    {
        $crumbs = [
            ['label' => 'Geographic', 'url' => Menus::admin_url('geographic'), 'from' => [['tab' => '']]],
            ['label' => 'Countries', 'url' => Menus::admin_url('geographic', ['tab' => 'countries']), 'from' => [['tab' => 'countries']]],
        ];
        $referrer = Menus::admin_url('geographic', ['from' => '2026-01-01']);

        $result = PageHeader::applyReferrer($crumbs, $referrer);

        $this->assertSame($referrer, $result[0]['url']);
        $this->assertSame($crumbs[1]['url'], $result[1]['url']);
    }

    public function test_referrer_from_another_section_or_single_report_is_ignored()
    {
        $crumbs = [['label' => 'Devices', 'url' => Menus::admin_url('devices'), 'from' => true]];

        $this->assertSame($crumbs, PageHeader::applyReferrer($crumbs, Menus::admin_url('visitors')));
        $this->assertSame($crumbs, PageHeader::applyReferrer($crumbs, Menus::admin_url('devices', ['type' => 'single-browser', 'browser' => 'Chrome'])));
        $this->assertSame($crumbs, PageHeader::applyReferrer($crumbs, ''));
    }

    public function test_only_admin_screens_of_this_site_count_as_referrer()
    {
        $this->assertTrue(PageHeader::isAdminScreenUrl(admin_url('admin.php?page=wps_visitors_page')));
        $this->assertFalse(PageHeader::isAdminScreenUrl('https://evil.example/wp-admin/admin.php?page=wps_visitors_page'));
        $this->assertFalse(PageHeader::isAdminScreenUrl(admin_url('edit.php')));
    }

    public function test_legacy_args_build_a_crumb_and_skip_generic_back_titles()
    {
        $header = PageHeader::fromLegacyArgs([
            'title'     => 'Event: Signup',
            'backUrl'   => Menus::admin_url('author-analytics'),
            'backTitle' => 'Previous Page',
            'flagImage' => 'https://example.test/flag.svg',
        ]);

        $this->assertCount(1, $header['crumbs']);
        $this->assertNotSame('Previous Page', $header['crumbs'][0]['label']);
        $this->assertTrue($header['crumbs'][0]['from']);
        $this->assertSame('Event: Signup', $header['title']);
        $this->assertSame('flag', $header['icon']['shape']);

        $tabbed = PageHeader::fromLegacyArgs(['backUrl' => Menus::admin_url('devices', ['tab' => 'browsers']), 'backTitle' => 'Browsers']);
        $last   = end($tabbed['crumbs']);
        $this->assertSame('Browsers', $last['label']);
        $this->assertSame(Menus::admin_url('devices', ['tab' => 'browsers']), $last['url']);
    }

    public function test_normalize_drops_empty_facts_and_crumbs_and_falls_back_to_title_arg()
    {
        $header = PageHeader::normalize([
            'crumbs' => [['label' => ''], ['label' => 'Devices', 'url' => '#']],
            'facts'  => [['text' => ''], ['text' => '8 versions'], 'bad'],
            'icon'   => ['src' => ''],
        ], ['title' => '<b>Chrome</b>', 'tooltip' => 'Tip']);

        $this->assertSame('Chrome', $header['title']);
        $this->assertCount(1, $header['crumbs']);
        $this->assertCount(1, $header['facts']);
        $this->assertNull($header['icon']);
        $this->assertSame('Tip', $header['tooltip']);
    }

    public function test_resolve_uses_explicit_header_and_filter()
    {
        $filter = function ($header) {
            $header['tag'] = 'Filtered';
            return $header;
        };
        add_filter('wp_statistics_page_header', $filter);

        $header = PageHeader::resolve(['pageHeader' => ['title' => 'Chrome', 'crumbs' => []], 'forceBackUrl' => true]);

        remove_filter('wp_statistics_page_header', $filter);

        $this->assertSame('Chrome', $header['title']);
        $this->assertSame('Filtered', $header['tag']);
        $this->assertNull(PageHeader::resolve(['title' => 'Overview']));
    }

    public function test_term_header_has_name_without_quotes_and_taxonomy_tag()
    {
        $termId = self::factory()->category->create(['name' => 'News']);
        $header = SingleHeaders::term($termId);

        $this->assertSame('News', $header['title']);
        $this->assertTrue($header['facts'][0]['tag']);
        $this->assertNotEmpty($header['link']);
        $this->assertCount(2, $header['crumbs']);
    }

    public function test_post_header_lists_type_author_and_terms()
    {
        $userId = self::factory()->user->create(['display_name' => 'Anastasia']);
        $postId = self::factory()->post->create(['post_title' => 'Hello there', 'post_author' => $userId]);
        $header = SingleHeaders::post($postId);
        $texts  = array_column($header['facts'], 'text');

        $this->assertSame('Hello there', $header['title']);
        $this->assertContains('Anastasia', $texts);
        $this->assertContains('Post', $texts);
        $this->assertNull(SingleHeaders::post(999999));
    }
}
