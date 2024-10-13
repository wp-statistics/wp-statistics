<?php

namespace WP_Statistics\Service\Admin\LicenseManagement;

use Exception;
use WP_Statistics;
use WP_Statistics\Service\Admin\LicenseManagement\Plugin\PluginActions;
use WP_Statistics\Service\Admin\LicenseManagement\Plugin\PluginHandler;
use WP_Statistics\Service\Admin\LicenseManagement\Plugin\PluginUpdater;

class LicenseManagementManager
{
    private $apiCommunicator;
    private $pluginHandler;
    private $handledPlugins = [];

    public function __construct()
    {
        $this->apiCommunicator = new ApiCommunicator();
        $this->pluginHandler   = new PluginHandler();

        // Initialize the necessary components
        $this->initializeMenu();
        $this->initializeActionCallbacks();
        $this->initializePluginUpdaters();

        add_action('admin_init', [$this, 'checkForPluginsWithoutLicenses']);
        add_filter('wp_statistics_enable_upgrade_to_bundle', [$this, 'removeUpgradeToBundleButton']);
    }

    /**
     * Initialize the menu item for the License Management.
     */
    private function initializeMenu()
    {
        add_filter('wp_statistics_admin_menu_list', [$this, 'addMenuItem']);
    }

    public function addMenuItem($items)
    {
        $items['plugins'] = [
            'sub'      => 'overview',
            'title'    => __('Add-Ons', 'wp-statistics'),
            'name'     => '<span class="wps-text-warning">' . __('Add-Ons', 'wp-statistics') . '</span>',
            'page_url' => 'plugins',
            'callback' => LicenseManagerPage::class,
            'priority' => 90,
            'break'    => true,
        ];
        return $items;
    }

    /**
     * Initialize AJAX callbacks for various license management actions.
     */
    private function initializeActionCallbacks()
    {
        add_filter('wp_statistics_ajax_list', [new PluginActions(), 'registerAjaxCallbacks']);
    }

    /**
     * Initialize the PluginUpdater for all stored licenses.
     */
    private function initializePluginUpdaters()
    {
        $storedLicenses = $this->apiCommunicator->getStoredLicenses();

        if (!empty($storedLicenses)) {
            foreach ($storedLicenses as $licenseData) {
                $licenseKey = $licenseData['license']->license_key;

                foreach ($licenseData['products'] as $productSlug) {
                    // Avoid duplicate handling for the same product
                    if (!in_array($productSlug, $this->handledPlugins)) {
                        $this->initializePluginUpdaterIfValid($productSlug, $licenseKey);
                    }
                }
            }
        }
    }

    /**
     * Initialize PluginUpdater for a specific product and license key.
     *
     * @param string $pluginSlug The slug of the plugin (e.g., 'wp-statistics-data-plus').
     * @param string $licenseKey The license key for the product.
     */
    private function initializePluginUpdaterIfValid($pluginSlug, $licenseKey)
    {
        try {
            if (!$this->pluginHandler->isPluginActive($pluginSlug)) {
                return;
            }

            // Get the dynamic version of the plugin
            $pluginData = $this->pluginHandler->getPluginData($pluginSlug);
            if (!$pluginData) {
                throw new Exception(sprintf(__('Plugin data not found for: %s', 'wp-statistics'), $pluginSlug));
            }

            // Initialize PluginUpdater with the version and license key
            $pluginUpdater = new PluginUpdater($pluginSlug, $pluginData['Version'], $licenseKey);
            $pluginUpdater->handle();

            $this->handledPlugins[] = $pluginSlug;

        } catch (Exception $e) {
            WP_Statistics::log(sprintf('Failed to initialize PluginUpdater for %s: %s', $pluginSlug, $e->getMessage()));
        }
    }

    /**
     * Loop through plugins and show license notice for those without a valid license
     */
    public function checkForPluginsWithoutLicenses()
    {
        $plugins = get_plugins();

        foreach ($plugins as $pluginFile => $pluginData) {
            if (strpos($pluginFile, 'wp-statistics-') === 0) {
                $licenseKey = $this->apiCommunicator->getValidLicenseForProduct($pluginData['TextDomain']);

                if (!$licenseKey) {
                    $pluginUpdater = new PluginUpdater($pluginData['TextDomain'], $pluginData['Version']);
                    $pluginUpdater->handleLicenseNotice();
                }
            }
        }
    }

    /**
     * Removes the "Upgrade to Bundle" buttons if the user has a premium license.
     *
     * @return bool
     *
     * @hooked filter: `wp_statistics_enable_upgrade_to_bundle` - 10
     */
    public function removeUpgradeToBundleButton()
    {
        return empty($this->apiCommunicator->userHasPremiumLicense());
    }
}
