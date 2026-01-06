<?php

namespace WP_Statistics\Service\Admin\Network;

use WP_Statistics\Components\View;

/**
 * Network Admin Menu Manager.
 *
 * Registers network admin menus for WordPress Multisite:
 * - Main Statistics menu (redirects to main site)
 * - Submenu items for each site's statistics dashboard
 * - Network Overview page (React SPA for cross-site analytics)
 *
 * @since 15.0.0
 */
class NetworkMenuManager
{
    /**
     * Network menu slug.
     */
    public const NETWORK_MENU_SLUG = 'wp-statistics-network';

    /**
     * Constructor - registers network admin menu hooks.
     */
    public function __construct()
    {
        if (!is_multisite()) {
            return;
        }

        add_action('network_admin_menu', [$this, 'registerNetworkMenus']);
    }

    /**
     * Register network admin menus.
     *
     * @return void
     */
    public function registerNetworkMenus()
    {
        if (!is_super_admin()) {
            return;
        }

        // Main network menu
        add_menu_page(
            __('Statistics', 'wp-statistics'),
            __('Statistics', 'wp-statistics'),
            'manage_network',
            self::NETWORK_MENU_SLUG,
            [$this, 'renderNetworkOverview'],
            'dashicons-chart-pie',
            3
        );

        // Network Overview submenu (first item, same slug as parent to replace default)
        add_submenu_page(
            self::NETWORK_MENU_SLUG,
            __('Network Overview', 'wp-statistics'),
            __('Network Overview', 'wp-statistics'),
            'manage_network',
            self::NETWORK_MENU_SLUG,
            [$this, 'renderNetworkOverview']
        );
    }

    /**
     * Render the Network Overview page.
     *
     * Uses the same React app container, which will detect the network context
     * and render the network overview route.
     *
     * @return void
     */
    public function renderNetworkOverview()
    {
        View::load(['pages/network/overview'], [
            'title'    => __('Network Overview', 'wp-statistics'),
            'pageName' => self::NETWORK_MENU_SLUG,
        ]);
    }
}
