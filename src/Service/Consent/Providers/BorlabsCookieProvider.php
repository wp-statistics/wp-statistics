<?php

namespace WP_Statistics\Service\Consent\Providers;

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
        // No hooks needed — auto-activation is handled by ConsentManager::detectAutoActivation().
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
