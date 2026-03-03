<?php

namespace WP_Statistics\Service\Consent\Providers;

use WP_Statistics\Components\Option;
use WP_Statistics\Service\Consent\AbstractConsentProvider;
use WP_Statistics\Service\Consent\ConsentStatus;

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

    public function getConsentStatus(): ConsentStatus
    {
        if (!function_exists('wp_rcb_consent_given')) {
            return ConsentStatus::none();
        }

        $base           = !empty(wp_rcb_consent_given('wp-statistics')['cookieOptIn']);
        $dataProcessing = !empty(wp_rcb_consent_given('wp-statistics-with-data-processing')['cookieOptIn']);

        if ($dataProcessing) {
            return ConsentStatus::full();
        }

        if ($base) {
            return ConsentStatus::anonymous();
        }

        return ConsentStatus::none();
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

    public function getJsConfig(): array
    {
        return ['mode' => 'real_cookie_banner'];
    }

    public function getJsHandles(): array
    {
        return ['real-cookie-banner-pro-banner'];
    }
}
