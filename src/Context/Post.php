<?php

namespace WP_Statistics\Context;

use WP_Statistics\Components\DateTime;
use WP_Statistics\Models\PostsModel;

/**
 * Context helper for post‑level date information.
 *
 * Provides utility methods for retrieving a single post object and its
 * basic metadata (e.g. publish date) for a given post type. The focus is on
 * one‑post operations.
 *
 * @package WP_Statistics\Context
 * @since   15.0.0
 */
final class Post
{
    /**
     * Retrieve a single post by type, ordered by a specific column.
     *
     * @param string $postType Post‑type slug to query. Default 'post'.
     * @param string $orderBy Column to sort by. Default 'post_date'.
     * @param string $order Sort direction: 'ASC' or 'DESC'. Default 'DESC'.
     *
     * @return object|null     The post object on success, or null if none found.
     */
    public static function get($postType, $orderBy = 'post_date', $order = 'DESC')
    {
        $postModel = new PostsModel();

        $post = $postModel->getPost([
            'post_type' => $postType,
            'order_by'  => $orderBy,
            'order'     => $order,
        ]);

        return $post;
    }

    /**
     * Get the most recent published‑post date for the default 'post' type.
     *
     * @return string Returned in 'Y‑m‑d' format, or '0' if no posts exist.
     */
    public static function getLastByDate()
    {
        $post = self::get('post');

        if (empty($post->post_date)) {
            return 0;
        }

        return DateTime::format($post->post_date, ['date_format' => 'Y-m-d']);
    }

    /**
     * Get the earliest published‑post date for the default 'post' type.
     *
     * @return string Returned in 'Y‑m‑d' format, or '0' if no posts exist.
     */
    public static function getEarliestByDate()
    {
        $post = self::get('post', 'post_date', 'ASC');

        if (empty($post->post_date)) {
            return 0;
        }

        return DateTime::format($post->post_date, ['date_format' => 'Y-m-d']);
    }
}
