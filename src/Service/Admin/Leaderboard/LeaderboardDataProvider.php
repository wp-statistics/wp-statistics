<?php

namespace WP_Statistics\Service\Admin\Leaderboard;

use WP_STATISTICS\Helper;
use WP_Statistics\Models\AuthorsModel;
use WP_Statistics\Models\PostsModel;
use WP_Statistics\Models\TaxonomyModel;

/**
 * Data for the leaderboard widgets: Top Authors (Author Analytics, Category Analytics)
 * and Top Categories (Category Analytics, Content Analytics, Data Plus author page).
 *
 * Each method returns:
 *  - rows            Leaderboard::buildRows() rows, ranked by views.
 *  - columns         Leaderboard::columns() flags.
 *  - published_total Posts published in the range (for the "N posts published" line).
 *  - post_type       The post type(s) the numbers cover.
 *
 * Three small queries per widget: the ranked rows, then published and comment counts for
 * the shown ids only.
 */
class LeaderboardDataProvider
{
    /**
     * @param array $args date, post_type, taxonomy, term, per_page.
     * @return array
     */
    public static function authors($args = [])
    {
        $args  = self::limitPostTypes($args, Helper::get_list_post_type());
        $model = new AuthorsModel();

        $ranked = $model->getAuthorsLeaderboard($args);
        $ids    = array_map(function ($row) {
            return (int) $row->id;
        }, $ranked);

        $supportsComments = self::supportsComments($args['post_type']);
        $published        = $model->countPublishedByAuthors($ids, $args);
        $comments         = $supportsComments ? $model->countCommentsByAuthors($ids, $args) : [];

        $rows = Leaderboard::buildRows($ranked, ['id' => 'id', 'name' => 'name'], $published, $comments);

        // Two authors with the same display name: show their @username.
        $rows = Leaderboard::disambiguate($rows, function ($row) {
            return ['slug' => !empty($row['raw']['nicename']) ? '@' . $row['raw']['nicename'] : ''];
        });

        return [
            'rows'            => $rows,
            'columns'         => Leaderboard::columns($rows, ['published' => true, 'comments' => $supportsComments]),
            'published_total' => (int) (new PostsModel())->countPosts(self::countArgs($args)),
            'post_type'       => $args['post_type'],
        ];
    }

    /**
     * @param array $args date, taxonomy, post_type, author_id, per_page.
     * @return array
     */
    public static function terms($args = [])
    {
        $args  = self::limitPostTypes($args, Helper::getPostTypes());
        $model = new TaxonomyModel();

        $ranked = $model->getTermsLeaderboard($args);
        $ttIds  = array_map(function ($row) {
            return (int) $row->term_taxonomy_id;
        }, $ranked);

        $supportsComments = self::supportsComments($args['post_type']);
        $published        = $model->countPublishedByTerms($ttIds, $args);
        $comments         = $supportsComments ? $model->countCommentsByTerms($ttIds, $args) : [];

        $rows = Leaderboard::buildRows($ranked, ['id' => 'term_id', 'name' => 'term_name', 'count_key' => 'term_taxonomy_id'], $published, $comments);
        $rows = Leaderboard::disambiguate($rows, [__CLASS__, 'termContext']);

        return [
            'rows'            => $rows,
            'columns'         => Leaderboard::columns($rows, ['published' => true, 'comments' => $supportsComments]),
            'published_total' => (int) (new PostsModel())->countPosts(self::countArgs($args)),
            'post_type'       => $args['post_type'],
        ];
    }

    /**
     * Top terms for every taxonomy of a post type, for widgets with one tab per taxonomy.
     *
     * @param array $args date, post_type, author_id, per_page.
     * @param array|null $taxonomies Taxonomy names. Default: the post type's public taxonomies.
     * @return array taxonomy => self::terms() result. Taxonomies without rows are left out.
     */
    public static function termsByTaxonomy($args = [], $taxonomies = null)
    {
        $postType = !empty($args['post_type']) ? $args['post_type'] : 'post';

        if ($taxonomies === null) {
            $taxonomies = array_keys(Helper::get_list_taxonomy(true));
            $taxonomies = array_values(array_filter($taxonomies, function ($taxonomy) use ($postType) {
                foreach ((array) $postType as $type) {
                    if (is_object_in_taxonomy($type, $taxonomy)) {
                        return true;
                    }
                }

                return false;
            }));
        }

        // Without Data Plus only Categories and Tags are tracked.
        if (!Helper::isAddOnActive('data-plus')) {
            $taxonomies = array_values(array_filter($taxonomies, function ($taxonomy) {
                return !Helper::isCustomTaxonomy($taxonomy);
            }));
        }

        $result = [];

        foreach ($taxonomies as $taxonomy) {
            $data = self::terms(array_merge($args, ['taxonomy' => $taxonomy]));

            if (!empty($data['rows'])) {
                $result[$taxonomy] = $data;
            }
        }

        return $result;
    }

