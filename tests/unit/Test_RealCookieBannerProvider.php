<?php

use WP_Statistics\Service\Consent\TrackingLevel;
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

    public function test_tracking_level_none_when_function_missing()
    {
        // wp_rcb_consent_given() does not exist — fail closed (don't track)
        $this->assertSame(TrackingLevel::NONE, $this->provider->getTrackingLevel());
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
}
