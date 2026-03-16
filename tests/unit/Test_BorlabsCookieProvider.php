<?php

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

    public function test_js_config_mode_is_borlabs_cookie()
    {
        $config = $this->provider->getJsConfig();
        $this->assertEquals('borlabs_cookie', $config['mode']);
    }

    public function test_js_handles_is_empty()
    {
        $handles = $this->provider->getJsDependencies();
        $this->assertEmpty($handles);
    }

    public function test_is_service_installed_returns_false_without_borlabs()
    {
        $this->assertFalse($this->provider->isServiceInstalled());
    }

    public function test_is_service_installed_is_memoized()
    {
        $first  = $this->provider->isServiceInstalled();
        $second = $this->provider->isServiceInstalled();
        $this->assertSame($first, $second);
    }
}
