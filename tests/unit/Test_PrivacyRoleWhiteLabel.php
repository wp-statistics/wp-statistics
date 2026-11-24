<?php

use WP_STATISTICS\Admin_Assets;
use WP_STATISTICS\IP;
use WP_STATISTICS\Option;
use WP_Statistics\Abstracts\BaseTabView;
use WP_Statistics\Components\View;
use WP_Statistics\Decorators\VisitorDecorator;
use WP_Statistics\Service\Admin\AddonPageFallback;
use WP_Statistics\Service\Admin\Metabox\MetaboxHelper;
use WP_Statistics\Service\Admin\PageHeader\SingleHeaders;

/**
 * 14.17 final QA (round 5): one privacy rule for IPs and user names everywhere, admin-only links and upsells
 * for people with the manage capability, white label hides upsells, friendly fallbacks for unknown tabs and
 * add-on pages that are turned off.
 */
class Test_PrivacyRoleWhiteLabel extends WP_UnitTestCase
{
    private $options = [];

    public function setUp(): void
    {
        parent::setUp();
        require_once WP_STATISTICS_DIR . 'includes/admin/class-wp-statistics-admin-assets.php';
        require_once WP_STATISTICS_DIR . 'includes/admin/pages/class-wp-statistics-admin-page-settings.php';
        foreach (['hash_ips', 'visitors_log', 'manage_capability', 'read_capability'] as $key) {
            $this->options[$key] = Option::get($key);
        }
        Option::update('manage_capability', 'manage_options');
        Option::update('read_capability', 'manage_options');
    }

    public function tearDown(): void
    {
        foreach ($this->options as $key => $value) {
            Option::update($key, $value);
        }
        remove_all_filters('wp_statistics_hide_addon_prompts');
        unset($_REQUEST['tab']);
        wp_set_current_user(0);
        parent::tearDown();
    }

    private function visitor($ip, $userId = 0)
    {
        return new VisitorDecorator((object)[
            'ID'           => 7,
            'ip'           => $ip,
            'user_id'      => $userId,
            'location'     => 'DE',
            'city'         => 'Berlin',
            'region'       => 'Berlin',
            'continent'    => 'Europe',
            'agent'        => 'Chrome',
            'version'      => '120',
            'platform'     => 'Windows',
            'device'       => 'desktop',
            'model'        => '',
            'hits'         => 5,
            'referred'     => '',
            'source_channel' => 'direct',
            'source_name'  => '',
            'last_counter' => '2026-11-20',
            'last_view'    => '2026-11-20 10:00:00',
            'first_view'   => '2026-11-20 09:00:00',
            'UAString'     => '',
        ]);
    }

    /**
     * Renders a partial and lets exceptions through (View::load() only logs them).
     */
    private function render($view, $args)
    {
        extract($args); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
        ob_start();
        try {
            include WP_STATISTICS_DIR . 'views/' . $view . '.php';
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }

        return ob_get_clean();
    }

    private function hashedIp()
    {
        return IP::$hash_ip_prefix . str_repeat('a1b2c3d4', 5);
    }

    private function login($role)
    {
        $id = self::factory()->user->create(['role' => $role, 'display_name' => 'Jane Editor']);
        wp_set_current_user($id);

        return $id;
    }

    // --- Privacy: one rule for card, header, profile and title ---

    public function test_raw_ip_is_shown_only_while_hashing_is_off()
    {
        Option::update('hash_ips', false);
        $this->assertTrue($this->visitor('203.0.113.9')->isIpVisible());

        Option::update('hash_ips', true);
        // An older row saved before hashing was switched on still holds the raw IP: hidden too.
        $this->assertFalse($this->visitor('203.0.113.9')->isIpVisible());
    }

    public function test_user_tracking_does_not_reveal_raw_ips_while_hashing_is_on()
    {
        // The old rule was `!hash_ips || visitors_log`: user tracking on showed raw IPs.
        Option::update('hash_ips', true);
        Option::update('visitors_log', true);

        $this->assertFalse($this->visitor('203.0.113.9')->isIpVisible());
    }

