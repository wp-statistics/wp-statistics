<?php

namespace WP_Statistics\Service\Admin\LicenseManagement;

use WP_Statistics\Components\Assets;
use WP_Statistics\Components\RemoteRequest;
use WP_STATISTICS\Menus;
use WP_Statistics\Utils\Request;

class LicenseManagementManager
{
    public function __construct()
    {
        add_filter('wp_statistics_admin_menu_list', [$this, 'addMenuItem']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueScripts']);
        add_filter('wp_statistics_ajax_list', [$this, 'registerAjaxCallbacks']);
    }

    /**
     * Adds menu item.
     *
     * @param array $items
     *
     * @return array
     *
     * @hooked filter: `wp_statistics_admin_menu_list` - 10
     */
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
     * Enqueues admin scripts.
     *
     * @return void
     *
     * @hooked action: `admin_enqueue_scripts` - 10
     */
    public function enqueueScripts()
    {
        if (Menus::in_page('plugins')) {
            Assets::script('license-manager', 'js/license-manager.js', ['jquery'], [
                'ajaxUrl' => admin_url('admin-ajax.php?nonce=' . wp_create_nonce('wp_statistics_license_manager')),
            ], true);
        }
    }

    /**
     * Registers AJAX actions and callbacks.
     *
     * @param array $list
     *
     * @return array
     *
     * @hooked filter: `wp_statistics_ajax_list` - 10
     */
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

        return $list;
    }

    /**
     * Handles `check_license` ajax call and checks license status.
     *
     * @return void
     */
    public function check_license_action_callback()
    {
        if (!wp_verify_nonce(wp_unslash(Request::get('nonce')), 'wp_statistics_license_manager')) {
            wp_send_json_error([
                'message' => __('Access denied.', 'wp-statistics'),
            ], 200); // Return 200 HTTP code to prevent console from showing a 404 error
            exit;
        }

        $licenseKey = Request::has('license') ? wp_unslash(Request::get('license')) : '';

        $licenseValidator = new LicenseValidator();
        wp_send_json_success([
            'licenses' => $licenseValidator->validateLicense($licenseKey),
        ]);

        exit;
    }

    /**
     * Handles `download_plugin` ajax call and downloads a plugin.
     *
     * @return void
     */
    public function download_plugin_action_callback()
    {
        if (!wp_verify_nonce(wp_unslash(Request::get('nonce')), 'wp_statistics_license_manager')) {
            wp_send_json_error([
                'message' => __('Access denied.', 'wp-statistics'),
            ], 200);
            exit;
        }

        $downloadUrl = Request::has('download_url') ? wp_unslash(Request::get('download_url')) : '';
        if (empty($downloadUrl)) {
            wp_send_json_error([
                'message' => esc_html__('Invalid Download URL!', 'wp-statistics')
            ], 200);
            exit;
        }

        $request = new RemoteRequest($downloadUrl);

        $downloadedFile = $request->downloadToSite(WP_PLUGIN_DIR);
        if (is_wp_error($downloadedFile)) {
            wp_send_json_error([
                'message' => $downloadedFile->get_error_message('wp_statistics_download_url_error'),
            ], 200);
            exit;
        }

        wp_send_json_success([
            'url' => $downloadedFile,
        ]);
    }
}
