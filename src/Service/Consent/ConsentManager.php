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

    public function __construct()
    {
        $this->registerBuiltInProviders();
        $this->applyProvidersFilter();
        $this->registerAvailableProviders();
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

    private function resolveActiveProvider(): void
    {
        $key      = Option::getValue('consent_integration', '');
        $provider = $this->getProvider($key);

        if ($provider && $provider->isAvailable()) {
            $this->activeProvider = $provider;
        } else {
            $this->activeProvider = $this->providers['none'] ?? new NoneConsentProvider();

            if (!empty($key) && $key !== 'none') {
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
            $key      = Option::getValue('consent_integration', '');
            $provider = $this->getProvider($key);

            if (!$provider || $provider instanceof NoneConsentProvider) {
                return;
            }

            if (!$provider->isAvailable()) {
                Option::updateValue('consent_integration', '');
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

    public function getConsentStatus(): ConsentStatus
    {
        return $this->activeProvider->getConsentStatus();
    }

    public function hasConsent(): bool
    {
        return $this->activeProvider->hasConsent();
    }

    public function shouldTrackAnonymously(): bool
    {
        return $this->activeProvider->trackAnonymously();
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

        if ($provider instanceof NoneConsentProvider) {
            return ['name' => null, 'status' => []];
        }

        return [
            'name'   => $provider->getKey(),
            'status' => $provider->getStatus(),
        ];
    }

    /**
     * Get detection notices for available but unconfigured providers.
     */
    public function getDetectionNotices(): array
    {
        if (Option::getValue('consent_integration')) {
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
