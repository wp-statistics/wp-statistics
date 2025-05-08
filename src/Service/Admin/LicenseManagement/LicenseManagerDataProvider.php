<?php

namespace WP_Statistics\Service\Admin\LicenseManagement;

use WP_Statistics\Exception\SystemErrorException;
use WP_Statistics\Service\Admin\LicenseManagement\Plugin\PluginHelper;
use WP_Statistics\Utils\Request;
use WP_Statistics\Service\Admin\LicenseManagement\Plugin\PluginHandler;

class LicenseManagerDataProvider
{
    protected $args;
    private $apiCommunicator;

    public function __construct($args = [])
    {
        $this->args            = $args;
        $this->apiCommunicator = new ApiCommunicator();
    }

    /**
     * Returns data for "Add-Ons" tab.
     *
     * @return array
     */
    public function getAddOnsData()
    {
        $addOnsList     = [];
        $activeAddOns   = [];
        $inactiveAddOns = [];

        // Try migrating old license keys before handling the new ones
        $licenseMigration = new LicenseMigration($this->apiCommunicator);
        $licenseMigration->migrateOldLicenses();

        // Try to fetch licensed add-ons first
        $addOnsList = PluginHelper::getRemotePlugins();

        if (empty($addOnsList)) {
            throw new SystemErrorException(esc_html__('Failed to retrieve the list of available add-ons. Please try again later.'));
        }

        // Separate active and inactive add-ons
        foreach ($addOnsList as $addOn) {
            if ($addOn->isActivated()) {
                $activeAddOns[] = $addOn;
            } else {
                $inactiveAddOns[] = $addOn;
            }
        }

        return [
            'active_addons'   => $activeAddOns,
            'inactive_addons' => $inactiveAddOns,
        ];
    }

    /**
     * Returns data for "Download Add-ons" tab.
     *
     * @return array
     */
    public function getDownloadsData()
    {
        $result = [
            'licensed_addons'     => [],
            'not_included_addons' => [],
            'display_select_all'  => false
        ];

        $licenseKey      = Request::get('license_key');
        $purchasedAddons = PluginHelper::getLicensedPlugins($licenseKey);

        foreach (PluginHelper::getRemotePlugins() as $addOn) {
            if (in_array($addOn->getSlug(), $purchasedAddons)) {
                $result['licensed_addons'][] = $addOn;
            } else {
                $result['not_included_addons'][] = $addOn;
            }

            if ($addOn->isLicenseValid() && (!$addOn->isInstalled() || $addOn->isUpdateAvailable())) {
                // Add-on can be downloaded, display the "Select All" button
                $result['display_select_all'] = true;
            }
        }

        return $result;
    }

    /**
     * Returns data for "Get Started" tab.
     *
     * @return array
     */
    public function getGetStartedData()
    {
        $result = [
            'licensed_addons'      => [],
            'selected_addons'      => Request::has('addons') ? Request::get('addons', [], 'array') : [],
            'display_activate_all' => false
        ];

        $licenseKey      = Request::get('license_key');
        $purchasedAddons = PluginHelper::getLicensedPlugins($licenseKey);

        // Fetch all licensed add-ons
        foreach (PluginHelper::getRemotePlugins() as $addOn) {
            if (in_array($addOn->getSlug(), $purchasedAddons)) {
                $result['licensed_addons'][] = $addOn;

                // Add-on can be activated, display the "Activate All" button
                if ($addOn->isInstalled() && !$addOn->isActivated()) {
                    $result['display_activate_all'] = true;
                }
            }
        }

        return $result;
    }

    /**
     * Check if the user has a premium license.
     *
     * @return bool True if a premium license is available, false otherwise.
     */
    public function isPremiumUser()
    {
        return (bool)LicenseHelper::isPremiumLicenseAvailable();
    }

    /**
     * Checks if the current user has a valid premium license.
     *
     * This method first verifies whether the user is identified as a premium user.
     * If so, it retrieves the license information using the LicenseHelper. It then
     * checks if the license status is marked as 'valid'.
     *
     * @return bool Returns true if the premium license is valid; otherwise, false.
     */
    public function hasValidPremiumLicense()
    {
        if ($this->isPremiumUser()) {
            $premiumLicenseInfo = LicenseHelper::getLicenseInfo(LicenseHelper::isPremiumLicenseAvailable());

            return isset($premiumLicenseInfo['status']) && $premiumLicenseInfo['status'] == 'valid';
        }

        return false;
    }

