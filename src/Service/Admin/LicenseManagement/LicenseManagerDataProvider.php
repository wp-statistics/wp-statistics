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
     * Return a list of the product for view
     *
     * @return ProductDecorator[]
     * @throws Exception
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
     * @throws Exception
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
        $this->validateLicenseKeyInUrl();

        $addOnsList     = [];
        $activeAddOns   = [];
        $inactiveAddOns = [];

        // Try migrating old license keys before handling the new ones
        $licenseMigration = new LicenseMigration($this->apiCommunicator);
        $licenseMigration->migrateOldLicenses();

        // Try to fetch licensed add-ons first
        try {
            $addOnsList = $this->getLicensedProductList();

            // If previous attempt had failed (because of invalid licenses, invalid domain, etc.), try to fetch all add-ons
            if (empty($addOnsList)) {
                $addOnsList = $this->getProductList();
            }

        } catch (Exception $e) {

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

        $this->redirectOnEmptyLicenses();

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
        } catch (Exception $e) {
            $licensedAddOns    = [];
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
        $this->redirectOnEmptyLicenses();

        $licensedAddOns = [];
        $selectedAddOns = Request::has('addons') ? Request::get('addons', [], 'array') : [];

        // Don't display the "Activate All" button if no add-ons can be activated
        $displayActivateAll = false;

        // Fetch all licensed add-ons
        try {
            foreach ($this->getLicensedProductList() as $addOn) {
                if ($addOn->isLicensed()) {
                    $licensedAddOns[] = $addOn;

                    if ($addOn->isInstalled() && !$addOn->isActivated()) {
                        // Add-on can be activated, display the "Activate All" button
                        $displayActivateAll = true;
                    }
                }
            }
        } catch (Exception $e) {
            Notice::addFlashNotice($e->getMessage(), 'warning');
            wp_redirect(Menus::admin_url('plugins', ['tab' => 'downloads']));
            exit;

            $licensedAddOns = [];
        }

        if (empty($licensedAddOns)) {
            Notice::addFlashNotice(__('No licensed add-ons were found!', 'wp-statistics'), 'warning');
            wp_redirect(Menus::admin_url('plugins', ['tab' => 'downloads']));
            exit;
        }

        return [
            'licensed_addons'      => $licensedAddOns,
            'selected_addons'      => $selectedAddOns,
            'display_activate_all' => $displayActivateAll,
        ];
    }

    /**
     * Checks for `license_key` parameter in the URL and will redirect the user to the second step if the licenses is valid.
     *
     * @return void
     */
    private function validateLicenseKeyInUrl()
    {
        if (!Request::has('license_key')) {
            return;
        }

        try {
            $this->apiCommunicator->validateLicense(Request::get('license_key', ''));
        } catch (Exception $e) {
            Notice::addFlashNotice(esc_html($e->getMessage()), 'error');
            return;
        }

        Notice::addFlashNotice(__('License added successfully.', 'wp-statistics'), 'success');
        wp_redirect(Menus::admin_url('plugins', ['tab' => 'downloads']));
        exit;
    }

    /**
     * Redirects the user back to the first step if no licenses were stored in the database.
     *
     * @return void
     */
    private function redirectOnEmptyLicenses()
    {
        if (!empty($this->apiCommunicator->getStoredLicenses())) {
            return;
        }

        Notice::addFlashNotice(__('No licenses were found!', 'wp-statistics'), 'error');
        wp_redirect(Menus::admin_url('plugins', ['tab' => 'add-license']));
        exit;
    }
}
