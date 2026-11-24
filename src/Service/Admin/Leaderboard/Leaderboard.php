<?php

namespace WP_Statistics\Service\Admin\Leaderboard;

/**
 * Plain helpers behind the leaderboard widgets (Top Authors, Top Categories) and the
 * compact content list (Top Contents): the per-post maths, which columns are worth
 * showing, how two rows with the same name are told apart, and the initial/colour used
 * when there is no avatar or featured image. No database access, so it is unit-testable.
 */
class Leaderboard
{
    /**
     * Tile and avatar colours. All carry white text at 4.5:1 or better.
     */
    const PALETTE = ['#7C3AED', '#0891B2', '#DB2777', '#EA580C', '#16A34A', '#404BF2', '#0E7490', '#9333EA', '#B45309', '#BE185D'];

    /**
     * The metrics a leaderboard can be ranked by on the server (the sortable column headers).
     * Comments joins them when the content takes comments (LeaderboardDataProvider).
     */
    const RANKINGS = ['views', 'published', 'views_per_post'];

    /**
     * Every metric a header can rank by, including the optional Comments column.
     */
    const SORTABLE = ['views', 'published', 'views_per_post', 'comments'];

    /**
     * Sort directions, as in aria-sort. The first click on a header gives the first one.
     */
    const DIRECTIONS = ['descending', 'ascending'];

    /**
     * Views per post = views / posts published in the range, the same as the Authors and
     * Categories reports ("Published" and "Views per post" columns there).
     *
     * @param int|float $views
     * @param int $published Posts published in the range.
     * @return float|null Null when nothing was published in the range.
     */
    public static function perPost($views, $published)
    {
        $posts = (int) $published;

        if ($posts <= 0) {
            return null;
        }

        return round(((float) $views) / $posts, 1);
    }

    /**
     * Build the rows of a leaderboard from the ranked query rows plus the side counts.
     *
     * @param object[]|array[] $ranked    Rows with views (+ published, posts_with_views), already ranked.
     * @param array            $keys      ['id' => field holding the row id, 'name' => field holding the name,
     *                                     'count_key' => field used to look up $published / $comments (default: id)].
     * @param int[]            $publishedMap count_key value => posts published in the range. Used when a row
     *                                    has no 'published' field of its own.
     * @param int[]            $comments  count_key value => comments written in the range.
     * @return array[] id, name, views, posts_with_views, views_per_post, published, comments, raw.
     */
    public static function buildRows($ranked, $keys, $publishedMap = [], $comments = [])
    {
        $idKey    = isset($keys['id']) ? $keys['id'] : 'id';
        $nameKey  = isset($keys['name']) ? $keys['name'] : 'name';
        $countKey = isset($keys['count_key']) ? $keys['count_key'] : $idKey;

        $rows = [];

        foreach ((array) $ranked as $item) {
            $item      = (array) $item;
            $id        = isset($item[$idKey]) ? (int) $item[$idKey] : 0;
            $count     = isset($item[$countKey]) ? (int) $item[$countKey] : $id;
            $views     = isset($item['views']) ? (int) $item['views'] : 0;
            $posts     = isset($item['posts_with_views']) ? (int) $item['posts_with_views'] : 0;
            $published = isset($item['published']) ? (int) $item['published'] : (isset($publishedMap[$count]) ? (int) $publishedMap[$count] : 0);

            $rows[] = [
                'id'               => $id,
                'name'             => isset($item[$nameKey]) ? trim(wp_strip_all_tags((string) $item[$nameKey])) : '',
                'views'            => $views,
                'posts_with_views' => $posts,
                'views_per_post'   => self::perPost($views, $published),
                'published'        => $published,
                'comments'         => isset($comments[$count]) ? (int) $comments[$count] : 0,
                'tag'              => null,
                'raw'              => $item,
            ];
        }

        return $rows;
    }

    /**
     * Which columns to show. Views, Posts (published in the range) and Views per post are always on,
     * like the Authors report; Comments only when the content takes comments and someone commented.
     *
     * @param array[] $rows    All shown rows (every ranking).
     * @param array   $options 'comments' (bool) the content type takes comments.
     * @return bool[] views, published, views_per_post, comments (posts_with_views is kept for old callers, off).
     */
    public static function columns($rows, $options = [])
    {
        $hasComments = false;

        foreach ((array) $rows as $row) {
            if (!empty($row['comments'])) {
                $hasComments = true;
            }
        }

        return [
            'views'            => true,
            'published'        => true,
            'views_per_post'   => true,
            'posts_with_views' => false,
            // Comments off, or nobody commented in the range: hide the column.
            'comments'         => !empty($options['comments']) && $hasComments,
        ];
    }

