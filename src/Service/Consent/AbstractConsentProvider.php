<?php

namespace WP_Statistics\Service\Consent;

abstract class AbstractConsentProvider implements ConsentProviderInterface
{
    protected string $key = '';
    protected string $pluginPath = '';

    abstract public function getName(): string;

    abstract public function hasConsent(): bool;

    abstract public function trackAnonymously(): bool;

    abstract public function getJsConfig(): array;

    public function getConsentStatus(): ConsentStatus
    {
        if ($this->hasConsent() && !$this->trackAnonymously()) {
            return ConsentStatus::full();
        }

        if ($this->trackAnonymously()) {
            return ConsentStatus::anonymous();
        }

        return ConsentStatus::none();
    }

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
