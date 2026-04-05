<?php

namespace WP_Statistics\Service\Admin\PrivacyAudit;

abstract class AbstractPrivacyCheck implements PrivacyCheckInterface
{
    private function makeResult(string $status, string $message): array
    {
        return [
            'key'          => $this->getKey(),
            'label'        => $this->getLabel(),
            'description'  => $this->getDescription(),
            'status'       => $status,
            'message'      => $message,
            'category'     => $this->getCategory(),
            'settingsLink' => $this->getSettingsLink(),
        ];
    }

    protected function pass(string $message): array
    {
        return $this->makeResult('pass', $message);
    }

    protected function warning(string $message): array
    {
        return $this->makeResult('warning', $message);
    }

    protected function fail(string $message): array
    {
        return $this->makeResult('fail', $message);
    }
}
