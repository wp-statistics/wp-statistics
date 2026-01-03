<?php

namespace WP_Statistics\Service\Admin\Network;

use WP_STATISTICS\Helper;
use WP_STATISTICS\Option;
use WP_STATISTICS\User;

/**
 * Network Manager for WP Statistics v15.
 *
 * Handles multisite network admin functionality including:
 * - Network admin menu
 * - Cross-site statistics overview
 * - Site navigation
 *
 * @since 15.0.0
 */
class NetworkManager
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        if (!is_multisite()) {
            return;
        }

        add_action('network_admin_menu', [$this, 'registerNetworkMenu']);
    }

    /**
     * Register network admin menu.
     *
     * @return void
     */
    public function registerNetworkMenu()
    {
        $readCap   = User::ExistCapability(Option::get('read_capability', 'manage_options'));
        $manageCap = User::ExistCapability(Option::get('manage_capability', 'manage_options'));

        // Add the top level menu
        add_menu_page(
            __('Statistics', 'wp-statistics'),
            __('Statistics', 'wp-statistics'),
            $readCap,
            'wp-statistics-network',
            [$this, 'renderOverview'],
            'dashicons-chart-pie'
        );

        // Add Overview submenu
        add_submenu_page(
            'wp-statistics-network',
            __('Overview', 'wp-statistics'),
            __('Overview', 'wp-statistics'),
            $readCap,
            'wp-statistics-network',
            [$this, 'renderOverview']
        );

        // Add submenu for each site
        $sites = Helper::get_wp_sites_list();
        foreach ($sites as $blogId) {
            $details = get_blog_details($blogId);
            if ($details) {
                add_submenu_page(
                    'wp-statistics-network',
                    $details->blogname,
                    $details->blogname,
                    $manageCap,
                    'wp_statistics_blogid_' . $blogId,
                    [$this, 'redirectToBlog']
                );
            }
        }
    }

    /**
     * Render network overview page.
     *
     * @return void
     */
    public function renderOverview()
    {
        $sites = Helper::get_wp_sites_list();
        $links = $this->getPageLinks();

        ?>
        <div class="wrap wps-wrap">
            <h1><?php esc_html_e('WP Statistics - Network Overview', 'wp-statistics'); ?></h1>

            <table class="widefat wp-list-table striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Website', 'wp-statistics'); ?></th>
                        <th><?php esc_html_e('Quick Links', 'wp-statistics'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sites as $blogId) : ?>
                        <?php $details = get_blog_details($blogId); ?>
                        <?php if ($details) : ?>
                            <tr>
                                <td>
                                    <strong><?php echo esc_html($details->blogname); ?></strong>
                                    <br>
                                    <small><?php echo esc_url($details->siteurl); ?></small>
                                </td>
                                <td>
                                    <?php echo $this->renderQuickLinks($blogId, $links); ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Get page links for quick navigation.
     *
     * @return array Page links.
     */
    private function getPageLinks()
    {
        return [
            __('Dashboard', 'wp-statistics')   => 'wps_overview_page',
            __('Settings', 'wp-statistics')    => 'wps_settings_page',
        ];
    }

    /**
     * Render quick links for a site.
     *
     * @param int   $blogId Blog ID.
     * @param array $links  Page links.
     * @return string HTML links.
     */
    private function renderQuickLinks($blogId, $links)
    {
        $baseUrl = get_admin_url($blogId, '/') . 'admin.php?page=';
        $output  = [];

        foreach ($links as $label => $page) {
            $url      = esc_url($baseUrl . $page);
            $output[] = sprintf('<a href="%s">%s</a>', $url, esc_html($label));
        }

        return implode(' | ', $output);
    }

    /**
     * Redirect to specific blog's statistics.
     *
     * @return void
     */
    public function redirectToBlog()
    {
        global $plugin_page;

        $blogId = str_replace('wp_statistics_blogid_', '', $plugin_page);
        $url    = esc_url(get_admin_url($blogId) . '/admin.php?page=wps_overview_page');

        wp_redirect($url);
        exit;
    }
}
