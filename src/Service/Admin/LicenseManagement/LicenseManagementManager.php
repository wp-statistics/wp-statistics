<?php

namespace WP_Statistics\Service\Admin\LicenseManagement;

use WP_Statistics\Components\Assets;
use WP_STATISTICS\Menus;
use WP_Statistics\Utils\Request;
use Exception;

class LicenseManagementManager
{
    private $licenseService;
    private $pluginInstaller;

    public function __construct(LicenseManagementService $licenseService, PluginInstaller $pluginInstaller)
    {
        $this->licenseService  = $licenseService;
        $this->pluginInstaller = $pluginInstaller;

        add_filter('wp_statistics_admin_menu_list', [$this, 'addMenuItem']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueScripts']);
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

    public function enqueueScripts()
    {
        if (Menus::in_page('plugins')) {
            Assets::script('license-manager', 'js/license-manager.js', ['jquery'], [
                'ajaxUrl' => admin_url('admin-ajax.php?nonce=' . wp_create_nonce('wp_statistics_license_manager')),
            ], true);
        }
    }

    public function registerAjaxCallbacks($list)
    {
        $list[] = [
            'class'  => $this,
            'action' => 'check_license',
        ];
        $list[] = [
            'class'  => $this,
            'action' => 'download_plugins_bulk',
        ];
        $list[] = [
            'class'  => $this,
            'action' => 'activate_plugin',
        ];
        return $list;
    }

    public function check_license()
    {
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

    public function download_plugins_bulk()
    {
        try {
            $licenseKey  = Request::has('license_key') ? wp_unslash(Request::get('license_key')) : false;
            $pluginSlugs = Request::has('plugin_slugs') ? wp_unslash(Request::get('plugin_slugs')) : false; // Array of selected plugin slugs

            if (!$licenseKey || !$pluginSlugs || !is_array($pluginSlugs)) {
                throw new Exception('License key or selected plugins are missing.');
            }

            foreach ($pluginSlugs as $pluginSlug) {
                // Get the download URL for each plugin slug
                $downloadUrl = $this->licenseService->getPluginDownloadUrl($licenseKey, $pluginSlug);

                if (!$downloadUrl) {
                    throw new Exception('Download URL not found for plugin: ' . $pluginSlug);
                }

                // Install the plugin
                $this->pluginInstaller->installPlugin($downloadUrl);
            }

            wp_send_json_success([
                'message' => 'Selected plugins downloaded successfully.',
            ]);

        } catch (Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage(),
            ]);
        }

        exit;
    }

    public function activate_plugin()
    {
        try {
            $pluginSlug = Request::has('plugin_slug') ? wp_unslash(Request::get('plugin_slug')) : false;

            if (!$pluginSlug) {
                throw new Exception('Plugin slug is missing.');
            }

            $this->pluginInstaller->activatePlugin($pluginSlug);

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
