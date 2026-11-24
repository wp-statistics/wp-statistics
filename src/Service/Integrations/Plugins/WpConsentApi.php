<?php

namespace WP_Statistics\Service\Integrations\Plugins;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

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
        return $this->isActive() && empty($this->getCompatiblePlugins());
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

    public function hasConsent()
    {
        if (!function_exists('wp_has_consent')) {
            return true;
        }

        return wp_has_consent('statistics') || wp_has_consent('statistics-anonymous');
    }

    public function trackAnonymously()
    {
        if (!function_exists('wp_has_consent')) {
            return false;
        }

        return wp_has_consent('statistics-anonymous') && !wp_has_consent('statistics');
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

        // The saved choice is never cleared here: without a cookie plugin the setting stays, the
        // tracker keeps asking WP Consent API, and the admin sees a notice on WP Statistics pages.
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
            'has_consent' => $this->hasConsent(),
        ];
    }

    /**
     * Warns when WP Consent API is active but no cookie plugin gives it consent to read.
     *
     * @return string The warning, or an empty string when the setup is fine.
     */
    public function getConfigurationWarning()
    {
        if (!$this->isAvailable()) {
            return parent::getConfigurationWarning();
        }

        if (!empty($this->getCompatiblePlugins())) {
            return '';
        }

        return esc_html__('Consent integration is set to WP Consent API, but no compatible cookie plugin is active. Visitors are tracked as WP Consent API allows until you activate one.', 'wp-statistics');
    }

    /**
     * Return an array of active compatible plugins with WP Consent API.
     *
     * Compatible means a plugin from the known list, or any other active plugin that registers
     * itself with WP Consent API and provides a consent type (so it manages consent, rather than
     * only reading it). Extend the list with the `wp_statistics_consent_api_compatible_plugins` filter.
     *
     * @return array Plugin basename => plugin name.
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
            $plugins['cookiehub/cookiehub.php'] = esc_html__('CookieHub', 'wp-statistics');
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

        $plugins = array_merge($this->getRegisteredPlugins(), $plugins);

        /**
         * Filters the active cookie plugins that work with the WP Consent API integration.
         *
         * @param array $plugins Plugin basename => plugin name.
         */
        $plugins = apply_filters('wp_statistics_consent_api_compatible_plugins', $plugins);

        return is_array($plugins) ? $plugins : [];
    }

    /**
     * Returns active plugins that register with WP Consent API and set a consent type.
     *
     * Plugins that only read consent (WooCommerce, Site Kit, WP Statistics itself) register too,
     * so registration alone is not enough: a cookie plugin also answers `wp_get_consent_type`.
     *
     * @return array Plugin basename => plugin name.
     */
    protected function getRegisteredPlugins()
    {
        if (!$this->isActive() || !has_filter('wp_get_consent_type')) {
            return [];
        }

        $activePlugins = (array)get_option('active_plugins', []);

        if (is_multisite()) {
            $activePlugins = array_merge($activePlugins, array_keys((array)get_site_option('active_sitewide_plugins', [])));
        }

        $ownPlugin   = plugin_basename(WP_STATISTICS_MAIN_FILE);
        $allPlugins  = function_exists('get_plugins') ? get_plugins() : [];
        $plugins     = [];

        foreach (array_unique($activePlugins) as $plugin) {
            if (!is_string($plugin) || $plugin === $ownPlugin || $plugin === $this->getPath()) {
                continue;
            }

            $isRegistered = function_exists('consent_api_registered')
                ? consent_api_registered($plugin)
                : apply_filters("wp_consent_api_registered_{$plugin}", false);

            if (!$isRegistered) {
                continue;
            }

            $plugins[$plugin] = !empty($allPlugins[$plugin]['Name'])
                ? esc_html($allPlugins[$plugin]['Name'])
                : esc_html(dirname($plugin) !== '.' ? dirname($plugin) : $plugin);
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
