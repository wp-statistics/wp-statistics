<?php

namespace WP_Statistics\Components;

use WP_Statistics\Globals\Option;
use WP_Statistics\Utils\Request;
use WP_Statistics\Utils\User;

/**
 * Handles registration and management of WP Statistics admin menu pages.
 *
 * This class maps logical page keys to their corresponding WordPress admin slugs,
 * constructs admin URLs, determines if the current request is within a plugin page,
 * and retrieves menu configurations. It acts as a centralized utility for managing
 * the dashboard navigation structure for WP Statistics.
 *
 * @package WP_Statistics\Components
 * @since 15.0.0
 * @todo Move to dashboard bootstrap helper
 */
class Menu
{
    /**
     * Map of logical page keys to their raw slug strings.
     *
     * @var array<string,string>
     */
    public const PAGES = [
        'overview'           => 'overview',
        'exclusions'         => 'exclusions',
        'referrals'          => 'referrals',
        'optimization'       => 'optimization',
        'settings'           => 'settings',
        'plugins'            => 'plugins',
        'author-analytics'   => 'author-analytics',
        'privacy-audit'      => 'privacy-audit',
        'geographic'         => 'geographic',
        'content-analytics'  => 'content-analytics',
        'devices'            => 'devices',
        'category-analytics' => 'category-analytics',
        'pages'              => 'pages',
        'visitors'           => 'visitors',
    ];

    /** Template for assembling the final admin-page slug. */
    private const ADMIN_MENU_SLUG_TEMPLATE = 'wps_[slug]_page';

    /** Template for the "load-*" action when a top-level page loads. */
    private const LOAD_ADMIN_SLUG_TEMPLATE = 'toplevel_page_[slug]';

    /**
     * Return an associative array with the generated admin-page slugs keyed by
     * their logical name.
     *
     * @return array<string,string>
     */
    public static function getList()
    {
        $list = [];
        foreach (self::PAGES as $key => $slug) {
            $list[$key] = self::buildPageSlug($slug);
        }
        return apply_filters('wp_statistics_admin_page_list', $list);
    }

    /**
     * Build the full admin-page slug (e.g. "wps_overview_page").
     *
     * @param string $pageSlug Raw slug without prefix/suffix
     * @return string Complete admin page slug
     */
    public static function buildPageSlug(string $pageSlug)
    {
        return str_ireplace('[slug]', $pageSlug, self::ADMIN_MENU_SLUG_TEMPLATE);
    }

    /**
     * Convenience wrapper around add_query_arg() that produces the correct URL
     * to a WP-Statistics admin page.
     *
     * @param string|null $page A logical page key or a full slug.
     * @param array<string,mixed> $args Additional query-string parameters.
     * @return string Complete admin URL
     */
    public static function getAdminUrl($page = null, $args = [])
    {
        if ($page !== null && array_key_exists($page, self::getList())) {
            $page = self::buildPageSlug($page);
        }

        return add_query_arg(array_merge(['page' => $page], $args), admin_url('admin.php'));
    }

    /**
     * Check if the current request is within a given WP-Statistics admin page.
     *
     * @param string $page Page key to check for
     * @return bool True if on the specified admin page
     */
    public static function isOnPage(string $page)
    {
        global $pagenow;
        return (is_admin() && $pagenow === 'admin.php' && isset($_REQUEST['page']) && $_REQUEST['page'] === self::buildPageSlug($page));
    }

    /**
     * Check if the current admin screen belongs to any WP-Statistics page.
     *
     * @return bool True when on a WP-Statistics admin page
     */
    public static function isInPluginPage()
    {
        global $pagenow;
        if (is_admin() && $pagenow === 'admin.php' && isset($_REQUEST['page'])) {
            $pageName = self::getPageKeyFromSlug(sanitize_text_field($_REQUEST['page']));
            if (is_array($pageName) && count($pageName) > 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * Extract the logical page key from a complete admin-page slug.
     *
     * Example: "wps_hits_page" → ["hits"]
     *
     * @param string $pageSlug The full admin-page slug
     * @return array<int,string> Array with the extracted key
     */
    public static function getPageKeyFromSlug(string $pageSlug)
    {
        $adminMenuSlug = explode('[slug]', self::ADMIN_MENU_SLUG_TEMPLATE);
        preg_match('/(?<=' . $adminMenuSlug[0] . ').*?(?=' . $adminMenuSlug[1] . ')/', $pageSlug, $pageName);
        return $pageName;
    }

    /**
     * Get the default load action for any WordPress page slug.
     *
     * @param string $pageSlug Raw page slug
     * @return string Load action slug
     */
    public static function getLoadActionSlug(string $pageSlug)
    {
        return str_ireplace('[slug]', self::buildPageSlug($pageSlug), self::LOAD_ADMIN_SLUG_TEMPLATE);
    }

    /**
     * Get menu list configuration.
     *
     * Returns the configuration array for WP-Statistics admin menus including
     * titles, capabilities, methods, and other menu properties.
     *
     * @return array<string,array<string,mixed>> Menu configuration array
     */
    public static function getMenuList()
    {
        $manageCap = User::getExistingCapability(Option::getValue('manage_capability', 'manage_options'));

        /**
         * List of WP Statistics Admin Menu
         *
         * --- Array Arg -----
         * name       : Menu name
         * title      : Page title / if not exist [title == name]
         * cap        : min require capability @default $read_cap
         * icon       : WordPress DashIcon name
         * method     : method that call in page @default log
         * sub        : if sub menu , add main menu slug
         * page_url   : link of Slug Url Page @see WP_Statistics::$page
         * break      : add new line after sub menu if break key == true
         * require    : the Condition From Wp-statistics Option if == true for show admin menu
         *
         */
        $list = [
            'settings' => [
                'sub'      => 'overview',
                'title'    => __('Settings', 'wp-statistics'),
                'cap'      => $manageCap,
                'page_url' => 'settings',
                'method'   => 'settings',
                'priority' => 100,
            ],
            'optimize' => [
                'sub'      => 'overview',
                'title'    => __('Optimization', 'wp-statistics'),
                'cap'      => $manageCap,
                'page_url' => 'optimization',
                'method'   => 'optimization',
                'priority' => 110,
            ]
        ];

        /**
         * WP Statistics Admin Page List
         *
         * @example add_filter('wp_statistics_admin_menu_list', function( $list ){ unset( $list['plugins'] ); return $list; });
         */
        $list = apply_filters('wp_statistics_admin_menu_list', $list);

        // Sort submenus by priority
        uasort($list, function ($a, $b) {
            if (empty($a['priority'])) {
                $a['priority'] = 999;
            }
            if (empty($b['priority'])) {
                $b['priority'] = 999;
            }

            if ($a['priority'] == $b['priority']) {
                return 0;
            }
            return ($a['priority'] < $b['priority']) ? -1 : 1;
        });

        return $list;
    }

    /**
     * Retrieve information about the currently loaded WP-Statistics page.
     *
     * @return array<string,mixed>|false Array with the menu-list entry or false when not on an applicable page
     */
    public static function getCurrentPage()
    {
        $currentPage = Request::get('page');
        $pagesList   = self::getMenuList();

        if (!$currentPage) {
            return false;
        }

        $currentPage = self::getPageKeyFromSlug($currentPage);
        $currentPage = reset($currentPage);

        $currentPage = array_filter($pagesList, function ($page) use ($currentPage) {
            return $page['page_url'] === $currentPage;
        });

        return reset($currentPage);
    }
}