<?php

use WP_Statistics\Service\Consent\ConsentStatus;
use WP_Statistics\Service\Consent\Providers\RealCookieBannerProvider;

/**
 * @group consent
 */
class Test_RealCookieBannerProvider extends WP_UnitTestCase
{
    private RealCookieBannerProvider $provider;

    public function setUp(): void
    {
        parent::setUp();
        $this->provider = new RealCookieBannerProvider();
    }

    public function test_key_is_real_cookie_banner()
    {
        $this->assertEquals('real_cookie_banner', $this->provider->getKey());
    }

    public function test_consent_status_is_none_when_function_missing()
    {
        // Intentionally fail closed when wp_rcb_consent_given() is unavailable
        $this->assertTrue($this->provider->getConsentStatus()->equals(ConsentStatus::none()));
    }

    public function test_has_consent_fails_closed_when_function_missing()
    {
        // wp_rcb_consent_given() does not exist — fail closed (don't track)
        $this->assertFalse($this->provider->hasConsent());
    }

    public function test_track_anonymously_returns_false_when_function_missing()
    {
        $this->assertFalse($this->provider->trackAnonymously());
    }

    public function test_js_config_mode_is_real_cookie_banner()
    {
        $config = $this->provider->getJsConfig();
        $this->assertEquals('real_cookie_banner', $config['mode']);
    }

    public function test_js_handles_includes_rcb_banner()
    {
        $handles = $this->provider->getJsHandles();
        $this->assertContains('real-cookie-banner-pro-banner', $handles);
    }

    public function test_status_includes_required_keys()
    {
        $status = $this->provider->getStatus();
        $this->assertArrayHasKey('has_consent', $status);
        $this->assertArrayHasKey('track_anonymously', $status);
    }
}
