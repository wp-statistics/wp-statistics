<?php

namespace WP_Statistics\Utils;

use WP_STATISTICS\Helper;
use WP_STATISTICS\Menus;

/**
 * Utility class for WordPress page detection and information.
 *
 * Provides methods to detect the current page type, get page information,
 * and convert URIs to post IDs.
 *
 * @package WP_Statistics\Utils
 * @since 15.0.0
 */
class Page
{
    /**
     * Get WordPress Page Type.
     *
     * Detects the current page type based on WordPress conditional functions.
     *
     * @return array {
     *     Page type information.
     *     @type string $type         The page type (e.g., 'post', 'page', 'home', 'category').
     *     @type int    $id           The page/post/term ID.
     *     @type string $search_query The search query if on search page.
     * }
     */
    public static function getType()
    {
        // Set Default Option
        $currentPage = ['type' => 'unknown', 'id' => 0, 'search_query' => ''];

        // Check Query object
        $id = get_queried_object_id();
        if (is_numeric($id) && $id > 0) {
            $currentPage['id'] = $id;
        }

        // WooCommerce Product
        if (class_exists('WooCommerce')) {
            if (function_exists('is_product') && is_product()) {
                return wp_parse_args(['type' => 'product'], $currentPage);
            }
            if (function_exists('is_shop') && is_shop()) {
                $shopPageID        = wc_get_page_id('shop');
                $currentPage['id'] = $shopPageID;
                return wp_parse_args(['type' => 'page'], $currentPage);
            }
        }

        // Home Page or Front Page
        if (is_front_page() || is_home()) {
            return wp_parse_args(['type' => 'home'], $currentPage);
        }

        // Attachment View
        if (is_attachment()) {
            $currentPage['type'] = 'attachment';
        }

        // Is Archive Page
        if (is_archive()) {
            $currentPage['type'] = 'archive';
        }

        // Single Post From All Post Type
        if (is_singular()) {
            $postType = get_post_type();
            if ($postType !== 'post') {
                $postType = 'post_type_' . $postType;
            }
            $currentPage['type'] = $postType;
        }

        // Single Page
        if (is_page()) {
            $currentPage['type'] = 'page';
        }

        // Category Page
        if (is_category()) {
            $currentPage['type'] = 'category';
        }

        // Tag Page
        if (is_tag()) {
            $currentPage['type'] = 'post_tag';
        }

        // Is Custom Term From Taxonomy
        if (is_tax()) {
            $term                = get_queried_object();
            $currentPage['type'] = 'tax_' . $term->taxonomy;
        }

        // Is Author Page
        if (is_author()) {
            $currentPage['type'] = 'author';
        }

        // Is search page
        $searchQuery = sanitize_text_field(get_search_query(false));
        if (trim($searchQuery) !== '') {
            return ['type' => 'search', 'id' => 0, 'search_query' => $searchQuery];
        }

        // Is 404 Page
        if (is_404()) {
            $currentPage['type'] = '404';
        }

        // Add WordPress Feed
        if (is_feed()) {
            $currentPage['type'] = 'feed';
        }

        // Add WordPress Login Page
        if (Helper::is_login_page()) {
            $currentPage['type'] = 'loginpage';
        }

        return apply_filters('wp_statistics_current_page', $currentPage);
    }

    /**
     * Get current page URI.
     *
     * @return string The current page URI.
     * @see Uri::get() This is an alias for Uri::get().
     */
    public static function getUri()
    {
        return Uri::get();
    }

