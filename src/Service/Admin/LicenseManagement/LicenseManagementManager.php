<?php

namespace WP_Statistics\Service\Admin\LicenseManagement;

use WP_Statistics\Utils\Request;
use Exception;

class LicenseManagementManager
{
    private $licenseService;
    private $pluginHandler;

    public function __construct()
    {
        $this->licenseService  = new LicenseManagementService();
        $this->pluginHandler   = new PluginHandler();

        add_filter('wp_statistics_admin_menu_list', [$this, 'addMenuItem']);
        add_filter('wp_statistics_ajax_list', [$this, 'registerAjaxCallbacks']);
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
                throw new Exception('License key is missing.');
            }

            // Merge the product list with the license and installation status
            $mergedProductList = $this->licenseService->mergeProductStatusWithLicense($licenseKey);

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
                throw new Exception('Plugin slug is missing.');
            }

            if (empty($licenseKey)) {
                $licenseKey = $this->licenseService->getValidLicenseForProduct($pluginSlug);
            }

            if (empty($licenseKey)) {
                throw new Exception('License key is missing.');
            }

            $downloadUrl = $this->licenseService->getPluginDownloadUrl($licenseKey, $pluginSlug);
            if (!$downloadUrl) {
                throw new Exception('Download URL not found!');
            }

            // Download and install the plugin
            $this->pluginHandler->downloadAndInstallPlugin($downloadUrl);

            wp_send_json_success([
                'message' => 'Plugin downloaded and installed successfully.',
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
                throw new \Exception(__('Plugin slug missing.', 'wp-statistics'));
            }

            wp_send_json_success([
                'active' => $this->pluginHandler->isPluginActive($pluginSlug),
                'data'   => $this->pluginHandler->getPluginData($pluginSlug),
            ]);
        } catch (\Exception $e) {
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
                throw new Exception('Plugin slug is missing.');
            }

            $this->pluginHandler->activatePlugin($pluginSlug);

            wp_send_json_success([
                'message' => 'Plugin activated successfully.',
            ]);
        } catch (Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage(),
            ]);
        }

        exit;
    }
}
