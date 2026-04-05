<?php

namespace WP_Statistics\Service\Admin\PrivacyAudit;

abstract class AbstractPrivacyCheck implements PrivacyCheckInterface
{
    protected function pass(string $message): PrivacyCheckResult
    {
        return PrivacyCheckResult::pass(
            $this->getKey(),
            $this->getLabel(),
            $this->getDescription(),
            $message,
            $this->getCategory(),
            $this->getSettingsLink()
        );
    }

    protected function warning(string $message): PrivacyCheckResult
    {
        return PrivacyCheckResult::warning(
            $this->getKey(),
            $this->getLabel(),
            $this->getDescription(),
            $message,
            $this->getCategory(),
            $this->getSettingsLink()
        );
    }

    protected function fail(string $message): PrivacyCheckResult
    {
        return PrivacyCheckResult::fail(
            $this->getKey(),
            $this->getLabel(),
            $this->getDescription(),
            $message,
            $this->getCategory(),
            $this->getSettingsLink()
        );
    }
}
