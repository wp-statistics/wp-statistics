<?php

namespace WP_Statistics\Service\Admin\PrivacyAudit\Checks;

use WP_Statistics\Components\Option;
use WP_Statistics\Service\Admin\PrivacyAudit\AbstractPrivacyCheck;
use WP_Statistics\Service\Admin\PrivacyAudit\PrivacyCheckResult;

class VisitorLoggingCheck extends AbstractPrivacyCheck
{
    public function getKey(): string
    {
        return 'visitor_logging';
    }

    public function getLabel(): string
    {
        return __('Visitor Logging', 'wp-statistics');
    }

    public function getDescription(): string
    {
        return __('Checks whether logged-in user IDs are associated with visit records.', 'wp-statistics');
    }

    public function getCategory(): string
    {
        return 'data_collection';
    }

    public function getSettingsLink(): string
    {
        return '/settings/general';
    }

    public function run(): PrivacyCheckResult
    {
        if (Option::getValue('visitors_log', false)) {
            return $this->warning(
                __('Logged-in user IDs are linked to visit records. This creates personally identifiable browsing histories.', 'wp-statistics')
            );
        }

        return $this->pass(
            __('User IDs are not linked to visit records. Browsing data remains anonymous.', 'wp-statistics')
        );
    }
}
