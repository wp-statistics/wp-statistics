<?php

use WP_Statistics\Service\Consent\ConsentManager;
use WP_Statistics\Service\Consent\ConsentProviderInterface;
use WP_Statistics\Service\Consent\TrackingLevel;
use WP_Statistics\Service\Consent\Providers\BorlabsCookieProvider;
use WP_Statistics\Service\Consent\Providers\NoneConsentProvider;

/**
 * @group consent
 */
class Test_ConsentManager extends WP_UnitTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        update_option('wp_statistics', array_merge(
            get_option('wp_statistics', []),
            ['consent_integration' => 'none']
        ));
    }

    private function createManager(): ConsentManager
    {
        $manager = new ConsentManager();
        $manager->boot();

        return $manager;
    }

    public function test_get_providers_returns_all_built_in_providers()
    {
        $manager   = $this->createManager();
        $providers = $manager->getProviders();

        $this->assertArrayHasKey('none', $providers);
        $this->assertArrayHasKey('wp_consent_api', $providers);
        $this->assertArrayHasKey('real_cookie_banner', $providers);
        $this->assertArrayHasKey('borlabs_cookie', $providers);
    }

    public function test_active_provider_defaults_to_none()
    {
        $manager  = $this->createManager();
        $provider = $manager->getActiveProvider();

        $this->assertInstanceOf(NoneConsentProvider::class, $provider);
    }

    public function test_active_provider_falls_back_to_none_when_configured_provider_unavailable()
    {
        update_option('wp_statistics', array_merge(
            get_option('wp_statistics', []),
            ['consent_integration' => 'wp_consent_api']
        ));

        // WP Consent API plugin is not active in test env
        $manager = $this->createManager();
        $this->assertInstanceOf(NoneConsentProvider::class, $manager->getActiveProvider());
    }

    public function test_active_provider_falls_back_to_none_when_key_is_unknown()
    {
        update_option('wp_statistics', array_merge(
            get_option('wp_statistics', []),
            ['consent_integration' => 'nonexistent_provider']
        ));

        $manager = $this->createManager();
        $this->assertInstanceOf(NoneConsentProvider::class, $manager->getActiveProvider());
    }

    public function test_tracking_level_returns_full_for_none_provider()
    {
        $manager = $this->createManager();
        $this->assertSame(TrackingLevel::FULL, $manager->getActiveProvider()->getTrackingLevel());
    }

    public function test_should_anonymize_defaults_false()
    {
        $manager = $this->createManager();
        $this->assertFalse($manager->getActiveProvider()->shouldAnonymize());
    }

    public function test_tracking_level_anonymous_when_provider_tracks_anonymously()
    {
        $manager = $this->buildManagerWithMockProvider(TrackingLevel::ANONYMOUS);

        $this->assertSame(TrackingLevel::ANONYMOUS, $manager->getActiveProvider()->getTrackingLevel());
        $this->assertTrue($manager->getActiveProvider()->shouldAnonymize());
    }

    public function test_tracking_level_none_when_no_consent()
    {
        $manager = $this->buildManagerWithMockProvider(TrackingLevel::NONE);

        $this->assertSame(TrackingLevel::NONE, $manager->getActiveProvider()->getTrackingLevel());
        $this->assertFalse($manager->getActiveProvider()->shouldAnonymize());
    }

    private function buildManagerWithMockProvider(string $trackingLevel): ConsentManager
    {
        $mock = $this->createMock(ConsentProviderInterface::class);
        $mock->method('getKey')->willReturn('mock_provider');
        $mock->method('getTrackingLevel')->willReturn($trackingLevel);
        $mock->method('shouldAnonymize')->willReturn($trackingLevel === TrackingLevel::ANONYMOUS);
        $mock->method('isAvailable')->willReturn(true);
        $mock->method('getJsConfig')->willReturn(['mode' => 'mock']);
        $mock->method('getJsHandles')->willReturn([]);

        update_option('wp_statistics', array_merge(
            get_option('wp_statistics', []),
            ['consent_integration' => 'mock_provider']
        ));

        add_filter('wp_statistics_consent_providers', function ($providers) use ($mock) {
            $providers['mock_provider'] = $mock;
            return $providers;
        });

        $manager = new ConsentManager();
        $manager->boot();

        remove_all_filters('wp_statistics_consent_providers');

        return $manager;
    }

    public function test_get_provider_returns_null_for_unknown_key()
    {
        $manager = $this->createManager();
        $this->assertNull($manager->getProvider('nonexistent'));
    }

    public function test_get_provider_returns_provider_for_known_key()
    {
        $manager  = $this->createManager();
        $provider = $manager->getProvider('none');

        $this->assertInstanceOf(ConsentProviderInterface::class, $provider);
        $this->assertEquals('none', $provider->getKey());
    }

    public function test_get_tracker_config_returns_array_with_mode()
    {
        $manager = $this->createManager();
        $config  = $manager->getTrackerConfig();

        $this->assertIsArray($config);
        $this->assertArrayHasKey('mode', $config);
    }

    public function test_register_provider_adds_to_registry()
    {
        $manager = $this->createManager();

        $mock = $this->createMock(ConsentProviderInterface::class);
        $mock->method('getKey')->willReturn('custom_provider');

        $manager->registerProvider($mock);

        $this->assertNotNull($manager->getProvider('custom_provider'));
    }

    public function test_get_js_dependencies_returns_array()
    {
        $manager = $this->createManager();
        $deps    = $manager->getJsDependencies();

        $this->assertIsArray($deps);
    }

    public function test_none_provider_always_preserved_after_filter()
    {
        add_filter('wp_statistics_consent_providers', function () {
            return [];
        });

        $manager = $this->createManager();
        $this->assertNotNull($manager->getProvider('none'));
        $this->assertInstanceOf(NoneConsentProvider::class, $manager->getProvider('none'));

        remove_all_filters('wp_statistics_consent_providers');
    }

    public function test_filter_ignores_non_array_return()
    {
        add_filter('wp_statistics_consent_providers', function () {
            return null;
        });

        $manager   = $this->createManager();
        $providers = $manager->getProviders();

        $this->assertArrayHasKey('none', $providers);
        $this->assertArrayHasKey('wp_consent_api', $providers);

        remove_all_filters('wp_statistics_consent_providers');
    }

    public function test_active_provider_is_none_before_boot()
    {
        $manager = new ConsentManager();

        $this->assertInstanceOf(NoneConsentProvider::class, $manager->getActiveProvider());
        $this->assertSame(TrackingLevel::FULL, $manager->getActiveProvider()->getTrackingLevel());
    }

    public function test_tracking_level_full_when_mock_provider_has_consent()
    {
        $manager = $this->buildManagerWithMockProvider(TrackingLevel::FULL);

        $this->assertSame(TrackingLevel::FULL, $manager->getActiveProvider()->getTrackingLevel());
        $this->assertFalse($manager->getActiveProvider()->shouldAnonymize());
    }

    public function test_boot_is_idempotent()
    {
        $manager = $this->createManager();
        $manager->boot(); // second call

        $this->assertInstanceOf(NoneConsentProvider::class, $manager->getActiveProvider());
    }

    private function buildMockBorlabs(bool $available, bool $serviceInstalled): BorlabsCookieProvider
    {
        $mock = $this->getMockBuilder(BorlabsCookieProvider::class)
            ->onlyMethods(['isAvailable', 'isServiceInstalled'])
            ->getMock();
        $mock->method('isAvailable')->willReturn($available);
        $mock->method('isServiceInstalled')->willReturn($serviceInstalled);

        return $mock;
    }

    private function createManagerWithBorlabsMock(BorlabsCookieProvider $borlabsMock): ConsentManager
    {
        add_filter('wp_statistics_consent_providers', function ($providers) use ($borlabsMock) {
            $providers['borlabs_cookie'] = $borlabsMock;
            return $providers;
        });

        $manager = new ConsentManager();
        $manager->boot();

        remove_all_filters('wp_statistics_consent_providers');

        return $manager;
    }

    public function test_detect_auto_activation_activates_borlabs_when_service_installed()
    {
        update_option('wp_statistics', array_merge(
            get_option('wp_statistics', []),
            ['consent_integration' => 'none']
        ));

        $this->createManagerWithBorlabsMock($this->buildMockBorlabs(true, true));

        $opts = get_option('wp_statistics', []);
        $this->assertSame('borlabs_cookie', $opts['consent_integration']);
    }

    public function test_detect_auto_activation_clears_borlabs_when_service_uninstalled()
    {
        update_option('wp_statistics', array_merge(
            get_option('wp_statistics', []),
            ['consent_integration' => 'borlabs_cookie']
        ));

        $this->createManagerWithBorlabsMock($this->buildMockBorlabs(true, false));

        $opts = get_option('wp_statistics', []);
        $this->assertSame('none', $opts['consent_integration']);
    }

    public function test_detect_auto_activation_does_not_override_explicit_provider()
    {
        update_option('wp_statistics', array_merge(
            get_option('wp_statistics', []),
            ['consent_integration' => 'wp_consent_api']
        ));

        $this->createManagerWithBorlabsMock($this->buildMockBorlabs(true, true));

        $opts = get_option('wp_statistics', []);
        $this->assertSame('wp_consent_api', $opts['consent_integration']);
    }

    public function test_detect_auto_activation_skips_when_borlabs_removed_by_filter()
    {
        update_option('wp_statistics', array_merge(
            get_option('wp_statistics', []),
            ['consent_integration' => 'none']
        ));

        add_filter('wp_statistics_consent_providers', function ($providers) {
            unset($providers['borlabs_cookie']);
            return $providers;
        });

        $manager = $this->createManager();

        $opts = get_option('wp_statistics', []);
        $this->assertSame('none', $opts['consent_integration']);

        remove_all_filters('wp_statistics_consent_providers');
    }

    public function test_detect_auto_activation_skips_when_borlabs_not_available()
    {
        update_option('wp_statistics', array_merge(
            get_option('wp_statistics', []),
            ['consent_integration' => 'none']
        ));

        $this->createManagerWithBorlabsMock($this->buildMockBorlabs(false, false));

        $opts = get_option('wp_statistics', []);
        $this->assertSame('none', $opts['consent_integration']);
    }

    public function test_detection_notices_empty_when_integration_configured()
    {
        update_option('wp_statistics', array_merge(
            get_option('wp_statistics', []),
            ['consent_integration' => 'wp_consent_api']
        ));

        $manager = $this->createManager();
        $this->assertEmpty($manager->getDetectionNotices());
    }

    public function test_detection_notices_returns_array()
    {
        $manager = $this->createManager();
        $notices = $manager->getDetectionNotices();

        $this->assertIsArray($notices);
    }

    public function test_detection_notices_not_suppressed_when_integration_is_none_sentinel()
    {
        update_option('wp_statistics', array_merge(
            get_option('wp_statistics', []),
            ['consent_integration' => 'none']
        ));

        $noticeProvider = new class implements ConsentProviderInterface {
            public function getKey(): string
            {
                return 'custom_notice_provider';
            }

            public function getName(): string
            {
                return 'Custom Notice Provider';
            }

            public function isAvailable(): bool
            {
                return true;
            }

            public function isSelectable(): bool
            {
                return true;
            }

            public function shouldShowNotice(): bool
            {
                return true;
            }

            public function getTrackingLevel(): string
            {
                return \WP_Statistics\Service\Consent\TrackingLevel::FULL;
            }

            public function shouldAnonymize(): bool
            {
                return false;
            }

            public function register(): void
            {
            }

            public function getJsHandles(): array
            {
                return [];
            }

            public function getJsConfig(): array
            {
                return ['mode' => 'custom_notice_provider'];
            }
        };

        add_filter('wp_statistics_consent_providers', function ($providers) use ($noticeProvider) {
            $providers[] = $noticeProvider;
            return $providers;
        });

        $manager = $this->createManager();
        $notices = $manager->getDetectionNotices();

        $keys = array_map(function ($provider) {
            return $provider->getKey();
        }, $notices);

        $this->assertContains('custom_notice_provider', $keys);

        remove_all_filters('wp_statistics_consent_providers');
    }
}
