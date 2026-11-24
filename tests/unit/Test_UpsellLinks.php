<?php

use WP_Statistics\Components\View;
use WP_STATISTICS\Admin_Template;
use WP_STATISTICS\Helper;
use WP_Statistics\Service\Admin\LicenseManagement\UpsellLink;
use WP_Statistics\Service\Admin\ModalHandler\Modal;
use WP_Statistics\Service\HooksManager;

/**
 * Upsell links: the words say where you go, on every surface.
 *
 *  - "Upgrade to Premium" = pricing page, new tab, never the popup.
 *  - "Learn more"         = wp-statistics.com add-on page (or add-ons list), new tab, never the popup.
 *  - "See what's included" = opens the Premium popup (small pointers only).
 *  - Every wp-statistics.com link: utm_source=wp-statistics, utm_medium=<surface>, utm_campaign=<add-on|premium>.
 *  - White label: no popup markup and no upsell on any of these surfaces.
 */
class Test_UpsellLinks extends WP_UnitTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $admin = self::factory()->user->create(['role' => 'administrator']);
        if (is_multisite()) {
            grant_super_admin($admin);
        }
        wp_set_current_user($admin);
    }

    public function tearDown(): void
    {
        remove_all_filters('wp_statistics_hide_addon_prompts');
        remove_all_filters('pre_wp_mail');
        parent::tearDown();
    }

    /**
     * Every link in the HTML: label, href, classes, target.
     *
     * @return array[]
     */
    private function links($html)
    {
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="utf-8"?><div>' . $html . '</div>');
        libxml_clear_errors();

        $links = [];
        foreach (['a', 'button'] as $tag) {
            foreach ($doc->getElementsByTagName($tag) as $node) {
                $links[] = [
                    'label'  => trim(preg_replace('/\s+/', ' ', $node->textContent)),
                    'aria'   => $node->getAttribute('aria-label'),
                    'href'   => $node->getAttribute('href'),
                    'class'  => $node->getAttribute('class'),
                    'target' => $node->getAttribute('target'),
                    'medium' => $node->getAttribute('data-surface'),
                ];
            }
        }

        return $links;
    }

    private function query($url)
    {
        parse_str((string)wp_parse_url($url, PHP_URL_QUERY), $query);

        return $query;
    }

    /**
     * The label → action rules, plus the utm scheme on every wp-statistics.com link.
     *
     * @return int Number of upsell links checked.
     */
    private function assertLabelRules($html, $surface)
    {
        $checked = 0;

        foreach ($this->links($html) as $link) {
            $popup = strpos($link['class'], 'js-wps-openPremiumModal') !== false;
            $label = $link['label'];

            if ($label === 'Upgrade to Premium') {
                $this->assertStringStartsWith(WP_STATISTICS_SITE_URL . '/pricing/', $link['href'], "$surface: Upgrade to Premium goes to pricing");
                $this->assertSame('_blank', $link['target'], "$surface: Upgrade to Premium opens a new tab");
                $this->assertFalse($popup, "$surface: Upgrade to Premium never opens the popup");
                $this->assertSame('upgrade', $this->query($link['href'])['utm_content'] ?? '', $surface);
                $checked++;
            } elseif ($label === 'Learn more') {
                $this->assertStringStartsWith(WP_STATISTICS_SITE_URL . '/add-ons/', $link['href'], "$surface: Learn more goes to an add-on page");
                $this->assertSame('_blank', $link['target'], "$surface: Learn more opens a new tab");
                $this->assertFalse($popup, "$surface: Learn more never opens the popup");
                $checked++;
            } elseif (strpos($label, "See what's included") !== false || strpos($link['aria'], "See what's included") === 0) {
                $this->assertTrue($popup, "$surface: See what's included opens the popup");
                $this->assertNotSame('_blank', $link['target'], "$surface: a popup trigger is not a new-tab link");
                $this->assertNotSame('', $link['medium'], "$surface: popup trigger names its surface");
                $checked++;
            } else {
                $this->assertFalse($popup, "$surface: only See what's included opens the popup ($label)");
            }

            foreach (['Upgrade To Premium', 'Go Premium', 'Get Premium', 'Buy Premium Now', 'Purchase', 'Upgrade now', 'Upgrade Now', 'Learn More', 'Learn about'] as $old) {
                $this->assertStringNotContainsString($old, $label, $surface);
            }

            if (strpos($link['href'], WP_STATISTICS_SITE_URL) === 0) {
                $query = $this->query($link['href']);
                $this->assertSame('wp-statistics', $query['utm_source'] ?? '', "$surface: utm_source {$link['href']}");
                $this->assertNotEmpty($query['utm_medium'] ?? '', "$surface: utm_medium {$link['href']}");
                // Sales links (pricing, add-on pages) name the surface they sit on; guides and docs keep their own tags.
                if (preg_match('#^' . preg_quote(WP_STATISTICS_SITE_URL, '#') . '/(pricing|add-ons)/#', $link['href'])) {
                    $this->assertNotSame('link', $query['utm_medium'] ?? '', "$surface: utm_medium names the surface {$link['href']}");
                }
                $this->assertNotEmpty($query['utm_campaign'] ?? '', "$surface: utm_campaign {$link['href']}");
                $this->assertSame(1, substr_count($link['href'], '?'), "$surface: one query string {$link['href']}");
            }
        }

        return $checked;
    }

    // Link helper ---------------------------------------------------------------------

    public function test_link_helper_builds_one_scheme()
    {
        $this->assertSame(
            WP_STATISTICS_SITE_URL . '/pricing/?utm_source=wp-statistics&utm_medium=settings-card&utm_campaign=marketing&utm_content=upgrade',
            UpsellLink::pricing('settings-card', 'wp-statistics-marketing')
        );
        $this->assertSame(
            WP_STATISTICS_SITE_URL . '/add-ons/wp-statistics-realtime-stats/?utm_source=wp-statistics&utm_medium=lock-page&utm_campaign=realtime-stats&utm_content=learn-more',
            UpsellLink::addon('wp-statistics-realtime-stats', 'lock-page')
        );
        $this->assertSame(
            WP_STATISTICS_SITE_URL . '/add-ons/?utm_source=wp-statistics&utm_medium=popup&utm_campaign=premium&utm_content=learn-more',
            UpsellLink::addon('first-step', 'popup')
        );
        $this->assertSame('premium', UpsellLink::campaign(''));
        $this->assertSame('data-plus', UpsellLink::campaign('wp-statistics-data-plus'));
        // A URL that already has a query string keeps it, with one "?".
        $this->assertSame(
            WP_STATISTICS_SITE_URL . '/contact-us/?step=purchasing&utm_source=wp-statistics&utm_medium=add-ons-page&utm_campaign=premium&utm_content=support',
            UpsellLink::tag(WP_STATISTICS_SITE_URL . '/contact-us/?step=purchasing', 'add-ons-page', 'premium', 'support')
        );
        $this->assertSame(' data-target="wp-statistics-data-plus" data-surface="teaser"', UpsellLink::popupAttributes('wp-statistics-data-plus', 'teaser'));
    }

    // Big surfaces: both buttons, no popup ------------------------------------------------

    public function test_settings_card_and_lock_page_follow_the_rules()
    {
        $card = (string)View::load('components/lock-sections/setting-unlock-premium', ['addon_modal_target' => 'wp-statistics-marketing'], true);
        $this->assertSame(2, $this->assertLabelRules($card, 'settings-card'));
        $this->assertStringContainsString('utm_medium=settings-card&#038;utm_campaign=marketing', $card);

        $lock = (string)View::load('pages/lock-page', [
            'page_title' => 'See which campaigns bring your visitors',
            'benefits'   => ['One', 'Two', 'Three'],
            'addon_name' => 'Marketing',
            'addon_slug' => 'wp-statistics-marketing',
            'campaign'   => 'marketing',
        ], true);
        $this->assertSame(2, $this->assertLabelRules($lock, 'lock-page'));
        $this->assertStringContainsString('utm_medium=lock-page&#038;utm_campaign=marketing', $lock);
        $this->assertStringNotContainsString('js-wps-openPremiumModal', $lock);
    }

    public function test_every_lock_page_caller_follows_the_rules()
    {
        foreach (['pages/referrals/campaigns', 'pages/referrals/utm-performance', 'pages/referrals/google-search', 'pages/page-insights/entry-pages', 'pages/page-insights/exit-pages', 'pages/visitor-insights/search-terms'] as $view) {
            $html = (string)View::load($view, [], true);
            $this->assertSame(2, $this->assertLabelRules($html, $view), $view);
        }

        // Category Pages with a custom taxonomy: the standard lock page now (was the old "Upgrade Now." box).
        $html = (string)View::load('pages/page-insights/category', ['showLockedPage' => true], true);
        $this->assertSame(2, $this->assertLabelRules($html, 'category'));
        $this->assertStringContainsString('utm_campaign=data-plus', $html);
    }

    public function test_popup_links_go_to_pricing_and_add_on_pages_and_never_reopen_it()
    {
        $html = (string)View::load('components/modals/introduce-premium/introduce-premium-modal', [], true);

        // 9 steps × 2 buttons on a free site.
        $this->assertSame(18, $this->assertLabelRules($html, 'popup'));
        $this->assertSame(18, substr_count($html, 'js-wps-premiumModalLink'));
        $this->assertSame(18, substr_count($html, 'utm_medium=popup'));
        $this->assertStringNotContainsString('pop-up-premium', $html);
        $this->assertStringContainsString('/add-ons/wp-statistics-marketing/?utm_source=wp-statistics&#038;utm_medium=popup&#038;utm_campaign=marketing&#038;utm_content=learn-more', $html);
    }

    public function test_showcase_upgrades_to_pricing_and_info_icons_open_the_popup()
    {
        $html = (string)View::load('components/premium-showcase', [], true);

        $this->assertSame(2 + 9, $this->assertLabelRules($html, 'showcase'));
        $this->assertSame(2, substr_count($html, '>Upgrade to Premium</a>'));
        $this->assertSame(9, substr_count($html, 'data-surface="showcase"'));
        $this->assertStringNotContainsString('https://wp-statistics.com/pricing/?utm_source=wp-statistics&amp;utm_medium=link', $html);
    }

    // Small pointers: "See what's included" opens the popup ---------------------------------

    public function test_small_pointers_open_the_popup_with_their_surface()
    {
        $html = (string)View::load('metabox/go-premium', ['widget_id' => 'go-premium'], true);
        $this->assertSame(1, $this->assertLabelRules($html, 'go-premium-box'));

        $html = (string)View::load('components/objects/premium-locked-button', ['label' => 'Export', 'addon' => 'wp-statistics-advanced-reporting'], true);
        $this->assertStringContainsString('js-wps-openPremiumModal', $html);
        $this->assertStringContainsString('data-surface="locked-button"', $html);
        $this->assertStringContainsString("See what&#039;s included", $html);

        $html = (string)View::load('components/author-box', [
            'show_preview' => true, 'author_id' => 1, 'author_name' => 'Ann', 'count' => 3, 'count_label' => 'posts', 'counter' => 1,
        ], true);
        $this->assertStringContainsString('data-surface="author-box"', $html);

        $html = Admin_Template::get_template('settings/notifications', [], true);
        $this->assertSame(1, $this->assertLabelRules($html, 'settings-notifications'));
        $this->assertStringContainsString('data-target="wp-statistics-advanced-reporting" data-surface="settings-notifications"', $html);
    }

    private function tabbedHeader($tabs)
    {
        \WP_Statistics\Globals\Context::update('wps_page', ['slug' => 'referrals', 'tab' => 'campaigns']);

        return Admin_Template::get_template('layout/tabbed-page-header', ['tabs' => $tabs], true);
    }

    public function test_locked_tab_links_to_its_lock_page()
    {
        $link = admin_url('admin.php?page=wps_referrals_page&tab=campaigns');
        $html = $this->tabbedHeader([['id' => 'campaigns', 'link' => $link, 'title' => 'Campaigns', 'class' => '', 'locked' => true, 'lockedTarget' => 'wp-statistics-marketing']]);

        $this->assertStringContainsString('href="' . esc_attr($link) . '" class="wps-locked"', $html);
        $this->assertStringNotContainsString('js-wps-openPremiumModal wps-locked', $html);
    }

    // Notices, Add-ons page, header, Plugins screen, email ---------------------------------

    public function test_notices_and_add_license_use_the_one_label()
    {
        $html = (string)View::load('components/notices/upgrade-to-premium', [], true);
        $this->assertSame(2, $this->assertLabelRules($html, 'notice'));
        $this->assertStringNotContainsString('/support/', $html);

        $html = (string)View::load('components/notices/no-license', ['data' => ['install_addon_link' => admin_url('admin.php?page=wps_plugins_page&tab=add-license')]], true);
        $this->assertSame(1, $this->assertLabelRules($html, 'notice'));

        $html = (string)View::load('pages/license-manager/add-license', [], true);
        $this->assertSame(1, $this->assertLabelRules($html, 'add-license'));
    }

    public function test_header_link_and_plugins_row()
    {
        $html = \WP_Statistics\Service\Admin\LicenseManagement\LicenseHelper::renderLicenseStatusLink(false, null);
        $this->assertSame(1, $this->assertLabelRules($html, 'header'));
        $this->assertStringContainsString('utm_medium=header', $html);

        $links = implode('', (new HooksManager())->addActionLinks([]));
        $this->assertSame(1, $this->assertLabelRules($links, 'plugins-list'));
        $this->assertStringContainsString('utm_medium=plugins-list', $links);
    }

    public function test_header_menu_says_go_premium_on_free_sites()
    {
        // "Add-ons" only once a valid license is saved; an installed add-on alone keeps "Go Premium".
        $header = file_get_contents(WP_STATISTICS_DIR . 'includes/admin/templates/header.php');
        $this->assertStringContainsString("LicenseHelper::isValidLicenseAvailable() ? __('Add-ons', 'wp-statistics') : __('Go Premium', 'wp-statistics')", $header);
        $this->assertStringNotContainsString('isFreeOnly', $header);
    }

    public function test_sidebar_menu_says_go_premium_until_a_license_is_valid()
    {
        $user = self::factory()->user->create(['role' => 'administrator']);
        if (is_multisite()) {
            grant_super_admin($user);
        }
        wp_set_current_user($user);

        $manager = new \WP_Statistics\Service\Admin\LicenseManagement\LicenseManagementManager();
        $this->assertSame('Go Premium', $manager->addMenuItem([])['plugins']['title']);

        \WP_STATISTICS\Option::saveOptionGroup('TEST-MENU-KEY', ['status' => 'valid', 'sku' => 'premium', 'products' => []], \WP_Statistics\Service\Admin\LicenseManagement\LicenseHelper::LICENSE_OPTION_KEY);
        $this->assertSame('Add-ons', $manager->addMenuItem([])['plugins']['title']);
        \WP_STATISTICS\Option::deleteOptionGroup('TEST-MENU-KEY', \WP_Statistics\Service\Admin\LicenseManagement\LicenseHelper::LICENSE_OPTION_KEY);
    }

    private function captureReportEmail()
    {
        $sent = '';
        add_filter('pre_wp_mail', function ($return, $atts) use (&$sent) {
            $sent = $atts['message'];
            return true;
        }, 10, 2);
        Helper::send_mail('admin@example.org', 'Report', '<p>Body</p>', true, ['is_report' => true]);

        return $sent;
    }

    public function test_email_footer_ad_has_the_right_utm_source()
    {
        $mail = $this->captureReportEmail();

        $this->assertStringContainsString('/add-ons/wp-statistics-advanced-reporting/?utm_source=wp-statistics&#038;utm_medium=email&#038;utm_campaign=advanced-reporting', $mail);
        $this->assertStringNotContainsString('utm_source=wp_statistics', $mail);
    }

    // White label --------------------------------------------------------------------------

    public function test_white_label_hides_every_upsell_and_the_popup()
    {
        add_filter('wp_statistics_hide_addon_prompts', '__return_true');

        ob_start();
        Modal::render('introduce-premium');
        Modal::render('welcome-premium');
        $this->assertSame('', trim(ob_get_clean()));

        $this->assertSame('', trim((string)View::load('components/objects/premium-locked-button', ['label' => 'Export', 'addon' => 'wp-statistics-advanced-reporting'], true)));
        $this->assertSame('', trim((string)View::load('components/premium-showcase', [], true)));

        $author = (string)View::load('components/author-box', [
            'show_preview' => true, 'author_id' => 1, 'author_name' => 'Ann', 'count' => 3, 'count_label' => 'posts', 'counter' => 1,
        ], true);
        $this->assertStringNotContainsString('js-wps-openPremiumModal', $author);

        $notifications = Admin_Template::get_template('settings/notifications', [], true);
        $this->assertStringNotContainsString('js-wps-openPremiumModal', $notifications);
        $this->assertStringNotContainsString('Advanced Reporting add-on', $notifications);

        $links = implode('', (new HooksManager())->addActionLinks([]));
        $this->assertStringNotContainsString('Upgrade to Premium', $links);
        $this->assertStringNotContainsString('/pricing/', $links);

        $this->assertStringNotContainsString('wp-statistics-advanced-reporting', $this->captureReportEmail());

        // Locked tabs are left out.
        $html = $this->tabbedHeader([['id' => 'campaigns', 'link' => admin_url('admin.php?page=wps_referrals_page&tab=campaigns'), 'title' => 'Campaigns', 'class' => '', 'locked' => true]]);
        $this->assertStringNotContainsString('wps-locked', $html);
    }

    public function test_popup_renders_without_white_label()
    {
        ob_start();
        Modal::render('introduce-premium');
        $html = ob_get_clean();

        $this->assertStringContainsString('js-wps-premiumModal', $html);
    }
}
