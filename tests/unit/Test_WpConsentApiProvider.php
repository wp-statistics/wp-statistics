<?php

use WP_Statistics\Service\Consent\ConsentStatus;
use WP_Statistics\Service\Consent\Providers\WpConsentApiProvider;

/**
 * @group consent
 */
class Test_WpConsentApiProvider extends WP_UnitTestCase
{
    private WpConsentApiProvider $provider;

    public function setUp(): void
    {
        parent::setUp();
        $this->provider = new WpConsentApiProvider();
    }

    public function test_key_is_wp_consent_api()
    {
        $this->assertEquals('wp_consent_api', $this->provider->getKey());
    }

    public function test_has_consent_returns_true_when_consent_level_is_disabled()
    {
        update_option('wp_statistics', array_merge(
            get_option('wp_statistics', []),
            ['consent_level_integration' => 'disabled']
        ));

        $this->assertTrue($this->provider->hasConsent());
    }

    public function test_has_consent_fails_closed_when_function_missing()
    {
        update_option('wp_statistics', array_merge(
            get_option('wp_statistics', []),
            ['consent_level_integration' => 'statistics']
        ));

        // wp_has_consent() does not exist in test env — should fail closed
        $this->assertFalse($this->provider->hasConsent());
    }

    public function test_has_consent_returns_false_when_default_consent_level()
    {
        // Default consent level is 'functional' (not 'disabled'), so hasConsent() delegates
        // to wp_has_consent(), which does not exist in the test environment -- returns false.
        $this->assertFalse($this->provider->hasConsent());
    }

    public function test_track_anonymously_reads_option()
    {
        update_option('wp_statistics', array_merge(
            get_option('wp_statistics', []),
            ['anonymous_tracking' => true]
        ));

        $this->assertTrue($this->provider->trackAnonymously());
    }

    public function test_track_anonymously_defaults_to_false()
    {
        $this->assertFalse($this->provider->trackAnonymously());
    }

    public function test_js_config_contains_mode()
    {
        $config = $this->provider->getJsConfig();
        $this->assertEquals('wp_consent_api', $config['mode']);
        $this->assertArrayHasKey('consentLevel', $config);
        $this->assertArrayHasKey('trackAnonymously', $config);
    }

    public function test_js_handles_includes_wp_consent_api()
    {
        $handles = $this->provider->getJsHandles();
        $this->assertContains('wp-consent-api', $handles);
    }

    public function test_status_includes_consent_level()
    {
        $status = $this->provider->getStatus();
        $this->assertArrayHasKey('consent_level', $status);
        $this->assertArrayHasKey('has_consent', $status);
        $this->assertArrayHasKey('track_anonymously', $status);
    }

    public function test_get_compatible_plugins_returns_array()
    {
        $plugins = $this->provider->getCompatiblePlugins();
        $this->assertIsArray($plugins);
    }

    public function test_consent_level_defaults_to_functional()
    {
        $this->assertEquals('functional', $this->provider->getConsentLevel());
    }

    public function test_consent_level_reads_option()
    {
        update_option('wp_statistics', array_merge(
            get_option('wp_statistics', []),
            ['consent_level_integration' => 'functional']
        ));

        $this->assertEquals('functional', $this->provider->getConsentLevel());
    }

    public function test_consent_status_full_when_disabled_no_anon()
    {
        update_option('wp_statistics', array_merge(
            get_option('wp_statistics', []),
            ['consent_level_integration' => 'disabled', 'anonymous_tracking' => false]
        ));

        $this->assertTrue($this->provider->getConsentStatus()->equals(ConsentStatus::full()));
    }

    public function test_consent_status_anonymous_when_disabled_with_anon()
    {
        update_option('wp_statistics', array_merge(
            get_option('wp_statistics', []),
            ['consent_level_integration' => 'disabled', 'anonymous_tracking' => true]
        ));

        $this->assertTrue($this->provider->getConsentStatus()->equals(ConsentStatus::anonymous()));
    }

    public function test_consent_status_none_when_no_consent_no_anon()
    {
        update_option('wp_statistics', array_merge(
            get_option('wp_statistics', []),
            ['consent_level_integration' => 'statistics', 'anonymous_tracking' => false]
        ));

        // wp_has_consent() not available in test env → hasConsent() returns false
        $this->assertTrue($this->provider->getConsentStatus()->equals(ConsentStatus::none()));
    }

    public function test_consent_status_anonymous_when_no_consent_with_anon()
    {
        update_option('wp_statistics', array_merge(
            get_option('wp_statistics', []),
            ['consent_level_integration' => 'statistics', 'anonymous_tracking' => true]
        ));

        // wp_has_consent() not available → hasConsent() returns false, but anon is true
        $this->assertTrue($this->provider->getConsentStatus()->equals(ConsentStatus::anonymous()));
    }
}
