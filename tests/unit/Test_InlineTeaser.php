<?php

use WP_STATISTICS\Option;
use WP_Statistics\Components\View;
use WP_Statistics\Decorators\VisitorDecorator;
use WP_Statistics\Service\Admin\LicenseManagement\InlineTeaser;
use WP_Statistics\Service\Admin\LicenseManagement\LicenseHelper;
use WP_Statistics\Service\Admin\LicenseManagement\Plugin\PluginHandler;
use WP_Statistics\Service\Admin\VisitorInsights\VisitorJourney;

/**
 * S2-A inline teaser: the add-on situation helper, the per-user close and the partial for each situation.
 */
class Test_InlineTeaser extends WP_UnitTestCase
{
    const SLUG = 'wp-statistics-data-plus';

    private $situation;

    public function setUp(): void
    {
        parent::setUp();
        $this->situation = null;
        add_filter('wp_statistics_addon_situation', [$this, 'forceSituation']);
    }

    public function tearDown(): void
    {
        remove_filter('wp_statistics_addon_situation', [$this, 'forceSituation']);
        remove_all_filters('wp_statistics_hide_addon_prompts');
        Option::deleteOptionGroup('TEST-KEY-1', LicenseHelper::LICENSE_OPTION_KEY);
        delete_option('wpstatistics_customization_settings');
        update_option('active_plugins', []);
        parent::tearDown();
    }

    public function forceSituation($situation)
    {
        return $this->situation ?: $situation;
    }

    private function handler($installed, $active)
    {
        return new class($installed, $active) extends PluginHandler {
            private $installed;
            private $active;

            public function __construct($installed, $active)
            {
                $this->installed = $installed;
                $this->active    = $active;
            }

            public function isPluginInstalled($pluginSlug)
            {
                return $this->installed;
            }

            public function isPluginActive($pluginSlug)
            {
                return $this->active;
            }
        };
    }

    private function situation($installed, $active)
    {
        remove_filter('wp_statistics_addon_situation', [$this, 'forceSituation']);
        $result = LicenseHelper::getAddonSituation(self::SLUG, $this->handler($installed, $active));
        add_filter('wp_statistics_addon_situation', [$this, 'forceSituation']);

        return $result;
    }

    private function licence($status)
    {
        Option::saveOptionGroup('TEST-KEY-1', ['status' => $status, 'sku' => 'premium', 'products' => [self::SLUG]], LicenseHelper::LICENSE_OPTION_KEY);
    }

    private function admin()
    {
        $userId = self::factory()->user->create(['role' => 'administrator']);

        // On multisite only a super admin can activate or install plugins.
        if (is_multisite()) {
            grant_super_admin($userId);
        }

        wp_set_current_user($userId);
    }

    private function render($args = [])
    {
        return (string)View::load('components/lock-sections/inline-teaser', array_merge([
            'id'         => 'dp-journey',
            'addon'      => self::SLUG,
            'addon_name' => 'Data Plus',
            'message'    => 'Clicks and downloads are tracked with Data Plus.',
            'campaign'   => 'dp-journey',
            'preview'    => '<ol class="sample"><li>Downloaded user-guide.pdf</li></ol>',
        ], $args), true);
    }

    // Situation helper ---------------------------------------------------------

    public function test_active_add_on_is_active_even_with_an_expired_or_missing_licence()
    {
        $this->assertSame(LicenseHelper::SITUATION_ACTIVE, $this->situation(true, true));

        $this->licence('license_expired');
        $this->assertSame(LicenseHelper::SITUATION_ACTIVE, $this->situation(true, true));
    }

    public function test_installed_but_off_is_inactive()
    {
        $this->assertSame(LicenseHelper::SITUATION_INACTIVE, $this->situation(true, false));
    }

    public function test_not_installed_with_a_valid_licence_is_in_plan()
    {
        $this->licence('valid');
        $this->assertSame(LicenseHelper::SITUATION_IN_PLAN, $this->situation(false, false));
    }

    public function test_not_installed_with_an_expired_or_no_licence_is_not_installed()
    {
        $this->assertSame(LicenseHelper::SITUATION_NOT_INSTALLED, $this->situation(false, false));

        $this->licence('license_expired');
        $this->assertSame(LicenseHelper::SITUATION_NOT_INSTALLED, $this->situation(false, false));
    }

