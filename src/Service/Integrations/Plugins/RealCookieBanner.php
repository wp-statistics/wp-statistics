<?php

namespace WP_Statistics\Service\Integrations\Plugins;

use WP_STATISTICS\Option;

class RealCookieBanner extends AbstractIntegration
{

    public static function isActive()
    {
        return class_exists(\DevOwl\RealCookieBanner\Core::class);
    }

    public function register()
    {
        add_action('wp_statistics_save_settings', [$this, 'clearTemplateCache']);
        add_action('RCB/Templates/TechnicalHandlingIntegration', [$this, 'handleIntegration']);
        // add_filter('RCB/Templates/Recommended', [$this, 'setRecommendedTemplate']);
    }

    public function clearTemplateCache()
    {
        wp_rcb_invalidate_templates_cache();
    }

    public function setRecommendedTemplate()
    {
        // TODO: Recommend a wp-statistics consent template based on settings
    }

    public function hasConsent()
    {
        if (!function_exists('wp_rcb_consent_given')) return true;

        $consent = wp_rcb_consent_given('');

        return isset($consent['consentGiven']) && $consent['consentGiven'];
    }

    public function handleIntegration($integration)
    {
        $options        = Option::getOptions();
        $defaultOptions = Option::defaultOption();
        $userOnline     = boolval($options['useronline'] ?? $defaultOptions['useronline']); // Monitor Online Visitors
        $anonymizeIps   = boolval($options['anonymize_ips'] ?? $defaultOptions['anonymize_ips']); // Anonymize IP Addresses
        $hashIps        = boolval($options['hash_ips'] ?? $defaultOptions['hash_ips']); // Hash IP Addresses
        $file           = constant('WP_STATISTICS_MAIN_FILE');

        /**
         * Legal reason for creating 2 services and not option for using WP Statistics without a service:
         *
         * Since WP Statistics no longer allows no direct reference to a user (previously this could be set under
         * Settings > Basic Tracking > Visitor Analytics > Track Unique Visitors), a Service in RCB must always be
         * created on a website, as personal data of website visitors are always processed for analysis, even if only
         * a hash/anonymized data is created for it. In our legal opinion, transparent information about this must
         * always be provided.
         */
        $processesPersonalData = !$anonymizeIps || !$hashIps;

        if ($userOnline || $processesPersonalData) {
            $integration->integrate($file, 'wp-statistics');
        }

        if ($processesPersonalData) {
            $integration->integrate($file, 'wp-statistics-with-data-processing');
        }
    }
}
