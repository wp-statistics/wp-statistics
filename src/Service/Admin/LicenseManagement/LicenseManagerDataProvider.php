<?php

namespace WP_Statistics\Service\Admin\LicenseManagement;

use Exception;
use WP_STATISTICS\Menus;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;
use WP_Statistics\Utils\Request;

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
        try {
            $addOnsList = $this->apiCommunicator->getPurchasedPlugins();
        } catch (Exception $th) {}

        // If previous attempt had failed (because of invalid licenses, invalid domain, etc.), try to fetch all add-ons
        if (empty($addOnsList)) {
            $this->apiCommunicator->getRemotePlugins();
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
        $licensedAddOns    = [];
        $notIncludedAddOns = [];

        // Don't display the "Select All" button if no add-ons can be downloaded
        $displaySelectAll = false;

        foreach ($this->apiCommunicator->getPurchasedPlugins() as $addOn) {
            if ($addOn->isLicensed()) {
                $licensedAddOns[] = $addOn;
            } else {
                $notIncludedAddOns[] = $addOn;
            }

            if ($addOn->isLicensed() && (!$addOn->isInstalled() || $addOn->isUpdateAvailable())) {
                // Add-on can be downloaded, display the "Select All" button
                $displaySelectAll = true;
            }
        }

        return [
            'licensed_addons'     => $licensedAddOns,
            'not_included_addons' => $notIncludedAddOns,
            'display_select_all'  => $displaySelectAll,
        ];
    }

    /**
     * Returns data for "Get Started" tab.
     *
     * @return array
     */
    public function getGetStartedData()
    {
        $licensedAddOns = [];
        $selectedAddOns = Request::has('addons') ? Request::get('addons', [], 'array') : [];

        // Don't display the "Activate All" button if no add-ons can be activated
        $displayActivateAll = false;

        // Fetch all licensed add-ons
        try {
            foreach ($this->apiCommunicator->getPurchasedPlugins() as $addOn) {
                if ($addOn->isLicensed()) {
                    $licensedAddOns[] = $addOn;

                    if ($addOn->isInstalled() && !$addOn->isActivated()) {
                        // Add-on can be activated, display the "Activate All" button
                        $displayActivateAll = true;
                    }
                }
            }
        } catch (Exception $e) {
            $licensedAddOns = [];
        }

        return [
            'licensed_addons'      => $licensedAddOns,
            'selected_addons'      => $selectedAddOns,
            'display_activate_all' => $displayActivateAll,
        ];
    }
}