    public function test_white_label_or_hidden_add_ons_menu_hides_prompts()
    {
        $this->assertFalse(LicenseHelper::areAddonPromptsHidden());

        update_option('active_plugins', ['wp-statistics-customization/wp-statistics-customization.php']);
        update_option('wpstatistics_customization_settings', ['wps_white_label' => '1']);
        $this->assertTrue(LicenseHelper::areAddonPromptsHidden());
        $this->assertSame(LicenseHelper::SITUATION_HIDDEN, $this->situation(true, false));
        $this->assertSame(LicenseHelper::SITUATION_HIDDEN, $this->situation(false, false));
        // A running add-on still shows its real data.
        $this->assertSame(LicenseHelper::SITUATION_ACTIVE, $this->situation(true, true));

        update_option('wpstatistics_customization_settings', ['wps_white_label' => '0', 'disable_menus' => ['plugins']]);
        $this->assertTrue(LicenseHelper::areAddonPromptsHidden());

        // Options of a switched-off Customization add-on do not count.
        update_option('active_plugins', []);
        update_option('wpstatistics_customization_settings', ['wps_white_label' => '1']);
        $this->assertFalse(LicenseHelper::areAddonPromptsHidden());
    }

    public function test_install_url_uses_the_licence_that_includes_the_add_on()
    {
        $this->assertStringContainsString('tab=add-license', LicenseHelper::getAddonInstallUrl(self::SLUG));

        $this->licence('valid');
        $url = LicenseHelper::getAddonInstallUrl(self::SLUG);
        $this->assertStringContainsString('tab=downloads', $url);
        $this->assertStringContainsString('license_key=TEST-KEY-1', $url);
    }

    // Per-user close -----------------------------------------------------------

    public function test_dismiss_is_saved_per_user()
    {
        $one = self::factory()->user->create();
        $two = self::factory()->user->create();

        $this->assertTrue(InlineTeaser::dismiss('dp-journey', $one));
        $this->assertTrue(InlineTeaser::dismiss('DP-Journey', $one)); // same id, saved once
        $this->assertSame(['dp-journey'], get_user_meta($one, InlineTeaser::USER_META, true));
        $this->assertTrue(InlineTeaser::isDismissed('dp-journey', $one));
        $this->assertFalse(InlineTeaser::isDismissed('dp-journey', $two));

        $this->assertFalse(InlineTeaser::dismiss('<>', $one));
        $this->assertFalse(InlineTeaser::dismiss('dp-journey', 0));
    }

    public function test_dismiss_ajax_action_is_registered_for_logged_in_users_only()
    {
        (new InlineTeaser())->register();

        $this->assertNotFalse(has_action('wp_ajax_wp_statistics_' . InlineTeaser::AJAX_ACTION));
        $this->assertFalse(has_action('wp_ajax_nopriv_wp_statistics_' . InlineTeaser::AJAX_ACTION));
    }

    // Partial --------------------------------------------------------------------

    public function test_nothing_renders_when_active_or_prompts_hidden()
    {
        $this->admin();

        $this->situation = LicenseHelper::SITUATION_ACTIVE;
        $this->assertSame('', trim($this->render()));

        $this->situation = LicenseHelper::SITUATION_HIDDEN;
        $this->assertSame('', trim($this->render()));
    }

    public function test_not_installed_shows_labelled_example_line_and_utm_link()
    {
        $this->admin();
        $this->situation = LicenseHelper::SITUATION_NOT_INSTALLED;
        $html = $this->render();

        $this->assertStringContainsString('wps-inline-teaser', $html);
        $this->assertStringContainsString('>Example<', $html);
        $this->assertStringContainsString('aria-hidden="true"', $html);
        $this->assertStringContainsString('Clicks and downloads are tracked with Data Plus.', $html);
        $this->assertStringContainsString("See what&#039;s included", $html);
        $this->assertStringContainsString('/add-ons/wp-statistics-data-plus/?utm_source=wp-statistics&#038;utm_medium=teaser&#038;utm_campaign=data-plus&#038;utm_content=learn-more', $html);
        $this->assertStringContainsString('data-surface="teaser"', $html);
        $this->assertStringNotContainsString('target="_blank"', $html);
        $this->assertStringContainsString('js-wps-inline-teaser-close', $html);
        $this->assertStringContainsString('data-nonce="' . wp_create_nonce(InlineTeaser::NONCE_ACTION) . '"', $html);
        // Opens the premium modal on the add-on; the URL stays as the no-JS fallback.
        $this->assertStringContainsString('js-wps-openPremiumModal', $html);
        $this->assertStringContainsString('data-target="wp-statistics-data-plus"', $html);
    }

    public function test_installed_but_off_links_to_the_plugins_screen_without_activating()
    {
        $this->admin();
        $this->situation = LicenseHelper::SITUATION_INACTIVE;
        $html = $this->render();

        $this->assertStringContainsString('Data Plus is installed but turned off.', $html);
        $this->assertStringContainsString('Activate Data Plus', $html);
        $this->assertStringContainsString('plugins.php?plugin_status=inactive&#038;s=wp-statistics-data-plus', $html);
        $this->assertStringNotContainsString('action=activate', $html);
        $this->assertStringNotContainsString('_wpnonce=', $html);
        $this->assertStringNotContainsString('js-wps-openPremiumModal', $html);
        $this->assertStringNotContainsString('Learn about', $html);
    }

