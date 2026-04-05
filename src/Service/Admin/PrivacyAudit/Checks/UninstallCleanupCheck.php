<?php

namespace WP_Statistics\Service\Admin\PrivacyAudit\Checks;

use WP_Statistics\Components\Option;
use WP_Statistics\Service\Admin\PrivacyAudit\AbstractPrivacyCheck;
use WP_Statistics\Service\Admin\PrivacyAudit\PrivacyCheckResult;

class UninstallCleanupCheck extends AbstractPrivacyCheck
{
    public function getKey(): string
    {
        return 'uninstall_cleanup';
    }

    public function getLabel(): string
    {
        return __('Uninstall Cleanup', 'wp-statistics');
    }

    public function getDescription(): string
    {
        return __('Checks whether all statistics data will be removed when the plugin is uninstalled.', 'wp-statistics');
    }

    public function getCategory(): string
    {
        return 'advanced';
    }

    public function getSettingsLink(): string
    {
        return '/settings/advanced';
    }

    public function run(): PrivacyCheckResult
    {
        if (!Option::getValue('delete_data_on_uninstall', false)) {
            return $this->warning(
                __('Statistics data will persist in the database after the plugin is uninstalled. Enable cleanup to remove all data on uninstall.', 'wp-statistics')
            );
        }

        return $this->pass(
            __('All statistics data will be removed when the plugin is uninstalled.', 'wp-statistics')
        );
    }
}