    /**
     * What tells a term apart from another with the same name.
     *
     * @param array $row A Leaderboard row built from getTermsLeaderboard().
     * @return array lang, parent, slug.
     */
    public static function termContext($row)
    {
        $raw      = isset($row['raw']) ? (array) $row['raw'] : [];
        $termId   = isset($raw['term_id']) ? (int) $raw['term_id'] : 0;
        $taxonomy = isset($raw['taxonomy']) ? (string) $raw['taxonomy'] : '';
        $parentId = isset($raw['parent']) ? (int) $raw['parent'] : 0;

        return [
            'lang'   => self::termLanguage($termId, $taxonomy, isset($raw['term_taxonomy_id']) ? (int) $raw['term_taxonomy_id'] : 0),
            'parent' => self::parentPath($parentId, $taxonomy),
            'slug'   => isset($raw['slug']) ? urldecode((string) $raw['slug']) : '',
        ];
    }

    /**
     * Language code of a term from Polylang or WPML, upper-cased ("DE"). Empty without either.
     *
     * @param int $termId
     * @param string $taxonomy
     * @param int $termTaxonomyId
     * @return string
     */
    public static function termLanguage($termId, $taxonomy, $termTaxonomyId = 0)
    {
        $code = '';

        if ($termId && function_exists('pll_get_term_language')) {
            $code = (string) pll_get_term_language($termId, 'slug');
        }

        if ($code === '' && $termId && defined('ICL_SITEPRESS_VERSION')) {
            $code = (string) apply_filters('wpml_element_language_code', null, [
                'element_id'   => $termTaxonomyId ? $termTaxonomyId : $termId,
                'element_type' => $taxonomy,
            ]);
        }

        /**
         * Filters the language tag shown next to a term whose name is used twice.
         *
         * @param string $code     Language code, e.g. "de". Empty when unknown.
         * @param int    $termId
         * @param string $taxonomy
         */
        $code = (string) apply_filters('wp_statistics_leaderboard_term_language', $code, $termId, $taxonomy);

        return strtoupper(substr($code, 0, 5));
    }

    /**
     * "News › Blog" style path of a term's parents. Empty for top-level terms.
     *
     * @param int $parentId
     * @param string $taxonomy
     * @return string
     */
    public static function parentPath($parentId, $taxonomy)
    {
        if (!$parentId || !$taxonomy) {
            return '';
        }

        $names     = [];
        $ancestors = array_reverse(array_merge([$parentId], get_ancestors($parentId, $taxonomy, 'taxonomy')));

        foreach ($ancestors as $ancestorId) {
            $term = get_term($ancestorId, $taxonomy);

            if ($term && !is_wp_error($term)) {
                $names[] = $term->name;
            }
        }

        return implode(' › ', $names);
    }

    /**
     * Without Data Plus, custom post types are not tracked: keep only the built-in ones.
     *
     * @param array $args
     * @param array $default
     * @return array
     */
    private static function limitPostTypes($args, $default)
    {
        $postTypes = !empty($args['post_type']) ? (array) $args['post_type'] : $default;

        if (!Helper::isAddOnActive('data-plus')) {
            $builtIn   = Helper::getDefaultPostTypes();
            $limited   = array_values(array_intersect($postTypes, $builtIn));
            $postTypes = !empty($limited) ? $limited : $builtIn;
        }

        $args['post_type'] = array_values($postTypes);

        return $args;
    }

    /**
     * @param array $args
     * @return array Args for PostsModel::countPosts().
     */
    private static function countArgs($args)
    {
        return Helper::filterArrayByKeys($args, ['date', 'post_type', 'author_id', 'taxonomy', 'term']);
    }

    /**
     * @param string|string[] $postTypes
     * @return bool
     */
    private static function supportsComments($postTypes)
    {
        foreach ((array) $postTypes as $postType) {
            if (post_type_supports($postType, 'comments')) {
                return true;
            }
        }

        return false;
    }
}