    public function test_in_plan_offers_install_on_the_add_ons_screen()
    {
        $this->admin();
        $this->situation = LicenseHelper::SITUATION_IN_PLAN;
        $html = $this->render();

        $this->assertStringContainsString('Data Plus is included in your plan.', $html);
        $this->assertStringContainsString('Install Data Plus', $html);
        $this->assertStringContainsString('page=wps_plugins_page', $html);
    }

    public function test_role_without_manage_capability_gets_no_buttons()
    {
        wp_set_current_user(self::factory()->user->create(['role' => 'editor']));

        foreach ([LicenseHelper::SITUATION_NOT_INSTALLED, LicenseHelper::SITUATION_INACTIVE, LicenseHelper::SITUATION_IN_PLAN] as $situation) {
            $this->situation = $situation;
            $html            = $this->render();

            $this->assertStringContainsString('Ask a site admin to turn on Data Plus.', $html, $situation);
            $this->assertStringNotContainsString('wps-inline-teaser__link', $html, $situation);
            // They can still close it.
            $this->assertStringContainsString('js-wps-inline-teaser-close', $html, $situation);
        }
    }

    public function test_dismissed_teaser_is_hidden_for_that_user_only()
    {
        $this->admin();
        $this->situation = LicenseHelper::SITUATION_NOT_INSTALLED;

        InlineTeaser::dismiss('dp-journey');
        $this->assertSame('', trim($this->render()));
        $this->assertNotSame('', trim($this->render(['id' => 'other-teaser'])));

        $this->admin();
        $this->assertNotSame('', trim($this->render()));
    }

    // Journey placement -----------------------------------------------------------

    private function journey()
    {
        $visitor = new VisitorDecorator((object)[
            'ID' => 7, 'ip' => '203.0.113.7', 'user_id' => 0, 'hits' => 1,
            'first_view' => '2026-04-19 20:05:00', 'last_view' => '2026-04-19 20:05:00', 'last_counter' => '2026-04-19',
            'agent' => 'Firefox', 'platform' => 'Windows', 'location' => 'ES', 'city' => 'Málaga',
        ]);
        $visit  = VisitorJourney::buildVisit($visitor, [(object)['visitor_id' => 7, 'date' => '2026-04-19 20:05:00', 'page_id' => 0]], [], true);

        return (string)View::load('components/visitor-journey', ['visits' => [$visit], 'visitor' => $visitor], true);
    }

    public function test_journey_shows_one_quiet_line_without_data_plus()
    {
        $this->admin();
        $this->situation = LicenseHelper::SITUATION_NOT_INSTALLED;
        $html = $this->journey();

        $this->assertSame(1, substr_count($html, 'wps-journey__teaser'));
        $this->assertStringContainsString('wps-inline-teaser--line', $html);
        // No example rows any more: two mini icons and one line.
        $this->assertSame(0, substr_count($html, 'wps-journey__step--event'));
        $this->assertStringNotContainsString('>Example<', $html);
        $this->assertSame(1, substr_count($html, 'wps-inline-teaser__icon--download'));
        $this->assertSame(1, substr_count($html, 'wps-inline-teaser__icon--click'));
        $this->assertStringContainsString('Clicks and downloads show up here with Data Plus.', $html);
        // Not installed: "See what's included" opens the Premium popup on Data Plus.
        $this->assertStringContainsString("See what&#039;s included", $html);
        $this->assertStringContainsString('js-wps-openPremiumModal', $html);
        $this->assertStringContainsString('data-target="wp-statistics-data-plus"', $html);
    }

    public function test_journey_line_points_to_plugins_page_when_installed_but_off()
    {
        $this->admin();
        $this->situation = LicenseHelper::SITUATION_INACTIVE;
        $html = $this->journey();

        $this->assertStringContainsString('Data Plus is installed but turned off.', $html);
        $this->assertStringContainsString('plugins.php', $html);
        $this->assertStringNotContainsString('js-wps-openPremiumModal', $html);
    }

    public function test_icons_ignore_unknown_names()
    {
        $this->admin();
        $this->situation = LicenseHelper::SITUATION_NOT_INSTALLED;
        $html = (string)View::load('components/lock-sections/inline-teaser', [
            'id' => 'dp-journey', 'addon' => self::SLUG, 'addon_name' => 'Data Plus', 'icons' => ['download', '<script>'],
        ], true);

        $this->assertSame(1, substr_count($html, 'wps-inline-teaser__icon '));
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringNotContainsString('wps-inline-teaser__lock', $html);
    }

    public function test_journey_has_no_teaser_while_data_plus_runs()
    {
        $this->admin();
        $this->situation = LicenseHelper::SITUATION_ACTIVE;
        $html = $this->journey();

        $this->assertStringNotContainsString('wps-inline-teaser', $html);
        $this->assertStringNotContainsString('user-guide.pdf', $html);
    }
}
