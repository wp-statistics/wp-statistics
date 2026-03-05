<?php

namespace WP_Statistics\Service\Consent\Providers;

use WP_Statistics\Service\Consent\AbstractConsentProvider;

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

    public function hasConsent(): bool
    {
        return true;
    }

    public function trackAnonymously(): bool
    {
        return false;
    }
}
