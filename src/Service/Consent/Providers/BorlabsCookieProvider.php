<?php

namespace WP_Statistics\Service\Consent\Providers;

use WP_Statistics\Components\Option;
use WP_Statistics\Service\Consent\AbstractConsentProvider;
use WP_Statistics\Utils\Query;

class BorlabsCookieProvider extends AbstractConsentProvider
{
    protected string $key = 'borlabs_cookie';
    protected string $pluginPath = 'borlabs-cookie/borlabs-cookie.php';

    private ?bool $serviceInstalled = null;

    public function getName(): string
    {
        return esc_html__('Borlabs Cookie', 'wp-statistics');
    }

    public function isSelectable(): bool
    {
        return $this->isAvailable() && $this->isServiceInstalled();
    }

    public function shouldShowNotice(): bool
    {
        return $this->isAvailable() && $this->isServiceInstalled();
    }

    public function hasConsent(): bool
    {
        return true; // Borlabs blocks the script; if running, consent was given
    }

    public function register(): void
    {
        $currentIntegration = Option::getValue('consent_integration', 'none');

        // If another provider is explicitly configured, don't interfere
        if ($currentIntegration !== 'none' && $currentIntegration !== '' && $currentIntegration !== $this->key) {
            return;
        }

        $isServiceActive = $this->isServiceInstalled();

        // If Borlabs was the active integration but the service was removed, clear it
        if ($currentIntegration === $this->key && !$isServiceActive) {
            Option::updateValue('consent_integration', 'none');
            return;
        }

        // Auto-activate when no provider is configured and Borlabs service is active
        if ($isServiceActive) {
            Option::updateValue('consent_integration', $this->key);
        }
    }

    public function isServiceInstalled(): bool
    {
        if ($this->serviceInstalled !== null) {
            return $this->serviceInstalled;
        }

        if (!class_exists('Borlabs\Cookie\Repository\Service\ServiceRepository')) {
            $this->serviceInstalled = false;
            return false;
        }

        $row = Query::select('1')
            ->from('borlabs_cookie_services')
            ->where('`key`', '=', 'wp-statistics')
            ->where('status', '=', '1')
            ->getRow();

        $this->serviceInstalled = !empty($row);
        return $this->serviceInstalled;
    }

}
