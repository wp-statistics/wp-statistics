<?php

namespace WP_Statistics\Service\Admin\PrivacyAudit\Checks;

use WP_Statistics\Components\Option;
use WP_Statistics\Service\Admin\PrivacyAudit\AbstractPrivacyCheck;
use WP_Statistics\Service\Consent\ConsentManager;

class ConsentIntegrationCheck extends AbstractPrivacyCheck
{
    public function getKey(): string
    {
        return 'consent_integration';
    }

    public function getLabel(): string
    {
        return __('Consent Integration', 'wp-statistics');
    }

    public function getDescription(): string
    {
        return __('Checks whether a consent management plugin is detected and integrated.', 'wp-statistics');
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

        if ($hasProviders && !$consentEnabled) {
            $providerNames = array_map(fn($p) => $p->getName(), $availableProviders);

            return $this->fail(
                sprintf(
                    // translators: %s is a comma-separated list of consent plugin names
                    __('Consent plugin detected (%s) but integration is disabled. Visitor tracking may run without consent.', 'wp-statistics'),
                    implode(', ', $providerNames)
                )
            );
        }

        if (!$hasProviders) {
            return $this->warning(
                __('No consent management plugin detected. Consider installing one to comply with GDPR and similar regulations.', 'wp-statistics')
            );
        }

        return $this->pass(
            __('Consent integration is active. Tracking respects visitor consent choices.', 'wp-statistics')
        );
    }
}
