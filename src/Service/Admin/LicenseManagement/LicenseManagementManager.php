<?php

namespace WP_Statistics\Service\Admin\LicenseManagement;

use Exception;
use WP_Statistics;
use WP_Statistics\Service\Admin\LicenseManagement\Plugin\PluginHandler;
use WP_Statistics\Service\Admin\LicenseManagement\Plugin\PluginUpdater;
use WP_Statistics\Utils\Request;

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
        $this->initializeAjaxCallbacks();
        $this->initializePluginUpdaters();
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
    private function initializeAjaxCallbacks()
    {
        add_filter('wp_statistics_ajax_list', [$this, 'registerAjaxCallbacks']);
    }

    public function registerAjaxCallbacks($list)
    {
        $list[] = [
            'class'  => $this,
            'action' => 'check_license',
        ];
        $list[] = [
            'class'  => $this,
            'action' => 'download_plugin',
        ];
        $list[] = [
            'class'  => $this,
            'action' => 'check_plugin',
        ];
        $list[] = [
            'class'  => $this,
            'action' => 'activate_plugin',
        ];
        return $list;
    }

    public function check_license_action_callback()
    {
        check_ajax_referer('wp_rest', 'wps_nonce');

        try {
            $licenseKey = Request::has('license_key') ? wp_unslash(Request::get('license_key')) : false;

            if (!$licenseKey) {
                throw new Exception(__('License key is missing.', 'wp-statistics'));
            }

            // Merge the product list with the license and installation status
            $mergedProductList = $this->apiCommunicator->mergeProductStatusWithLicense($licenseKey);

            wp_send_json_success([
                'products' => $mergedProductList,
                'message'  => __('License is valid.', 'wp-statistics'),
            ]);
        } catch (Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage(),
            ]);
        }

        exit;
    }

    public function download_plugin_action_callback()
    {
        check_ajax_referer('wp_rest', 'wps_nonce');

        try {
            $licenseKey = Request::has('license_key') ? wp_unslash(Request::get('license_key')) : false;
            $pluginSlug = Request::has('plugin_slug') ? wp_unslash(Request::get('plugin_slug')) : false;

            if (!$pluginSlug) {
                throw new Exception(__('Plugin slug is missing.', 'wp-statistics'));
            }

            if (empty($licenseKey)) {
                $licenseKey = $this->apiCommunicator->getValidLicenseForProduct($pluginSlug);
            }

            if (empty($licenseKey)) {
                throw new Exception(__('License key is missing.', 'wp-statistics'));
            }

            $downloadUrl = $this->apiCommunicator->getPluginDownloadUrl($licenseKey, $pluginSlug);
            if (!$downloadUrl) {
                throw new Exception(__('Download URL not found!', 'wp-statistics'));
            }

            // Download and install the plugin
            $this->pluginHandler->downloadAndInstallPlugin($downloadUrl);

            wp_send_json_success([
                'message' => __('Plugin downloaded and installed successfully.', 'wp-statistics'),
            ]);
        } catch (Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage(),
            ]);
        }

        exit;
    }

    /**
     * Handles `check_plugin` ajax call and returns info about a local plugin.
     *
     * @return void
     */
    public function check_plugin_action_callback()
    {
        check_ajax_referer('wp_rest', 'wps_nonce');

        try {
            $pluginSlug = Request::has('plugin_slug') ? wp_unslash(Request::get('plugin_slug')) : false;
            if (!$pluginSlug) {
                throw new Exception(__('Plugin slug is missing.', 'wp-statistics'));
            }

            wp_send_json_success([
                'active' => $this->pluginHandler->isPluginActive($pluginSlug),
                'data'   => $this->pluginHandler->getPluginData($pluginSlug),
            ]);
        } catch (Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage(),
            ]);
        }

        exit;
    }

    public function activate_plugin_action_callback()
    {
        check_ajax_referer('wp_rest', 'wps_nonce');

        try {
            $pluginSlug = Request::has('plugin_slug') ? wp_unslash(Request::get('plugin_slug')) : false;

            if (!$pluginSlug) {
                throw new Exception(__('Plugin slug is missing.', 'wp-statistics'));
            }

            $this->pluginHandler->activatePlugin($pluginSlug);

            wp_send_json_success([
                'message' => __('Plugin activated successfully.', 'wp-statistics'),
            ]);
        } catch (Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage(),
            ]);
        }

        exit;
    }

    /**
     * Initialize the PluginUpdater for all stored licenses.
     */
    private function initializePluginUpdaters()
    {
        // Get all stored licenses
        $storedLicenses = $this->apiCommunicator->getStoredLicenses();

        if (!empty($storedLicenses)) {
            // Loop through each stored license
            foreach ($storedLicenses as $licenseData) {
                $licenseKey = $licenseData['license']->license_key;

                // Loop through each associated product for this license
                foreach ($licenseData['products'] as $productSlug) {
                    try {
                        if (!$this->pluginHandler->isPluginActive($productSlug)) {
                            continue;
                        }
                    } catch (Exception $e) {
                        // Plugin is not installed
                        continue;
                    }

                    // Avoid duplicate handling for the same product
                    if (!in_array($productSlug, $this->handledPlugins)) {
                        $this->initializePluginUpdater($productSlug, $licenseKey);
                        $this->handledPlugins[] = $productSlug;
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
    private function initializePluginUpdater($pluginSlug, $licenseKey)
    {
        try {
            // Get the dynamic version of the plugin
            $pluginData = $this->pluginHandler->getPluginData($pluginSlug);

            if (!$pluginData) {
                throw new Exception(sprintf(__('Plugin data not found for: %s', 'wp-statistics'), $pluginSlug));
            }

            // Initialize PluginUpdater with the version and license key
            $pluginUpdater = new PluginUpdater($pluginSlug, $pluginData['Version'], $licenseKey);
            $pluginUpdater->handle();

        } catch (Exception $e) {
            WP_Statistics::log(sprintf('Failed to initialize PluginUpdater for %s: %s', $pluginSlug, $e->getMessage()));
        }
    }
}