    /**
     * Retrieves a list of invalid licenses.
     *
     * Fetches all licenses using LicenseHelper and filters out those
     * whose status is not 'valid'.
     *
     * @return array List of invalid license keys.
     */
    public function getInvalidLicenses(): array
    {
        $licenses = LicenseHelper::getLicenses('all');

        return array_keys(array_filter($licenses ?? [], function ($info) {
            return ($info['status'] ?? null) !== 'valid';
        }));
    }

    /**
     * Checks if there is at least one license of any type.
     *
     * This method retrieves all available licenses using the LicenseHelper
     * and returns true if there is at least one license present.
     *
     * @return bool True if there is at least one license, false otherwise.
     */
    public function hasAnyLicense()
    {
        $licenses = LicenseHelper::getLicenses('all');
        return is_array($licenses) && !empty($licenses);
    }

    /**
     * Retrieves a list of missing add-ons when a valid premium license is present.
     *
     * This method checks if the user has a valid premium license, and if so,
     * iterates through the expected add-ons defined in PluginHelper. It returns
     * an associative array of add-ons that are not currently installed.
     *
     * @return array
     */
    public function getMissingAddOnsForPremiumLicense()
    {
        if (!$this->hasValidPremiumLicense()) {
            return [];
        }

        $pluginHandler = new pluginHandler();
        $missingAddOns = [];

        foreach (PluginHelper::$plugins as $plugin => $title) {
            if (!$pluginHandler->isPluginInstalled($plugin)) {
                $missingAddOns[] = PluginHelper::getRemotePluginBySlug($plugin);
            }
        }

        return $missingAddOns;
    }

    /**
     * Retrieves a list of installed WP Statistics add-ons.
     *
     * This method checks all known WP Statistics add-ons and returns those that
     * are currently installed on the system.
     *
     * @return array Associative array of installed add-ons in the format ['addon-slug' => 'Addon Name'].
     *               Returns an empty array if no known add-ons are installed.
     */
    public function getInstalledAddOns()
    {
        $pluginHandler = new pluginHandler();
        $addOns        = [];

        foreach (PluginHelper::$plugins as $plugin => $title) {
            if ($pluginHandler->isPluginInstalled($plugin)) {
                $addOns[$plugin] = $title;
            }
        }

        return $addOns;
    }

    /**
     * Retrieves a list of installed add-ons that do not have valid licenses.
     *
     * This method uses the list of currently installed add-ons and filters out
     * those that lack a valid license.
     *
     * @return array Associative array of unlicensed installed add-ons ['addon-slug' => 'Addon Name'].
     *               Returns an empty array if all installed add-ons are licensed.
     */
    public function getInstalledAddOnsWithoutLicense(): array
    {
        $installedAddOns = $this->getInstalledAddOns();

        if (empty($installedAddOns)) {
            return [];
        }

        $unlicensedAddOns = [];

        foreach ($installedAddOns as $plugin => $title) {
            if (!LicenseHelper::isPluginLicenseValid($plugin)) {
                $unlicensedAddOns[] = PluginHelper::getRemotePluginBySlug($plugin);
            }
        }

        return $unlicensedAddOns;
    }

    /**
     * Retrieves a list of installed WP Statistics add-ons that are currently inactive.
     *
     * This method checks all installed WP Statistics add-ons and returns those
     * that are not currently active (i.e., installed but deactivated).
     *
     * @return array Associative array of inactive add-ons ['addon-slug' => 'Addon Name'].
     *               Returns an empty array if all installed add-ons are active.
     */
    public function getInactiveInstalledAddOns(): array
    {
        $installedAddOns = $this->getInstalledAddOns();

        if (empty($installedAddOns)) {
            return [];
        }

        $pluginHandler  = new pluginHandler();
        $inactiveAddOns = [];

        foreach ($installedAddOns as $plugin => $title) {
            if (!$pluginHandler->isPluginActive($plugin)) {
                $inactiveAddOns[] = PluginHelper::getRemotePluginBySlug($plugin);
            }
        }

        return $inactiveAddOns;
    }
}
