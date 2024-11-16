<?php

namespace WP_Statistics\Service\Integrations;

use WP_STATISTICS\Option;
use WP_Statistics\Service\Integrations\Plugins\WpConsentApi;
use WP_Statistics\Service\Integrations\Plugins\RealCookieBanner;
use WP_Statistics\Service\Integrations\Plugins\AbstractIntegration;


class IntegrationHelper
{
    /**
     * List of integrations to register.
     * @var AbstractIntegration[]
     */
    public static $integrations = [
        WpConsentApi::class,
        RealCookieBanner::class
    ];

    /**
     * Get an integration class by name.
     *
     * @param string $integration The name of the integration (e.g. "wp_consent_api").
     * @return AbstractIntegration|false
     */
    public static function getIntegration($integration)
    {
        foreach (self::$integrations as $class) {
            $class = new $class();

            if ($class->getKey() === $integration) {
                return $class;
            }
        }

        return false;
    }

    /**
     * Return an array of all integrations.
     *
     * @return AbstractIntegration[]
     */
    public static function getAllIntegrations()
    {
        $integrations = [];

        foreach (self::$integrations as $name => $class) {
            $integration = new $class();

            $integrations[$name] = $integration;
        }

        return $integrations;
    }

    /**
     * Returns the currently selected integration class.
     *
     * @return AbstractIntegration|false False if no integration is selected.
     */
    public static function getCurrentIntegration()
    {
        $selectedIntegration = Option::get('consent_integration');

        if (empty($selectedIntegration)) return false;

        return self::getIntegration($selectedIntegration);
    }

    /**
     * Returns the currently selected integration status.
     *
     * @return array
     */
    public static function getIntegrationStatus()
    {
        $status = [
            'name'      => null,
            'status'    => []
        ];

        $integration = self::getCurrentIntegration();

        if (empty($integration)) return $status;

        $status['name']     = $integration->getKey();
        $status['status']   = $integration->getStatus();

        return $status;
    }
}
