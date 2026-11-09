<?php

namespace WP_Statistics\Service\Integrations\Plugins;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_STATISTICS\Option;

class RealCookieBanner extends AbstractIntegration
{
    protected $key = 'real_cookie_banner';
    protected $path = 'real-cookie-banner-pro/index.php';

    /**
     * Returns the name of the integration.
     *
     * @return  string
     */
    public function getName()
    {
        return esc_html__('Real Cookie Banner PRO', 'wp-statistics');
    }

    public function register()
    {
        add_action('wp_statistics_save_settings', [$this, 'clearTemplateCache']);
        add_action('RCB/Templates/TechnicalHandlingIntegration', [$this, 'handleIntegration']);
    }

    public function clearTemplateCache()
    {
        if (function_exists('wp_rcb_invalidate_templates_cache')) {
            wp_rcb_invalidate_templates_cache();
        }
    }

    public function trackAnonymously()
    {
        if (!function_exists('wp_rcb_consent_given')) {
            return false;
        }

        return $this->isOptedIn('wp-statistics') && !$this->isOptedIn('wp-statistics-with-data-processing');
    }

    public function hasConsent()
    {
        if (!function_exists('wp_rcb_consent_given')) {
            return true;
        }

        $baseConsent = wp_rcb_consent_given('wp-statistics');
        $baseConsent = !empty($baseConsent['cookieOptIn']);

        return $baseConsent || $this->isOptedIn('wp-statistics-with-data-processing');
    }

    /**
     * Whether the visitor accepted a service the site owner created in Real Cookie Banner.
     *
     * Real Cookie Banner reports cookieOptIn = true for a service that does not exist, so a missing
     * data-processing service (never added, or not registered because IPs are hashed) would count
     * visitors who declined. Same check as the tracker's `dataProcessing.cookie != null`.
     *
     * @param string $service
     * @return bool
     */
    private function isOptedIn($service)
    {
        $consent = wp_rcb_consent_given($service);

        return !empty($consent['cookie']) && !empty($consent['cookieOptIn']);
    }

    public function handleIntegration($integration)
    {
        $options        = Option::getOptions();
        $defaultOptions = Option::defaultOption();
        $hashIps        = boolval($options['hash_ips'] ?? $defaultOptions['hash_ips']);
        $file           = constant('WP_STATISTICS_MAIN_FILE');

        $integration->integrate($file, 'wp-statistics');

        if (!$hashIps) {
            $integration->integrate($file, 'wp-statistics-with-data-processing');
        }
    }

    /**
     * Return an array of js handles for this integration.
     * The result will be used as dependencies for the tracker js file
     *
     * @return  array
     */
    public function getJsHandles()
    {
        return ['real-cookie-banner-pro-banner'];
    }
}