    public function test_hashed_or_empty_ip_is_never_visible()
    {
        Option::update('hash_ips', false);

        $this->assertFalse($this->visitor($this->hashedIp())->isIpVisible());
        $this->assertFalse($this->visitor('')->isIpVisible());
    }

    public function test_user_is_shown_only_while_user_tracking_is_on()
    {
        $userId  = self::factory()->user->create(['display_name' => 'Tracked Person']);
        $visitor = $this->visitor('203.0.113.9', $userId);

        Option::update('visitors_log', true);
        $this->assertNotNull($visitor->getVisibleUser());
        $this->assertSame('Tracked Person', $visitor->getDisplayLabel());

        Option::update('visitors_log', false);
        $this->assertNull($visitor->getVisibleUser());
        $this->assertNotSame('Tracked Person', $visitor->getDisplayLabel());
    }

    public function test_display_label_never_contains_a_hidden_raw_ip()
    {
        Option::update('visitors_log', false);

        Option::update('hash_ips', true);
        $this->assertSame('Anonymous Visitor', $this->visitor('203.0.113.9')->getDisplayLabel());

        Option::update('hash_ips', false);
        $this->assertSame('203.0.113.9', $this->visitor('203.0.113.9')->getDisplayLabel());

        // A hash is shown as its short, non-reversible form.
        $this->assertStringStartsWith('#', $this->visitor($this->hashedIp())->getDisplayLabel());
    }

    public function test_visitor_card_hides_raw_ip_and_ip_actions_while_hashing_is_on()
    {
        $this->login('administrator');
        Option::update('hash_ips', true);
        Option::update('visitors_log', true);

        $html = $this->render('components/visitor-information', ['visitor' => $this->visitor('203.0.113.9')]);

        $this->assertStringNotContainsString('203.0.113.9', $html);
        $this->assertStringNotContainsString('data-wps-copy="', $html);
        $this->assertStringContainsString('IP hidden', $html);
    }

    public function test_visitor_profile_follows_the_card_rule()
    {
        Option::update('hash_ips', true);
        Option::update('visitors_log', false);

        $html = $this->render('components/visitor-profile', ['visitor' => $this->visitor('203.0.113.9'), 'summary' => ['visits' => 3, 'views' => 5]]);

        $this->assertStringNotContainsString('203.0.113.9', $html);
        $this->assertStringContainsString('Anonymous Visitor', $html);
        $this->assertStringContainsString('Not logged in · IP hidden', $html);
        $this->assertStringContainsString('assets/images/anonymous.svg', $html, 'same incognito icon as the Logged-in Users widget');
        $this->assertStringNotContainsString('Guest', $html);
        $this->assertStringNotContainsString('from this IP', $html);
    }

    public function test_single_visitor_header_shows_short_hash_for_hashed_ip()
    {
        Option::update('hash_ips', true);

        $header = SingleHeaders::visitor($this->visitor($this->hashedIp()));
        $facts  = wp_list_pluck($header['facts'], 'text');

        $this->assertContains('#a1b2c3d4 · hashed', $facts);
        $this->assertNotContains('Hashed IP', $facts);
    }

    public function test_single_visitor_header_says_anonymous_and_keeps_ip_privacy_as_a_fact()
    {
        Option::update('hash_ips', true);
        Option::update('visitors_log', false);

        $header = SingleHeaders::visitor($this->visitor('203.0.113.9'));

        $this->assertSame('Anonymous Visitor', $header['title']);
        $this->assertSame('Anonymous', $header['tag']);
        $this->assertSame('anonymous', $header['icon']['shape']);
        $this->assertStringEndsWith('assets/images/anonymous.svg', $header['icon']['src']);
        $facts = wp_list_pluck($header['facts'], 'text');
        $this->assertContains('IP hidden', $facts);
    }

    // --- Roles and white label ---

    public function test_go_premium_widget_is_hidden_for_users_without_manage_capability()
    {
        $this->login('editor');
        $this->assertTrue(MetaboxHelper::shouldHideGoPremium());
    }

    public function test_go_premium_widget_is_hidden_under_white_label()
    {
        $this->login('administrator');
        add_filter('wp_statistics_hide_addon_prompts', '__return_true');

        $this->assertTrue(MetaboxHelper::shouldHideGoPremium());
    }

