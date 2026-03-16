<?php

namespace WP_Statistics\Service\Consent\Providers;

use WP_Statistics\Service\Consent\AbstractConsentProvider;

class WpConsentApiProvider extends AbstractConsentProvider
{
    protected string $key = 'wp_consent_api';
    protected string $pluginPath = 'wp-consent-api/wp-consent-api.php';

    public function getName(): string
    {
        return esc_html__('WP Consent API', 'wp-statistics');
    }

    public function register(): void
    {
        $plugin = plugin_basename(WP_STATISTICS_MAIN_FILE);
        add_filter("wp_consent_api_registered_{$plugin}", '__return_true');
    }

    public function getJsDependencies(): array
    {
        return ['wp-consent-api'];
    }

    public function getInlineScript(): string
    {
        return <<<'JS'
(function() {
    var r = window.WpStatisticsConsentAdapters = window.WpStatisticsConsentAdapters || {};
    if (!r.wp_consent_api) {
        r.wp_consent_api = {
            init: function(params) {
                var levels = params.levels;
                var addFilter = params.addFilter;
                var doAction = params.doAction;

                if (!window.wp_consent_type && !window.wp_fallback_consent_type) {
                    window.wp_fallback_consent_type = 'optin';
                }

                addFilter('trackingLevel', function() {
                    if (typeof window.wp_has_consent !== 'function') {
                        console.warn('WP Statistics: wp_has_consent() is not available. Blocking tracking until consent change.');
                        return levels.none;
                    }

                    if (window.wp_has_consent('statistics')) {
                        return levels.full;
                    }
                    if (window.wp_has_consent('statistics-anonymous')) {
                        return levels.anonymous;
                    }

                    return levels.none;
                });

                document.addEventListener('wp_listen_for_consent_change', function(e) {
                    var changed = e.detail;
                    if (changed && (changed['statistics'] === 'allow' || changed['statistics-anonymous'] === 'allow')) {
                        doAction('consentChanged');
                    }
                });
            }
        };
    }
})();
JS;
    }

    public function getCompatiblePlugins(): array
    {
        $compatiblePlugins = [
            'beautiful-and-responsive-cookie-consent/nsc_bar-cookie-consent.php' => 'Beautiful Cookie Consent Banner',
            'complianz-gdpr/complianz-gpdr.php'                                  => 'Complianz',
            'complianz-gdpr-premium/complianz-gpdr-premium.php'                  => 'Complianz Premium',
            'cookiebot/cookiebot.php'                                            => 'Cookiebot',
            'cookiehub/cookiehub.php'                                            => 'CookieHub',
            'cookie-law-info/cookie-law-info.php'                                => 'CookieYes',
            'gdpr-cookie-compliance/moove-gdpr.php'                              => 'GDPR Cookie Compliance',
            'pressidium-cookie-consent/pressidium-cookie-consent.php'            => 'Pressidium Cookie Consent',
            'conzent/conzent.php'                                                => 'Conzent',
            'consent-studio-wordpress-plugin-stable/plugin.php'                  => 'Consent Studio',
            'webtoffee-gdpr-cookie-consent/webtoffee-gdpr-cookie-consent.php'   => 'GDPR Cookie Consent Plugin',
            'clickio-consent/clickioconsent.php'                                 => 'Clickio Consent',
            'consent-manager/consentmanager.php'                                 => 'consentmanager Cookie Banner',
            'cookiefirst-gdpr-cookie-consent-banner/cookiefirst-plugin.php'      => 'CookieFirst',
            'trustarc-cookie-consent-manager/trustarc-cmp.php'                   => 'TrustArc Cookie Consent Manager',
            'iubenda-cookie-law-solution/iubenda_cookie_solution.php'            => 'iubenda Cookie Consent',
        ];

        $activePlugins = [];
        foreach ($compatiblePlugins as $path => $name) {
            if (is_plugin_active($path)) {
                $activePlugins[$path] = esc_html($name);
            }
        }

        return $activePlugins;
    }
}
