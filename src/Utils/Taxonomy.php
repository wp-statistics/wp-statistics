<?php

namespace WP_Statistics\Utils;

/**
 * Utility class for working with WordPress taxonomies.
 *
 * Provides methods to retrieve core and custom taxonomies,
 * determine whether a taxonomy is built-in or custom, retrieve taxonomy labels,
 * and find associated post types. Useful for analyzing and interacting with
 * registered taxonomy structures.
 *
 * @package WP_Statistics\Utils
 * @since 15.0.0
 */
class Taxonomy
{
    /**
     * Retrieve public taxonomies.
     *
     * Always includes core 'category' and 'post_tag'. Appends any custom
     * public taxonomies that expose a rewrite slug. When $hideEmpty is true,
     * skips taxonomies that contain no terms.
     *
     * @param bool $hideEmpty Optional. Exclude empty taxonomies. Default false.
     * @return array Associative array of taxonomy slug => label.
     */
    public static function getAll($hideEmpty = false)
    {
        $taxonomyList = [
            'category' => esc_html__('Category', 'wp-statistics'),
            'post_tag' => esc_html__('Tags', 'wp-statistics'),
        ];

        foreach (self::getCustom() as $taxonomy) {
            if (empty($taxonomy->rewrite['slug'])) {
                continue;
            }

            if ($hideEmpty && wp_count_terms($taxonomy->name) === 0) {
                continue;
            }

            $taxonomyList[$taxonomy->name] = $taxonomy->labels->name;
        }

        return $taxonomyList;
    }

    /**
     * Retrieve custom public taxonomies.
     *
     * @return array Array of WP_Taxonomy objects.
     */
    public static function getCustom()
    {
        return get_taxonomies(
            [
                'public'   => true,
                '_builtin' => false,
            ],
            'objects'
        );
    }

    /**
     * Check whether a taxonomy slug is custom (non‑core).
     *
     * @param string $name Taxonomy slug.
     * @return bool True if the taxonomy is not built‑in.
     */
    public static function isCustom($name)
    {
        $taxonomy = get_taxonomy($name);

        if (!empty($taxonomy)) {
            return !$taxonomy->_builtin;
        }

        return false;
    }

    /**
     * Retrieve post types that use a given taxonomy.
     *
     * Uses {@see PostType::getQueryableTypes()} to restrict the search to
     * publicly‑queryable post types, then checks each for the taxonomy slug.
     *
     * @param string $taxonomy Taxonomy slug.
     * @return array List of post‑type slugs.
     */
    public static function getPostTypes($taxonomy)
    {
        $taxonomyPostTypes = [];
        $postTypes         = PostType::getQueryableTypes();

        foreach ($postTypes as $postType) {
            $taxonomies = get_object_taxonomies($postType);

            if (in_array($taxonomy, $taxonomies)) {
                $taxonomyPostTypes[] = $postType;
            }
        }

        return $taxonomyPostTypes;
    }

    /**
     * Get the human‑readable label for a taxonomy.
     *
     * @param string $name Taxonomy slug.
     * @param bool $singular Optional. True for the singular label; false for
     *                          the plural form. Default false.
     * @return string Taxonomy label or an empty string if the taxonomy is
     *                unknown.
     */
    public static function getName($name, $singular = false)
    {
        $taxonomy = get_taxonomy($name);

        if (empty($taxonomy)) {
            return '';
        }

        return $singular == true
            ? $taxonomy->labels->singular_name
            : $taxonomy->labels->name;
    }
}