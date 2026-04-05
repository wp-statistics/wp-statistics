<?php

namespace WP_Statistics\Service\Admin\PrivacyAudit\Checks;

use WP_Statistics\Components\Option;
use WP_Statistics\Service\Admin\PrivacyAudit\AbstractPrivacyCheck;

class HashRotationCheck extends AbstractPrivacyCheck
{
    public function getKey(): string
    {
        return 'hash_rotation';
    }

    public function getLabel(): string
    {
        return __('Hash Rotation', 'wp-statistics');
    }

    public function getDescription(): string
    {
        return __('Checks whether the visitor hash salt rotates regularly to limit tracking duration.', 'wp-statistics');
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
        $interval = Option::getValue('hash_rotation_interval', 'daily');

        if ($interval === 'disabled') {
            return $this->warning(
                __('Hash rotation is disabled. The same visitor will always produce the same hash, which reduces privacy protection.', 'wp-statistics')
            );
        }

        return $this->pass(
            sprintf(
                // translators: %s is the rotation interval (e.g., "daily", "weekly", "monthly")
                __('Hash salt rotates %s, limiting visitor tracking duration.', 'wp-statistics'),
                $interval
            )
        );
    }
}
