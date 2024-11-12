<?php

namespace WP_Statistics\Service\Integrations\Plugins;

use WP_STATISTICS\Option;
use WP_CONSENT_API;

class WpConsentApi extends AbstractIntegration
{
    /**
     * Checks if "WP Consent API" plugin is activated.
     *
     * @return  bool
     */
    public static function isActive()
    {
        return class_exists(WP_CONSENT_API::class);
    }

    public function hasConsent()
    {
        $consentLevel = Option::get('consent_level_integration', 'disabled');

        if (!function_exists('wp_has_consent') || $consentLevel === 'disabled') return true;

        return wp_has_consent($consentLevel);
    }

    /**
     * Registers our plugin in "WP Consent API'.
     * @return  void
     */
    public function register()
    {
        $plugin = plugin_basename(WP_STATISTICS_MAIN_FILE);
        add_filter("wp_consent_api_registered_{$plugin}", '__return_true');
    }
}
