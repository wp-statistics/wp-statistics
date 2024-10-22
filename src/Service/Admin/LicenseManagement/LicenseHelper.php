<?php
namespace WP_Statistics\Service\Admin\LicenseManagement;

use Exception;
use WP_STATISTICS\Option;

class LicenseHelper
{
    const LICENSE_OPTION_KEY = 'licenses';

    /**
     * Returns all licenses stored in the WordPress database.
     *
     * @return array
     */
    public static function getLicenses()
    {
        return Option::getOptionGroup(self::LICENSE_OPTION_KEY) ?? [];
    }

    /**
     * Returns all valid licenses.
     *
     * @return array
     */
    public static function getValidLicenses()
    {
        $licenses = self::getLicenses();

        // Filter licenses with active status
        $licenses = array_filter($licenses, function ($license) {
            return $license['status'] == true;
        });

        return $licenses;
    }

    /**
     * Returns the stored data for a given license key.
     *
     * @param string $licenseKey
     *
     * @return object|false License data if found, false otherwise.
     */
    public static function getLicenseData($licenseKey)
    {
        $licenses = self::getValidLicenses();
        return isset($license[$licenseKey]) ? $licenses[$licenseKey] : false;
    }

    /**
     * Returns the first validated license key that contains the add-on with the given slug.
     *
     * @param string $slug
     *
     * @return string|null License key. `null` if no valid licenses was found for this slug.
     */
    public static function getPluginLicense($slug)
    {
        foreach (self::getValidLicenses() as $key => $license) {
            if (empty($license['products'])) continue;

            if (in_array($slug, $license['products'])) {
                return $key;
            }
        }

        return null;
    }

    /**
     * Format license data to be stored in the database.
     *
     * @param object $licenseData The license data from the API.
     * @return array The formatted license data.
     */
    private static function formatLicenseData($licenseData)
    {
        $data = [
            'status'        => !empty($licenseData) ? true : false,
            'type'          => $licenseData->license_details->type ?? null,
            'sku'           => $licenseData->license_details->sku ?? null,
            'max_domains'   => $licenseData->license_details->max_domains ?? null,
            'user'          => $licenseData->license_details->user ?? null,
            'products'      => isset($licenseData->products) ? wp_list_pluck($licenseData->products, 'slug') : null,
        ];

        return $data;
    }

    /**
     * Store license details in the WordPress database.
     *
     * @param string $licenseKey
     * @param object $license
     */
    public static function saveLicense($licenseKey, $licenseData)
    {
        $data = self::formatLicenseData($licenseData);

        Option::saveOptionGroup($licenseKey, $data, self::LICENSE_OPTION_KEY);
    }

    /**
     * Removes a license from WordPress database.
     *
     * @param string $licenseKey
     *
     * @return void
     */
    public static function removeLicense($licenseKey)
    {
        Option::deleteOptionGroup($licenseKey, self::LICENSE_OPTION_KEY);
    }

    /**
     * Checks if user has any valid license or not.
     *
     * @return bool
     */
    public static function isLicenseAvailable()
    {
        return !empty(self::getValidLicenses());
    }

    /**
     * Checks all stored licenses to see if any of them is premium.
     *
     * @return string|null License key. `null` if no premium licenses were found.
     */
    public static function isPremiumLicenseAvailable()
    {
        foreach (self::getValidLicenses() as $key => $data) {
            if (!empty($data['sku']) && $data['sku'] === 'premium') {
                return $key;
            }
        }

        return null;
    }
}