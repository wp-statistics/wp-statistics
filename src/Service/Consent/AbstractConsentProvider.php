<?php

namespace WP_Statistics\Service\Consent;

use WP_Statistics\Components\Option;

abstract class AbstractConsentProvider implements ConsentProviderInterface
{
    protected string $key = '';
    protected string $pluginPath = '';

    abstract public function getName(): string;

    abstract public function hasConsent(): bool;

    public function trackAnonymously(): bool
    {
        return (bool) Option::getValue('anonymous_tracking', false);
    }

    public function getJsConfig(): array
    {
        return ['mode' => $this->key];
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

    public function getStatus(): ConsentStatus
    {
        return new ConsentStatus(
            $this->hasConsent(),
            $this->trackAnonymously(),
        );
    }
}
