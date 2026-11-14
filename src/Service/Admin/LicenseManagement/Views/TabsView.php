<?php

namespace WP_Statistics\Service\Admin\LicenseManagement\Views;

use Exception;
use WP_Statistics\Components\SystemCleaner;
use WP_Statistics\Components\View;
use WP_Statistics\Utils\Request;
use WP_STATISTICS\Menus;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Abstracts\BaseTabView;
use WP_Statistics\Exception\SystemErrorException;
use WP_Statistics\Service\Admin\LicenseManagement\ApiCommunicator;
use WP_Statistics\Service\Admin\LicenseManagement\LicenseHelper;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;
use WP_Statistics\Service\Admin\LicenseManagement\LicenseManagerDataProvider;

class TabsView extends BaseTabView
{
    protected $defaultTab = 'add-ons';
    protected $tabs = [
        'add-ons',
        'add-license',
        'downloads',
        'get-started',
    ];

    private $apiCommunicator;

    public function __construct()
    {
        $this->dataProvider    = new LicenseManagerDataProvider();
        $this->apiCommunicator = new ApiCommunicator();
        $this->checkUserAccess();
        $this->handleUrlLicenseValidation();
        $this->checkLicensesStatus();

        parent::__construct();
    }

    /**
     * Prevent access to certain tabs if the user is not an admin.
     *
     * @throws SystemErrorException if the user does not have permission to access the page.
     */
    private function checkUserAccess()
    {
        if (!is_main_site() && !$this->isTab('add-ons')) {
            throw new SystemErrorException(esc_html__('You do not have permission to access this page.', 'wp-statistics'));
        }
    }

    /**
     * Checks the licenses status if the current tab is 'add-ons'.
     */
    private function checkLicensesStatus()
    {
        if ($this->isTab('add-ons')) {
            LicenseHelper::checkLicensesStatus();
        }
    }

    /**
     * Validate the license key sent via URL
     *
     * @return void
     */
    private function handleUrlLicenseValidation()
    {
        $license = Request::get('license_key');

        if (!empty($license)) {
            try {
                $this->apiCommunicator->validateLicense($license);
            } catch (Exception $e) {
                // Show a notice instead of letting a transient validation failure fatal the page.
                Notice::renderNotice($e->getMessage(), $e->getCode(), 'error');
            }
        }
    }

    /**
     * Returns the current tab to be displayed.
     *
     * @return string
     */
    public function getCurrentTab()
    {
        $currentTab = Request::get('tab', $this->defaultTab);

        // If license key is sent via URL, redirect to "Downloads" tab
        if (in_array($currentTab, ['add-ons', 'add-license']) && Request::has('license_key')) {
            return 'downloads';
        }

        // If license key has not been found, prevent accessing certain tabs
        if (in_array($currentTab, ['downloads', 'get-started']) && !Request::has('license_key')) {
            return 'add-license';
        }

        return $currentTab;
    }

    /**
     * Link to the download step with the saved valid license, or to the license step when there is none.
     *
     * @return string
     */
    private function getInstallLinkArgs()
    {
        $validKeys = array_keys(LicenseHelper::getLicenses('valid'));

        return Menus::admin_url('plugins', !empty($validKeys) ? ['tab' => 'downloads', 'license_key' => $validKeys[0]] : ['tab' => 'add-license']);
    }

    /**
     * The license keys saved on this site, for the "Your licenses" card. Keys are masked, and the
     * card refers to a key by its masked form, so a full key never reaches the page.
     *
     * @return array
     */
    private function getSavedLicenses()
    {
        $rows = [];

        foreach (LicenseHelper::getLicenses('all') as $key => $license) {
            $rows[] = [
                'masked'  => LicenseHelper::maskKey($key),
                'status'  => $license['status'] ?? '',
                'sku'     => $license['sku'] ?? '',
                'covers'  => is_array($license['products'] ?? null) ? count($license['products']) : 0,
                'valid_until' => $license['valid_until'] ?? null,
                'max_domains' => $license['max_domains'] ?? null,
            ];
        }

        return $rows;
    }

