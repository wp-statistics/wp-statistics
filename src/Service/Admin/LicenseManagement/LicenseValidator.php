<?php

namespace WP_Statistics\Service\Admin\LicenseManagement;

use WP_STATISTICS\Helper;
use WP_STATISTICS\Option;

/**
 * This class handles licenses status and validations.
 */
class LicenseValidator
{
    private $optionKey    = 'wp_statistics_license_keys';
    private $transientKey = 'wp_statistics_licenses';

    /**
     * Validates the given license (or all licenses).
     *
     * @param string $licenseKey Validate this license only.
     *
     * @return mixed Array of returned responses from `license/status` endpoint, or response from the given license only.
     */
    public function validateLicense($licenseKey = '')
    {
        // Get already validated licenses from transient
        $validatedLicenses = get_transient($this->transientKey);
        if (empty($validatedLicenses)) {
            $validatedLicenses = [];
        }

        /** @var array Array of saved license keys in database. */
        $allLicenseKeys = Option::get($this->optionKey, []);

        // Get all license keys from database if `$licenseKey` parameter is empty
        $licenseKeysToValidate = !empty($licenseKey) ? [$licenseKey] : $allLicenseKeys;

        $result = [];

        foreach ($licenseKeysToValidate as $currentLicenseKey) {
            $response = '';
            try {
                // Try to validate current license key
                $response = LicenseManagerApi::call(LicenseManagerApi::LICENSE_STATUS, 'GET', [
                    'license_key' => $currentLicenseKey,
                    'domain'      => Helper::get_domain_name(home_url()),
                ]);

                $result[$currentLicenseKey] = $response;

                // Also store the result in the validated license array
                $validatedLicenses[$currentLicenseKey] = $response;

                // And store the key in database
                if (!in_array($currentLicenseKey, $allLicenseKeys)) {
                    $allLicenseKeys[] = $currentLicenseKey;
                }
            } catch (\Exception $e) {
                $result[$currentLicenseKey] = ['error' => $e->getMessage()];
            }
        }

        // Update validated licenses transient
        delete_transient($this->transientKey);
        set_transient($this->transientKey, $validatedLicenses, 12 * HOUR_IN_SECONDS);

        // Update license keys in database
        Option::update($this->optionKey, $allLicenseKeys);

        // Return result of the asked license (or all validated licenses if `$licenseKey` parameter is empty)
        return !empty($licenseKey) ? $result[$licenseKey] : $validatedLicenses;
    }

    /**
     * Returns validated licenses.
     *
     * @return array
     */
    public function getValidatedLicenses()
    {
        // Get already validated licenses from transient
        $validatedLicenses = get_transient($this->transientKey);

        // Validate all licenses if the saved array in transient is empty
        if (empty($validatedLicenses)) {
            $validatedLicenses = $this->validateLicense();
        }

        return $validatedLicenses;
    }
}
