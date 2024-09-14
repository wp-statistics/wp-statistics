<?php

namespace WP_Statistics\Service\Admin\LicenseManagement;

use WP_STATISTICS\Option;
use WP_Statistics\Service\Admin\LicenseManagement\ApiHandler\LicenseManagerApiFactory;
use WP_Statistics\Service\Admin\LicenseManagement\ApiHandler\LicenseStatusResponseDecorator;

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
     * @return array Format: `['licenseKey' => new LicenseStatusResponseDecorator() OR {ErrorMessage}, 'licenseKey' => new LicenseStatusResponseDecorator() OR {ErrorMessage}, ...]`.
     */
    public function validateLicense($licenseKey = '')
    {
        // Get already validated licenses from transient
        $validatedLicenses = get_transient($this->transientKey);
        if (!empty($validatedLicenses)) {
            // Return the requested license status decorator from transient if `$licenseKey` not empty
            if (!empty($licenseKey) && !empty($validatedLicenses[$licenseKey]) && $validatedLicenses[$licenseKey] instanceof LicenseStatusResponseDecorator) {
                return [$licenseKey => $validatedLicenses[$licenseKey]];
            }
        } else {
            $validatedLicenses = [];
        }

        /** @var array Array of saved license keys in database. */
        $allLicenseKeys = Option::get($this->optionKey, []);

        // Get all license keys from database if `$licenseKey` parameter is empty
        $licenseKeysToValidate = !empty($licenseKey) ? [$licenseKey] : $allLicenseKeys;

        $result = [];

        foreach ($licenseKeysToValidate as $currentLicenseKey) {
            try {
                $licenseManagerStatusApi = LicenseManagerApiFactory::getStatusApi($currentLicenseKey);

                // Return current license status decorator to user
                $result[$currentLicenseKey] = $licenseManagerStatusApi;

                // Also store it in transient
                $validatedLicenses[$currentLicenseKey] = $licenseManagerStatusApi;

                // And store the key in database
                if (!in_array($currentLicenseKey, $allLicenseKeys)) {
                    $allLicenseKeys[] = $currentLicenseKey;
                }
            } catch (\Exception $e) {
                $result[$currentLicenseKey] = [$e->getMessage()];
            }
        }

        // Update validated licenses transient
        delete_transient($this->transientKey);
        set_transient($this->transientKey, $validatedLicenses, 12 * HOUR_IN_SECONDS);

        // Update license keys in database
        Option::update($this->optionKey, $allLicenseKeys);

        // Return result of the asked license (or all validated licenses if `$licenseKey` parameter is empty)
        return !empty($licenseKey) ? [$licenseKey => $result[$licenseKey]] : $validatedLicenses;
    }

    /**
     * Returns all validated licenses.
     *
     * @return array Format: `['licenseKey' => new LicenseStatusResponseDecorator() OR {ErrorMessage}, 'licenseKey' => new LicenseStatusResponseDecorator() OR {ErrorMessage}, ...]`.
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
