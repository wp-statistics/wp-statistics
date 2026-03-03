<?php

use WP_Statistics\Service\Consent\ConsentStatus;
use WP_Statistics\Service\Consent\Providers\BorlabsCookieProvider;

/**
 * @group consent
 */
class Test_BorlabsCookieProvider extends WP_UnitTestCase
{
    private BorlabsCookieProvider $provider;

    public function setUp(): void
    {
        parent::setUp();
        $this->provider = new BorlabsCookieProvider();
    }

    public function test_key_is_borlabs_cookie()
    {
        $this->assertEquals('borlabs_cookie', $this->provider->getKey());
    }

    public function test_consent_status_is_full()
    {
        update_option('wp_statistics', array_merge(
            get_option('wp_statistics', []),
            ['anonymous_tracking' => false]
        ));

        $this->assertTrue($this->provider->getConsentStatus()->equals(ConsentStatus::full()));
    }

    public function test_always_has_consent()
    {
        // Borlabs blocks the script; if it runs, consent is given
        $this->assertTrue($this->provider->hasConsent());
    }

    public function test_tracks_anonymously_when_option_enabled()
    {
        update_option('wp_statistics', array_merge(
            get_option('wp_statistics', []),
            ['anonymous_tracking' => true]
        ));

        $this->assertTrue($this->provider->trackAnonymously());
        $this->assertTrue($this->provider->getConsentStatus()->equals(ConsentStatus::anonymous()));
    }

    public function test_does_not_track_anonymously_when_option_disabled()
    {
        update_option('wp_statistics', array_merge(
            get_option('wp_statistics', []),
            ['anonymous_tracking' => false]
        ));

        $this->assertFalse($this->provider->trackAnonymously());
    }

    public function test_js_config_mode_is_borlabs_cookie()
    {
        $config = $this->provider->getJsConfig();
        $this->assertEquals('borlabs_cookie', $config['mode']);
    }

    public function test_js_handles_is_empty()
    {
        $handles = $this->provider->getJsHandles();
        $this->assertEmpty($handles);
    }

    public function test_has_consent_with_anonymous_tracking()
    {
        update_option('wp_statistics', array_merge(
            get_option('wp_statistics', []),
            ['anonymous_tracking' => true]
        ));

        // getConsentStatus() → anonymous() → shouldTrack() → true
        $this->assertTrue($this->provider->hasConsent());
    }

    public function test_is_service_installed_returns_false_without_borlabs()
    {
        // ServiceRepository class doesn't exist in test env
        $this->assertFalse($this->provider->isServiceInstalled());
    }

    public function test_is_service_installed_is_memoized()
    {
        // Call twice, should return same result (memoized)
        $first  = $this->provider->isServiceInstalled();
        $second = $this->provider->isServiceInstalled();
        $this->assertSame($first, $second);
    }
}
