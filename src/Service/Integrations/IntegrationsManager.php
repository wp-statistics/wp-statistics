<?php

namespace WP_Statistics\Service\Integrations;

use WP_STATISTICS\Option;

class IntegrationsManager
{
    /**
     * IntegrationsManager constructor.
     */
    public function __construct()
    {
        $this->registerIntegrations();

        add_action('update_option_active_plugins', [$this, 'unsetIntegrationUponDeactivation']);
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

    /**
     * When a plugin is deactivated, check if any integration is active.
     * If none are active, reset the integration value to none.
     */
    public function unsetIntegrationUponDeactivation()
    {
        $isAnyIntegrationActive = false;

        $integrations = IntegrationHelper::getAllIntegrations();

        foreach ($integrations as $integration) {
            if ($integration->isActive()) {
                $isAnyIntegrationActive = true;
                break;
            }
        }

        if (!$isAnyIntegrationActive) {
            Option::update('consent_integration', '');
        }
    }
}
