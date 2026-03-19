<?php

namespace WP_Statistics\Service\Consent;

interface ConsentProviderInterface
{
    public function getKey(): string;
    public function getName(): string;
    public function isAvailable(): bool;
    public function register(): void;
    public function getJsDependencies(): array;
    public function getJsConfig(): array;
    public function getInlineScript(): string;
}
