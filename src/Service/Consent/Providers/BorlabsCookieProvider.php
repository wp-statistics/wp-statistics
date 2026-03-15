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

    /**
     * When true, the integration is forced — Borlabs physically blocks the tracking script,
     * so the user cannot select "None" without breaking tracking. The settings UI uses this
     * to disable the "None" option and show an explanatory notice.
     */
    public function isSelectable(): bool
    {
        return $this->isAvailable() && $this->isServiceInstalled();
    }

    public function shouldShowNotice(): bool
    {
        return $this->isAvailable() && $this->isServiceInstalled();
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
