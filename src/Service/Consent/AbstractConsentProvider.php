<?php

namespace WP_Statistics\Service\Consent;

abstract class AbstractConsentProvider implements ConsentProviderInterface
{
    protected string $key = '';
    protected string $pluginPath = '';

    abstract public function getName(): string;

    abstract public function getConsentStatus(): ConsentStatus;

    abstract public function getJsConfig(): array;

    public function getKey(): string
    {
        return $this->key;
    }

    public function isAvailable(): bool
    {
        return is_plugin_active($this->pluginPath);
    }

    public function isSelectable(): bool
    {
        return $this->isAvailable();
    }

    public function shouldShowNotice(): bool
    {
        return $this->isAvailable();
    }

    public function hasConsent(): bool
    {
        return $this->getConsentStatus()->shouldTrack();
    }

    public function trackAnonymously(): bool
    {
        return $this->getConsentStatus()->shouldAnonymize();
    }

    public function register(): void
    {
    }

    public function getJsHandles(): array
    {
        return [];
    }

    public function getStatus(): array
    {
        return [
            'has_consent'       => $this->hasConsent(),
            'track_anonymously' => $this->trackAnonymously(),
        ];
    }
}
