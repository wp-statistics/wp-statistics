<?php

namespace WP_Statistics\Service\Integrations;

use WP_STATISTICS\Option;

class RealCookieBanner extends AbstractIntegration
{

    public static function isActive()
    {
        return class_exists(\DevOwl\RealCookieBanner\Core::class);
    }

    public function register()
    {
        add_action('RCB/Templates/TechnicalHandlingIntegration', [$this, 'handleIntegration']);
    }

    public function handleIntegration($integration)
    {
        if (!class_exists(Option::class) || !defined('WP_STATISTICS_MAIN_FILE')) {
            return;
        }

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
