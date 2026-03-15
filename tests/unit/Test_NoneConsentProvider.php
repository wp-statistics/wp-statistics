<?php

use WP_Statistics\Service\Consent\Providers\NoneConsentProvider;

/**
 * @group consent
 */
class Test_NoneConsentProvider extends WP_UnitTestCase
{
    private NoneConsentProvider $provider;

    public function setUp(): void
    {
        parent::setUp();
        $this->provider = new NoneConsentProvider();
    }

    public function test_key_is_none()
    {
        $this->assertEquals('none', $this->provider->getKey());
    }

    public function test_is_always_available()
    {
        $this->assertTrue($this->provider->isAvailable());
    }

    public function test_is_always_selectable()
    {
        $this->assertTrue($this->provider->isSelectable());
    }

    public function test_should_not_show_notice()
    {
        $this->assertFalse($this->provider->shouldShowNotice());
    }

    public function test_js_config_mode_is_none()
    {
        $config = $this->provider->getJsConfig();
        $this->assertEquals('none', $config['mode']);
    }
}
