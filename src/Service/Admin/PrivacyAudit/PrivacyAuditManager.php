<?php

namespace WP_Statistics\Service\Admin\PrivacyAudit;

use WP_Statistics\Service\Admin\PrivacyAudit\Checks\IpStorageCheck;
use WP_Statistics\Service\Admin\PrivacyAudit\Checks\HashRotationCheck;
use WP_Statistics\Service\Admin\PrivacyAudit\Checks\ConsentIntegrationCheck;
use WP_Statistics\Service\Admin\PrivacyAudit\Checks\VisitorLoggingCheck;
use WP_Statistics\Service\Admin\PrivacyAudit\Checks\DataRetentionCheck;
use WP_Statistics\Service\Admin\PrivacyAudit\Checks\UninstallCleanupCheck;

class PrivacyAuditManager
{
    /**
     * @var array<string, class-string<PrivacyCheckInterface>>
     */
    private array $checkClasses = [
        'ip_storage'          => IpStorageCheck::class,
        'hash_rotation'       => HashRotationCheck::class,
        'consent'             => ConsentIntegrationCheck::class,
        'visitor_logging'     => VisitorLoggingCheck::class,
        'data_retention'      => DataRetentionCheck::class,
        'uninstall_cleanup'   => UninstallCleanupCheck::class,
    ];

    /**
     * @var array[]
     */
    private array $results = [];

    /**
     * Run all privacy checks.
     *
     * @return array[]
     */
    public function runAll(): array
    {
        $this->results = [];

        foreach ($this->checkClasses as $key => $class) {
            $check = new $class();
            $this->results[$key] = $check->run();
        }

        return $this->results;
    }

    /**
     * Get pass/warning/fail counts from last run.
     *
     * @return array{passCount: int, warningCount: int, failCount: int}
     */
    public function getSummary(): array
    {
        $summary = [
            'passCount'    => 0,
            'warningCount' => 0,
            'failCount'    => 0,
        ];

        foreach ($this->results as $result) {
            switch ($result['status']) {
                case 'pass':
                    $summary['passCount']++;
                    break;
                case 'warning':
                    $summary['warningCount']++;
                    break;
                case 'fail':
                    $summary['failCount']++;
                    break;
            }
        }

        return $summary;
    }

    /**
     * Get category labels for grouping checks in the UI.
     *
     * @return array<string, string>
     */
    public function getCategories(): array
    {
        return [
            'data_collection' => __('Data Collection', 'wp-statistics'),
            'consent'         => __('Consent Management', 'wp-statistics'),
            'data_retention'  => __('Data Retention', 'wp-statistics'),
            'advanced'        => __('Advanced', 'wp-statistics'),
        ];
    }
}
