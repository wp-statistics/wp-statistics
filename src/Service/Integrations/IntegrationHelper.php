<?php

namespace WP_Statistics\Service\Integrations;

use WP_Statistics\Service\Integrations\Plugins\WpConsentApi;
use WP_Statistics\Service\Integrations\Plugins\RealCookieBanner;
use WP_Statistics\Service\Integrations\Plugins\AbstractIntegration;


class IntegrationHelper
{
    /**
     * List of integrations to register.
     * @var  array
     */
    public static $integrations = [
        'wpConsentApi'      => WpConsentApi::class,
        'realCookieBanner'  => RealCookieBanner::class
    ];

    /**
     * Get an integration class by name.
     *
     * @param string $integration The name of the integration (e.g. "wpConsentApi").
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
            /** @var AbstractIntegration $class */
            $integration = new $class();

            if (!$integration->isActive()) continue;

            $integrations[$name] = $integration;
        }

        return $integrations;
    }
}
