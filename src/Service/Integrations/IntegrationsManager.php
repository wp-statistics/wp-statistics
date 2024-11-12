<?php

namespace WP_Statistics\Service\Integrations;

use WP_Statistics\Service\Integrations\Plugins\RealCookieBanner;
use WP_Statistics\Service\Integrations\Plugins\WpConsentApi;

class IntegrationsManager
{
    /**
     * IntegrationsManager constructor.
     */
    public function __construct()
    {
        $this->registerIntegrations();
    }

    /**
     * Registers all integrations.
     * @return  void
     */
    private function registerIntegrations()
    {
        $integrations = IntegrationHelper::getIntegrations();

        foreach ($integrations as $integration) {
            $integration->register();
        }
    }
}
