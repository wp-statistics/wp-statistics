<?php

namespace WP_Statistics\Utils;

use WP_Statistics\Option;

/**
 * Utilities for working with query parameters in url.
 *
 * @package WP_Statistics\Utils
 * @since   15.0.0
 */
final class QueryParams
{
    /**
     * Return the allow‑list of URL query parameters.
     *
     * @param string $type           'array' → array ; anything else → newline
     *                               delimited string.
     * @param bool   $ignoreReserved Exclude WP‑core reserved terms?
     *
     * @return array|string
     */
    public static function getAllowedList($type = 'array', $ignoreReserved = false)
    {
        $option = Option::get('query_params_allow_list');

        if (! empty($option)) {
            $list = array_map('trim', explode("\n", $option));
        } else {
            $list = self::getDefaultAllowedList('array');
        }

        if ($ignoreReserved) {
            $list = array_diff($list, self::getWpReservedTerms());
        }

        return ($type === 'array') ? $list : implode("\n", $list);
    }

    /**
     * Built‑in default allow‑list shipped with the plugin.
     *
     * @param string $type 'array' or any other value for newline string.
     * @return array|string
     */
    public static function getDefaultAllowedList($type = 'array')
    {
       $allowList = [
            'ref',
            'source',
            'utm_source',
            'utm_medium',
            'utm_campaign',
            'utm_content',
            'utm_term',
            'utm_id',
            's',
            'p'
        ];

        return ($type === 'array')
            ? $allowList
            : implode("\n", $allowList);
    }

    /**
     * WordPress‑reserved query vars & rewrite tags.
     *
     * @return array Filterable list of reserved terms.
     */
    public static function getWpReservedTerms()
    {
        $terms = [
            'action', 'attachment', 'attachment_id', 'author', 'author_name', 'calendar', 'cat',
            'category', 'category__and', 'category__in', 'category__not_in', 'category_name',
            'comments_per_page', 'comments_popup', 'custom', 'customize_messenger_channel',
            'customized', 'cpage', 'day', 'debug', 'embed', 'error', 'exact', 'feed', 'fields',
            'hour', 'link', 'link_category', 'm', 'minute', 'monthnum', 'more', 'name',
            'nav_menu', 'nonce', 'nopaging', 'offset', 'order', 'orderby', 'p', 'page',
            'page_id', 'paged', 'pagename', 'pb', 'perm', 'post', 'post__in', 'post__not_in',
            'post_format', 'post_mime_type', 'post_status', 'post_tag', 'post_type', 'posts',
            'posts_per_archive_page', 'posts_per_page', 'preview', 'robots', 's', 'search',
            'second', 'sentence', 'showposts', 'static', 'status', 'subpost', 'subpost_id',
            'tag', 'tag__and', 'tag__in', 'tag__not_in', 'tag_id', 'tag_slug__and',
            'tag_slug__in', 'taxonomy', 'tb', 'term', 'terms', 'theme', 'themes', 'title',
            'type', 'types', 'w', 'withcomments', 'withoutcomments', 'year',
        ];

        /**
         * Filter the list of reserved terms in WordPress.
         *
         * @since 6.1 (WP core)
         *
         * @param array $terms Reserved terms.
         */
        return apply_filters('wp_reserved_terms', $terms);
    }
}