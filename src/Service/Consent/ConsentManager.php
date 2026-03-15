<?php

namespace WP_Statistics\Service\Consent;

use WP_Statistics\Components\Option;
use WP_Statistics\Utils\Request;
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

    /**
     * Get the effective tracking level.
     *
     * The JS tracker is the source of truth for consent. It checks the
     * consent provider's browser API directly and sends the result as
     * tracking_level in the hit payload.
     *
     * When no valid client flag is present: defaults to FULL if no consent
     * provider is configured (no consent needed), or NONE if a consent
     * provider is active (fail-closed to prevent tracking without consent).
     *
     * @return string
     */
    public function getTrackingLevel(): string
    {
        $clientLevel = Request::get('tracking_level', '');

        if (in_array($clientLevel, TrackingLevel::all(), true)) {
            return $clientLevel;
        }

        // Fail-closed when consent provider is active
        if (!($this->activeProvider instanceof NoneConsentProvider)) {
            return TrackingLevel::NONE;
        }

        return TrackingLevel::FULL;
    }

    /**
     * Whether to anonymize visitor data based on effective tracking level.
     *
     * @return bool
     */
    public function shouldAnonymize(): bool
    {
        return $this->getTrackingLevel() !== TrackingLevel::FULL;
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
