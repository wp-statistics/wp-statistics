<?php

namespace WP_STATISTICS;

use WP_Statistics\Utils\Page;
use WP_Statistics\Utils\Uri;

/**
 * Legacy Pages class for backward compatibility.
 *
 * @deprecated 15.0.0 Use \WP_Statistics\Utils\Page instead.
 * @see \WP_Statistics\Utils\Page
 *
 * This class is maintained for backward compatibility with add-ons.
 * New code should use the v15 Page utility class.
 */
class Pages
{
    /**
     * Check Active Record Pages.
     *
     * @deprecated 15.0.0
     * @return mixed
     */
    public static function active()
    {
        return (has_filter('wp_statistics_active_pages')) ? apply_filters('wp_statistics_active_pages', true) : 1;
    }

    /**
     * Get WordPress Page Type.
     *
     * @deprecated 15.0.0 Use \WP_Statistics\Utils\Page::getType() instead.
     * @return array
     */
    public static function get_page_type()
    {
        return Page::getType();
    }

    /**
     * Get Page URL.
     *
     * @deprecated 15.0.0 Use \WP_Statistics\Utils\Uri::get() instead.
     * @return string
     */
    public static function get_page_uri()
    {
        return Uri::get();
    }

    /**
     * Sanitize Page URI For Push to Database.
     *
     * @deprecated 15.0.0
     * @param object $visitorProfile
     * @return string
     */
    public static function sanitize_page_uri($visitorProfile)
    {
        return \WP_Statistics\Utils\Uri::getByVisitor($visitorProfile);
    }

    /**
     * Get Page information.
     *
     * @deprecated 15.0.0 Use \WP_Statistics\Utils\Page::getInfo() instead.
     * @param int    $page_id
     * @param string $type
     * @param mixed  $slug
     * @return array
     */
    public static function get_page_info($page_id, $type = 'post', $slug = false)
    {
        return Page::getInfo($page_id, $type, $slug);
    }

    /**
     * Convert URL to Page ID.
     *
     * @deprecated 15.0.0 Use \WP_Statistics\Utils\Page::uriToId() instead.
     * @param string $uri
     * @return int
     */
    public static function uri_to_id($uri)
    {
        return Page::uriToId($uri);
    }

    /**
     * Get Post Type by ID.
     *
     * @deprecated 15.0.0 Use \WP_Statistics\Utils\Page::getPostType() instead.
     * @param int $post_id
     * @return string
     */
    public static function get_post_type($post_id)
    {
        return Page::getPostType($post_id);
    }

    /**
     * Check if page is home page.
     *
     * @deprecated 15.0.0 Use \WP_Statistics\Utils\Page::isHome() instead.
     * @param int|false $postID
     * @return bool
     */
    public static function checkIfPageIsHome($postID = false)
    {
        return Page::isHome($postID);
    }
}
