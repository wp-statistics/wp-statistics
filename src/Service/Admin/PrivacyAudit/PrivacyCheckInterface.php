<?php

namespace WP_Statistics\Service\Admin\PrivacyAudit;

interface PrivacyCheckInterface
{
    public function getKey(): string;

    public function getLabel(): string;

    public function getDescription(): string;

    public function getCategory(): string;

    public function getSettingsLink(): string;

    public function run(): array;
}
