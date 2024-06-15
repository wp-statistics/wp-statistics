<?php

namespace WP_Statistics\Service\Integrations;

use WP_CONSENT_API;

class WpConsentApi
{
    public function __construct()
    {
        $this->registerPluginInWpConsentApi();
    }

    /**
     * Checks if "WP Consent API" plugin is activated.
     *
     * @return  bool
     */
    public static function isWpConsentApiActive()
    {
        return class_exists(WP_CONSENT_API::class);
    }

    /**
     * Registers our plugin in "WP Consent API'.
     *
     * @return  void
     */
    public function registerPluginInWpConsentApi()
    {
        if (!self::isWpConsentApiActive()) return;

        $plugin = plugin_basename(WP_STATISTICS_MAIN_FILE);
        add_filter("wp_consent_api_registered_{$plugin}", '__return_true');
    }
}
