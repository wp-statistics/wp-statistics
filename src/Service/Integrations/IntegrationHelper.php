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
        'wp_consent_api'      => WpConsentApi::class,
        'real_cookie_banner'  => RealCookieBanner::class
    ];

    /**
     * Get an integration class by name.
     *
     * @param string $integration The name of the integration (e.g. "wp_consent_api").
     * @return AbstractIntegration|false
     */
    public static function getIntegration($integration)
    {
        return !empty($integration) && isset(self::$integrations[$integration])
            ? new self::$integrations[$integration]
            : false;
    }

       /**
     * Returns the currently selected integration status.
     *
     * @return array
     */
    public static function getIntegrationStatus()
    {
        $status = [
            'integration' => null,
            'has_consent' => false
        ];

        $selectedIntegration    = Option::get('consent_integration');
        $integration            = self::getIntegration($selectedIntegration);

        if (empty($integration)) return $status;

        $status['integration'] = $selectedIntegration;
        $status['has_consent'] = $integration->hasConsent();

        return $status;
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

        return self::getIntegration($selectedIntegration);
    }

}
