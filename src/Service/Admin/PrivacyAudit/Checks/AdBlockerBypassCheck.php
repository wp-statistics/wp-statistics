<?php

namespace WP_Statistics\Service\Admin\PrivacyAudit\Checks;

use WP_Statistics\Components\Option;
use WP_Statistics\Service\Admin\PrivacyAudit\AbstractPrivacyCheck;
use WP_Statistics\Service\Admin\PrivacyAudit\PrivacyCheckResult;

class AdBlockerBypassCheck extends AbstractPrivacyCheck
{
    public function getKey(): string
    {
        return 'ad_blocker_bypass';
    }

    public function getLabel(): string
    {
        return __('Ad Blocker Bypass', 'wp-statistics');
    }

    public function getDescription(): string
    {
        return __('Checks whether tracking is configured to bypass ad blockers and privacy tools.', 'wp-statistics');
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
        if (Option::getValue('bypass_ad_blockers', false)) {
            return $this->warning(
                __('Tracking is configured to bypass ad blockers. This may conflict with visitor privacy preferences.', 'wp-statistics')
            );
        }

        return $this->pass(
            __('Standard tracking method is in use. Visitor privacy tools are respected.', 'wp-statistics')
        );
    }
}
