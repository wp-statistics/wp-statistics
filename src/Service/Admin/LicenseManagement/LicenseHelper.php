<?php
namespace WP_Statistics\Service\Admin\LicenseManagement;

use Exception;
use WP_Statistics\Exception\LicenseException;
use WP_STATISTICS\Option;

class LicenseHelper
{
    const LICENSE_OPTION_KEY = 'licenses';

    /**
     * Returns license stored in the WordPress database. By default only valid licenses are returned.
     *
     * @param string $status param to filter licenses by status. Could be: `valid`, `license_expired` or `all`
     * @return array
     */
    public static function getLicenses($status = 'valid')
    {
        $licenses = Option::getOptionGroup(self::LICENSE_OPTION_KEY) ?? [];

        if (!empty($status) && $status !== 'all') {
            $licenses = array_filter($licenses, function ($license) use ($status) {
                return $license['status'] === $status;
            });
        }

        return $licenses;
    }

    /**
     * Returns the stored info for a given license key.
     *
     * @param string $licenseKey
     *
     * @return object|false License data if found, false otherwise.
     */
    public static function getLicenseInfo($licenseKey)
    {
        $licenses = self::getLicenses('all');
        return $licenses[$licenseKey] ?? false;
    }

    /**
     * Returns the license key that contains the add-on with the given slug, if any.
     *
     * @param string $slug
     *
     * @return string|null License key. `null` if no valid licenses was found for this slug.
     */
    public static function getPluginLicense($slug)
    {
        // Prioritize valid licenses over expired
        $licenses = array_merge(self::getLicenses('valid'), self::getLicenses('license_expired'));

        foreach ($licenses as $key => $license) {
            if (empty($license['products'])) continue;

            if (in_array($slug, $license['products'])) {
                return $key;
            }
        }

        return null;
    }

    /**
     * Retrieve the status of a given plugin slug.
     *
     * @param string $slug The slug of the plugin.
     *
     * @return string|null The status of the plugin, e.g. 'valid', 'expired', or null if no matching license was found.
     */
    public static function getPluginLicenseStatus($slug)
    {
        $licenseKey = self::getPluginLicense($slug);
        $status     = self::getLicenseInfo($licenseKey);

        return $status['status'] ?? null;
    }

    /**
     * Checks if the given plugin slug has a valid license.
     *
     * @param string $slug The slug of the plugin.
     *
     * @return bool True if the plugin has a valid license, false otherwise.
     */
    public static function isPluginLicenseValid($slug)
    {
        $status = self::getPluginLicenseStatus($slug);
        return $status === 'valid';
    }

    /**
     * Checks if the given plugin is expired.
     *
     * @param string $slug The slug of the plugin.
     */
    public static function isPluginLicenseExpired($slug)
    {
        $status = self::getPluginLicenseStatus($slug);
        return $status === 'license_expired';
    }

    /**
     * Store license details in the WordPress database.
     *
     * @param string $licenseKey
     * @param object $license
     */
    public static function storeLicense($licenseKey, $licenseData)
    {
        $data = [
            'status'        => $licenseData->status,
            'type'          => $licenseData->license_details->type ?? null,
            'sku'           => $licenseData->license_details->sku ?? null,
            'max_domains'   => $licenseData->license_details->max_domains ?? null,
            'user'          => $licenseData->license_details->user ?? null,
            'products'      => isset($licenseData->products) ? wp_list_pluck($licenseData->products, 'slug') : null,
        ];

        Option::saveOptionGroup($licenseKey, $data, self::LICENSE_OPTION_KEY);
    }

    /**
     * Update license in the database
     *
     * @param string $licenseKey
     * @param object $license
     */
    public static function updateLicense($licenseKey, $licenseData)
    {
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
     * Checks if user has any valid license or not.
     *
     * @return bool
     */
    public static function isValidLicenseAvailable()
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
        foreach (self::getLicenses() as $key => $data) {
            if (!empty($data['sku']) && $data['sku'] === 'premium') {
                return $key;
            }
        }

        return null;
    }

    /**
     * Checks all stored licenses to see if they are valid or not.
     *
     * @return void
     */
    public static function checkLicensesStatus()
    {
        $apiCommunicator = new ApiCommunicator();
        $licenses        = LicenseHelper::getLicenses('all');

        foreach ($licenses as $key => $data) {
            try {
                $licenseData = $apiCommunicator->validateLicense($key);
                LicenseHelper::storeLicense($key, $licenseData);
            } catch (LicenseException $e) {
                // If status is empty, do nothing (probably server error, or connection issue)
                if (!$e->getStatus()) return;

                // If license is expired, update the status
                if ($e->getStatus() === 'license_expired') {
                    $data['status'] = $e->getStatus();
                    LicenseHelper::updateLicense($key, $data);
                    return;
                }

                // If license is invalid, remove the license
                LicenseHelper::removeLicense($key);
            }
        }
    }
}