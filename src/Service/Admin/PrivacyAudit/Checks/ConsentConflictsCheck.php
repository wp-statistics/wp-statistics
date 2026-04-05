<?php

namespace WP_Statistics\Service\Admin\PrivacyAudit\Checks;

use WP_Statistics\Service\Admin\PrivacyAudit\AbstractPrivacyCheck;
use WP_Statistics\Service\Admin\PrivacyAudit\PrivacyCheckResult;
use WP_Statistics\Service\Consent\ConsentManager;

class ConsentConflictsCheck extends AbstractPrivacyCheck
{
    public function getKey(): string
    {
        return 'consent_conflicts';
    }

    public function getLabel(): string
    {
        return __('Consent Provider Conflicts', 'wp-statistics');
    }

    public function getDescription(): string
    {
        return __('Checks whether multiple consent management plugins are active simultaneously.', 'wp-statistics');
    }

    public function getCategory(): string
    {
        return 'consent';
    }

    public function getSettingsLink(): string
    {
        return '/settings/privacy';
    }

    public function run(): PrivacyCheckResult
    {
        $consentManager = new ConsentManager();
        $consentManager->boot();

        if ($consentManager->hasConflictingProviders()) {
            $providerNames = array_map(fn($p) => $p->getName(), $consentManager->getAvailableProviders());

            return $this->warning(
                sprintf(
                    // translators: %s is a comma-separated list of consent plugin names
                    __('Multiple consent plugins detected: %s. This may cause unpredictable consent behavior.', 'wp-statistics'),
                    implode(', ', $providerNames)
                )
            );
        }

        return $this->pass(
            __('No consent provider conflicts detected.', 'wp-statistics')
        );
    }
}
