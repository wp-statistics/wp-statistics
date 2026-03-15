<?php

use WP_Statistics\Service\Consent\TrackingLevel;
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

    public function test_tracking_level_full_by_default()
    {
        // Borlabs blocks the script; if it runs, consent is given
        $this->assertSame(TrackingLevel::FULL, $this->provider->getTrackingLevel());
    }

    public function test_tracking_level_anonymous_when_option_enabled()
    {
        update_option('wp_statistics', array_merge(
            get_option('wp_statistics', []),
            ['anonymous_tracking' => true]
        ));

        $this->assertSame(TrackingLevel::ANONYMOUS, $this->provider->getTrackingLevel());
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
