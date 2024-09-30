<?php

namespace WP_Statistics\Service\Admin\LicenseManagement;

use WP_STATISTICS\Option;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;

class LicenseMigration
{
    private $apiCommunicator;
    private $optionMap = [
        'wp-statistics-advanced-reporting' => 'wpstatistics_advanced_reporting_settings',
        'wp-statistics-customization'      => 'wpstatistics_customization_settings',
        'wp-statistics-widgets'            => 'wpstatistics_widgets_settings',
        'wp-statistics-realtime-stats'     => 'wpstatistics_realtime_stats_settings',
        'wp-statistics-mini-chart'         => 'wpstatistics_mini_chart_settings',
        'wp-statistics-rest-api'           => 'wpstatistics_rest_api_settings',
        'wp-statistics-data-plus'          => 'wpstatistics_data_plus_settings',
    ];

    public function __construct(ApiCommunicator $apiCommunicator)
    {
        $this->apiCommunicator = $apiCommunicator;
    }

    /**
     * Migrate old licenses to the new license structure.
     */
    public function migrateOldLicenses()
    {
        // Prevent already migrated licenses from migrating again
        $storedLicenses = array_keys($this->apiCommunicator->getStoredLicenses());

        // All licenses migrated successfully without any errors
        $allLicensesMigrated = true;

        foreach ($this->optionMap as $addonSlug => $optionName) {
            $licenseData = get_option($optionName);

            if ($licenseData) {
                $licenseKey = $licenseData['license_key'] ?? null;

                if ($licenseKey) {
                    if (in_array($licenseKey, $storedLicenses)) {
                        continue;
                    }

                    // Validate and store the new license structure
                    try {
                        $this->apiCommunicator->validateLicense($licenseKey);
                    } catch (\Exception $e) {
                        $allLicensesMigrated = false;

                        // translators: 1: Add-on slug - 2: Error message.
                        Notice::addNotice(sprintf(__('Failed to migrate license for %s: %s', 'wp-statistics'), $addonSlug, $e->getMessage()), 'license_migration', 'error');
                    }
                }
            }
        }

        if ($allLicensesMigrated) {
            Option::saveOptionGroup('licenses_migrated', true, 'jobs');
        }
    }
}
