<?php

namespace WP_Statistics\Service\Admin\LicenseManagement;

use WP_Statistics\Components\Assets;
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
        $items['license_manager'] = [
            'sub'      => 'overview',
            'title'    => esc_html__('License Manager', 'wp-statistics'),
            'name'     => '<span class="wps-text-warning">' . esc_html__('License Manager', 'wp-statistics') . '</span>',
            'page_url' => 'license_manager',
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
        if (Menus::in_page('license_manager')) {
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

        return $list;
    }

    /**
     * Handles `check_license` ajax call and checks license status.
     *
     * @return void
     */
    public function check_license_action_callback()
    {
        try {
            if (!wp_verify_nonce(wp_unslash(Request::get('nonce')), 'wp_statistics_license_manager')) {
                throw new \Exception(__('Access denied.', 'wp-statistics'));
            }
            $licenseKey = Request::has('license') ? wp_unslash(Request::get('license')) : '';

            $licenseValidator = new LicenseValidator();
            wp_send_json_success([
                'licenses' => $licenseValidator->validateLicense($licenseKey),
            ]);
        } catch (\Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage()
            ], 200); // Return 200 HTTP code to prevent console from showing a 404 error
        }
    }
}
