<?php

namespace WP_Statistics\Service\Admin\LicenseManagement;

use WP_STATISTICS\Menus;
use WP_Statistics\Utils\Request;

class LicenseManagerDataProvider
{
    protected $args;
    private $licenseService;

    public function __construct($args = [])
    {
        $this->args           = $args;
        $this->licenseService = new LicenseManagementService();
    }

    /**
     * Return a list of the product for view
     *
     * @return ProductDecorator[]
     * @throws \Exception
     */
    public function getProductList()
    {
        return $this->licenseService->getProductList();
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
        return $this->licenseService->mergeProductsListWithAllValidLicenses();
    }

    /**
     * Returns data for "Add-Ons" tab.
     *
     * @return array
     */
    public function getAddOnsData()
    {
        $activeAddOns   = [];
        $inactiveAddOns = [];

        try {
            foreach ($this->getLicensedProductList() as $addOn) {
                if ($addOn->isActivated()) {
                    $activeAddOns[] = $addOn;
                } else {
                    $inactiveAddOns[] = $addOn;
                }
            }
        } catch (\Exception $e) {
            $activeAddOns   = [];
            $inactiveAddOns = [];
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
        // Redirect back to second step if the `addons` have not been sent via Ajax
        if (!Request::has('addons') || !is_array(Request::get('addons'))) {
            wp_redirect(Menus::admin_url('plugins', ['tab' => 'downloads']));
            exit;
        }

        $selectedAddOns = Request::get('addons');
        $addOns         = [];

        try {
            foreach ($this->getLicensedProductList() as $addOn) {
                if ($addOn->isLicensed() && in_array($addOn->getSlug(), $selectedAddOns)) {
                    $addOns[] = $addOn;
                }
            }
        } catch (\Exception $e) {
            $addOns = [];
        }

        return [
            'addons' => $addOns,
        ];
    }
}
