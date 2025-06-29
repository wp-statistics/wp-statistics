<?php

namespace WP_Statistics\Context;

use WP_Statistics\Components\DateTime;
use WP_Statistics\Models\PostsModel;

/**
 * Helper for posts across all post types.
 *
 * Provides utility methods for retrieving posts, counting published items,
 * and computing averages for posting frequency.
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

    /**
     * Calculate average posting frequency for standard posts.
     *
     * When <code>$daysBetween</code> is <code>true</code> returns the average
     * <em>days between</em> published posts. Otherwise returns the average
     * <em>posts per day</em>. Uses the date of the earliest published post
     * as the starting point.
     *
     * @param bool $daysBetween Optional. True for days-between, false for
     *                          posts-per-day. Default false.
     * @return float            Rounded average, or 0 when no posts exist.
     */
    public static function getPublishRate($daysBetween = false)
    {
        $postsCount = PostType::countPublished();
        if ($postsCount === 0) {
            return 0;
        }

        $firstPost = self::get('post', 'post_date', 'ASC');
        if (empty($firstPost->post_date)) {
            return 0;
        }

        $daysSpan = max(
            1,
            (int)floor((time() - strtotime($firstPost->post_date)) / DAY_IN_SECONDS)
        );

        return $daysBetween
            ? round($daysSpan / $postsCount, 0)   // days between posts
            : round($postsCount / $daysSpan, 2); // posts per day
    }
}
