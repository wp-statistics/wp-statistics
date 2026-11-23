<?php

namespace WP_STATISTICS;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_Statistics\Components\DateRange;
use WP_Statistics\Components\View;
use WP_Statistics\Models\VisitorsModel;

class Network
{
    /**
     * Network constructor.
     */
    public function __construct()
    {
        /**
         * The network admin only exists on multisite. On a single site,
         * wp-admin/network/admin.php still fires network_admin_menu before it
         * stops with "Multisite support is not enabled.", and the multisite
         * functions this menu needs (get_sites()) are not loaded.
         */
        if (!is_multisite()) {
            return;
        }

        add_action('network_admin_menu', array($this, 'wp_admin_menu'));
    }

    /**
     * Transient that caches a site's key numbers on the network overview (stored per site).
     */
    const NUMBERS_TRANSIENT = 'wps_network_overview_numbers';

    /**
     * Load WordPress Network Admin Menu
     */
    public function wp_admin_menu()
    {

        // Get the read/write capabilities required to view/manage the plugin as set by the user.
        $read_cap   = User::ExistCapability(Option::get('read_capability', 'manage_options'));
        $manage_cap = User::ExistCapability(Option::get('manage_capability', 'manage_options'));

        // Add the top level menu.
        add_menu_page(__('Statistics', 'wp-statistics'), __('Statistics', 'wp-statistics'), $read_cap, WP_STATISTICS_MAIN_FILE, array($this, 'overview'), 'dashicons-chart-pie');

        // Add the sub items.
        add_submenu_page(WP_STATISTICS_MAIN_FILE, __('Overview', 'wp-statistics'), __('Overview', 'wp-statistics'), $read_cap, WP_STATISTICS_MAIN_FILE, array($this, 'overview'));

        // One sub item per site. The slug is the site's Overview URL and there is no
        // callback, so WordPress links straight to it (no redirect page in between).
        $sites = Helper::get_wp_sites_list();
        foreach ($sites as $blog_id) {
            $details = get_blog_details($blog_id);
            if (empty($details)) {
                continue;
            }

            add_submenu_page(WP_STATISTICS_MAIN_FILE, $details->blogname, $details->blogname, $manage_cap, self::getSiteUrl($blog_id, 'overview'));
        }
    }

    /**
     * Admin URL of one WP Statistics page on a site.
     *
     * @param int $blogId
     * @param string $page Page key from Menus::$pages (overview, visitors, ...).
     * @return string
     */
    public static function getSiteUrl($blogId, $page = 'overview')
    {
        return get_admin_url((int)$blogId, 'admin.php?page=' . Menus::get_page_slug($page));
    }

    /**
     * Report links shown for every site. Keys are page keys from Menus::$pages.
     *
     * @return array<string, string>
     */
    public static function getReportLinks()
    {
        return [
            'overview'          => __('Overview', 'wp-statistics'),
            'visitors'          => __('Visitors', 'wp-statistics'),
            'pages'             => __('Pages', 'wp-statistics'),
            'referrals'         => __('Referrals', 'wp-statistics'),
            'content-analytics' => __('Content', 'wp-statistics'),
            'settings'          => __('Settings', 'wp-statistics'),
        ];
    }

    /**
     * Whether WP Statistics runs on a site (network-activated or activated on that site).
     *
     * @param int $blogId
     * @return bool
     */
    private static function isActiveOnSite($blogId)
    {
        if (!function_exists('is_plugin_active_for_network')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $basename = plugin_basename(WP_STATISTICS_MAIN_FILE);

        if (is_plugin_active_for_network($basename)) {
            return true;
        }

        $active = get_blog_option($blogId, 'active_plugins', []);

        return is_array($active) && in_array($basename, $active, true);
    }

    /**
     * Collects the data for the network overview: one entry per site with its name,
     * address, key numbers for the last 7 days and links to its reports.
     *
     * @return array[]
     */
    public static function getSitesOverview()
    {
        $items = [];

        foreach (Helper::get_wp_sites_list() as $blogId) {
            $blogId  = (int)$blogId;
            $details = get_blog_details($blogId);

            if (empty($details)) {
                continue;
            }

            $active = self::isActiveOnSite($blogId);
            $item   = [
                'id'       => $blogId,
                'name'     => $details->blogname,
                'url'      => $details->home,
                'active'   => $active,
                'visitors' => null,
                'views'    => null,
                'links'    => [],
            ];

            if ($active) {
                switch_to_blog($blogId);

                $numbers = self::getSiteNumbers();
                $item['visitors'] = $numbers['visitors'];
                $item['views']    = $numbers['views'];

                foreach (self::getReportLinks() as $page => $label) {
                    $item['links'][] = [
                        'label' => $label,
                        'url'   => self::getSiteUrl($blogId, $page),
                    ];
                }

                restore_current_blog();
            }

            $items[] = $item;
        }

        return $items;
    }

    /**
     * Visitors and views of the current site for the last 7 days, cached for an hour.
     * Null values mean the numbers are not available (for example, no tables yet).
     *
     * @return array{visitors: int|null, views: int|null}
     */
    private static function getSiteNumbers()
    {
        $cached = get_transient(self::NUMBERS_TRANSIENT);
        if (is_array($cached) && array_key_exists('visitors', $cached)) {
            return $cached;
        }

        $numbers = ['visitors' => null, 'views' => null];

        try {
            if (DB::table('visitor') && DB::ExistTable(DB::table('visitor'))) {
                $date           = DateRange::get('7days');
                $visitorsModel  = new VisitorsModel();
                $numbers        = [
                    'visitors' => (int)$visitorsModel->countVisitors(['date' => $date]),
                    'views'    => (int)$visitorsModel->countHits(['date' => $date]),
                ];
            }
        } catch (\Throwable $e) {
            $numbers = ['visitors' => null, 'views' => null];
        }

        set_transient(self::NUMBERS_TRANSIENT, $numbers, HOUR_IN_SECONDS);

        return $numbers;
    }

    /**
     * Network Overview
     */
    public function overview()
    {
        View::load('pages/network/overview', ['sites' => self::getSitesOverview()]);
    }

    /**
     * Goto Network Blog
     *
     * @deprecated 14.17.0 The site menu items link to the site's Overview directly.
     */
    public function goto_blog()
    {
        global $plugin_page;
        $blog_id = (int)str_replace('wp_statistics_blogid_', '', (string)$plugin_page);
        $url     = self::getSiteUrl($blog_id, 'overview');
        echo '<script>window.location.href = ' . wp_json_encode(esc_url_raw($url)) . ';</script>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }
}

new Network;
