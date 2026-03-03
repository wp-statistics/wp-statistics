<?php

namespace WP_Statistics\Service\Consent\Providers;

use WP_Statistics\Service\Consent\AbstractConsentProvider;
use WP_Statistics\Service\Consent\ConsentStatus;

class NoneConsentProvider extends AbstractConsentProvider
{
    protected string $key = 'none';

    public function getName(): string
    {
        return esc_html__('None', 'wp-statistics');
    }

    public function isAvailable(): bool
    {
        return true;
    }

    public function isSelectable(): bool
    {
        return true;
    }

    public function shouldShowNotice(): bool
    {
        return false;
    }

    public function getConsentStatus(): ConsentStatus
    {
        return ConsentStatus::full();
    }

    public function getJsConfig(): array
    {
        return ['mode' => 'none'];
    }
}
