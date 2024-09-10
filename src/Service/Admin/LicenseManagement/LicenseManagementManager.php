<?php

namespace WP_Statistics\Service\Admin\LicenseManagement;

use WP_Statistics\Components\Assets;
use WP_STATISTICS\Menus;

class LicenseManagementManager
{
    public function __construct()
    {
        add_filter('wp_statistics_admin_menu_list', [$this, 'addMenuItem']);

        add_action('admin_enqueue_scripts', [$this, 'enqueueScripts']);

        add_action('wp_ajax_wp_statistics_check_license', [$this, 'checkLicenseAjaxCallback']);
        add_action('wp_ajax_nopriv_wp_statistics_check_license', [$this, 'checkLicenseAjaxCallback']);
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
     * Handles `check_license` ajax call and checks license status.
     *
     * @return void
     *
     * @hooked action: `wp_ajax_wp_statistics_check_license` - 10
     * @hooked action: `wp_ajax_nopriv_wp_statistics_check_license` - 10
     */
    public function checkLicenseAjaxCallback()
    {
        try {
            if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['nonce'])), 'wp_statistics_license_manager')) {
                throw new \Exception(__('Access denied.', 'wp-statistics'));
            }
            $licenseKey = isset($_GET['license']) ? sanitize_text_field(wp_unslash($_GET['nonce'])) : '';

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
