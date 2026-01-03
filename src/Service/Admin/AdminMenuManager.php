<?php

namespace WP_Statistics\Service\Admin;

use WP_Statistics\Globals\Option;
use WP_Statistics\Utils\User;
use WP_Statistics\Components\View;

/**
 * V15 Admin Menu Manager.
 *
 * Registers only the v15 React-based admin menus:
 * - Dashboard (main menu)
 * - Settings (submenu link to same page with hash route)
 *
 * Both Dashboard and Settings use the SAME React SPA.
 * Navigation is via React Router hash routes, avoiding page reloads.
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
     * Constructor - registers admin menu hooks.
     */
    public function __construct()
    {
        add_action('admin_menu', [$this, 'registerMenus']);
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
     * Render the v15 React App.
     *
     * Single entry point for the entire React SPA.
     * Routes are handled by React Router via hash.
     *
     * @return void
     */
    public function renderApp()
    {
        View::load(['pages/root/index'], [
            'title'    => '',
            'pageName' => self::MENU_SLUG,
        ]);
    }
}
