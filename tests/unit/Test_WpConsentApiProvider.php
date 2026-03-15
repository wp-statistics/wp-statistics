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

    public function test_tracking_level_none_when_wp_has_consent_missing()
    {
        $this->assertSame(TrackingLevel::NONE, $this->provider->getTrackingLevel());
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_tracking_level_full_when_statistics_consent_given()
    {
        require_once __DIR__ . '/../bootstrap.php';

        function wp_has_consent($category) {
            return $category === 'statistics';
        }

        $provider = new WpConsentApiProvider();
        $this->assertSame(TrackingLevel::FULL, $provider->getTrackingLevel());
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_tracking_level_anonymous_when_only_statistics_anonymous_consent_given()
    {
        require_once __DIR__ . '/../bootstrap.php';

        function wp_has_consent($category) {
            return $category === 'statistics-anonymous';
        }

        $provider = new WpConsentApiProvider();
        $this->assertSame(TrackingLevel::ANONYMOUS, $provider->getTrackingLevel());
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_tracking_level_none_when_no_consent_given()
    {
        require_once __DIR__ . '/../bootstrap.php';

        function wp_has_consent($category) {
            return false;
        }

        $provider = new WpConsentApiProvider();
        $this->assertSame(TrackingLevel::NONE, $provider->getTrackingLevel());
    }

    public function test_js_config_contains_only_mode()
    {
        $config = $this->provider->getJsConfig();
        $this->assertEquals('wp_consent_api', $config['mode']);
        $this->assertArrayNotHasKey('consentLevel', $config);
        $this->assertArrayNotHasKey('trackAnonymously', $config);
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
}
