<?php

use WP_Statistics\Service\Consent\ConsentManager;
use WP_Statistics\Service\Consent\ConsentProviderInterface;
use WP_Statistics\Service\Consent\TrackingLevel;
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
            ['consent_integration' => 'none'] // explicit opt-out so built-in providers don't auto-activate
        ));
        // Clear any tracking_level from previous tests
        unset($_REQUEST['tracking_level'], $_POST['tracking_level'], $_GET['tracking_level']);
    }

    public function tearDown(): void
    {
        unset($_REQUEST['tracking_level'], $_POST['tracking_level'], $_GET['tracking_level']);
        parent::tearDown();
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

    public function test_tracking_level_defaults_to_full_without_request_param()
    {
        $manager = $this->createManager();
        $this->assertSame(TrackingLevel::FULL, $manager->getTrackingLevel());
    }

    public function test_should_anonymize_false_when_full()
    {
        $_REQUEST['tracking_level'] = TrackingLevel::FULL;
        $manager = $this->createManager();
        $this->assertFalse($manager->shouldAnonymize());
    }

    public function test_should_anonymize_true_when_anonymous()
    {
        $_REQUEST['tracking_level'] = TrackingLevel::ANONYMOUS;
        $manager = $this->createManager();
        $this->assertTrue($manager->shouldAnonymize());
    }

    public function test_should_anonymize_true_when_none()
    {
        $_REQUEST['tracking_level'] = TrackingLevel::NONE;
        $manager = $this->createManager();
        $this->assertTrue($manager->shouldAnonymize());
    }

    public function test_tracking_level_reads_from_request()
    {
        $_REQUEST['tracking_level'] = TrackingLevel::ANONYMOUS;
        $manager = $this->createManager();
        $this->assertSame(TrackingLevel::ANONYMOUS, $manager->getTrackingLevel());
    }

    public function test_tracking_level_ignores_invalid_request_value()
    {
        $_REQUEST['tracking_level'] = 'invalid_value';
        $manager = $this->createManager();
        $this->assertSame(TrackingLevel::FULL, $manager->getTrackingLevel());
    }

    private function buildManagerWithMockProvider(): ConsentManager
    {
        $mock = $this->createMock(ConsentProviderInterface::class);
        $mock->method('getKey')->willReturn('mock_provider');
        $mock->method('isAvailable')->willReturn(true);
        $mock->method('getJsConfig')->willReturn(['mode' => 'mock']);
        $mock->method('getJsDependencies')->willReturn([]);

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

    public function test_active_provider_js_config_returns_array_with_mode()
    {
        $manager = $this->createManager();
        $config  = $manager->getActiveProvider()->getJsConfig();

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

    public function test_active_provider_js_handles_returns_array()
    {
        $manager = $this->createManager();
        $deps    = $manager->getActiveProvider()->getJsDependencies();

        $this->assertIsArray($deps);
    }

    public function test_active_provider_inline_script_returns_empty_for_none()
    {
        $manager = $this->createManager();
        $this->assertSame('', $manager->getActiveProvider()->getInlineScript());
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
    }

    public function test_boot_is_idempotent()
    {
        $manager = $this->createManager();
        $manager->boot(); // second call

        $this->assertInstanceOf(NoneConsentProvider::class, $manager->getActiveProvider());
    }

    private function createMockProvider(string $key, bool $available = true, bool $selectable = true): ConsentProviderInterface
    {
        $mock = $this->createMock(ConsentProviderInterface::class);
        $mock->method('getKey')->willReturn($key);
        $mock->method('isAvailable')->willReturn($available);
        $mock->method('isSelectable')->willReturn($selectable);
        $mock->method('shouldShowNotice')->willReturn($available);
        $mock->method('getJsConfig')->willReturn(['mode' => $key]);
        $mock->method('getJsDependencies')->willReturn([]);
        $mock->method('getInlineScript')->willReturn('');

        return $mock;
    }

    private function createManagerWithMockProviders(array $mocks): ConsentManager
    {
        add_filter('wp_statistics_consent_providers', function ($providers) use ($mocks) {
            foreach ($mocks as $mock) {
                $providers[$mock->getKey()] = $mock;
            }
            return $providers;
        });

        $manager = new ConsentManager();
        $manager->boot();

        remove_all_filters('wp_statistics_consent_providers');

        return $manager;
    }

    public function test_auto_activates_single_available_provider_when_unconfigured()
    {
        // Empty string = never configured (fresh install)
        update_option('wp_statistics', array_merge(
            get_option('wp_statistics', []),
            ['consent_integration' => '']
        ));

        $mock = $this->createMockProvider('test_provider');
        $manager = $this->createManagerWithMockProviders([$mock]);

        $this->assertSame('test_provider', $manager->getActiveProvider()->getKey());
    }

    public function test_auto_activates_first_provider_when_multiple_available()
    {
        update_option('wp_statistics', array_merge(
            get_option('wp_statistics', []),
            ['consent_integration' => '']
        ));

        $mock1 = $this->createMockProvider('provider_a');
        $mock2 = $this->createMockProvider('provider_b');
        $manager = $this->createManagerWithMockProviders([$mock1, $mock2]);

        // Should pick the first available one
        $active = $manager->getActiveProvider();
        $this->assertNotInstanceOf(NoneConsentProvider::class, $active);
    }

    public function test_explicit_none_prevents_auto_activation()
    {
        // 'none' = user deliberately chose no consent integration
        update_option('wp_statistics', array_merge(
            get_option('wp_statistics', []),
            ['consent_integration' => 'none']
        ));

        $mock = $this->createMockProvider('test_provider');
        $manager = $this->createManagerWithMockProviders([$mock]);

        $this->assertInstanceOf(NoneConsentProvider::class, $manager->getActiveProvider());
    }

    public function test_explicit_selection_is_respected()
    {
        update_option('wp_statistics', array_merge(
            get_option('wp_statistics', []),
            ['consent_integration' => 'provider_b']
        ));

        $mock1 = $this->createMockProvider('provider_a');
        $mock2 = $this->createMockProvider('provider_b');
        $manager = $this->createManagerWithMockProviders([$mock1, $mock2]);

        $this->assertSame('provider_b', $manager->getActiveProvider()->getKey());
    }

    public function test_falls_back_to_none_when_selected_provider_unavailable()
    {
        update_option('wp_statistics', array_merge(
            get_option('wp_statistics', []),
            ['consent_integration' => 'unavailable_provider']
        ));

        $manager = $this->createManager();
        $this->assertInstanceOf(NoneConsentProvider::class, $manager->getActiveProvider());
    }

    public function test_has_conflicting_providers_true_when_multiple_available()
    {
        $mock1 = $this->createMockProvider('provider_a');
        $mock2 = $this->createMockProvider('provider_b');
        $manager = $this->createManagerWithMockProviders([$mock1, $mock2]);

        $this->assertTrue($manager->hasConflictingProviders());
    }

    public function test_has_conflicting_providers_false_when_single_available()
    {
        $mock = $this->createMockProvider('test_provider');
        $manager = $this->createManagerWithMockProviders([$mock]);

        $this->assertFalse($manager->hasConflictingProviders());
    }

    public function test_detection_notices_empty_when_explicitly_configured()
    {
        update_option('wp_statistics', array_merge(
            get_option('wp_statistics', []),
            ['consent_integration' => 'wp_consent_api']
        ));

        $manager = $this->createManager();
        $this->assertEmpty($manager->getDetectionNotices());
    }

    public function test_detection_notices_empty_when_explicitly_none()
    {
        // 'none' is an explicit choice — no notices
        update_option('wp_statistics', array_merge(
            get_option('wp_statistics', []),
            ['consent_integration' => 'none']
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

    public function test_detection_notices_shown_when_unconfigured()
    {
        update_option('wp_statistics', array_merge(
            get_option('wp_statistics', []),
            ['consent_integration' => '']
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

            public function register(): void
            {
            }

            public function getJsDependencies(): array
            {
                return [];
            }

            public function getJsConfig(): array
            {
                return ['mode' => 'custom_notice_provider'];
            }

            public function getInlineScript(): string
            {
                return '';
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
