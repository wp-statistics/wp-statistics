<?php

namespace WP_Statistics\Service\Consent;

abstract class AbstractConsentProvider implements ConsentProviderInterface
{
    protected string $key = '';
    protected string $pluginPath = '';

    abstract public function getName(): string;

    abstract public function getTrackingLevel(): string;

    public function shouldAnonymize(): bool
    {
        return $this->getTrackingLevel() === TrackingLevel::ANONYMOUS;
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
}
