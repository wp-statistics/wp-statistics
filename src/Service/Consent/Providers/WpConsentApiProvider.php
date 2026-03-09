<?php

namespace WP_Statistics\Service\Consent\Providers;

use WP_Statistics\Service\Consent\AbstractConsentProvider;
use WP_Statistics\Service\Consent\TrackingLevel;

class WpConsentApiProvider extends AbstractConsentProvider
{
    protected string $key = 'wp_consent_api';
    protected string $pluginPath = 'wp-consent-api/wp-consent-api.php';

    public function getName(): string
    {
        return esc_html__('WP Consent API', 'wp-statistics');
    }

    public function shouldShowNotice(): bool
    {
        return $this->isAvailable() && !empty($this->getCompatiblePlugins());
    }

    public function getTrackingLevel(): string
    {
        if (!function_exists('wp_has_consent')) {
            return TrackingLevel::NONE;
        }

        if (wp_has_consent('statistics')) {
            return TrackingLevel::FULL;
        }

        if (wp_has_consent('statistics-anonymous')) {
            return TrackingLevel::ANONYMOUS;
        }

        return TrackingLevel::NONE;
    }

    public function register(): void
    {
        $plugin = plugin_basename(WP_STATISTICS_MAIN_FILE);
        add_filter("wp_consent_api_registered_{$plugin}", '__return_true');
    }

    public function getJsHandles(): array
    {
        return ['wp-consent-api'];
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
