<?php

namespace WP_Statistics\Service\Admin\PrivacyAudit\Checks;

use WP_Statistics\Components\Option;
use WP_Statistics\Service\Admin\PrivacyAudit\AbstractPrivacyCheck;
use WP_Statistics\Service\Consent\ConsentManager;
use WP_Statistics\Service\Consent\Providers\WpConsentApiProvider;

class ConsentIntegrationCheck extends AbstractPrivacyCheck
{
    public function getKey(): string
    {
        return 'consent';
    }

    public function getLabel(): string
    {
        return __('Consent Management', 'wp-statistics');
    }

    public function getDescription(): string
    {
        return __('Checks consent plugin detection, integration status, and provider conflicts.', 'wp-statistics');
    }

    public function getCategory(): string
    {
        return 'consent';
    }

    public function getSettingsLink(): string
    {
        return '/settings/privacy';
    }

    public function run(): array
    {
        $consentEnabled = Option::getValue('consent_integration', false);
        $consentManager = new ConsentManager();
        $consentManager->boot();
        $availableProviders = $consentManager->getAvailableProviders();
        $hasProviders       = !empty($availableProviders);
        $collectsSensitive  = Option::getValue('store_ip', false) || Option::getValue('visitors_log', false);

        // Consent plugin detected but integration disabled
        if ($hasProviders && !$consentEnabled) {
            $providerNames = array_map(fn($p) => $p->getName(), $availableProviders);

            return $this->fail(
                sprintf(
                    __('Consent plugin detected (%s) but integration is disabled. Visitor tracking may run without consent.', 'wp-statistics'),
                    implode(', ', $providerNames)
                )
            );
        }

        // Multiple consent plugins active
        if ($hasProviders && $consentManager->hasConflictingProviders()) {
            $providerNames = array_map(fn($p) => $p->getName(), $availableProviders);

            return $this->warning(
                sprintf(
                    __('Multiple consent plugins detected: %s. This may cause unpredictable behavior.', 'wp-statistics'),
                    implode(', ', $providerNames)
                )
            );
        }

        // WP Consent API active but no banner plugin to show the actual consent dialog
        if ($consentEnabled && $hasProviders) {
            $activeProvider = $consentManager->getActiveProvider();

            if ($activeProvider instanceof WpConsentApiProvider && empty($activeProvider->getCompatiblePlugins())) {
                return $this->warning(
                    __('WP Consent API is active but no compatible consent banner plugin is installed. A banner plugin (e.g. Complianz, CookieYes) is required to display the consent dialog.', 'wp-statistics')
                );
            }
        }

        // No consent plugin and collecting sensitive data
        if (!$hasProviders && $collectsSensitive) {
            return $this->warning(
                __('Your settings collect sensitive data (IP addresses or user IDs) but no consent plugin is installed. Consider adding one to comply with privacy regulations.', 'wp-statistics')
            );
        }

        // No consent plugin but settings are privacy-friendly
        if (!$hasProviders) {
            return $this->pass(
                __('No consent plugin detected. Your current settings are privacy-friendly, so consent is optional but recommended.', 'wp-statistics')
            );
        }

        return $this->pass(
            __('Consent integration is active. Tracking respects visitor consent choices.', 'wp-statistics')
        );
    }
}
