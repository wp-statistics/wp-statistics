<?php

namespace WP_Statistics\Service\Admin\PrivacyAudit\Checks;

use WP_Statistics\Components\Option;
use WP_Statistics\Service\Admin\PrivacyAudit\AbstractPrivacyCheck;

class DataRetentionCheck extends AbstractPrivacyCheck
{
    public function getKey(): string
    {
        return 'data_retention';
    }

    public function getLabel(): string
    {
        return __('Data Retention', 'wp-statistics');
    }

    public function getDescription(): string
    {
        return __('Checks whether a data retention policy is configured to limit how long visitor data is kept.', 'wp-statistics');
    }

    public function getCategory(): string
    {
        return 'data_retention';
    }

    public function getSettingsLink(): string
    {
        return '/settings/data-management';
    }

    public function run(): array
    {
        $mode = Option::getValue('data_retention_mode', 'forever');

        if ($mode === 'forever') {
            return $this->warning(
                __('Data is retained indefinitely. Consider setting a retention period to minimize stored personal data.', 'wp-statistics')
            );
        }

        $days = Option::getValue('data_retention_days', 180);

        return $this->pass(
            sprintf(
                // translators: %d is the number of days data is retained before deletion
                __('Data is automatically purged after %d days.', 'wp-statistics'),
                $days
            )
        );
    }
}
