<?php

namespace WP_Statistics\Tests\PrivacyAudit;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_STATISTICS\Option;
use WP_Statistics\Service\Admin\PrivacyAudit\Audits\RecommendConsent;
use WP_Statistics\Service\Admin\PrivacyAudit\Faqs\RequireConsent;
use WP_Statistics\Service\Admin\PrivacyAudit\Faqs\RequireMention;
use WP_Statistics\Service\Integrations\ConsentOnlyTracking;
use WP_UnitTestCase;

// WP Consent API and Real Cookie Banner are not installed in the test environment. These stand in for
// their functions, the same way the real plugins answer them.
if (!function_exists('wp_get_consent_type')) {
    eval('function wp_get_consent_type() { return apply_filters("wp_get_consent_type", ""); }'); // phpcs:ignore Squiz.PHP.Eval.Discouraged
}
if (!function_exists('wp_rcb_consent_given')) {
    eval('function wp_rcb_consent_given($id) { $s = isset($GLOBALS["wp_statistics_test_rcb_services"]) ? $GLOBALS["wp_statistics_test_rcb_services"] : []; return array_key_exists($id, $s) ? ["cookie" => true, "cookieOptIn" => $s[$id]] : ["cookie" => null, "cookieOptIn" => true]; }'); // phpcs:ignore Squiz.PHP.Eval.Discouraged
}

/**
 * The consent item and the consent FAQs of the Privacy Audit page must agree with the
 * "Some Visitors Are Not Counted" notice (ConsentOnlyTracking::getInfo()), and name the settings
 * that make WP Statistics record personal data.
 */
class Test_PrivacyAuditConsent extends WP_UnitTestCase
{
    const CONSENT_API   = 'wp-consent-api/wp-consent-api.php';
    const COOKIE_PLUGIN = 'cookiehold/cookiehold.php';
    const RCB           = 'real-cookie-banner/index.php';

    private $previousOptions;
    private $previousPlugins;

    public function setUp(): void
    {
        parent::setUp();

        $this->previousOptions = Option::getOptions();
        $this->previousPlugins = get_option('active_plugins', []);

        // Settings that record no personal data.
        Option::update('hash_ips', true);
        Option::update('anonymize_ips', true);
        Option::update('store_ua', false);
        Option::update('visitors_log', false);
        Option::update('anonymous_tracking', false);
        Option::update('consent_integration', '');
    }

    public function tearDown(): void
    {
        remove_all_filters('wp_get_consent_type');
        remove_all_filters('wp_consent_api_registered_' . self::COOKIE_PLUGIN);
        unset($GLOBALS['wp_statistics_test_rcb_services']);
        update_option('active_plugins', $this->previousPlugins);
        Option::save_options($this->previousOptions);

        parent::tearDown();
    }

    /** WP Consent API with a cookie plugin that sets the given consent type, as CookieHold does. */
    private function useWpConsentApi($consentType)
    {
        update_option('active_plugins', [self::CONSENT_API, self::COOKIE_PLUGIN]);
        add_filter('wp_consent_api_registered_' . self::COOKIE_PLUGIN, '__return_true');
        add_filter('wp_get_consent_type', function () use ($consentType) {
            return $consentType;
        });
        Option::update('consent_integration', 'wp_consent_api');
    }

    private function auditState()
    {
        return RecommendConsent::getState();
    }

    public function test_no_plugin_and_no_personal_data_is_only_a_suggestion()
    {
        $state = $this->auditState();

        $this->assertSame('recommended', RecommendConsent::getStatus());
        $this->assertSame('success', RequireConsent::getStatus());
        $this->assertStringContainsString('only visitors who accept are counted', $state['notes']);
    }

    public function test_no_plugin_with_personal_data_names_each_setting()
    {
        Option::update('hash_ips', false);
        Option::update('store_ua', true);
        Option::update('visitors_log', true);

        $this->assertSame('action_required', RecommendConsent::getStatus());
        $this->assertSame([
            'Hash IP Addresses is off.',
            'Store Entire User Agent String is on.',
            'Track Logged-In User Activity is on.',
        ], RequireConsent::getPersonalDataSettings());

        $faq = RequireConsent::getState();
        $this->assertSame('warning', $faq['status']);
        $this->assertStringContainsString('Hash IP Addresses is off.', $faq['notes']);
        $this->assertStringContainsString('Track Logged-In User Activity is on.', $faq['notes']);

        $mention = RequireMention::getState();
        $this->assertStringContainsString('Store Entire User Agent String is on.', $mention['notes']);

        $this->assertStringContainsString('Hash IP Addresses is off.', $this->auditState()['notes']);
    }