    public function test_go_premium_widget_is_hidden_when_data_plus_is_active()
    {
        $this->login('administrator');
        $active = function ($plugins) { return array_values(array_diff((array) $plugins, ['wp-statistics-data-plus/wp-statistics-data-plus.php'])); };
        add_filter('option_active_plugins', $active, 99);
        $this->assertFalse(MetaboxHelper::shouldHideGoPremium());
        remove_filter('option_active_plugins', $active, 99);

        $on = function ($plugins) { return array_merge((array) $plugins, ['wp-statistics-data-plus/wp-statistics-data-plus.php']); };
        add_filter('option_active_plugins', $on, 99);
        $this->assertTrue(MetaboxHelper::shouldHideGoPremium());
        remove_filter('option_active_plugins', $on, 99);
    }

    public function test_default_about_widget_is_hidden_under_white_label_unless_a_custom_widget_is_set()
    {
        $this->login('administrator');
        $customization = function ($plugins) { return array_merge((array) $plugins, ['wp-statistics-customization/wp-statistics-customization.php']); };
        $settings      = ['wps_white_label' => '1', 'show_wps_about_widget_overview' => 'no'];
        $option        = function () use (&$settings) { return $settings; };
        add_filter('option_active_plugins', $customization, 99);
        add_filter('pre_option_wpstatistics_customization_settings', $option);

        if (!\WP_STATISTICS\Helper::isAddOnActive('customization')) {
            remove_filter('pre_option_wpstatistics_customization_settings', $option);
            $this->markTestSkipped('Customization cannot be faked active here.');
        }

        $this->assertFalse((new \WP_Statistics\Service\Admin\Metabox\Metaboxes\AboutWPS())->isActive());

        $settings['show_wps_about_widget_overview'] = 'yes';
        $this->assertTrue((new \WP_Statistics\Service\Admin\Metabox\Metaboxes\AboutWPS())->isActive());

        $settings = ['wps_white_label' => '', 'show_wps_about_widget_overview' => 'no'];
        $this->assertTrue((new \WP_Statistics\Service\Admin\Metabox\Metaboxes\AboutWPS())->isActive());

        remove_filter('pre_option_wpstatistics_customization_settings', $option);
        remove_filter('option_active_plugins', $customization, 99);
    }

    public function test_report_hidden_by_customization_gets_a_friendly_page_not_a_bare_403()
    {
        $active   = function ($plugins) { return array_merge((array) $plugins, ['wp-statistics-customization/wp-statistics-customization.php']); };
        $settings = function () { return ['disable_menus' => ['devices', 'content_analytics']]; };
        add_filter('option_active_plugins', $active, 99);
        add_filter('pre_option_wpstatistics_customization_settings', $settings);

        $pages = AddonPageFallback::getDisabledMenuPages();
        $this->assertContains('wps_devices_page', $pages);
        $this->assertContains('wps_content-analytics_page', $pages);

        $this->assertSame('turned-off-admin', AddonPageFallback::decideDisabled('wps_devices_page', $pages, true, true));
        $this->assertSame('turned-off', AddonPageFallback::decideDisabled('wps_devices_page', $pages, true, false));
        $this->assertSame('', AddonPageFallback::decideDisabled('wps_devices_page', $pages, false, false), 'No WP Statistics access');
        $this->assertSame('', AddonPageFallback::decideDisabled('wps_geographic_page', $pages, true, true), 'Not hidden');

        remove_filter('pre_option_wpstatistics_customization_settings', $settings);
        remove_filter('option_active_plugins', $active, 99);
        $this->assertSame([], AddonPageFallback::getDisabledMenuPages(), 'Customization off: nothing is hidden');
    }

    public function test_enable_email_reports_box_is_for_settings_managers_only()
    {
        $this->login('editor');
        $this->assertSame('', trim(View::load('components/meta-box/enable-mail', [], true)));

        $this->login('administrator');
        $this->assertStringContainsString('Enable Now', View::load('components/meta-box/enable-mail', [], true));
    }

