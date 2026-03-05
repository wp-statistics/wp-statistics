<?php

namespace WP_Statistics\Service\Consent\Providers;

use WP_Statistics\Components\Option;
use WP_Statistics\Service\Consent\AbstractConsentProvider;

class RealCookieBannerProvider extends AbstractConsentProvider
{
    protected string $key = 'real_cookie_banner';
    protected string $pluginPath = 'real-cookie-banner-pro/index.php';

    public function getName(): string
    {
        return esc_html__('Real Cookie Banner PRO', 'wp-statistics');
    }

    public function register(): void
    {
        add_action('wp_statistics_save_settings', [$this, 'clearTemplateCache']);
        add_action('RCB/Templates/TechnicalHandlingIntegration', [$this, 'handleIntegration']);
    }

    public function clearTemplateCache(): void
    {
        if (function_exists('wp_rcb_invalidate_templates_cache')) {
            wp_rcb_invalidate_templates_cache();
        }
    }

    public function hasConsent(): bool
    {
        // Fail closed: if the RCB function is unavailable
        // (e.g. plugin partially loaded or deactivated), do not track.
        if (!function_exists('wp_rcb_consent_given')) {
            return false;
        }

        return !empty(wp_rcb_consent_given('wp-statistics')['cookieOptIn'])
            || !empty(wp_rcb_consent_given('wp-statistics-with-data-processing')['cookieOptIn']);
    }

    public function trackAnonymously(): bool
    {
        // Fail closed: see hasConsent() rationale.
        if (!function_exists('wp_rcb_consent_given')) {
            return false;
        }

        $base           = !empty(wp_rcb_consent_given('wp-statistics')['cookieOptIn']);
        $dataProcessing = !empty(wp_rcb_consent_given('wp-statistics-with-data-processing')['cookieOptIn']);

        return $base && !$dataProcessing;
    }

    public function handleIntegration($integration): void
    {
        $storeIp = (bool) Option::getValue('store_ip');
        $file    = WP_STATISTICS_MAIN_FILE;

        $integration->integrate($file, 'wp-statistics');

        if ($storeIp) {
            $integration->integrate($file, 'wp-statistics-with-data-processing');
        }
    }

    public function getJsHandles(): array
    {
        return ['real-cookie-banner-pro-banner'];
    }
}
