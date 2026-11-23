<?php

use WP_Statistics\Components\View;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Service\Admin\LicenseManagement\LicenseManagerDataProvider;
use WP_Statistics\Service\Admin\LicenseManagement\Plugin\AddonCatalog;
use WP_Statistics\Service\Admin\LicenseManagement\Plugin\PluginHelper;

/**
 * Upsell redesign: settings banner, lock page, Go Premium box, Premium popup and the saved add-on list.
 */
class Test_UpsellSurfaces extends WP_UnitTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        wp_set_current_user(self::factory()->user->create(['role' => 'administrator']));
    }

    public function tearDown(): void
    {
        remove_all_filters('pre_http_request');
        remove_all_filters('wp_statistics_hide_addon_prompts');
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
            $this->assertGreaterThanOrEqual(3, count($copy['chips']), $slug);
            $this->assertLessThanOrEqual(4, count($copy['chips']), $slug);
            foreach (['name', 'headline', 'value', 'short'] as $key) {
                $this->assertNotEmpty($copy[$key], "$slug $key");
            }
        }

        $this->assertContains('AI agents (MCP)', $all['wp-statistics-rest-api']['chips']);
    }

    // Settings banner -----------------------------------------------------------

    public function test_settings_banner_is_one_soft_strip_with_chips_and_buttons()
    {
        $html = Admin_Template::get_template('layout/partials/addon-premium-feature', [
            'addon_title'               => 'Rest API Add-on',
            'addon_modal_target'        => 'wp-statistics-rest-api',
            'addon_campaign'            => 'rest-api',
            'addon_documentation_title' => 'API documentation',
            'addon_documentation_slug'  => 'https://documenter.getpostman.com/view/3239688/2s8Z6vZER4',
        ], true);

        $this->assertSame(1, substr_count($html, 'class="wps-upsell-banner"'));
        $this->assertStringContainsString('Unlock REST API settings', $html);
        $this->assertStringContainsString('AI agents (MCP)', $html);
        $this->assertStringContainsString('wps-upsell-chips__chip--ai', $html);
        $this->assertSame(1, substr_count($html, 'Upgrade to Premium'));
        $this->assertStringContainsString('utm_campaign=rest-api', $html);
        $this->assertStringContainsString('data-target="wp-statistics-rest-api"', $html);
        $this->assertStringContainsString('API documentation', $html);
        // The old box is gone.
        $this->assertStringNotContainsString('Unlock Premium Features with', $html);
        $this->assertStringNotContainsString('wps-premium-feature__items', $html);
    }

    public function test_every_settings_tab_uses_the_banner_copy()
    {
        foreach (AddonCatalog::SLUGS as $slug) {
            $html = (string)View::load('components/lock-sections/setting-unlock-premium', ['addon_modal_target' => $slug], true);
            $copy = AddonCatalog::get($slug);

            $this->assertStringContainsString(esc_html($copy['value']), $html, $slug);
            $this->assertSame(count($copy['chips']), substr_count($html, '<li class="wps-upsell-chips__chip'), $slug);
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

    public function test_go_premium_button_opens_the_popup_and_keeps_pricing_href()
    {
        $html = (string)View::load('metabox/go-premium', ['widget_id' => 'go-premium'], true);

        $this->assertStringContainsString('Get More with Premium Analytics', $html);
        $this->assertMatchesRegularExpression('/<a class="[^"]*js-wps-openPremiumModal[^"]*" data-target="first-step" href="[^"]*\/pricing\//', $html);
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

    public function test_refresh_request_needs_a_valid_nonce()
    {
        $_REQUEST['wps_refresh_addons'] = 'nope';
        $this->assertFalse(LicenseManagerDataProvider::isRefreshRequest());

        $_REQUEST['wps_refresh_addons'] = wp_create_nonce(LicenseManagerDataProvider::REFRESH_NONCE);
        $this->assertTrue(LicenseManagerDataProvider::isRefreshRequest());

        unset($_REQUEST['wps_refresh_addons']);
    }
}
