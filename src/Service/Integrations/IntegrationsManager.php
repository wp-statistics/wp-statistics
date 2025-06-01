<?php

namespace WP_Statistics\Service\Integrations;

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
        $integrations = IntegrationHelper::getAllIntegrations();

        foreach ($integrations as $integration) {
            if (!$integration->isActive()) continue;

            $integration->register();
        }
    }
}
