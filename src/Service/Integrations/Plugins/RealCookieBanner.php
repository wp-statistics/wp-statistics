<?php

namespace WP_Statistics\Service\Integrations\Plugins;

use WP_STATISTICS\Option;

class RealCookieBanner extends AbstractIntegration
{
    /**
     * Returns the key of the integration.
     *
     * @return string
     */
    public function getKey()
    {
        return 'real_cookie_banner';
    }

    /**
     * Returns the name of the integration.
     *
     * @return  string
     */
    public function getName()
    {
        return esc_html__('Real Cookie Banner', 'wp-statistics');
    }

    public function isActive()
    {
        return class_exists(\DevOwl\RealCookieBanner\Core::class);
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

    public function hasConsent()
    {
        if (!function_exists('wp_rcb_consent_given')) {
            return true;
        }

        $consent = wp_rcb_consent_given('');

        return isset($consent['consentGiven']) && $consent['consentGiven'];
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
     * Return the status of the integration.
     *
     * @return array The status of the integration.
     */
    public function getStatus()
    {
        return [
            'is_active'   => $this->isActive(),
            'has_consent' => $this->hasConsent()
        ];
    }
}
