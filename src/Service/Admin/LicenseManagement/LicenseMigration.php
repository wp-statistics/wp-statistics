<?php

namespace WP_Statistics\Service\Admin\LicenseManagement;

class LicenseMigration
{
    private $licenseService;
    private $optionMap = [
        'wp-statistics-advanced-reporting' => 'wpstatistics_advanced_reporting_settings',
        'wp-statistics-customization'      => 'wpstatistics_customization_settings',
        'wp-statistics-widgets'            => 'wpstatistics_widgets_settings',
        'wp-statistics-realtime-stats'     => 'wpstatistics_realtime_stats_settings',
        'wp-statistics-mini-chart'         => 'wpstatistics_mini_chart_settings',
        'wp-statistics-rest-api'           => 'wpstatistics_rest_api_settings',
        'wp-statistics-data-plus'          => 'wpstatistics_data_plus_settings',
    ];

    public function __construct(LicenseManagementService $licenseService)
    {
        $this->licenseService = $licenseService;
    }

    /**
     * Migrate old licenses to the new license structure.
     */
    public function migrateOldLicenses()
    {
        foreach ($this->optionMap as $addonSlug => $optionName) {
            $licenseData = get_option($optionName);

            if ($licenseData) {
                $licenseKey = $licenseData['license_key'] ?? null;

                if ($licenseKey) {
                    // Validate and store the new license structure
                    try {
                        $this->licenseService->validateLicense($licenseKey);
                        printf(__('Migrated license for %s successfully.', 'wp-statistics'), $addonSlug);
                    } catch (\Exception $e) {
                        printf(__('Failed to migrate license for %s: %s', 'wp-statistics'), $addonSlug, $e->getMessage());
                    }
                }
            }
        }
    }
}
