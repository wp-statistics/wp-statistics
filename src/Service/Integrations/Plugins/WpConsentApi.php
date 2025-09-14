<?php

namespace WP_Statistics\Service\Integrations\Plugins;

use WP_STATISTICS\Option;

class WpConsentApi extends AbstractIntegration
{
    protected $key = 'wp_consent_api';
    protected $path = 'wp-consent-api/wp-consent-api.php';

    /**
     * Check if integration option is selectable
     *
     * @return bool
     */
    public function isSelectable()
    {
        return $this->isActive() && !empty($this->getCompatiblePlugins());
    }

    /**
     * Checks if the notice should be shown
     *
     * @return bool true
     */
    public function showNotice()
    {
        return $this->isActive() && (empty($this->getCompatiblePlugins()) || $this->getConsentLevel() === 'disabled');
    }

    /**
     * Returns the name of the integration.
     *
     * @return  string
     */
    public function getName()
    {
        return esc_html__('WP Consent API', 'wp-statistics');
    }

    /**
     * Return the consent level required for the integration to work.
     *
     * @return  string  The consent level
     */
    public function getConsentLevel()
    {
        return Option::get('consent_level_integration', 'functional');
    }

    public function hasConsent()
    {
        if (!function_exists('wp_has_consent')) {
            return true;
        }

        $consentLevel = $this->getConsentLevel();

        return wp_has_consent($consentLevel);
    }

    /**
     * Registers our plugin in "WP Consent API'.
     * @return  void
     */
    public function register()
    {
        $integration = Option::get('consent_integration');

        // If any other consent integration is active, return
        if (!empty($integration) && $integration !== $this->getKey()) return;

        // If no compatible plugin found, deactivate the integration and return early
        if ($integration === $this->getKey() && empty($this->getCompatiblePlugins())) {
            Option::update('consent_integration', '');
            return;
        }

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
            'consent_level'     => $this->getConsentLevel(),
            'track_anonymously' => $this->trackAnonymously()
        ];
    }

    /**
     * Return an array of active compatible plugins with WP Consent API.
     *
     * @return array
     */
    public function getCompatiblePlugins()
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

        if (is_plugin_active('pressidium-cookie-consent/pressidium-cookie-consent.php')) {
            $plugins['pressidium-cookie-consent/pressidium-cookie-consent.php'] = esc_html__('Pressidium Cookie Consent', 'wp-statistics');
        }

        if (is_plugin_active('conzent/conzent.php')) {
            $plugins['conzent/conzent.php'] = esc_html__('Conzent', 'wp-statistics');
        }

        if (is_plugin_active('consent-studio-wordpress-plugin-stable/plugin.php')) {
            $plugins['consent-studio-wordpress-plugin-stable/plugin.php'] = esc_html__('Consent Studio', 'wp-statistics');
        }

        if (is_plugin_active('webtoffee-gdpr-cookie-consent/webtoffee-gdpr-cookie-consent.php')) {
            $plugins['webtoffee-gdpr-cookie-consent/webtoffee-gdpr-cookie-consent.php'] = esc_html__('GDPR Cookie Consent Plugin – CCPA Ready', 'wp-statistics');
        }

        if (is_plugin_active('clickio-consent/clickioconsent.php')) {
            $plugins['clickio-consent/clickioconsent.php'] = esc_html__('Clickio Consent', 'wp-statistics');
        }

        if (is_plugin_active('consent-manager/consentmanager.php')) {
            $plugins['consent-manager/consentmanager.php'] = esc_html__('consentmanager Cookie Banner', 'wp-statistics');
        }

        if (is_plugin_active('cookiefirst-gdpr-cookie-consent-banner/cookiefirst-plugin.php')) {
            $plugins['cookiefirst-gdpr-cookie-consent-banner/cookiefirst-plugin.php'] = esc_html__('CookieFirst', 'wp-statistics');
        }

        if (is_plugin_active('trustarc-cookie-consent-manager/trustarc-cmp.php')) {
            $plugins['trustarc-cookie-consent-manager/trustarc-cmp.php'] = esc_html__('TrustArc Cookie Consent Manager', 'wp-statistics');
        }

        if (is_plugin_active('iubenda-cookie-law-solution/iubenda_cookie_solution.php')) {
            $plugins['iubenda-cookie-law-solution/iubenda_cookie_solution.php'] = esc_html__('iubenda Cookie Consent', 'wp-statistics');
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
