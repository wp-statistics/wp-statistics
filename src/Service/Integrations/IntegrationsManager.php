<?php

namespace WP_Statistics\Service\Integrations;

class IntegrationsManager
{
    /**
     * List of integrations to register.
     * @var  array
     */
    private $integrations = [
        WpConsentApi::class,
    ];

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
        foreach ($this->integrations as $integration) {
            if (!class_exists($integration)) {
                continue;
            }

            $integration = new $integration();
            $integration->register();
        }
    }
}
