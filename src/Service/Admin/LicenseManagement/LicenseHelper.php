<?php
namespace WP_Statistics\Service\Admin\LicenseManagement;

use Exception;
use WP_STATISTICS\Option;

class LicenseHelper
{
    const LICENSE_OPTION_KEY = 'licenses';

    /**
     * Returns the licenses stored in the WordPress database.
     *
     * @return array
     */
    public static function getLicenses()
    {
        return Option::getOptionGroup(self::LICENSE_OPTION_KEY);
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
        foreach (self::getLicenses() as $key => $license) {
            if (empty($license['products'])) continue;

            if (in_array($slug, $license['products'])) {
                return $key;
            }
        }

        return null;
    }

    /**
     * Store license details in the WordPress database.
     *
     * @param string $licenseKey
     * @param object $license
     */
    public static function storeLicense($licenseKey, $license)
    {
        $licenseData = [
            'license'  => $license->license_details,
            'products' => wp_list_pluck($license->products, 'slug'),
        ];

        Option::saveOptionGroup($licenseKey, $licenseData, self::LICENSE_OPTION_KEY);
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
     * Checks if user has any license or not.
     *
     * @return bool
     */
    public static function isLicenseAvailable()
    {
        return !empty(self::getLicenses());
    }

    /**
     * Checks all stored licenses to see if any of them is premium.
     *
     * @return string|null License key. `null` if no premium licenses were found.
     */
    public static function isPremiumLicenseAvailable()
    {
        foreach (self::getLicenses() as $key => $license) {
            if (empty($license['license'])) {
                continue;
            }

            if (!empty($license['license']->sku) && $license['license']->sku === 'premium') {
                return $key;
            }
        }

        return null;
    }
}