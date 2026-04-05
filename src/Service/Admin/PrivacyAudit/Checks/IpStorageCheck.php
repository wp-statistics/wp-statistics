<?php

namespace WP_Statistics\Service\Admin\PrivacyAudit\Checks;

use WP_Statistics\Components\Option;
use WP_Statistics\Service\Admin\PrivacyAudit\AbstractPrivacyCheck;

class IpStorageCheck extends AbstractPrivacyCheck
{
    public function getKey(): string
    {
        return 'ip_storage';
    }

    public function getLabel(): string
    {
        return __('IP Address Storage', 'wp-statistics');
    }

    public function getDescription(): string
    {
        return __('Checks whether full visitor IP addresses are stored in the database.', 'wp-statistics');
    }

    public function getCategory(): string
    {
        return 'data_collection';
    }

    public function getSettingsLink(): string
    {
        return '/settings/privacy';
    }

    public function run(): array
    {
        if (Option::getValue('store_ip', false)) {
            return $this->fail(
                __('Full IP addresses are being stored. This may require explicit user consent under GDPR and similar regulations.', 'wp-statistics')
            );
        }

        return $this->pass(
            __('Only anonymous hashes are stored. No raw IP addresses are recorded.', 'wp-statistics')
        );
    }
}
