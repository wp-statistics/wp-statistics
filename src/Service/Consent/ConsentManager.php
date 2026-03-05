<?php

namespace WP_Statistics\Service\Consent;

use WP_Statistics\Components\Option;
use WP_Statistics\Service\Consent\Providers\NoneConsentProvider;
use WP_Statistics\Service\Consent\Providers\WpConsentApiProvider;
use WP_Statistics\Service\Consent\Providers\RealCookieBannerProvider;
use WP_Statistics\Service\Consent\Providers\BorlabsCookieProvider;

class ConsentManager
{
    /**
     * @var ConsentProviderInterface[]
     */
    private array $providers = [];

    private ConsentProviderInterface $activeProvider;

    private bool $booted = false;

    public function __construct()
    {
        $this->registerBuiltInProviders();
        $this->applyProvidersFilter();
        $this->activeProvider = $this->providers['none'] ?? new NoneConsentProvider();
    }

    public function boot(): void
    {
        if ($this->booted) {
            return;
        }

        $this->booted = true;
        $this->registerAvailableProviders();
        $this->detectAutoActivation();
        $this->resolveActiveProvider();
        $this->registerDeactivationHook();
    }

    private function registerBuiltInProviders(): void
    {
        $builtIn = [
            new NoneConsentProvider(),
            new WpConsentApiProvider(),
            new RealCookieBannerProvider(),
            new BorlabsCookieProvider(),
        ];

        foreach ($builtIn as $provider) {
            $this->providers[$provider->getKey()] = $provider;
        }
    }

    private function applyProvidersFilter(): void
    {
        $noneProvider = $this->providers['none'];

        $filtered = apply_filters('wp_statistics_consent_providers', $this->providers);

        if (is_array($filtered)) {
            $this->providers = [];
            foreach ($filtered as $provider) {
                if ($provider instanceof ConsentProviderInterface) {
                    $this->providers[$provider->getKey()] = $provider;
                }
            }
        }

        // Ensure 'none' provider is always available as fallback
        if (!isset($this->providers['none'])) {
            $this->providers['none'] = $noneProvider;
        }
    }

    /**
     * Register all available providers.
     *
     * Runs register() on every provider whose plugin is active, matching the
     * development branch behavior where IntegrationsManager called register()
     * on all active integrations. This allows providers like Borlabs Cookie to
     * auto-activate before the active provider is resolved.
     */
    private function registerAvailableProviders(): void
    {
        foreach ($this->providers as $provider) {
            if ($provider instanceof NoneConsentProvider) {
                continue;
            }

            if ($provider->isAvailable()) {
                $provider->register();
            }
        }
    }

    private function detectAutoActivation(): void
    {
        $borlabs = $this->getProvider('borlabs_cookie');
        if (!$borlabs instanceof BorlabsCookieProvider || !$borlabs->isAvailable()) {
            return;
        }

        $currentIntegration = Option::getValue('consent_integration', 'none');

        // If another provider is explicitly configured, don't interfere
        if ($currentIntegration !== 'none' && $currentIntegration !== '' && $currentIntegration !== 'borlabs_cookie') {
            return;
        }

        $isServiceActive = $borlabs->isServiceInstalled();

        // If Borlabs was the active integration but the service was removed, clear it
        if ($currentIntegration === 'borlabs_cookie' && !$isServiceActive) {
            Option::updateValue('consent_integration', 'none');
            return;
        }

        // Auto-activate when no provider is configured and Borlabs service is active
        if (($currentIntegration === 'none' || $currentIntegration === '') && $isServiceActive) {
            Option::updateValue('consent_integration', 'borlabs_cookie');
        }
    }

    private function resolveActiveProvider(): void
    {
        $key      = Option::getValue('consent_integration', 'none');
        $provider = $this->getProvider($key);

        if ($provider && $provider->isAvailable()) {
            $this->activeProvider = $provider;
        } else {
            $this->activeProvider = $this->providers['none'] ?? new NoneConsentProvider();

            if ($key !== 'none' && $key !== '') {
                error_log(sprintf(
                    'WP Statistics: Consent provider "%s" is configured but %s. Falling back to "none".',
                    $key,
                    $provider ? 'not available' : 'not registered'
                ));
            }
        }
    }

    private function registerDeactivationHook(): void
    {
        add_action('update_option_active_plugins', function () {
            $key = Option::getValue('consent_integration', 'none');
            if ($key === '') {
                $key = 'none';
            }
            $provider = $this->getProvider($key);

            if (!$provider || $provider instanceof NoneConsentProvider) {
                return;
            }

            if (!$provider->isAvailable()) {
                Option::updateValue('consent_integration', 'none');
            }
        });
    }

    public function registerProvider(ConsentProviderInterface $provider): void
    {
        $this->providers[$provider->getKey()] = $provider;
    }

    public function getProvider(string $key): ?ConsentProviderInterface
    {
        return $this->providers[$key] ?? null;
    }

    /**
     * @return ConsentProviderInterface[]
     */
    public function getProviders(): array
    {
        return $this->providers;
    }

    public function getActiveProvider(): ConsentProviderInterface
    {
        return $this->activeProvider;
    }

    public function getTrackingLevel(): string
    {
        return $this->activeProvider->getTrackingLevel();
    }

    public function shouldAnonymize(): bool
    {
        return $this->getTrackingLevel() === TrackingLevel::ANONYMOUS;
    }

    public function getTrackerConfig(): array
    {
        return $this->activeProvider->getJsConfig();
    }

    public function getJsDependencies(): array
    {
        return $this->activeProvider->getJsHandles();
    }

    /**
     * Full status for settings UI and diagnostics.
     */
    public function getIntegrationStatus(): array
    {
        $provider = $this->activeProvider;

        return [
            'name'   => $provider instanceof NoneConsentProvider ? null : $provider->getKey(),
            'status' => $provider->getTrackingLevel(),
        ];
    }

    /**
     * Get detection notices for available but unconfigured providers.
     */
    public function getDetectionNotices(): array
    {
        if (!($this->activeProvider instanceof NoneConsentProvider)) {
            return [];
        }

        $notices = [];
        foreach ($this->providers as $provider) {
            if ($provider instanceof NoneConsentProvider) {
                continue;
            }
            if ($provider->shouldShowNotice()) {
                $notices[] = $provider;
            }
        }

        return $notices;
    }
}
