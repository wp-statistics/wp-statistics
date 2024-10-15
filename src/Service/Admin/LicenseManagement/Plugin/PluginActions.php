<?php
namespace WP_Statistics\Service\Admin\LicenseManagement\Plugin;

use WP_Statistics\Utils\Request;
use WP_Statistics\Service\Admin\LicenseManagement\ApiCommunicator;
use WP_Statistics\Service\Admin\LicenseManagement\Plugin\PluginHandler;

class PluginActions
{
    private $apiCommunicator;
    private $pluginHandler;

    public function __construct()
    {
        $this->apiCommunicator = new ApiCommunicator();
        $this->pluginHandler   = new PluginHandler();
    }

    public function registerAjaxCallbacks($list)
    {
        $list[] = [
            'class'     => $this,
            'action'    => 'check_license'
        ];
        $list[] = [
            'class'     => $this,
            'action'    => 'download_plugin'
        ];
        $list[] = [
            'class'     => $this,
            'action'    => 'check_plugin'
        ];
        $list[] = [
            'class'     => $this,
            'action'    => 'activate_plugin'
        ];

        return $list;
    }

    public function check_license_action_callback()
    {
        check_ajax_referer('wp_rest', 'wps_nonce');

        try {
            $licenseKey = Request::has('license_key') ? wp_unslash(Request::get('license_key')) : false;

            if (!$licenseKey) {
                throw new \Exception(__('License key is missing.', 'wp-statistics'));
            }

            $purchasedPlugins = $this->apiCommunicator->getPurchasedPlugins($licenseKey);

            wp_send_json_success([
                'plugins'   => $purchasedPlugins,
                'message'   => __('License is valid.', 'wp-statistics'),
            ]);
        } catch (\Exception $e) {
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
                throw new \Exception(__('Plugin slug is missing.', 'wp-statistics'));
            }

            if (empty($licenseKey)) {
                $licenseKey = $this->apiCommunicator->getValidLicenseForProduct($pluginSlug);
            }

            if (empty($licenseKey)) {
                throw new \Exception(__('License key is missing.', 'wp-statistics'));
            }

            $downloadUrl = $this->apiCommunicator->getPluginDownloadUrl($licenseKey, $pluginSlug);
            if (!$downloadUrl) {
                throw new \Exception(__('Download URL not found!', 'wp-statistics'));
            }

            // Download and install the plugin
            $this->pluginHandler->downloadAndInstallPlugin($downloadUrl);

            wp_send_json_success([
                'message' => __('Plugin downloaded and installed successfully.', 'wp-statistics'),
            ]);
        } catch (\Exception $e) {
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
                throw new \Exception(__('Plugin slug is missing.', 'wp-statistics'));
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
                throw new \Exception(__('Plugin slug is missing.', 'wp-statistics'));
            }

            $this->pluginHandler->activatePlugin($pluginSlug);

            wp_send_json_success([
                'message' => __('Plugin activated successfully.', 'wp-statistics'),
            ]);
        } catch (\Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage(),
            ]);
        }

        exit;
    }
}