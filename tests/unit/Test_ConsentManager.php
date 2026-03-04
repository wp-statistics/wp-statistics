<?php

use WP_Statistics\Service\Consent\ConsentManager;
use WP_Statistics\Service\Consent\ConsentProviderInterface;
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

    public function test_get_providers_returns_all_built_in_providers()
    {
        $manager   = new ConsentManager();
        $providers = $manager->getProviders();

        $this->assertArrayHasKey('none', $providers);
        $this->assertArrayHasKey('wp_consent_api', $providers);
        $this->assertArrayHasKey('real_cookie_banner', $providers);
        $this->assertArrayHasKey('borlabs_cookie', $providers);
    }

    public function test_active_provider_defaults_to_none()
    {
        $manager  = new ConsentManager();
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
        $manager = new ConsentManager();
        $this->assertInstanceOf(NoneConsentProvider::class, $manager->getActiveProvider());
    }

    public function test_active_provider_falls_back_to_none_when_key_is_unknown()
    {
        update_option('wp_statistics', array_merge(
            get_option('wp_statistics', []),
            ['consent_integration' => 'nonexistent_provider']
        ));

        $manager = new ConsentManager();
        $this->assertInstanceOf(NoneConsentProvider::class, $manager->getActiveProvider());
    }

    public function test_get_consent_status_returns_full_for_none_provider()
    {
        $manager = new ConsentManager();
        $this->assertSame(ConsentManager::FULL, $manager->getConsentStatus());
    }

    public function test_has_consent_delegates_to_active_provider()
    {
        $manager = new ConsentManager();
        // NoneConsentProvider always returns true
        $this->assertTrue($manager->hasConsent());
    }

    public function test_should_anonymize_defaults_false()
    {
        $manager = new ConsentManager();
        $this->assertFalse($manager->shouldAnonymize());
    }

    public function test_should_track_returns_true_for_none_provider()
    {
        $manager = new ConsentManager();
        $this->assertTrue($manager->shouldTrack());
    }

    public function test_consent_status_anonymous_when_provider_tracks_anonymously()
    {
        $manager = $this->buildManagerWithMockProvider(true, true);

        $this->assertSame(ConsentManager::ANONYMOUS, $manager->getConsentStatus());
        $this->assertTrue($manager->shouldTrack());
        $this->assertTrue($manager->shouldAnonymize());
    }

    public function test_consent_status_none_when_no_consent()
    {
        $manager = $this->buildManagerWithMockProvider(false, false);

        $this->assertSame(ConsentManager::NONE, $manager->getConsentStatus());
        $this->assertFalse($manager->shouldTrack());
        $this->assertFalse($manager->shouldAnonymize());
    }

    public function test_consent_status_anonymous_when_no_consent_but_anonymous_tracking()
    {
        $manager = $this->buildManagerWithMockProvider(false, true);

        $this->assertSame(ConsentManager::ANONYMOUS, $manager->getConsentStatus());
        $this->assertTrue($manager->shouldTrack());
        $this->assertTrue($manager->shouldAnonymize());
    }

    private function buildManagerWithMockProvider(bool $hasConsent, bool $trackAnonymously): ConsentManager
    {
        $mock = $this->createMock(ConsentProviderInterface::class);
        $mock->method('getKey')->willReturn('mock_provider');
        $mock->method('hasConsent')->willReturn($hasConsent);
        $mock->method('trackAnonymously')->willReturn($trackAnonymously);
        $mock->method('isAvailable')->willReturn(true);
        $mock->method('getJsConfig')->willReturn(['mode' => 'mock']);
        $mock->method('getJsHandles')->willReturn([]);
        $mock->method('getStatus')->willReturn([]);

        update_option('wp_statistics', array_merge(
            get_option('wp_statistics', []),
            ['consent_integration' => 'mock_provider']
        ));

        add_filter('wp_statistics_consent_providers', function ($providers) use ($mock) {
            $providers['mock_provider'] = $mock;
            return $providers;
        });

        $manager = new ConsentManager();

        remove_all_filters('wp_statistics_consent_providers');

        return $manager;
    }

    public function test_get_provider_returns_null_for_unknown_key()
    {
        $manager = new ConsentManager();
        $this->assertNull($manager->getProvider('nonexistent'));
    }

    public function test_get_provider_returns_provider_for_known_key()
    {
        $manager  = new ConsentManager();
        $provider = $manager->getProvider('none');

        $this->assertInstanceOf(ConsentProviderInterface::class, $provider);
        $this->assertEquals('none', $provider->getKey());
    }

    public function test_get_tracker_config_returns_array_with_mode()
    {
        $manager = new ConsentManager();
        $config  = $manager->getTrackerConfig();

        $this->assertIsArray($config);
        $this->assertArrayHasKey('mode', $config);
    }

    public function test_register_provider_adds_to_registry()
    {
        $manager = new ConsentManager();

        $mock = $this->createMock(ConsentProviderInterface::class);
        $mock->method('getKey')->willReturn('custom_provider');

        $manager->registerProvider($mock);

        $this->assertNotNull($manager->getProvider('custom_provider'));
    }

    public function test_get_js_dependencies_returns_array()
    {
        $manager = new ConsentManager();
        $deps    = $manager->getJsDependencies();

        $this->assertIsArray($deps);
    }

    public function test_none_provider_always_preserved_after_filter()
    {
        // Filter that removes all providers
        add_filter('wp_statistics_consent_providers', function () {
            return [];
        });

        $manager = new ConsentManager();
        $this->assertNotNull($manager->getProvider('none'));
        $this->assertInstanceOf(NoneConsentProvider::class, $manager->getProvider('none'));

        remove_all_filters('wp_statistics_consent_providers');
    }

    public function test_filter_ignores_non_array_return()
    {
        add_filter('wp_statistics_consent_providers', function () {
            return null;
        });

        $manager   = new ConsentManager();
        $providers = $manager->getProviders();

        // Should still have all built-in providers
        $this->assertArrayHasKey('none', $providers);
        $this->assertArrayHasKey('wp_consent_api', $providers);

        remove_all_filters('wp_statistics_consent_providers');
    }

    public function test_integration_status_returns_null_name_for_none_provider()
    {
        $manager = new ConsentManager();
        $status  = $manager->getIntegrationStatus();

        $this->assertNull($status['name']);
        $this->assertEmpty($status['status']);
    }

    public function test_detection_notices_empty_when_integration_configured()
    {
        update_option('wp_statistics', array_merge(
            get_option('wp_statistics', []),
            ['consent_integration' => 'wp_consent_api']
        ));

        $manager = new ConsentManager();
        $this->assertEmpty($manager->getDetectionNotices());
    }

    public function test_detection_notices_returns_array()
    {
        $manager = new ConsentManager();
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

            public function hasConsent(): bool
            {
                return true;
            }

            public function trackAnonymously(): bool
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

            public function getStatus(): array
            {
                return ['has_consent' => true, 'track_anonymously' => false];
            }
        };

        add_filter('wp_statistics_consent_providers', function ($providers) use ($noticeProvider) {
            $providers[] = $noticeProvider;
            return $providers;
        });

        $manager = new ConsentManager();
        $notices = $manager->getDetectionNotices();

        $keys = array_map(function ($provider) {
            return $provider->getKey();
        }, $notices);

        $this->assertContains('custom_notice_provider', $keys);

        remove_all_filters('wp_statistics_consent_providers');
    }
}
