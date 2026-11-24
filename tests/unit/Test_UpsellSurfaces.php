<?php

use WP_Statistics\Components\View;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Service\Admin\LicenseManagement\LicenseManagerDataProvider;
use WP_Statistics\Service\Admin\LicenseManagement\Plugin\AddonCatalog;
use WP_Statistics\Service\Admin\LicenseManagement\Plugin\PluginHelper;
use WP_Statistics\Service\Admin\LicenseManagement\Plugin\PluginHandler;
use WP_Statistics\Service\Admin\LicenseManagement\LicenseHelper;
use WP_Statistics\Service\Admin\LicenseManagement\LicenseManagementManager;
use WP_STATISTICS\Option;

/**
 * Upsell redesign: settings banner, lock page, Go Premium box, Premium popup and the saved add-on list.
 */
class Test_UpsellSurfaces extends WP_UnitTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $admin = self::factory()->user->create(['role' => 'administrator']);
        // On multisite only a super admin can activate plugins.
        if (is_multisite()) {
            grant_super_admin($admin);
        }
        wp_set_current_user($admin);
    }

    public function tearDown(): void
    {
        remove_all_filters('pre_http_request');
        remove_all_filters('wp_statistics_hide_addon_prompts');
        remove_all_filters('wp_statistics_addon_situation');
        Option::deleteOptionGroup('TEST-KEY-1', LicenseHelper::LICENSE_OPTION_KEY);
        PluginHelper::clearFetchFailure();
        unset($_GET['tab'], $_REQUEST['tab']);
        parent::tearDown();
    }

    // Catalog -----------------------------------------------------------------

    public function test_catalog_has_short_copy_for_all_8_add_ons()
    {
        $all = AddonCatalog::all();

        $this->assertSame(AddonCatalog::SLUGS, array_keys($all));
        $this->assertEqualsCanonicalizing(array_keys(PluginHelper::$plugins), AddonCatalog::SLUGS);

        foreach ($all as $slug => $copy) {
            $this->assertCount(3, $copy['benefits'], $slug);
            foreach (['name', 'headline', 'short'] as $key) {
                $this->assertNotEmpty($copy[$key], "$slug $key");
            }
            $this->assertStringContainsString('<svg class="x"', AddonCatalog::getLineIcon($slug, 16, 'x'), $slug);
        }

        $this->assertContains('Connect ChatGPT, Claude and other AI agents (MCP)', $all['wp-statistics-rest-api']['benefits']);
        $this->assertSame('See which campaigns bring your visitors', $all['wp-statistics-marketing']['headline']);
        $this->assertSame('', AddonCatalog::getLineIcon('nope'));
    }

    // Settings card -------------------------------------------------------------

    public function test_settings_card_is_the_lock_page_text_block()
    {
        $html = Admin_Template::get_template('layout/partials/addon-premium-feature', [
            'addon_title'        => 'Rest API Add-on',
            'addon_modal_target' => 'wp-statistics-rest-api',
            'addon_campaign'     => 'rest-api',
        ], true);

        $this->assertSame(1, substr_count($html, 'class="wps-upsell-card"'));
        // No add-on kicker above the headline (owner test 14.17).
        $this->assertStringNotContainsString('REST API add-on', $html);
        $this->assertStringNotContainsString('wps-upsell-card__kicker', $html);
        $this->assertSame(1, substr_count($html, '<h3'));
        $this->assertStringContainsString('Your stats in any app or AI agent', $html);
        $this->assertStringContainsString('Connect ChatGPT, Claude and other AI agents (MCP)', $html);
        $this->assertSame(3, substr_count($html, 'wps-upsell-benefits__item'));
        $this->assertStringContainsString('Included in Premium with all 8 add-ons', $html);
        $this->assertStringContainsString('14-day money-back guarantee', $html);
        $this->assertStringContainsString('utm_campaign=rest-api', $html);
        // Guarantee first, then "Included in Premium" (owner change).
        $this->assertLessThan(strpos($html, 'Included in Premium with all 8 add-ons'), strpos($html, '14-day money-back guarantee'));

        // "Upgrade to Premium" = pricing page, "Learn more" = add-on page, both new tab; no popup on a settings card.
        $this->assertMatchesRegularExpression('#<a class="wps-upsell-btn wps-upsell-btn--primary" href="https://wp-statistics.com/pricing/\?utm_source=wp-statistics&\#038;utm_medium=settings-card&\#038;utm_campaign=rest-api&\#038;utm_content=upgrade" target="_blank" rel="noopener">Upgrade to Premium</a>#', $html);
        $this->assertMatchesRegularExpression('#<a class="wps-upsell-btn wps-upsell-btn--secondary" href="https://wp-statistics.com/add-ons/wp-statistics-rest-api/\?utm_source=wp-statistics&\#038;utm_medium=settings-card&\#038;utm_campaign=rest-api&\#038;utm_content=learn-more" target="_blank" rel="noopener">Learn more</a>#', $html);
        $this->assertSame(0, substr_count($html, 'js-wps-openPremiumModal'));

        // The soft banner and the old box are gone.
        $this->assertStringNotContainsString('wps-upsell-banner', $html);
        $this->assertStringNotContainsString('wps-upsell-chips', $html);
        $this->assertStringNotContainsString('Unlock Premium Features with', $html);
    }

    public function test_every_settings_tab_uses_the_catalog_copy()
    {
        foreach (AddonCatalog::SLUGS as $slug) {
            $html = (string)View::load('components/lock-sections/setting-unlock-premium', ['addon_modal_target' => $slug], true);
            $copy = AddonCatalog::get($slug);

            $this->assertStringContainsString(esc_html($copy['headline']), $html, $slug);
            foreach ($copy['benefits'] as $benefit) {
                $this->assertStringContainsString(esc_html($benefit), $html, $slug);
            }
            $this->assertStringContainsString('/add-ons/' . $slug . '/', $html, $slug);
            $this->assertStringNotContainsString('js-wps-openPremiumModal', $html, $slug);
        }
    }

    public function test_settings_card_hidden_under_white_label()
    {
        add_filter('wp_statistics_hide_addon_prompts', '__return_true');

        $html = Admin_Template::get_template('layout/partials/addon-premium-feature', [
            'addon_title'        => 'Marketing Add-On',
            'addon_modal_target' => 'wp-statistics-marketing',
            'addon_campaign'     => 'marketing',
        ], true);

        $this->assertSame('', trim((string)$html));
    }

    public function test_settings_card_and_lock_page_share_the_same_copy()
    {
        $copy = AddonCatalog::get('wp-statistics-marketing');
        $html = (string)View::load('pages/referrals/campaigns', [], true);

        $this->assertStringContainsString(esc_html($copy['headline']), $html);
        foreach ($copy['benefits'] as $benefit) {
            $this->assertStringContainsString(esc_html($benefit), $html);
        }
    }

    // Lock page -----------------------------------------------------------------

    private function lockPage($args = [])
    {
        return (string)View::load('pages/lock-page', array_merge([
            'page_title' => 'See which campaigns bring your visitors',
            'benefits'   => ['One', 'Two', 'Three'],
            'addon_name' => 'Marketing',
            'addon_slug' => 'wp-statistics-marketing',
            'campaign'   => 'marketing',
            'images'     => ['marketing-lock.png', 'campaign-lock.png'],
        ], $args), true);
    }

    public function test_lock_page_is_split_with_one_headline_and_three_benefits()
    {
        $html = $this->lockPage();

        $this->assertStringContainsString('wps-lock-page__split', $html);
        $this->assertSame(1, substr_count($html, '<h1'));
        $this->assertSame(3, substr_count($html, 'wps-upsell-benefits__item'));
        $this->assertSame(1, substr_count($html, '>Upgrade to Premium<'));
        $this->assertSame(1, substr_count($html, '>Learn more<'));
        $this->assertSame(1, substr_count($html, 'Included in Premium with all 8 add-ons'));
        $this->assertSame(1, substr_count($html, '14-day money-back guarantee'));
        $this->assertLessThan(strpos($html, 'Included in Premium with all 8 add-ons'), strpos($html, '14-day money-back guarantee'));
        $this->assertSame(2, substr_count($html, 'wps-slider__slide'));
        $this->assertStringNotContainsString('wps-lock-page__head--second', $html);
    }

    public function test_no_lock_page_caller_passes_the_old_duplicate_args()
    {
        $root  = dirname(__DIR__, 2);
        $files = array_merge(
            glob($root . '/views/pages/*/*.php'),
            glob($root . '/src/Service/Admin/*/Views/*.php')
        );
        $callers = 0;

        foreach ($files as $file) {
            $code = file_get_contents($file);
            if (strpos($code, 'pages/lock-page') === false) continue;
            $callers++;

            foreach (['page_second_title', 'second_description', 'premium_btn_title', "'more_title'"] as $old) {
                $this->assertStringNotContainsString($old, $code, basename($file));
            }
            $this->assertStringContainsString("'benefits'", $code, basename($file));
        }

        $this->assertGreaterThanOrEqual(17, $callers);
    }

    public function test_locked_referral_pages_render_without_duplicated_text()
    {
        foreach (['campaigns', 'utm-performance', 'google-search'] as $page) {
            $html = (string)View::load('pages/referrals/' . $page, [], true);

            $this->assertSame(1, substr_count($html, 'wps-lock-page__head'), $page);
            $this->assertStringNotContainsString('WP Statistics Premium: Unlock More', $html, $page);
        }
    }

    // Go Premium box --------------------------------------------------------------

    public function test_go_premium_button_opens_the_popup()
    {
        $html = (string)View::load('metabox/go-premium', ['widget_id' => 'go-premium'], true);

        $this->assertStringContainsString('Get More with Premium Analytics', $html);
        $this->assertMatchesRegularExpression('#<a class="[^"]*js-wps-openPremiumModal[^"]*" href="https://wp-statistics.com/add-ons/\?[^"]*utm_medium=go-premium-box[^"]*" data-target="first-step" data-surface="go-premium-box">See what&\#039;s included</a>#', $html);
        $this->assertStringNotContainsString('target="_blank"', $html);
        $this->assertStringContainsString('14-day money-back guarantee', $html);
        $this->assertStringContainsString('wp_statistics_dismiss_widget', $html);
    }

    // Premium popup ---------------------------------------------------------------

    public function test_popup_has_intro_and_8_steps_and_a_separate_switcher()
    {
        $html = (string)View::load('components/modals/introduce-premium/introduce-premium-modal', [], true);

        $this->assertSame(9, substr_count($html, 'js-wps-premiumModalStep"'));
        $this->assertSame(8, substr_count($html, 'js-wps-premiumStepFeature'));
        $this->assertSame(27, substr_count($html, 'wps-upsell-benefits__item'));
        $this->assertStringContainsString('role="dialog"', $html);
        $this->assertStringContainsString('wps-premium-switcher', $html);
        // Learn more inside the popup goes to the add-on page, it doesn't reopen the popup.
        $this->assertStringNotContainsString('js-wps-openPremiumModal', $html);
        $this->assertStringContainsString('Your stats in any app or AI agent', $html);
        // No duplicate ids.
        preg_match_all('/ id="([^"]+)"/', $html, $ids);
        $this->assertSame(count($ids[1]), count(array_unique($ids[1])));
    }

    public function test_popup_step_for_an_installed_but_off_add_on_offers_activate()
    {
        add_filter('wp_statistics_addon_situation', function ($situation, $slug) {
            return $slug === 'wp-statistics-data-plus' ? \WP_Statistics\Service\Admin\LicenseManagement\LicenseHelper::SITUATION_INACTIVE : $situation;
        }, 10, 2);

        $html = (string)View::load('components/modals/introduce-premium/introduce-premium-modal', [], true);
        remove_all_filters('wp_statistics_addon_situation');

        preg_match('/<section[^>]*data-step="wp-statistics-data-plus".*?<\/section>/s', $html, $step);
        $this->assertNotEmpty($step);
        $step = $step[0];

        $this->assertStringContainsString('The Data Plus add-on isn', $step);
        $this->assertStringContainsString('Activate Data Plus', $step);
        $this->assertStringContainsString('plugins.php?plugin_status=inactive&#038;s=wp-statistics-data-plus', $step);
        $this->assertStringNotContainsString('no active license', $step);
        $this->assertStringNotContainsString('Activate your license', $step);
        $this->assertStringNotContainsString('/pricing/', $step);
        $this->assertStringContainsString('wps-premium-switcher__item--activate', $html);
        $this->assertStringContainsString('Turned off', $html);
    }

    public function test_popup_step_for_installed_but_off_asks_admin_when_user_cannot_activate()
    {
        $editor = self::factory()->user->create(['role' => 'editor']);
        wp_set_current_user($editor);
        add_filter('wp_statistics_addon_situation', function ($situation, $slug) {
            return $slug === 'wp-statistics-data-plus' ? \WP_Statistics\Service\Admin\LicenseManagement\LicenseHelper::SITUATION_INACTIVE : $situation;
        }, 10, 2);

        $html = (string)View::load('components/modals/introduce-premium/introduce-premium-modal', [], true);
        remove_all_filters('wp_statistics_addon_situation');

        preg_match('/<section[^>]*data-step="wp-statistics-data-plus".*?<\/section>/s', $html, $step);
        $this->assertStringContainsString('Ask a site admin to turn on Data Plus.', $step[0]);
        $this->assertStringNotContainsString('plugins.php', $step[0]);
    }

    // Add-ons screen, white label, locked pages ----------------------------------------

    private function saveLicense($sku)
    {
        Option::saveOptionGroup('TEST-KEY-1', ['status' => 'valid', 'sku' => $sku, 'products' => AddonCatalog::SLUGS], LicenseHelper::LICENSE_OPTION_KEY);
    }

    public function test_add_license_step_does_not_sell_premium_to_a_premium_site()
    {
        $this->saveLicense('premium');
        $html = (string)View::load('pages/license-manager/add-license', [], true);

        $this->assertStringNotContainsString('Buy Premium Now', $html);
        $this->assertStringNotContainsString('Unlock Premium Features', $html);
        $this->assertStringContainsString('Add Another License', $html);
        $this->assertStringContainsString('Download your add-ons', $html);
        $this->assertStringContainsString('tab=downloads&#038;license_key=TEST-KEY-1', $html);
    }

    public function test_add_license_step_keeps_the_offer_without_a_license()
    {
        $html = (string)View::load('pages/license-manager/add-license', [], true);

        $this->assertStringContainsString('Unlock Premium Features', $html);
        $this->assertStringContainsString('>Upgrade to Premium</a>', $html);
        $this->assertStringNotContainsString('Buy Premium Now', $html);
        $this->assertStringNotContainsString('Download your add-ons', $html);
        // The contact link used to have two "?" (…?step=purchasing/?utm_source=…).
        $this->assertStringContainsString('/contact-us/?step=purchasing&#038;utm_source=wp-statistics', $html);
        $this->assertSame(0, preg_match('#href="[^"]*\?[^"]*\?#', $html));
    }

    public function test_add_ons_menu_entry_is_hidden_under_white_label_but_the_page_stays_registered()
    {
        $manager = new LicenseManagementManager();
        $this->assertEmpty((new LicenseManagementManager())->addMenuItem([])['plugins']['hidden']);

        add_filter('wp_statistics_hide_addon_prompts', '__return_true');
        $item = $manager->addMenuItem([])['plugins'];
        $this->assertTrue($item['hidden']);

        global $submenu, $_registered_pages;
        $hook = get_plugin_page_hookname('wps_plugins_page', 'options.php');
        $menus = new \WP_STATISTICS\Menus();
        $menus->wp_admin_menu();

        $this->assertArrayHasKey($hook, (array)$_registered_pages);
        $slugs = array_column((array)($submenu['wps_overview_page'] ?? []), 2);
        $this->assertNotContains('wps_plugins_page', $slugs);
    }

    private function fakeHandler($active)
    {
        return new class($active) extends PluginHandler {
            private $active;

            public function __construct($active)
            {
                $this->active = $active;
            }

            public function isPluginActive($pluginSlug)
            {
                return in_array($pluginSlug, $this->active, true);
            }

            public function isPluginInstalled($pluginSlug)
            {
                return in_array($pluginSlug, $this->active, true);
            }
        };
    }

    public function test_locked_pages_send_an_active_add_on_to_its_real_screen()
    {
        $handler = $this->fakeHandler(['wp-statistics-realtime-stats', 'wp-statistics-mini-chart']);

        $this->assertStringContainsString('page=wp_statistics_realtime_stats', LicenseManagementManager::getLockedViewRedirect('locked-real-time', $handler));
        $this->assertStringContainsString('tab=mini-chart-settings', LicenseManagementManager::getLockedViewRedirect('locked-mini-chart', $handler));
        $this->assertNull(LicenseManagementManager::getLockedViewRedirect('add-ons', $handler));
    }

    public function test_locked_pages_stay_when_the_add_on_is_not_running()
    {
        $handler = $this->fakeHandler([]);

        $this->assertNull(LicenseManagementManager::getLockedViewRedirect('locked-real-time', $handler));
        $this->assertNull(LicenseManagementManager::getLockedViewRedirect('locked-mini-chart', $handler));
    }

    public function test_locked_page_shows_no_sales_copy_under_white_label()
    {
        add_filter('wp_statistics_hide_addon_prompts', '__return_true');
        $html = $this->lockPage(['addon_slug' => 'wp-statistics-realtime-stats', 'addon_name' => 'Real-Time Stats']);

        $this->assertStringContainsString('This feature is not available', $html);
        $this->assertStringNotContainsString('/pricing/', $html);
        $this->assertStringNotContainsString('Upgrade to Premium', $html);
    }

    private function header()
    {
        // The layout wrapper loads this file with require_once, so include it directly to render it more than once.
        $_GET['page'] = 'wps_overview_page';
        ob_start();
        include WP_STATISTICS_DIR . 'includes/admin/templates/header.php';
        unset($_GET['page']);
        return (string)ob_get_clean();
    }

    public function test_header_premium_badge_hidden_under_white_label()
    {
        $this->saveLicense('premium');
        $this->assertStringContainsString('wp-statistics-premium.svg', $this->header());

        add_filter('wp_statistics_hide_addon_prompts', '__return_true');
        $html = $this->header();
        $this->assertStringNotContainsString('wp-statistics-premium.svg', $html);
        $this->assertStringNotContainsString('wps-adminHeader__logo--premium', $html);
        $this->assertStringContainsString('white-header-logo.svg', $html);
    }

    public function test_header_has_no_add_ons_link_under_white_label()
    {
        $this->assertStringContainsString('page=wps_plugins_page', $this->header());

        add_filter('wp_statistics_hide_addon_prompts', '__return_true');
        $this->assertStringNotContainsString('page=wps_plugins_page', $this->header());
    }

    // Add-ons fallback ------------------------------------------------------------

    private function blockHttp()
    {
        add_filter('pre_http_request', function () {
            return new WP_Error('http_request_blocked', 'Blocked in test');
        });
        \WP_Statistics\Components\SystemCleaner::clearAddonsListCache();
    }

    public function test_bundled_list_has_8_add_ons_without_price_or_version()
    {
        $plugins = PluginHelper::getBundledPlugins();

        $this->assertCount(8, $plugins);
        foreach ($plugins as $plugin) {
            $this->assertTrue($plugin->isBundled());
            $this->assertSame('', $plugin->getVersion());
            $this->assertSame('', $plugin->getPrice());
            $this->assertStringContainsString('/add-ons/' . $plugin->getSlug() . '/', $plugin->getProductUrl());
        }
    }

    public function test_add_ons_data_falls_back_instead_of_throwing()
    {
        $this->blockHttp();

        $data = (new LicenseManagerDataProvider())->getAddOnsData();

        $this->assertTrue($data['is_fallback']);
        $this->assertCount(8, array_merge($data['active_addons'], $data['inactive_addons']));
    }

    public function test_downloads_data_falls_back_too()
    {
        $this->blockHttp();

        $data = (new LicenseManagerDataProvider())->getDownloadsData();

        $this->assertTrue($data['is_fallback']);
        $this->assertCount(8, $data['not_included_addons']);
    }

    public function test_fallback_notice_and_learn_more_cards()
    {
        $html = (string)View::load('components/notices/addons-list-fallback', ['retry_url' => LicenseManagerDataProvider::getRefreshUrl()], true);
        $this->assertStringContainsString('Couldn&#039;t refresh the list. Showing saved info.', $html);
        $this->assertStringContainsString('wps_refresh_addons=', $html);

        $plugins = PluginHelper::getBundledPlugins();
        $card    = (string)View::load('components/addon-box', ['addOn' => $plugins[0]], true);
        $this->assertStringContainsString('>Learn more<', $card);
        $this->assertStringNotContainsString('wps-postbox-addon__version', $card);
    }

    public function test_failed_list_fetch_is_not_retried_on_every_page_load()
    {
        $requests = 0;
        add_filter('pre_http_request', function () use (&$requests) {
            $requests++;
            return new WP_Error('http_request_blocked', 'Blocked in test');
        });
        \WP_Statistics\Components\SystemCleaner::clearAddonsListCache();

        $this->assertSame([], PluginHelper::getRemotePlugins());
        $tries = $requests;
        $this->assertGreaterThan(0, $tries);
        $this->assertNotFalse(get_transient(PluginHelper::FETCH_FAILED_TRANSIENT));

        // Later page loads use the saved list without asking the server again.
        PluginHelper::getRemotePlugins();
        $isFallback = false;
        $this->assertCount(8, PluginHelper::getPluginsWithFallback($isFallback));
        $this->assertTrue($isFallback);
        $this->assertSame($tries, $requests);

        // "Try again" clears the failure and asks once more.
        \WP_Statistics\Components\SystemCleaner::clearAddonsListCache();
        PluginHelper::getRemotePlugins();
        $this->assertGreaterThan($tries, $requests);
    }

    /**
     * Fakes an installed add-on file. Skips when the add-on really exists here, so a real install is never touched.
     */
    private function fakeInstalledAddon($slug)
    {
        $dir = WP_PLUGIN_DIR . '/' . $slug;
        if (file_exists("$dir/$slug.php")) {
            $this->markTestSkipped("$slug is really installed in this test site.");
        }
        wp_mkdir_p($dir);
        file_put_contents("$dir/$slug.php", "<?php\n/* Plugin Name: Test */\n");

        return function () use ($dir, $slug) {
            @unlink("$dir/$slug.php");
            @rmdir($dir);
        };
    }

    public function test_nothing_to_download_when_every_licensed_add_on_is_installed()
    {
        $slug = 'wp-statistics-data-plus';
        Option::saveOptionGroup('TEST-KEY-1', ['status' => 'valid', 'sku' => 'data-plus', 'products' => [$slug]], LicenseHelper::LICENSE_OPTION_KEY);
        $cleanup = $this->fakeInstalledAddon($slug);

        try {
            $this->assertFalse(PluginHelper::hasAddonsToDownload());

            $html = (string)View::load('pages/license-manager/add-license', [], true);
            $this->assertStringContainsString('Add Another License', $html);
            $this->assertStringNotContainsString('Download your add-ons', $html);

            // The stepper shows only the Add License step, no dead "Download Add-ons" link.
            $this->blockHttp();
            $_GET['tab'] = $_REQUEST['tab'] = 'add-license';
            ob_start();
            (new \WP_Statistics\Service\Admin\LicenseManagement\Views\TabsView())->render();
            $page = ob_get_clean();
            $this->assertStringContainsString('Add Your License', $page);
            $this->assertStringNotContainsString('wps-step-link__title">Download Add-ons', $page);
        } finally {
            $cleanup();
        }

        $this->assertTrue(PluginHelper::hasAddonsToDownload());
    }

    public function test_refresh_request_needs_a_valid_nonce()
    {
        $_REQUEST['wps_refresh_addons'] = 'nope';
        $this->assertFalse(LicenseManagerDataProvider::isRefreshRequest());

        $_REQUEST['wps_refresh_addons'] = wp_create_nonce(LicenseManagerDataProvider::REFRESH_NONCE);
        $this->assertTrue(LicenseManagerDataProvider::isRefreshRequest());

        unset($_REQUEST['wps_refresh_addons']);
    }
}
