<?php

namespace WP_Statistics\Service\Admin;

use WP_Statistics\Components\Option;
use WP_Statistics\Utils\User;
use WP_Statistics\Components\View;
use WP_STATISTICS\Menus;

/**
 * V15 Admin Menu Manager.
 *
 * Registers v15 React-based admin menus plus legacy PHP pages:
 * - Dashboard (main menu) - React SPA
 * - Settings (submenu) - React SPA hash route
 * - Privacy Audit - Legacy PHP page
 * - Help Center - Legacy PHP page
 * - Add-ons - Legacy PHP page (via LicenseManagementManager)
 *
 * @since 15.0.0
 */
class AdminMenuManager
{
    /**
     * Main menu slug for WP Statistics.
     */
    private const MENU_SLUG = 'wp-statistics';

    /**
     * Legacy menu slug prefix for v14-style pages.
     */
    private const LEGACY_SLUG_PREFIX = 'wps_';

    /**
     * Constructor - registers admin menu hooks.
     */
    public function __construct()
    {
        add_action('admin_menu', [$this, 'registerMenus']);
        add_filter('wp_statistics_admin_menu_list', [$this, 'registerLegacyPages']);
    }

    /**
     * Register v15 admin menus.
     *
     * @return void
     */
    public function registerMenus()
    {
        $readCapability = User::getExistingCapability(Option::getValue('read_capability', 'manage_options'));
        $manageCapability = User::getExistingCapability(Option::getValue('manage_capability', 'manage_options'));

        // Main Dashboard menu (position 3 = between Dashboard and Posts)
        add_menu_page(
            __('Statistics', 'wp-statistics'),
            __('Statistics', 'wp-statistics'),
            $readCapability,
            self::MENU_SLUG,
            [$this, 'renderApp'],
            'dashicons-chart-pie',
            3
        );

        // Settings submenu - links to same page with hash route (no page reload)
        add_submenu_page(
            self::MENU_SLUG,
            __('Settings', 'wp-statistics'),
            __('Settings', 'wp-statistics'),
            $manageCapability,
            self::MENU_SLUG . '#/settings/general',
            null // No callback - it's just a link
        );

        // Register legacy pages via Menus class (Add-ons, Privacy Audit, Help, etc.)
        $this->registerLegacyMenus();

        // Fix submenu labels and URLs
        global $submenu;
        if (isset($submenu[self::MENU_SLUG])) {
            foreach ($submenu[self::MENU_SLUG] as $key => $item) {
                // Rename first "Statistics" to "Dashboard" and add hash for SPA navigation
                if ($item[2] === self::MENU_SLUG && $item[0] === __('Statistics', 'wp-statistics')) {
                    $submenu[self::MENU_SLUG][$key][0] = __('Dashboard', 'wp-statistics');
                    $submenu[self::MENU_SLUG][$key][2] = admin_url('admin.php?page=' . self::MENU_SLUG . '#/overview');
                }
                // Fix Settings URL (WordPress adds admin.php?page= prefix)
                if (strpos($item[2], '#/settings') !== false) {
                    $submenu[self::MENU_SLUG][$key][2] = admin_url('admin.php?page=' . self::MENU_SLUG . '#/settings/general');
                }
            }
        }
    }

    /**
     * Register legacy pages (Privacy Audit, Help Center).
     *
     * These pages are added via the wp_statistics_admin_menu_list filter
     * which is used by the legacy Menus class.
     *
     * @param array $items Existing menu items.
     * @return array Modified menu items.
     */
    public function registerLegacyPages($items)
    {
        $manageCapability = User::getExistingCapability(Option::getValue('manage_capability', 'manage_options'));

        // Privacy Audit page
        $items['privacy-audit'] = [
            'sub'      => 'overview',
            'title'    => __('Privacy Audit', 'wp-statistics'),
            'page_url' => 'privacy-audit',
            'callback' => PrivacyAudit\PrivacyAuditPage::class,
            'cap'      => $manageCapability,
            'priority' => 95
        ];

        // Help Center page
        $items['help'] = [
            'sub'      => 'overview',
            'title'    => __('Help', 'wp-statistics'),
            'page_url' => 'help',
            'callback' => HelpCenter\HelpCenterPage::class,
            'cap'      => $manageCapability,
            'priority' => 120
        ];

        return $items;
    }

    /**
     * Register legacy menus using the Menus class.
     *
     * This enables pages registered via wp_statistics_admin_menu_list filter.
     *
     * @return void
     */
    private function registerLegacyMenus()
    {
        if (class_exists(Menus::class)) {
            $menus = new Menus();
            $menus->wp_admin_menu();
        }
    }

    /**
     * Render the v15 React App.
     *
     * Single entry point for the entire React SPA.
     * Routes are handled by React Router via hash.
     *
     * @return void
     */
    public function renderApp()
    {
        View::load(['pages/dashboard/index'], [
            'title'    => '',
            'pageName' => self::MENU_SLUG,
        ]);
    }
}
