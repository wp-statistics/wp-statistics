<?php

namespace WP_Statistics\Service\Integrations;

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
        'wp_consent_api'      => WpConsentApi::class,
        'real_cookie_banner'  => RealCookieBanner::class
    ];

    /**
     * Get an integration class by name.
     *
     * @param string $integration The name of the integration (e.g. "wp_consent_api").
     * @return AbstractIntegration|false
     */
    public static function get($integration)
    {
        return isset(self::$integrations[$integration])
            ? new self::$integrations[$integration]
            : false;
    }

    /**
     * Return an array of integrations that are active.
     *
     * @return AbstractIntegration[]
     */
    public static function getIntegrations()
    {
        $integrations = [];

        foreach (self::$integrations as $name => $class) {
            $integration = new $class();

            if (!$integration->isActive()) continue;

            $integrations[$name] = $integration;
        }

        return $integrations;
    }

    /**
     * Returns an array of status information for each integration.
     *
     * @return array
     */
    public static function getIntegrationsStatus()
    {
        $result = [];

        foreach (self::$integrations as $key => $integration) {
            $integration = new $integration();

            $result[$key] = [
                'is_active'     => $integration->isActive(),
                'has_consent'   => $integration->hasConsent()
            ];
        }

        return $result;
    }

    /**
     * Returns true if any of the active integrations have consent given.
     *
     * @return bool
     */
    public static function isAnyConsentGiven()
    {
        $integrations = self::getIntegrations();

        foreach ($integrations as $integration) {
            if (!$integration->hasConsent()) {
                return false;
            }
        }

        return true;
    }
}
