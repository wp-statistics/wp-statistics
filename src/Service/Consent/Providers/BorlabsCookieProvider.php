<?php

namespace WP_Statistics\Service\Consent\Providers;

use WP_Statistics\Components\Option;
use WP_Statistics\Service\Consent\AbstractConsentProvider;
use WP_Statistics\Service\Consent\ConsentStatus;
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

    public function getConsentStatus(): ConsentStatus
    {
        return ConsentStatus::full();
    }

    public function register(): void
    {
        $isServiceActive = $this->isServiceInstalled();

        // If Borlabs is configured but the WP Statistics service was removed, clear the integration
        if (Option::getValue('consent_integration') === $this->key && !$isServiceActive) {
            Option::updateValue('consent_integration', '');
        }

        // If the WP Statistics service is active in Borlabs, auto-activate the integration
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

    public function getJsConfig(): array
    {
        return ['mode' => 'borlabs_cookie'];
    }
}
