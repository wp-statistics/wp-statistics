<?php

namespace WP_Statistics\Service\Admin;

use WP_Statistics\Service\Admin\AccessControl\AccessLevel;
use WP_Statistics\Components\View;

/**
 * V15 Admin Menu Manager.
 *
 * Registers v15 React-based admin menus:
 * - Dashboard (main menu) - React SPA
 * - Settings (submenu) - React SPA hash route
 * - Tools (submenu) - React SPA hash route
 * - Upgrade to Premium (submenu) - React SPA hash route (TODO: implement React page)
 * - Help (submenu) - React SPA hash route (TODO: implement React page)
 *
 * Privacy Audit is under Tools → Privacy Audit (#/tools/privacy-audit).
 *
 * @since 15.0.0
 */
class AdminMenuManager
{
    /**
     * Main menu slug for WP Statistics.
     */
    public const MENU_SLUG = 'wp-statistics';

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
    }

    /**
     * Get legacy menu slug (wps_{page}_page format).
     *
     * @param string $page The page identifier.
     * @return string The legacy slug.
     */
    public static function getLegacySlug(string $page): string
    {
        return self::LEGACY_SLUG_PREFIX . $page . '_page';
    }

    /**
     * Register v15 admin menus.
     *
     * @return void
     */
    public function registerMenus()
    {
        $readCapability   = AccessLevel::getMinimumCapabilityForLevel(AccessLevel::OWN_CONTENT);
        $manageCapability = AccessLevel::getMinimumCapabilityForLevel(AccessLevel::MANAGE);

        // Main Dashboard menu (position 3 = between Dashboard and Posts)
        add_menu_page(
            __('Statistics', 'wp-statistics'),
            __('Statistics', 'wp-statistics'),
            $readCapability,
            self::MENU_SLUG,
            [$this, 'renderApp'],
            $this->getMenuIcon(),
            3
        );

        // Settings submenu - React SPA hash route
        add_submenu_page(
            self::MENU_SLUG,
            __('Settings', 'wp-statistics'),
            __('Settings', 'wp-statistics'),
            $manageCapability,
            self::MENU_SLUG . '#/settings/general',
            null
        );

        // Tools submenu - React SPA hash route
        add_submenu_page(
            self::MENU_SLUG,
            __('Tools', 'wp-statistics'),
            __('Tools', 'wp-statistics'),
            $manageCapability,
            self::MENU_SLUG . '#/tools/system-info',
            null
        );

        // Upgrade to Premium submenu - React SPA hash route
        // TODO: Implement React page for premium features management
        add_submenu_page(
            self::MENU_SLUG,
            __('Upgrade to Premium', 'wp-statistics'),
            sprintf('<span style="color:#F18D2A;">%s</span>', __('Upgrade to Premium', 'wp-statistics')),
            $manageCapability,
            self::MENU_SLUG . '#/premium',
            null
        );

        // Help submenu - React SPA hash route
        // TODO: Implement React page for help center
        add_submenu_page(
            self::MENU_SLUG,
            __('Help', 'wp-statistics'),
            __('Help', 'wp-statistics'),
            $manageCapability,
            self::MENU_SLUG . '#/help',
            null
        );

        // Fix submenu labels and URLs
        $this->fixSubmenuItems();
    }

    /**
     * Fix submenu labels and URLs.
     *
     * @return void
     */
    private function fixSubmenuItems()
    {
        global $submenu;

        if (!isset($submenu[self::MENU_SLUG])) {
            return;
        }

        foreach ($submenu[self::MENU_SLUG] as $key => $item) {
            // Rename first "Statistics" to "Dashboard" and add hash for SPA navigation
            if ($item[2] === self::MENU_SLUG && $item[0] === __('Statistics', 'wp-statistics')) {
                $submenu[self::MENU_SLUG][$key][0] = __('Dashboard', 'wp-statistics');
                $submenu[self::MENU_SLUG][$key][2] = admin_url('admin.php?page=' . self::MENU_SLUG . '#/overview');
            }

            // Fix hash route URLs (WordPress adds admin.php?page= prefix)
            if (strpos($item[2], '#/') !== false) {
                $hash = substr($item[2], strpos($item[2], '#/'));
                $submenu[self::MENU_SLUG][$key][2] = admin_url('admin.php?page=' . self::MENU_SLUG . $hash);
            }
        }
    }

    /**
     * Get the menu icon for admin menu.
     *
     * @return string Dashicons class name
     */
    private function getMenuIcon(): string
    {
        return 'dashicons-chart-pie';
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
        View::load(['pages/app/index'], [
            'title'    => '',
            'pageName' => self::MENU_SLUG,
        ]);
    }
}
