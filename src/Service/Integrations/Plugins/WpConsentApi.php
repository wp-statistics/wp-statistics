<?php

namespace WP_Statistics\Service\Integrations\Plugins;

use WP_STATISTICS\Option;

class WpConsentApi extends AbstractIntegration
{
    protected $key = 'wp_consent_api';
    protected $path = 'wp-consent-api/wp-consent-api.php';

    /**
     * Returns the name of the integration.
     *
     * @return  string
     */
    public function getName()
    {
        return esc_html__('WP Consent API', 'wp-statistics');
    }

    public function hasConsent()
    {
        if (!function_exists('wp_has_consent')) {
            return true;
        }

        $consentLevel = Option::get('consent_level_integration', 'disabled');

        return ($consentLevel === 'disabled') ? true : wp_has_consent($consentLevel);
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

    /**
     * Return the status of the integration.
     *
     * @return array The status of the integration.
     */
    public function getStatus()
    {
        return [
            'has_consent'       => $this->hasConsent(),
            'consent_level'     => Option::get('consent_level_integration', 'disabled'),
            'track_anonymously' => $this->trackAnonymously()
        ];
    }

    /**
     * Return an array of active compatible plugins with WP Consent API.
     *
     * @return array
     */
    public static function getCompatiblePlugins()
    {
        $plugins = [];

        if (is_plugin_active('beautiful-and-responsive-cookie-consent/nsc_bar-cookie-consent.php')) {
            $plugins['beautiful-and-responsive-cookie-consent/nsc_bar-cookie-consent.php'] = esc_html__('Beautiful Cookie Consent Banner', 'wp-statistics');
        }

        if (is_plugin_active('complianz-gdpr/complianz-gpdr.php')) {
            $plugins['complianz-gdpr/complianz-gpdr.php'] = esc_html__('Complianz', 'wp-statistics');
        }

        if (is_plugin_active('complianz-gdpr-premium/complianz-gpdr-premium.php')) {
            $plugins['complianz-gdpr-premium/complianz-gpdr-premium.php'] = esc_html__('Complianz Premium', 'wp-statistics');
        }

        if (is_plugin_active('cookiebot/cookiebot.php')) {
            $plugins['cookiebot/cookiebot.php'] = esc_html__('Cookiebot', 'wp-statistics');
        }

        if (is_plugin_active('cookiehub/cookiehub.php')) {
            $plugins['cookie-law-info/cookie-law-info.php'] = esc_html__('CookieHub', 'wp-statistics');
        }

        if (is_plugin_active('cookie-law-info/cookie-law-info.php')) {
            $plugins['cookie-law-info/cookie-law-info.php'] = esc_html__('CookieYes', 'wp-statistics');
        }

        if (is_plugin_active('gdpr-cookie-compliance/moove-gdpr.php')) {
            $plugins['gdpr-cookie-compliance/moove-gdpr.php'] = esc_html__('GDPR Cookie Compliance', 'wp-statistics');
        }

        return $plugins;
    }

    /**
     * Return an array of js handles for this integration.
     * The result will be used as dependencies for the tracker js file
     *
     * @return  array
     */
    public function getJsHandles()
    {
        return ['wp-consent-api'];
    }
}
