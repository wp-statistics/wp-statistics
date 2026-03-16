<?php

namespace WP_Statistics\Service\Consent;

abstract class AbstractConsentProvider implements ConsentProviderInterface
{
    protected string $key = '';
    protected string $pluginPath = '';

    abstract public function getName(): string;

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

    public function register(): void
    {
    }

    public function getJsDependencies(): array
    {
        return [];
    }

    public function getInlineScript(): string
    {
        return '';
    }
}
