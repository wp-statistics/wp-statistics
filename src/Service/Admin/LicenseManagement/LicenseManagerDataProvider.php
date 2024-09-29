<?php

namespace WP_Statistics\Service\Admin\LicenseManagement;

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
     * Return a list of the product for view
     *
     * @return ProductDecorator[]
     * @throws \Exception
     */
    public function getProductList()
    {
        return $this->apiCommunicator->getProductList();
    }

    /**
     * Returns a list of licensed products.
     *
     * @return ProductDecorator[]
     *
     * @throws \Exception
     */
    public function getLicensedProductList()
    {
        return $this->apiCommunicator->mergeProductsListWithAllValidLicenses();
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

        // Try to fetch licensed addons first
        try {
            $addOnsList = $this->getLicensedProductList();
        } catch (\Exception $e) {
        }

        // If previous attempt had failed (because of invalid licenses, invalid domain, etc.), try to fetch all addons
        if (empty($addOnsList)) {
            try {
                $addOnsList = $this->getProductList();
            } catch (\Exception $e) {
            }
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

        // Redirect back to first step if no licenses were stored in the database
        if (empty($this->apiCommunicator->getStoredLicenses())) {
            Notice::addFlashNotice(__('No licenses were found!', 'wp-statistics'), 'error');
            wp_redirect(Menus::admin_url('plugins', ['tab' => 'add-license']));
            exit;
        }

        // Don't display the "Select All" button if no add-ons can be downloaded
        $displaySelectAll = false;

        try {
            foreach ($this->getLicensedProductList() as $addOn) {
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
        } catch (\Exception $e) {
            $licensedAddOns   = [];
            $notIncludedAddOns = [];

            // Redirect back to first step
            Notice::addFlashNotice($e->getMessage(), 'error');
            wp_redirect(Menus::admin_url('plugins', ['tab' => 'add-license']));
            exit;
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
        // Redirect back to first step if no licenses were stored in the database
        if (empty($this->apiCommunicator->getStoredLicenses())) {
            Notice::addFlashNotice(__('No licenses were found!', 'wp-statistics'), 'error');
            wp_redirect(Menus::admin_url('plugins', ['tab' => 'add-license']));
            exit;
        }

        // Redirect back to second step if the `addons` have not been sent via Ajax
        if (!Request::has('addons') || !is_array(Request::get('addons'))) {
            Notice::addFlashNotice(__('No add-ons were selected!', 'wp-statistics'), 'error');
            wp_redirect(Menus::admin_url('plugins', ['tab' => 'downloads']));
            exit;
        }

        $selectedAddOns = Request::get('addons');
        $addOns         = [];

        // Don't display the "Activate All" button if no add-ons can be downloaded
        $displayActivateAll = false;

        try {
            foreach ($this->getLicensedProductList() as $addOn) {
                if ($addOn->isLicensed() && in_array($addOn->getSlug(), $selectedAddOns)) {
                    $addOns[] = $addOn;

                    if ($addOn->isInstalled() && !$addOn->isActivated()) {
                        // Add-on can be activated, display the "Activate All" button
                        $displayActivateAll = true;
                    }
                }
            }
        } catch (\Exception $e) {
            Notice::addFlashNotice($e->getMessage(), 'warning');
            wp_redirect(Menus::admin_url('plugins', ['tab' => 'downloads']));
            exit;

            $addOns = [];
        }

        if (empty($addOns)) {
            Notice::addFlashNotice(__('No licensed add-ons were selected!', 'wp-statistics'), 'warning');
            wp_redirect(Menus::admin_url('plugins', ['tab' => 'downloads']));
            exit;
        }

        return [
            'addons'               => $addOns,
            'display_activate_all' => $displayActivateAll,
        ];
    }
}