    public function test_opt_in_banner_is_not_passed_and_shows_the_notice_fix()
    {
        $this->useWpConsentApi('optin');

        $info  = ConsentOnlyTracking::getInfo();
        $state = $this->auditState();

        $this->assertNotNull($info);
        $this->assertSame('recommended', RecommendConsent::getStatus());
        $this->assertSame('Some Visitors Are Not Counted', $state['title']);
        $this->assertStringContainsString(esc_html($info['message']), $state['notes']);
        $this->assertStringContainsString(esc_html($info['button']), $state['notes']);
        $this->assertStringNotContainsString('without requiring consent', $state['notes']);
    }

    public function test_opt_in_banner_with_personal_data_does_not_offer_to_remove_consent()
    {
        $this->useWpConsentApi('optin');
        Option::update('hash_ips', false);

        $info  = ConsentOnlyTracking::getInfo();
        $state = $this->auditState();

        $this->assertSame(ConsentOnlyTracking::FIX_NONE, $info['fix']);
        $this->assertStringNotContainsString(esc_html($info['button']), $state['notes']);
        $this->assertStringContainsString('Hash IP Addresses is off.', $state['notes']);
        $this->assertStringContainsString('change these settings first', $state['notes']);
    }

    public function test_opt_out_banner_passes()
    {
        $this->useWpConsentApi('optout');

        $state = $this->auditState();

        $this->assertNull(ConsentOnlyTracking::getInfo());
        $this->assertSame('passed', RecommendConsent::getStatus());
        $this->assertSame('Consent Plugin Connected', $state['title']);
        $this->assertStringContainsString('opt-out', $state['notes']);
    }

    public function test_selected_plugin_that_is_inactive_needs_attention_and_visits_are_anonymous()
    {
        update_option('active_plugins', []);
        Option::update('consent_integration', 'cookieyes');
        Option::update('hash_ips', false);
        Option::update('visitors_log', true);

        $state = $this->auditState();

        $this->assertSame('action_required', RecommendConsent::getStatus());
        $this->assertSame('Consent Integration Needs Attention', $state['title']);
        $this->assertStringContainsString('CookieYes', $state['notes']);

        // Every visit is recorded anonymously while the consent plugin is missing.
        $this->assertSame([], RequireConsent::getPersonalDataSettings());
        $this->assertSame('success', RequireConsent::getStatus());
    }

    public function test_borlabs_without_its_service_needs_attention()
    {
        update_option('active_plugins', ['borlabs-cookie/borlabs-cookie.php']);
        Option::update('consent_integration', 'borlabs_cookie');

        $this->assertSame('action_required', RecommendConsent::getStatus());
        $this->assertSame('Consent Integration Needs Attention', $this->auditState()['title']);
    }

    public function test_real_cookie_banner_with_the_service_counts_only_visitors_who_accept()
    {
        update_option('active_plugins', [self::RCB]);
        Option::update('consent_integration', 'real_cookie_banner');
        $GLOBALS['wp_statistics_test_rcb_services'] = ['wp-statistics' => false];

        $this->assertSame('recommended', RecommendConsent::getStatus());
        $this->assertSame('Some Visitors Are Not Counted', $this->auditState()['title']);
    }

    public function test_real_cookie_banner_without_the_service_passes_only_without_personal_data()
    {
        update_option('active_plugins', [self::RCB]);
        Option::update('consent_integration', 'real_cookie_banner');

        $this->assertSame('passed', RecommendConsent::getStatus());
        $this->assertStringContainsString('does not ask visitors about WP Statistics', $this->auditState()['notes']);

        Option::update('visitors_log', true);

        $this->assertSame('action_required', RecommendConsent::getStatus());
        $this->assertStringContainsString('Track Logged-In User Activity is on.', $this->auditState()['notes']);

        // The FAQ does not claim the banner asks visitors when it does not.
        $this->assertStringNotContainsString('already follows', RequireConsent::getState()['notes']);
    }

    public function test_page_copy_uses_straight_quotes()
    {
        $files = array_merge(
            glob(WP_STATISTICS_DIR . 'src/Service/Admin/PrivacyAudit/*/*.php'),
            glob(WP_STATISTICS_DIR . 'views/components/privacy-audit/*.php'),
            glob(WP_STATISTICS_DIR . 'includes/admin/templates/layout/privacy-audit/*.php'),
            [WP_STATISTICS_DIR . 'includes/admin/templates/pages/privacy-audit.php']
        );

        foreach ($files as $file) {
            $this->assertDoesNotMatchRegularExpression('/[\x{2018}\x{2019}\x{201C}\x{201D}]/u', file_get_contents($file), basename($file));
        }
    }
}
