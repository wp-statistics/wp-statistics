<?php

namespace WP_Statistics\Service\Integrations;

use WP_STATISTICS\Menus;
use WP_STATISTICS\Option;
use WP_Statistics\Service\Integrations\Plugins\WpConsentApi;
use WP_Statistics\Service\Integrations\Plugins\RealCookieBanner;
use WP_Statistics\Service\Integrations\Plugins\BorlabsCookie;
use WP_Statistics\Service\Integrations\Plugins\AbstractIntegration;


class IntegrationHelper
{
    /**
     * List of integrations to register.
     * @var AbstractIntegration[]
     */
    public static $integrations = [
        WpConsentApi::class,
        RealCookieBanner::class,
        BorlabsCookie::class,
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

        foreach (self::$integrations as $class) {
            $integration = new $class();

            $integrations[] = $integration;
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
        $integration = Option::get('consent_integration');
        $integration = self::getIntegration($integration);

        return !empty($integration) && $integration->isActive()
            ? $integration
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
        $activeIntegration = self::getActiveIntegration();
        return !empty($activeIntegration) && ($activeIntegration->getKey() === $integration);
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
        $integration = self::getActiveIntegration();

        return !empty($integration) && $integration->trackAnonymously();
    }

    /**
     * Checks all integrations for active consent plugins and returns a list of notices.
     *
     * @return array
     */
    public static function getDetectionNotice()
    {
        $notices = [];

        if (Option::get('consent_integration')) return $notices;

        foreach (self::getAllIntegrations() as $integration) {
            if (!$integration->isActive()) continue;

            $notices[] = [
                'key'     => $integration->getKey(),
                'title'   => esc_html__('WP Statistics - Consent Plugin Detected', 'wp-statistics'),
                'content' => sprintf(
                    '%s <br> %s · %s',
                    sprintf(
                        __('We’ve detected <b>%s</b> on your site. To ensure WP Statistics respects visitor consent preferences, you can enable integration with this plugin.', 'wp-statistics'),
                        $integration->getName(),
                    ),
                    '<a href="' . esc_url(Menus::admin_url('settings', ['tab' => 'privacy-settings']) . '#consent_integration') . '">' . esc_html__('Activate integration ›', 'wp-statistics') . '</a>',
                    '<a target="_blank" href="https://wp-statistics.com/resources/integrating-wp-statistics-with-consent-management-plugins/?utm_source=wp-statistics&utm_medium=link&utm_campaign=privacy">' . esc_html__('Learn More ›', 'wp-statistics') . '</a>',
                )
            ];
        }

        return $notices;
    }
}