    /**
     * Get page information by ID and type.
     *
     * @param int         $pageId The page/post/term ID.
     * @param string      $type   The page type (e.g., 'post', 'page', 'category').
     * @param string|bool $slug   Optional URI/slug for context.
     * @return array {
     *     Page information.
     *     @type string $link      The page permalink.
     *     @type string $edit_link The edit link for the page.
     *     @type int    $object_id The page ID.
     *     @type string $title     The page title.
     *     @type string $report    The WP Statistics report URL.
     *     @type array  $meta      Additional metadata.
     * }
     */
    public static function getInfo($pageId, $type = 'post', $slug = false)
    {
        $arg      = [];
        $defaults = [
            'link'      => $slug,
            'edit_link' => '',
            'object_id' => $pageId,
            'title'     => '-',
            'report'    => Menus::admin_url('content-analytics', ['type' => 'single-resource', 'uri' => rawurlencode($slug)]),
            'meta'      => []
        ];

        if (strpos($type, 'tax_') === 0) {
            $type = 'tax';
        }

        if (!empty($type)) {
            switch ($type) {
                case 'product':
                case 'attachment':
                case 'post':
                case 'page':
                    $arg = [
                        'title'     => get_the_title($pageId),
                        'link'      => get_the_permalink($pageId),
                        'edit_link' => get_edit_post_link($pageId),
                        'report'    => Menus::admin_url('content-analytics', ['type' => 'single', 'post_id' => $pageId]),
                        'meta'      => [
                            'post_type' => get_post_type($pageId)
                        ]
                    ];
                    break;

                case 'category':
                case 'post_tag':
                case 'tax':
                    $term = get_term($pageId);
                    if (!is_wp_error($term) && $term !== null) {
                        $arg = [
                            'title'     => esc_html($term->name),
                            'link'      => (is_wp_error(get_term_link($pageId)) === true ? '' : get_term_link($pageId)),
                            'edit_link' => get_edit_term_link($pageId),
                            'report'    => Menus::admin_url('category-analytics', ['type' => 'single', 'term_id' => $term->term_taxonomy_id]),
                            'meta'      => [
                                'taxonomy'         => $term->taxonomy,
                                'term_taxonomy_id' => $term->term_taxonomy_id,
                                'count'            => $term->count
                            ]
                        ];
                    }
                    break;

                case 'home':
                    $arg = [
                        'title' => $pageId ? sprintf(__('Home Page: %s', 'wp-statistics'), get_the_title($pageId)) : __('Home Page', 'wp-statistics'),
                        'link'  => get_site_url(),
                        'meta'  => [
                            'post_type' => get_post_type($pageId)
                        ]
                    ];
                    if ($pageId) {
                        $arg['report'] = Menus::admin_url('content-analytics', ['type' => 'single', 'post_id' => $pageId]);
                    }
                    break;

                case 'author':
                    $userInfo = get_userdata($pageId);
                    if ($userInfo) {
                        $arg = [
                            'title'     => ($userInfo->display_name !== '' ? esc_html($userInfo->display_name) : esc_html($userInfo->first_name . ' ' . $userInfo->last_name)),
                            'link'      => get_author_posts_url($pageId),
                            'edit_link' => get_edit_user_link($pageId),
                            'report'    => Menus::admin_url('author-analytics', ['type' => 'single-author', 'author_id' => $userInfo->ID]),
                            'meta'      => [
                                'author_id' => $userInfo->ID
                            ]
                        ];
                    }
                    break;

                case 'feed':
                    $arg['title'] = __('Feed', 'wp-statistics');
                    break;

                case 'loginpage':
                    $arg['title'] = __('Login Page', 'wp-statistics');
                    break;

                case 'search':
                    $arg['title'] = __('Search Page', 'wp-statistics');
                    break;

                case '404':
                    $arg['title'] = sprintf(__('404 not found (%s)', 'wp-statistics'), esc_html(substr($slug, 0, 20)));
                    break;

                case 'archive':
                    if ($slug) {
                        $postType   = trim($slug, '/');
                        $postObject = get_post_type_object($postType);
                        if ($postObject instanceof \WP_Post_Type) {
                            $arg['title'] = sprintf(__('Post Archive: %s', 'wp-statistics'), $postObject->labels->name);
                            $arg['link']  = get_post_type_archive_link($postType);
                        } else {
                            $arg['title'] = sprintf(__('Post Archive: %s', 'wp-statistics'), $slug);
                            $arg['link']  = home_url($slug);
                        }
                    } else {
                        $arg['title'] = __('Post Archive', 'wp-statistics');
                    }
                    break;

                default:
                    $arg = [
                        'title'     => esc_html(get_the_title($pageId)),
                        'link'      => get_the_permalink($pageId),
                        'edit_link' => get_edit_post_link($pageId),
                        'meta'      => [
                            'post_type' => get_post_type($pageId)
                        ]
                    ];
                    if ($pageId) {
                        $arg['report'] = Menus::admin_url('content-analytics', ['type' => 'single', 'post_id' => $pageId]);
                    }
                    break;
            }
        }

        return wp_parse_args($arg, $defaults);
    }

    /**
     * Convert URI to Page ID.
     *
     * Looks up the page ID from the resources table by URI.
     *
     * @param string $uri The page URI.
     * @return int The page ID, or 0 if not found.
     */
    public static function uriToId($uri)
    {
        global $wpdb;

        $resourcesTable = $wpdb->prefix . 'statistics_resources';
        $urisTable      = $wpdb->prefix . 'statistics_resource_uris';

        // Try v15 tables first
        $result = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT r.resource_id FROM {$urisTable} ru
                 INNER JOIN {$resourcesTable} r ON ru.resource_id = r.ID
                 WHERE ru.uri = %s AND r.resource_id > 0
                 LIMIT 1",
                $uri
            )
        );

        if ($result) {
            return (int) $result;
        }

        // Fallback to legacy pages table if exists
        $legacyTable = $wpdb->prefix . 'statistics_pages';
        if ($wpdb->get_var("SHOW TABLES LIKE '{$legacyTable}'") === $legacyTable) {
            $result = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT id FROM `{$legacyTable}` WHERE `uri` = %s AND id > 0 ORDER BY date DESC LIMIT 1",
                    $uri
                )
            );
        }

        return $result ? (int) $result : 0;
    }

    /**
     * Get Post Type by ID.
     *
     * @param int $postId The post ID.
     * @return string The post type.
     */
    public static function getPostType($postId)
    {
        $postType = get_post_type($postId);
        return in_array($postType, ['post', 'page', 'product', 'attachment']) ? $postType : 'post_type_' . $postType;
    }

    /**
     * Check if page is the home page.
     *
     * @param int|false $postId The post ID to check.
     * @return bool True if the page is home.
     */
    public static function isHome($postId = false)
    {
        if (get_option('show_on_front') === 'page') {
            if (get_option('page_on_front') == $postId || get_option('page_for_posts') == $postId) {
                return true;
            }
        }
        return false;
    }
}