    public function test_lock_page_under_white_label_is_a_neutral_note()
    {
        $this->login('administrator');
        add_filter('wp_statistics_hide_addon_prompts', '__return_true');

        $html = View::load('pages/lock-page', ['addon_slug' => 'wp-statistics-data-plus', 'addon_name' => 'Data Plus'], true);

        $this->assertStringContainsString('This feature is not available', $html);
        $this->assertStringNotContainsString('wps-upsell-btn--primary', $html);
        $this->assertStringNotContainsString('pricing', $html);
    }

    public function test_settings_screen_answers_403_for_users_without_manage_capability()
    {
        $this->login('editor');
        $GLOBALS['pagenow'] = 'admin.php';
        $_REQUEST['page']   = 'wps_settings_page';
        set_current_screen('dashboard');

        $captured = null;
        $handler  = function () use (&$captured) {
            return function ($message, $title, $args) use (&$captured) {
                $captured = $args;
                throw new \RuntimeException('wp_die');
            };
        };
        add_filter('wp_die_handler', $handler);

        try {
            $page = (new \ReflectionClass(\WP_STATISTICS\settings_page::class))->newInstanceWithoutConstructor();
            $page->checkAccess();
        } catch (\RuntimeException $e) {
            // expected
        } finally {
            remove_filter('wp_die_handler', $handler);
            unset($_REQUEST['page']);
        }

        $this->assertIsArray($captured);
        $this->assertSame(403, (int)$captured['response']);
    }

    // --- Add-on pages that are turned off ---

    public function test_addon_page_fallback_decisions()
    {
        $page = 'wps_link_tracker_page';

        $this->assertSame('', AddonPageFallback::decide('some_other_page', false, false, true, true));
        $this->assertSame('', AddonPageFallback::decide($page, true, false, true, true), 'Add-on active: a real permission problem');
        $this->assertSame('', AddonPageFallback::decide($page, false, false, false, false), 'No WP Statistics access');
        $this->assertSame('unavailable', AddonPageFallback::decide($page, false, true, true, true), 'White label');
        $this->assertSame('redirect', AddonPageFallback::decide($page, false, false, true, true), 'Admin: the Add-ons page');
        $this->assertSame('ask-admin', AddonPageFallback::decide($page, false, false, true, false), 'Other users');
    }

    // --- Unknown tab: default tab, no exception, no log entry ---

    public function test_unknown_tab_falls_back_to_the_default_tab()
    {
        $_REQUEST['tab'] = 'logged-in-users';

        $view = new class extends BaseTabView {
            protected $defaultTab = 'overview';
            protected $tabs = ['overview', 'visitors'];

            public function render()
            {
            }

            public function currentTab()
            {
                return $this->getCurrentTab();
            }
        };

        $this->assertSame('overview', $view->currentTab());

        $_REQUEST['tab'] = 'visitors';
        $this->assertSame('visitors', $view->currentTab());
    }

    // --- Real-Time page: visitor card script before </body> ---

    public function test_visitor_card_script_goes_before_the_body_end_once()
    {
        $tag  = '<script src="https://example.test/visitor-card.min.js"></script>';
        $html = Admin_Assets::insertBeforeBodyEnd('<html><body><p>x</p></body></html>', $tag);

        $this->assertSame('<html><body><p>x</p>' . $tag . '</body></html>', $html);
        $this->assertSame($html, Admin_Assets::insertBeforeBodyEnd($html, $tag), 'Not added twice');
        $this->assertSame('', Admin_Assets::insertBeforeBodyEnd('', $tag));
    }

    // --- Small shared pieces ---

    public function test_internal_link_without_title_or_url_prints_the_unknown_dash()
    {
        $html = View::load('components/objects/internal-link', ['url' => '', 'title' => ''], true);

        $this->assertStringContainsString('—', $html);
        $this->assertStringNotContainsString('<a ', $html);
    }

    public function test_change_chip_drops_a_trailing_zero_decimal()
    {
        $this->assertStringContainsString('>50%<', str_replace(["\n", ' '], '', View::load('components/objects/change-chip', ['change' => 50.0], true)));
        $this->assertStringContainsString('12.5%', View::load('components/objects/change-chip', ['change' => 12.5], true));
    }
}
