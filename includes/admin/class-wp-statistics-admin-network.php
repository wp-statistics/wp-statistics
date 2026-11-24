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
        add_action('admin_enqueue_scripts', array($this, 'enqueueAssets'));
    }

    /**
     * Sorting, search and pages for the site table (the table works without it: all rows show).
     *
     * @param string $hook
     */
    public function enqueueAssets($hook)
    {
        if (!is_network_admin() || strpos((string)$hook, 'wp-statistics') === false) {
            return;
        }

        wp_enqueue_script('wp-statistics-network-overview', Admin_Assets::url('network-overview.min.js'), [], Admin_Assets::version(), true);
        wp_localize_script('wp-statistics-network-overview', 'wpsNetworkOverview', [
            /* translators: 1: first row number, 2: last row number, 3: number of sites. */
            'range'   => __('%1$s–%2$s of %3$s sites', 'wp-statistics'),
            /* translators: %s: number of sites. */
            'count'   => __('%s sites', 'wp-statistics'),
            'one'     => __('1 site', 'wp-statistics'),
            'noMatch' => __('No sites match your search.', 'wp-statistics'),
            'perPage' => 10,
        ]);
    }

    /**
     * Transient that caches a site's row numbers on the network overview (stored per site).
     */
    const NUMBERS_TRANSIENT = 'wps_network_overview_site';

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
     * Most sites the network overview loads. Above this the page lists the first ones only
     * (sorting, search and pages run in the browser over the loaded rows).
     */
    const MAX_SITES = 500;

    /**
     * Collects the data for the network overview: one row per site with its name, address,
     * visitors and views for the last 7 days, the change against the 7 days before, daily
     * views for the mini line and the most viewed page. Numbers come from a per-site cache.
     *
     * @return array[]
     */
    public static function getSitesOverview()
    {
        $items = [];

        foreach (self::getSiteIds() as $blogId) {
            $blogId  = (int)$blogId;
            $details = get_blog_details($blogId);

            if (empty($details)) {
                continue;
            }

            $active = self::isActiveOnSite($blogId);
            $item   = [
                'id'             => $blogId,
                'name'           => (string)$details->blogname,
                'url'            => (string)$details->home,
                'active'         => $active,
                'visitors'       => null,
                'views'          => null,
                'prev_visitors'  => null,
                'change'         => null,
                'daily'          => [],
                'top_page'       => null,
                'overview_url'   => '',
                'activate_url'   => get_admin_url($blogId, 'plugins.php'),
                'links'          => [],
            ];

            if ($active) {
                switch_to_blog($blogId);

                $item = array_merge($item, self::getSiteNumbers());

                $item['overview_url'] = self::getSiteUrl($blogId, 'overview');
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

        /**
         * Rows of the network overview table, one per site.
         *
         * @param array[] $items
         */
        return (array)apply_filters('wp_statistics_network_overview_sites', $items);
    }

    /**
     * Network totals over all rows: visitors, views and visitors of the 7 days before.
     *
     * @param array[] $sites Rows from getSitesOverview().
     * @return array{visitors: int, views: int, prev_visitors: int, change: float|null}
     */
    public static function getTotals($sites)
    {
        $totals = ['visitors' => 0, 'views' => 0, 'prev_visitors' => 0];

        foreach ($sites as $site) {
            foreach (array_keys($totals) as $key) {
                $totals[$key] += isset($site[$key]) ? (int)$site[$key] : 0;
            }
        }

        $totals['change'] = self::getChange($totals['visitors'], $totals['prev_visitors']);

        return $totals;
    }

    /**
     * Percent change from the previous period. Null when there is nothing to compare with
     * (no visitors before), so the page shows "New" or nothing instead of a made-up 100%.
     *
     * @param int|null $current
     * @param int|null $previous
     * @return float|null
     */
    public static function getChange($current, $previous)
    {
        if ($current === null || $previous === null || (int)$previous === 0) {
            return null;
        }

        return round(((int)$current - (int)$previous) / (int)$previous * 100, 1);
    }

    /**
     * IDs of the network's sites (not deleted), at most MAX_SITES.
     *
     * @return int[]
     */
    private static function getSiteIds()
    {
        if (!is_multisite()) {
            return [get_current_blog_id()];
        }

        $limit = (int)apply_filters('wp_statistics_network_overview_max_sites', self::MAX_SITES);

        return array_map('intval', get_sites([
            'fields'  => 'ids',
            'number'  => max(1, $limit),
            'deleted' => 0,
            'orderby' => 'id',
        ]));
    }

    /**
     * Numbers of the current site (call it inside switch_to_blog()), cached for an hour.
     * Visitors and views are null when the plugin's tables are missing.
     *
     * @return array{visitors: int|null, views: int|null, prev_visitors: int|null, change: float|null, daily: int[], top_page: array|null}
     */
    public static function getSiteNumbers()
    {
        $cached = get_transient(self::NUMBERS_TRANSIENT);
        if (is_array($cached) && array_key_exists('daily', $cached)) {
            return $cached;
        }

        $numbers = [
            'visitors'      => null,
            'views'         => null,
            'prev_visitors' => null,
            'change'        => null,
            'daily'         => [],
            'top_page'      => null,
        ];

        try {
            $visitorTable = DB::table('visitor');

            if ($visitorTable && DB::ExistTable($visitorTable)) {
                $date          = DateRange::get('7days');
                $prevDate      = DateRange::getPrevPeriod('7days');
                $visitorsModel = new VisitorsModel();

                $numbers['visitors']      = (int)$visitorsModel->countVisitors(['date' => $date]);
                $numbers['views']         = (int)$visitorsModel->countHits(['date' => $date]);
                $numbers['prev_visitors'] = (int)$visitorsModel->countVisitors(['date' => $prevDate]);
                $numbers['change']        = self::getChange($numbers['visitors'], $numbers['prev_visitors']);
                $numbers['daily']         = self::getDailyViews($date);
                $numbers['top_page']      = $numbers['views'] > 0 ? self::getTopPage($date) : null;
            }
        } catch (\Throwable $e) {
            $numbers['visitors'] = $numbers['views'] = $numbers['prev_visitors'] = $numbers['change'] = $numbers['top_page'] = null;
            $numbers['daily']    = [];
        }

        set_transient(self::NUMBERS_TRANSIENT, $numbers, HOUR_IN_SECONDS);

        return $numbers;
    }

    /**
     * Views per day of the current site, one value per day of the range (0 for days without visits).
     *
     * @param array{from: string, to: string} $date
     * @return int[]
     */
    private static function getDailyViews($date)
    {
        global $wpdb;

        $rows = $wpdb->get_results($wpdb->prepare(
            'SELECT `last_counter` AS `day`, SUM(`hits`) AS `views` FROM `' . DB::table('visitor') . '` WHERE `last_counter` BETWEEN %s AND %s GROUP BY `last_counter`', // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
            $date['from'],
            $date['to']
        ), ARRAY_A);

        $byDay = [];
        foreach ((array)$rows as $row) {
            $byDay[substr((string)$row['day'], 0, 10)] = (int)$row['views'];
        }

        $daily = [];
        $day   = strtotime($date['from'] . ' 00:00:00 UTC');
        $last  = strtotime($date['to'] . ' 00:00:00 UTC');

        for ($i = 0; $day <= $last && $i < 31; $i++, $day += DAY_IN_SECONDS) {
            $key     = gmdate('Y-m-d', $day);
            $daily[] = $byDay[$key] ?? 0;
        }

        return $daily;
    }

    /**
     * Most viewed page of the current site in the range.
     *
     * @param array{from: string, to: string} $date
     * @return array{title: string, url: string, views: int}|null
     */
    private static function getTopPage($date)
    {
        global $wpdb;

        $pagesTable = DB::table('pages');
        if (!$pagesTable || !DB::ExistTable($pagesTable)) {
            return null;
        }

        $row = $wpdb->get_row($wpdb->prepare(
            'SELECT `id`, `type`, `uri`, SUM(`count`) AS `views` FROM `' . $pagesTable . '` WHERE `date` BETWEEN %s AND %s GROUP BY `id`, `type`, `uri` ORDER BY `views` DESC LIMIT 1', // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
            $date['from'],
            $date['to']
        ), ARRAY_A);

        if (empty($row)) {
            return null;
        }

        $uri   = (string)$row['uri'];
        $title = '';
        $link  = '';

        if ($row['type'] === 'home') {
            $title = __('Home', 'wp-statistics');
        } elseif ((int)$row['id'] > 0) {
            $info  = Pages::get_page_info((int)$row['id'], (string)$row['type'], $uri);
            $title = isset($info['title']) ? (string)$info['title'] : '';
            $link  = isset($info['link']) && is_string($info['link']) && wp_http_validate_url($info['link']) ? $info['link'] : '';
        }

        // The stored URI is the full path (with the site's folder on subfolder networks),
        // so it goes after the host, not after home_url().
        if ($link === '') {
            $home = wp_parse_url(home_url());
            $link = (isset($home['scheme']) ? $home['scheme'] : 'http') . '://' . (isset($home['host']) ? $home['host'] : '') . (isset($home['port']) ? ':' . $home['port'] : '') . '/' . ltrim($uri, '/');
        }

        $title = trim(html_entity_decode(wp_strip_all_tags($title), ENT_QUOTES, get_bloginfo('charset')));
        if ($title === '' || $title === '-') {
            $title = $uri !== '' ? $uri : '/';
        }

        return [
            'title' => $title,
            'url'   => $link,
            'views' => (int)$row['views'],
        ];
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