    /**
     * Returns data for "Add-ons" tab.
     *
     * @return array
     */
    public function getAddOnsData()
    {
        $addOnsData                                 = $this->dataProvider->getAddOnsData();
        $addOnsData['has_any_license']              = $this->dataProvider->hasAnyLicense();
        $addOnsData['missing_add_ons']              = $this->dataProvider->getMissingAddOnsForPremiumLicense();
        $addOnsData['has_valid_premium_license']    = $this->dataProvider->hasValidPremiumLicense();
        $addOnsData['invalid_licenses']             = $this->dataProvider->getInvalidLicenses();
        $addOnsData['unlicensed_installed_add_ons'] = $this->dataProvider->getInstalledAddOnsWithoutLicense();
        $addOnsData['inactive_installed_add_ons']   = $this->dataProvider->getInactiveInstalledAddOns();
        $addOnsData['license_notice_type']          = $this->dataProvider->getLicenseNoticeType();
        $addOnsData['install_addon_link']           = esc_url(Menus::admin_url('plugins', ['tab' => 'add-license']));
        $addOnsData['download_addons_link']         = esc_url($this->getInstallLinkArgs());
        $addOnsData['saved_licenses']               = LicenseHelper::canManageLicenses() ? $this->getSavedLicenses() : [];

        return $addOnsData;
    }

    /**
     * Returns data for "Download Add-ons" tab.
     *
     * @return array
     */
    public function getDownloadsData()
    {
        return $this->dataProvider->getDownloadsData();
    }

    /**
     * Returns data for "Get Started" tab.
     *
     * @return array
     */
    public function getGetStartedData()
    {
        return $this->dataProvider->getGetStartedData();
    }

    public function render()
    {
        try {
            $currentTab = $this->getCurrentTab();
            $data       = $this->getTabData();
            $urlParams  = [];

            if (Request::get('license_key')) {
                $urlParams['license_key'] = Request::get('license_key');
            }

            $args = [
                'title'      => esc_html__('License Manager', 'wp-statistics'),
                'pageName'   => Menus::get_page_slug('plugins'),
                'custom_get' => ['tab' => $currentTab],
                'data'       => $data,
                'tabs'       => [
                    [
                        'link'  => Menus::admin_url('plugins', ['tab' => 'add-ons']),
                        'title' => esc_html__('Add-ons', 'wp-statistics'),
                        'class' => $this->isTab('add-ons') ? 'current' : '',
                    ],
                    [
                        'link'  => Menus::admin_url('plugins', ['tab' => 'add-license']),
                        'title' => esc_html__('Add Your License', 'wp-statistics'),
                        'class' => $this->isTab('add-license') ? 'current' : '',
                    ],
                    [
                        'link'  => Menus::admin_url('plugins', array_merge(['tab' => 'downloads'], $urlParams)),
                        'title' => esc_html__('Download Add-ons', 'wp-statistics'),
                        'class' => $this->isTab('downloads') ? 'current' : '',
                    ],
                    [
                        'link'  => Menus::admin_url('plugins', array_merge(['tab' => 'get-started'], $urlParams)),
                        'title' => esc_html__('Get Started', 'wp-statistics'),
                        'class' => $this->isTab('get-started') ? 'current' : '',
                    ],
                ]
            ];

            if ($this->isTab('add-ons')) {
                $args['title'] = LicenseHelper::isFreeOnly() ? esc_html__('Premium Features', 'wp-statistics') : esc_html__('Add-ons', 'wp-statistics');

                if (is_main_site() && !LicenseHelper::isFreeOnly()) {
                    $args['install_addon_btn_txt']  = esc_html__('Install Add-ons', 'wp-statistics');

                    // With a valid license already saved, go straight to the download step instead of asking for the key again.
                    $args['install_addon_btn_link'] = esc_url($this->getInstallLinkArgs());
                }

                // The free Premium tour has its own heading, so no page title bar there.
                Admin_Template::get_template(LicenseHelper::isFreeOnly() ? ['layout/header'] : ['layout/header', 'layout/title'], $args);
            } else {
                Admin_Template::get_template(['layout/header', 'layout/addon-header-steps'], $args);
            }

            View::load("pages/license-manager/$currentTab", $args);
            Admin_Template::get_template(['layout/postbox.hide', 'layout/footer'], $args);
        } catch (Exception $e) {
            Notice::renderNotice($e->getMessage(), $e->getCode(), 'error');
        }
    }
}