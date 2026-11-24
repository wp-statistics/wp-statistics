<?php

namespace WP_Statistics\Tests\Views;

use WP_Statistics\Components\View;
use WP_Statistics\Decorators\VisitorDecorator;
use WP_STATISTICS\Option;
use WP_UnitTestCase;

/**
 * Visitor Information cell + its visitor card (views/components/visitor-information.php).
 * All IPs, names and emails are made up.
 */
class Test_VisitorInformationView extends WP_UnitTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        // Start every test from the same privacy settings: the defaults differ
        // between single site and multisite test installs.
        Option::update('visitors_log', false);
        Option::update('hash_ips', false);
    }

    public function tearDown(): void
    {
        Option::update('visitors_log', false);
        Option::update('hash_ips', false);
        parent::tearDown();
    }

    private function render(array $row)
    {
        $visitor = new VisitorDecorator((object)array_merge([
            'ID'       => 42,
            'ip'       => '203.0.113.7',
            'location' => 'DE',
            'region'   => 'Bavaria',
            'city'     => 'Munich',
            'agent'    => 'Chrome',
            'version'  => '150.0.1',
            'platform' => 'Windows',
            'device'   => 'desktop',
            'model'    => '',
            'user_id'  => 0,
        ], $row));

        return View::load('components/visitor-information', ['visitor' => $visitor], true);
    }

    public function test_keeps_addon_markup_and_hides_card_for_pdf_and_email()
    {
        $html = $this->render([]);

        $this->assertStringContainsString('<ul class="wps-visitor__information--container wps-visitor-info wps-visitor-info--anonymous"', $html);
        $this->assertSame(6, substr_count($html, '<li class="wps-visitor__information'), 'four icons + the login status icon + the who item');
        $this->assertStringContainsString('wps-visitor__information__incognito-text', $html, 'IP text kept in markup for PDF export and add-ons');
        $this->assertMatchesRegularExpression('/class="wps-visitor-card"[^>]*hidden style="display:none"/', $html);
        $this->assertStringNotContainsString('wps-tooltip', $html, 'no per-icon tooltips any more');
        $this->assertStringContainsString('wps-visitor-info__slot--anonymous', $html, 'not logged in = incognito icon');
    }

    public function test_anonymous_visitor_uses_the_logged_in_users_widget_icon_and_wording()
    {
        $html = $this->render([]);
        $card = substr($html, strpos($html, 'class="wps-visitor-card"'));
        $icon = file_get_contents(WP_STATISTICS_DIR . 'assets/images/anonymous.svg');
        preg_match('/ d="([^"]+)"/', $icon, $m);

        $this->assertSame(2, substr_count($html, $m[1]), 'same incognito shape in the pill and the card avatar');
        $this->assertStringContainsString('<strong>Anonymous Visitor</strong>', $card);
        $this->assertStringContainsString('wps-visitor-card__tag--anonymous">Anonymous<', $card);
        $this->assertStringNotContainsString('Guest', $html);
        $this->assertStringNotContainsString('M3 3l18 18', $html, 'no eye-off icon any more');
    }

    public function test_pill_icon_is_login_status_only_ip_privacy_is_a_card_row()
    {
        Option::update('hash_ips', true);
        $html = $this->render([]);
        $card = substr($html, strpos($html, 'class="wps-visitor-card"'));

        $this->assertStringContainsString('wps-visitor-info--anonymous', $html);
        $this->assertStringNotContainsString('--private', $html);
        $this->assertStringContainsString('Anonymous Visitor, IP hidden', $html, 'both facts in the accessible name');
        $this->assertStringContainsString('IP hidden', $card);
    }

    public function test_trigger_is_a_button_controlling_the_card()
    {
        $html = $this->render([]);

        $this->assertMatchesRegularExpression('/role="button" tabindex="0" aria-haspopup="dialog" aria-expanded="false" aria-controls="(wps-visitor-card-[^"]+)" aria-label="Visitor details: Munich, Bavaria, Germany, Chrome on Windows, Desktop, Anonymous Visitor"/', $html);
        preg_match('/aria-controls="([^"]+)"/', $html, $m);
        $this->assertStringContainsString('id="' . $m[1] . '" role="dialog"', $html);
    }

    public function test_guest_card_has_ip_actions_and_facts()
    {
        $html = $this->render([]);
        $card = substr($html, strpos($html, 'class="wps-visitor-card"'));

        $this->assertStringContainsString('data-wps-copy="203.0.113.7"', $card);
        $this->assertStringContainsString('<span class="wps-visitor-card__ip">203.0.113.7</span>', $card);
        $this->assertSame(1, substr_count($card, '203.0.113.7</'), 'IP shown once in the card');
        $this->assertStringContainsString('Visitor Journey', $card);
        $this->assertStringContainsString('visitor_id=42', $card);
        $this->assertStringContainsString('tab=visitors&#038;ip=203.0.113.7&#038;exact_ip=1', $card);
        $this->assertStringContainsString('Anonymous Visitor', $card);
        $this->assertStringContainsString('Munich, Bavaria, <a', $card);
        $this->assertStringContainsString('country=DE', $card, 'country report link kept');
        $this->assertStringContainsString('agent=Chrome', $card, 'browser filter kept');
        $this->assertStringContainsString('platform=Windows', $card, 'OS filter kept');
        $this->assertStringContainsString('Chrome 150', $card);
        $this->assertSame(1, substr_count($card, 'wps-visitor-card__flag'), 'flag only once, in the location line');
        $this->assertStringContainsString('--wps-card-actions:3', $card);

        // Copy IP, Filter by IP, then Visitor Journey (primary) last.
        $copy    = strpos($card, 'Copy IP');
        $filter  = strpos($card, 'Filter by IP');
        $journey = strpos($card, 'wps-visitor-card__primary');
        $this->assertTrue($copy < $filter && $filter < $journey);
    }

    public function test_hashed_ip_shows_private_status_and_no_ip_actions()
    {
        $html = $this->render(['ip' => '#hash#e7b398f96b14993b571215e36b41850c65f39b1a']);
        $card = substr($html, strpos($html, 'class="wps-visitor-card"'));

        $this->assertStringContainsString('wps-visitor-info__slot--anonymous', $html, 'login status icon, not an IP icon');
        $this->assertStringContainsString('IP hidden', $html);
        $this->assertStringContainsString('IP hidden (hashed)', $card);
        $this->assertStringNotContainsString('data-wps-copy=', $card);
        $this->assertStringNotContainsString('Filter by IP', $card);
        $this->assertStringContainsString('location=DE', $card, 'country filter instead');
        $this->assertStringContainsString('--wps-card-actions:2', $card);
        $this->assertStringNotContainsString('e7b398f96b14993b571215e36b41850c65f39b1a', $html, 'full hash never printed');
    }

    public function test_hashed_ip_and_unknown_country_leaves_only_the_journey()
    {
        $html = $this->render(['ip' => '#hash#e7b398f96b14993b571215e36b41850c65f39b1a', 'location' => '000', 'city' => '', 'region' => '']);

        $this->assertStringContainsString('Location unknown', $html);
        $this->assertStringNotContainsString('Filter by country', $html);
        $this->assertStringContainsString('--wps-card-actions:1', $html);
    }

    public function test_ip_hidden_when_hash_option_on_and_users_not_tracked()
    {
        Option::update('hash_ips', true);
        $html = $this->render([]);

        $this->assertStringNotContainsString('203.0.113.7', $html);
        $this->assertStringContainsString('wps-visitor-info__slot--anonymous', $html);
    }

    public function test_logged_in_user_card_shows_name_email_role_and_id_to_admins()
    {
        Option::update('visitors_log', true);
        wp_set_current_user(self::factory()->user->create(['role' => 'administrator']));
        $userId = self::factory()->user->create(['display_name' => 'Ana Example', 'user_email' => 'ana@example.com', 'role' => 'author']);

        $html = $this->render(['user_id' => $userId]);

        $this->assertStringContainsString('wps-visitor-info__slot--user', $html, 'person icon in the pill');
        $this->assertStringContainsString('Logged-in user: Ana Example', $html);
        $this->assertStringContainsString('<span class="wps-visitor-info__name">Ana Example</span>', $html, 'user-text spans kept for add-ons');
        $this->assertStringContainsString('ana@example.com', $html);
        $this->assertStringContainsString('Logged in', $html);
        $this->assertStringContainsString('Author · user #' . $userId, $html);
        $this->assertStringContainsString('wps-visitor-card__initial">A<', $html);
        $this->assertSame(6, substr_count($html, '<li class="wps-visitor__information'));
    }

    public function test_editor_sees_name_but_not_email_or_role()
    {
        Option::update('visitors_log', true);
        wp_set_current_user(self::factory()->user->create(['role' => 'editor']));
        $userId = self::factory()->user->create(['display_name' => 'Ana Example', 'user_email' => 'ana@example.com', 'role' => 'author']);

        $html = $this->render(['user_id' => $userId, 'hits' => 3]);

        $this->assertStringContainsString('Ana Example', $html);
        $this->assertStringNotContainsString('ana@example.com', $html);
        $this->assertStringNotContainsString('Author', $html);
        $this->assertStringContainsString('3 views', $html);
    }

    public function test_user_hidden_when_user_tracking_off()
    {
        wp_set_current_user(self::factory()->user->create(['role' => 'administrator']));
        $userId = self::factory()->user->create(['display_name' => 'Ana Example', 'user_email' => 'ana@example.com']);

        $html = $this->render(['user_id' => $userId]);

        $this->assertStringNotContainsString('ana@example.com', $html);
        $this->assertStringNotContainsString('Ana Example', $html);
        $this->assertStringNotContainsString('wps-visitor-info__slot--user', $html);
        $this->assertStringContainsString('Anonymous Visitor', $html);
    }

    public function test_ipv6_is_kept_whole_in_the_card()
    {
        $html = $this->render(['ip' => '2001:db8:85a3::8a2e:370:7334']);

        $this->assertStringContainsString('<span class="wps-visitor-card__ip">2001:db8:85a3::8a2e:370:7334</span>', $html);
        $this->assertStringContainsString('data-wps-copy="2001:db8:85a3::8a2e:370:7334"', $html);
    }

    public function test_values_are_escaped()
    {
        Option::update('visitors_log', true);
        wp_set_current_user(self::factory()->user->create(['role' => 'administrator']));
        $userId = self::factory()->user->create(['display_name' => '<img src=x onerror=alert(1)>', 'user_email' => 'x@example.com']);

        $html = $this->render([
            'user_id' => $userId,
            'city'    => '<script>c()</script>',
            'agent'   => '"><b>agent',
            'model'   => '<i>model</i>',
        ]);

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringNotContainsString('<img src=x', $html);
        $this->assertStringNotContainsString('"><b>', $html);
        $this->assertStringNotContainsString('<i>model', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }
}
