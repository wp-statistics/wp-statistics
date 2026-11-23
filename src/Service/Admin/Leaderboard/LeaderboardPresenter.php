<?php

namespace WP_Statistics\Service\Admin\Leaderboard;

use WP_STATISTICS\Helper;
use WP_STATISTICS\Menus;

/**
 * Turns leaderboard data into what the views print: report links (following who may see
 * other authors' reports), avatar URLs, content items with thumbnails or initials, and the
 * "N posts published" line.
 */
class LeaderboardPresenter
{
    /**
     * Add 'url' and 'avatar' to author rows.
     *
     * @param array[] $rows
     * @param string  $postType Post type kept on the author report link.
     * @return array[]
     */
    public static function authorRows($rows, $postType = '')
    {
        // The single author report is a Data Plus page.
        $hasReport   = Helper::isAddOnActive('data-plus');
        $seeOthers   = current_user_can('edit_others_posts');
        $currentUser = get_current_user_id();
        $avatars     = (bool) get_option('show_avatars', true);

        foreach ($rows as &$row) {
            $id = (int) $row['id'];

            $row['url'] = '';
            if ($hasReport && ($seeOthers || $id === $currentUser)) {
                $args = ['type' => 'single-author', 'author_id' => $id];
                if (!empty($postType) && is_string($postType)) {
                    $args['pt'] = $postType;
                }

                $row['url'] = Menus::admin_url('author-analytics', $args);
            }

            // default=blank: without a Gravatar the image is transparent and the initial circle shows through.
            $row['avatar'] = $avatars ? get_avatar_url($id, ['size' => 48, 'default' => 'blank']) : '';
        }
        unset($row);

        return $rows;
    }

    /**
     * Add 'url' (Category Analytics single term report) to term rows.
     *
     * @param array[] $rows
     * @return array[]
     */
    public static function termRows($rows)
    {
        foreach ($rows as &$row) {
            $row['url'] = Menus::admin_url('category-analytics', ['type' => 'single', 'term_id' => (int) $row['id']]);
        }
        unset($row);

        return $rows;
    }

    /**
     * Items for components/content-rank-list.
     *
     * @param object[] $posts  Rows with ID, post_title and the metric field (views, comments, words, post_date).
     * @param string   $metric 'views', 'comments', 'words' or 'newest'.
     * @param callable $url    function (object $post): string report URL.
     * @return array[] id, title, url, link, thumb, initials, color, metric.
     */
    public static function contentItems($posts, $metric, $url)
    {
        $items = [];

        foreach ((array) $posts as $post) {
            $id    = (int) $post->ID;
            $title = trim(wp_strip_all_tags((string) $post->post_title));
            $title = $title !== '' ? $title : __('(no title)', 'wp-statistics');

            $thumb = has_post_thumbnail($id) ? get_the_post_thumbnail_url($id, 'thumbnail') : '';

            $items[] = [
                'id'       => $id,
                'title'    => $title,
                'url'      => call_user_func($url, $post),
                'link'     => get_permalink($id),
                'thumb'    => $thumb ? $thumb : '', // empty when the file is gone
                'initials' => Leaderboard::initials($title, 2),
                'color'    => Leaderboard::color($id),
                'metric'   => self::metricText($post, $metric),
            ];
        }

        return $items;
    }

    /**
     * "165 views", "3 comments", "1,200 words", or "Nov 20, 2026 · 12 views" for the newest list.
     *
     * @param object $post
     * @param string $metric
     * @return string
     */
    public static function metricText($post, $metric)
    {
        switch ($metric) {
            case 'comments':
                $count = isset($post->comments) ? (int) $post->comments : 0;
                /* translators: %s: number of comments */
                return sprintf(_n('%s comment', '%s comments', $count, 'wp-statistics'), self::number($count));

            case 'words':
                $count = isset($post->words) ? (int) $post->words : 0;
                /* translators: %s: number of words */
                return sprintf(_n('%s word', '%s words', $count, 'wp-statistics'), self::number($count));

            case 'newest':
                $date = !empty($post->post_date) ? date_i18n(get_option('date_format', 'M j, Y'), strtotime($post->post_date)) : '';
                $text = self::metricText($post, 'views');

                return $date ? $date . ' · ' . $text : $text;

            default:
                $count = isset($post->views) ? (int) $post->views : 0;
                /* translators: %s: Number of views. */
                return sprintf(_n('%s view', '%s views', $count, 'wp-statistics'), self::number($count));
        }
    }

    /**
     * "3 posts published in this range" for the line under a leaderboard.
     *
     * @param int $total
     * @param string|string[] $postType
     * @return string
     */
    public static function publishedNote($total, $postType)
    {
        $total     = (int) $total;
        $postTypes = array_values((array) $postType);
        $number    = number_format_i18n($total);

        if (count($postTypes) === 1 && $postTypes[0] === 'page') {
            /* translators: %s: number of pages */
            return sprintf(_n('%s page published in this range', '%s pages published in this range', $total, 'wp-statistics'), $number);
        }

        if (count($postTypes) === 1 && $postTypes[0] === 'product') {
            /* translators: %s: number of products */
            return sprintf(_n('%s product published in this range', '%s products published in this range', $total, 'wp-statistics'), $number);
        }

        if (count($postTypes) === 1 && $postTypes[0] !== 'post') {
            $object = get_post_type_object($postTypes[0]);
            $label  = $object ? $object->labels->name : $postTypes[0];

            /* translators: 1: post type name (plural), 2: number published */
            return sprintf(__('%1$s published in this range: %2$s', 'wp-statistics'), $label, $number);
        }

        if (count($postTypes) === 1) {
            /* translators: %s: number of posts */
            return sprintf(_n('%s post published in this range', '%s posts published in this range', $total, 'wp-statistics'), $number);
        }

        /* translators: %s: number of posts, pages and other content */
        return sprintf(_n('%s item published in this range', '%s items published in this range', $total, 'wp-statistics'), $number);
    }

    /**
     * @param int $number
     * @return string
     */
    private static function number($number)
    {
        $short = Helper::formatNumberWithUnit($number, 1);

        return $number < 1000 ? number_format_i18n($number) : (string) $short;
    }
}
