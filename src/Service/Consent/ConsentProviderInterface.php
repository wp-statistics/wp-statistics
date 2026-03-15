<?php

namespace WP_Statistics\Service\Consent;

interface ConsentProviderInterface
{
    public function getKey(): string;
    public function getName(): string;
    public function isAvailable(): bool;
    public function isSelectable(): bool;
    public function shouldShowNotice(): bool;
    public function register(): void;
    public function getJsHandles(): array;
    public function getJsConfig(): array;
}
