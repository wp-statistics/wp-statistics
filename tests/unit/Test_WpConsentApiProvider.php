<?php

use WP_Statistics\Service\Consent\TrackingLevel;
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

    public function test_tracking_level_full_when_consent_level_is_disabled()
    {
        update_option('wp_statistics', array_merge(
            get_option('wp_statistics', []),
            ['consent_level_integration' => 'disabled']
        ));

        $this->assertSame(TrackingLevel::FULL, $this->provider->getTrackingLevel());
    }

    public function test_tracking_level_none_when_function_missing()
    {
        update_option('wp_statistics', array_merge(
            get_option('wp_statistics', []),
            ['consent_level_integration' => 'statistics']
        ));

        // wp_has_consent() does not exist in test env — should fail closed
        $this->assertSame(TrackingLevel::NONE, $this->provider->getTrackingLevel());
    }

    public function test_tracking_level_none_when_default_consent_level()
    {
        // Default consent level is 'functional' (not 'disabled'), so getTrackingLevel() delegates
        // to wp_has_consent(), which does not exist in the test environment -- returns NONE.
        $this->assertSame(TrackingLevel::NONE, $this->provider->getTrackingLevel());
    }

    public function test_tracking_level_anonymous_when_option_enabled()
    {
        update_option('wp_statistics', array_merge(
            get_option('wp_statistics', []),
            ['anonymous_tracking' => true]
        ));

        $this->assertSame(TrackingLevel::ANONYMOUS, $this->provider->getTrackingLevel());
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
}
