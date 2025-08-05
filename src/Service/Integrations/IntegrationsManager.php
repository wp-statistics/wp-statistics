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

        add_action('update_option_active_plugins', [$this, 'unsetIntegrationUponDeactivation'], 10, 2);
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
     * When integration is deactivated, reset the integration option.
     */
    public function unsetIntegrationUponDeactivation($oldPlugins, $newPlugins)
    {
        $activeIntegration = Option::get('consent_integration');
        $activeIntegration = IntegrationHelper::getIntegration($activeIntegration);

        if (!$activeIntegration) return;

        $plugin              = $activeIntegration->getPath();
        $isPluginDeactivated = in_array($plugin, $oldPlugins) && !in_array($plugin, $newPlugins);

        if ($isPluginDeactivated) {
            Option::update('consent_integration', '');
        }
    }
}