    /**
     * Give rows that share a name a small tag so they can be told apart (two "Blog"
     * categories: one English, one German). Tries, in order, the language (Polylang/WPML),
     * the parent path ("News › Blog") and the slug, and uses the first one that is set
     * and different for every row in the group.
     *
     * @param array[]  $rows    Rows with 'name'.
     * @param callable $context function (array $row): array ['lang' => '', 'parent' => '', 'slug' => ''].
     *                          Only called for rows whose name collides.
     * @return array[] The rows, colliding ones with 'tag' => ['type' => lang|parent|slug, 'text' => '...'].
     */
    public static function disambiguate($rows, $context)
    {
        $groups = [];

        foreach ($rows as $index => $row) {
            $key = self::nameKey(isset($row['name']) ? $row['name'] : '');

            if ($key === '') {
                continue;
            }

            $groups[$key][] = $index;
        }

        foreach ($groups as $indexes) {
            if (count($indexes) < 2) {
                continue;
            }

            $contexts = [];
            foreach ($indexes as $index) {
                $contexts[$index] = wp_parse_args((array) call_user_func($context, $rows[$index]), ['lang' => '', 'parent' => '', 'slug' => '']);
            }

            $picked = null;
            foreach (['lang', 'parent', 'slug'] as $type) {
                $values = [];

                foreach ($contexts as $ctx) {
                    $values[] = self::nameKey($ctx[$type]);
                }

                if (!in_array('', $values, true) && count(array_unique($values)) === count($values)) {
                    $picked = $type;
                    break;
                }
            }

            foreach ($contexts as $index => $ctx) {
                if ($picked !== null) {
                    $rows[$index]['tag'] = ['type' => $picked, 'text' => (string) $ctx[$picked]];
                } elseif ($ctx['slug'] !== '') {
                    // Nothing tells every row apart: the slug still helps for most of them.
                    $rows[$index]['tag'] = ['type' => 'slug', 'text' => (string) $ctx['slug']];
                }
            }
        }

        return $rows;
    }

    /**
     * Initials for an avatar circle or a content tile: the first letter of the first
     * $letters words, upper-cased. Works for any script (Persian, Cyrillic, ...).
     *
     * @param string $text
     * @param int    $letters 1 for people, 2 for content titles.
     * @return string
     */
    public static function initials($text, $letters = 1)
    {
        $text  = trim(wp_strip_all_tags((string) $text));
        $words = preg_split('/[\s\-_\/]+/u', $text, -1, PREG_SPLIT_NO_EMPTY);

        if (empty($words)) {
            return '#';
        }

        $initials = '';
        foreach (array_slice($words, 0, max(1, (int) $letters)) as $word) {
            // Skip leading punctuation such as quotes or brackets
            if (preg_match('/[\p{L}\p{N}]/u', $word, $match)) {
                $initials .= $match[0];
            }
        }

        if ($initials === '') {
            return '#';
        }

        return function_exists('mb_strtoupper') ? mb_strtoupper($initials, 'UTF-8') : strtoupper($initials);
    }

    /**
     * A stable colour for an id or name, so the same author keeps the same colour.
     *
     * @param int|string $seed
     * @return string Hex colour.
     */
    public static function color($seed)
    {
        $palette = self::PALETTE;

        return $palette[abs(crc32((string) $seed)) % count($palette)];
    }

    /**
     * Format a "per post" average: one decimal, none when it is a whole number.
     *
     * @param float|null $value
     * @return string
     */
    public static function formatAverage($value)
    {
        if ($value === null) {
            return '0';
        }

        $value    = round((float) $value, 1);
        $decimals = (floor($value) == $value) ? 0 : 1;

        return function_exists('number_format_i18n') ? number_format_i18n($value, $decimals) : number_format($value, $decimals);
    }

    /**
     * @param string $name
     * @return string
     */
    private static function nameKey($name)
    {
        $name = trim(preg_replace('/\s+/u', ' ', (string) $name));

        return function_exists('mb_strtolower') ? mb_strtolower($name, 'UTF-8') : strtolower($name);
    }
}
