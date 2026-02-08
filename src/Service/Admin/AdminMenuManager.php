<?php

namespace WP_Statistics\Service\Admin;

use WP_Statistics\Service\Admin\AccessControl\AccessLevel;
use WP_Statistics\Components\View;
use WP_Statistics\Service\Admin\PrivacyAudit\PrivacyAuditPage;
use WP_Statistics\Service\Admin\HelpCenter\HelpCenterPage;
use WP_Statistics\Service\Admin\LicenseManagement\LicenseManagerPage;

/**
 * V15 Admin Menu Manager.
 *
 * Registers v15 React-based admin menus plus legacy PHP pages:
 * - Dashboard (main menu) - React SPA
 * - Settings (submenu) - React SPA hash route
 * - Add-ons - Legacy PHP page
 * - Privacy Audit - Legacy PHP page
 * - Help Center - Legacy PHP page
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
     * Page instances.
     */
    private $privacyAuditPage;
    private $helpCenterPage;
    private $addonsPage;

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

        // Settings submenu - links to same page with hash route (no page reload)
        add_submenu_page(
            self::MENU_SLUG,
            __('Settings', 'wp-statistics'),
            __('Settings', 'wp-statistics'),
            $manageCapability,
            self::MENU_SLUG . '#/settings/general',
            null // No callback - it's just a link
        );

        // Tools submenu - links to same page with hash route (no page reload)
        add_submenu_page(
            self::MENU_SLUG,
            __('Tools', 'wp-statistics'),
            __('Tools', 'wp-statistics'),
            $manageCapability,
            self::MENU_SLUG . '#/tools/system-info',
            null // No callback - it's just a link
        );

        // Add-ons page (legacy PHP)
        $this->addonsPage = new LicenseManagerPage();
        add_submenu_page(
            self::MENU_SLUG,
            __('Add-ons', 'wp-statistics'),
            '<span class="wps-text-warning">' . __('Add-ons', 'wp-statistics') . '</span>',
            $manageCapability,
            self::getLegacySlug('plugins'),
            [$this->addonsPage, 'view']
        );

        // Privacy Audit page (legacy PHP)
        $this->privacyAuditPage = new PrivacyAuditPage();
        add_submenu_page(
            self::MENU_SLUG,
            __('Privacy Audit', 'wp-statistics'),
            __('Privacy Audit', 'wp-statistics'),
            $manageCapability,
            self::getLegacySlug('privacy-audit'),
            [$this->privacyAuditPage, 'view']
        );

        // Help Center page (legacy PHP)
        $this->helpCenterPage = new HelpCenterPage();
        add_submenu_page(
            self::MENU_SLUG,
            __('Help', 'wp-statistics'),
            __('Help', 'wp-statistics'),
            $manageCapability,
            self::getLegacySlug('help'),
            [$this->helpCenterPage, 'view']
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

            // Fix Settings URL (WordPress adds admin.php?page= prefix)
            if (strpos($item[2], '#/settings') !== false) {
                $submenu[self::MENU_SLUG][$key][2] = admin_url('admin.php?page=' . self::MENU_SLUG . '#/settings/general');
            }

            // Fix Tools URL
            if (strpos($item[2], '#/tools') !== false) {
                $submenu[self::MENU_SLUG][$key][2] = admin_url('admin.php?page=' . self::MENU_SLUG . '#/tools/system-info');
            }
        }
    }

    /**
     * Get the menu icon for admin menu.
     *
     * Uses a WordPress dashicons icon for consistent styling
     * with the admin menu's different states (inactive, hover, active).
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
