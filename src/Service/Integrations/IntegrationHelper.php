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
     * Returns the currently active integration class.
     *
     * @return AbstractIntegration|false False if no integration is selected.
     */
    public static function getActiveIntegration()
    {
        $selectedIntegration = Option::get('consent_integration');
        $selectedIntegration = self::getIntegration($selectedIntegration);

        return !empty($selectedIntegration) && $selectedIntegration->isActive()
            ? $selectedIntegration
            : false;
    }

    /**
     * Checks if certain integration is active
     *
     * @param string $integration
     * @return bool
     */
    public static function isIntegrationActive($integration)
    {
        $activeIntegration = Option::get('consent_integration');
        return $activeIntegration === $integration;
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

        $integration = self::getActiveIntegration();

        if (!empty($integration)) {
            $status['name']     = $integration->getKey();
            $status['status']   = $integration->getStatus();
        }

        return $status;
    }

    /**
     * Checks if consent is given for the currently active integration.
     *
     * If there's no active integration, it assumes consent is given.
     *
     * @return bool
     */
    public static function isConsentGiven()
    {
        $integration = self::getActiveIntegration();

        return empty($integration) || $integration->hasConsent();
    }

    /**
     * Checks if the currently active integration requires anonymous tracking.
     *
     * If there's no active integration, it assumes it doesn't require anonymous tracking.
     *
     * @return bool
     */
    public static function shouldTrackAnonymously()
    {
        $integration    = self::getActiveIntegration();
        $isConsentGiven = self::isConsentGiven();

        return !empty($integration) && $integration->trackAnonymously() && !$isConsentGiven;
    }
}
