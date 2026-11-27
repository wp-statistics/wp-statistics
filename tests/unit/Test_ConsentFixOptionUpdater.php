<?php

use WP_Statistics\Service\Admin\AjaxOptionUpdater;
use WP_Statistics\Service\Integrations\ConsentOnlyTracking;

/**
 * The "Some Visitors Are Not Counted" fix uses the shared option updater
 * (a.wps-option__updater + AjaxOptionUpdater) instead of its own admin-post action.
 */
class Test_ConsentFixOptionUpdater extends WP_UnitTestCase
{
    public function test_consent_options_are_allowed_with_fixed_values_only()
    {
        $this->assertContains('consent_integration', AjaxOptionUpdater::getAllowedOptions());
        $this->assertNotContains('anonymous_tracking', AjaxOptionUpdater::getAllowedOptions());

        $values = AjaxOptionUpdater::getAllowedValues();
        $this->assertSame([''], $values['consent_integration'], 'only switching the integration off');
        $this->assertArrayNotHasKey('share_anonymous_data', $values);
    }

    public function test_fix_button_is_an_option_updater_link()
    {
        \WP_STATISTICS\Option::update('hash_ips', true);
        \WP_STATISTICS\Option::update('anonymize_ips', true);
        \WP_STATISTICS\Option::update('store_ua', false);
        \WP_STATISTICS\Option::update('visitors_log', false);

        $html = ConsentOnlyTracking::getFixButton([
            'option' => 'consent_integration',
            'value'  => '',
            'button' => 'Set Consent Plugin Integration to None',
        ]);

        $this->assertStringContainsString('class="button button-primary wps-option__updater"', $html);
        $this->assertStringContainsString('data-option="consent_integration"', $html);
        $this->assertStringContainsString('data-value=""', $html);
        $this->assertStringNotContainsString('admin-post.php', $html);
    }

    public function test_old_admin_post_action_is_gone()
    {
        $this->assertFalse(method_exists(ConsentOnlyTracking::class, 'handleFix'));
        $this->assertFalse(method_exists(ConsentOnlyTracking::class, 'getFixUrl'));
        $this->assertFalse(has_action('admin_post_wp_statistics_consent_only_tracking_fix'));
    }

    public function test_no_none_button_when_settings_record_personal_data()
    {
        \WP_STATISTICS\Option::update('hash_ips', false);
        \WP_STATISTICS\Option::update('anonymize_ips', true);
        \WP_STATISTICS\Option::update('store_ua', false);
        \WP_STATISTICS\Option::update('visitors_log', false);

        $this->assertNotEmpty(\WP_Statistics\Service\Admin\PrivacyAudit\Faqs\RequireConsent::getPersonalDataSettings(false));
        $this->assertFalse(ConsentOnlyTracking::isNoneSafe());
        $this->assertSame('', ConsentOnlyTracking::getFixButton(['option' => 'consent_integration', 'value' => '', 'button' => 'Set Consent Plugin Integration to None']));
    }
}
