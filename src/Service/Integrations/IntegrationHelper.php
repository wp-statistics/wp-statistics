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
     * Return an array of integrations that are active.
     *
     * @return AbstractIntegration[]
     */
    public static function getIntegrations()
    {
        $integrations = [];

        foreach (self::$integrations as $name => $class) {
            if (!$class::isActive()) continue;

            $integrations[$name] = new $class();
        }

        return $integrations;
    }

    /**
     * Get an integration class by name.
     *
     * @param string $integration The name of the integration (e.g. "wpConsentApi").
     * @return AbstractIntegration|false
     */
    public static function getIntegration($integration)
    {
        if (!isset(self::$integrations[$integration])) return false;
        return new self::$integrations[$integration];
    }
}
