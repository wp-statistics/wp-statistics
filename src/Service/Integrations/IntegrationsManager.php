<?php

namespace WP_Statistics\Service\Integrations;

if (!defined('ABSPATH')) exit; // Exit if accessed directly


class IntegrationsManager
{
    /**
     * IntegrationsManager constructor.
     */
    public function __construct()
    {
        add_action('init', [$this, 'registerIntegrations']);
    }

    /**
     * Registers all integrations.
     * @return  void
     */
    public function registerIntegrations()
    {
        $integrations = IntegrationHelper::getAllIntegrations();

        foreach ($integrations as $integration) {
            if (!$integration->isActive()) continue;

            $integration->register();
        }
    }
}
