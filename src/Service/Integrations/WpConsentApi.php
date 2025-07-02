<?php

namespace WP_Statistics\Service\Integrations;

use WP_CONSENT_API;

class WpConsentApi
{
    /**
     * Checks if "WP Consent API" plugin is activated.
     *
     * @return  bool
     */
    public static function isWpConsentApiActive()
    {
        return class_exists(WP_CONSENT_API::class);
    }

    public static function getCompatiblePlugins()
    {
        $plugins = [];

        if (is_plugin_active('complianz-gdpr/complianz-gpdr.php')) {
            $plugins['complianz'] = esc_html__('Complianz', 'wp-statistics');
        }

        if (is_plugin_active('cookiebot/cookiebot.php')) {
            $plugins['cookiebot'] = esc_html__('Cookiebot', 'wp-statistics');
        }

        if (is_plugin_active('cookiehub/cookiehub.php')) {
            $plugins['cookiehub'] = esc_html__('CookieHub', 'wp-statistics');
        }

        if (is_plugin_active('cookie-law-info/cookie-law-info.php')) {
            $plugins['cookieyes'] = esc_html__('CookieYes', 'wp-statistics');
        }

        if (is_plugin_active('gdpr-cookie-compliance/moove-gdpr.php')) {
            $plugins['gdpr-cookie-compliance'] = esc_html__('GDPR Cookie Compliance', 'wp-statistics');
        }

        return $plugins;
    }

    /**
     * Registers our plugin in "WP Consent API'.
     * @return  void
     */
    public function register()
    {
        if (self::isWpConsentApiActive()) {
            $plugin = plugin_basename(WP_STATISTICS_MAIN_FILE);
            add_filter("wp_consent_api_registered_{$plugin}", '__return_true');
        }
    }
}
