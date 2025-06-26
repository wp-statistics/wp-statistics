<?php

namespace WP_Statistics\Context;

/**
 * Context helper for WordPress post‑type.
 *
 * This class is only for functionalities related to post types,
 * and performing modifications on post types.
 *
 * @since 15.0.0
 */
final class PostType
{
    /**
     * Retrieve all public post types.
     *
     * Includes both built-in and custom post types, excluding attachments.
     *
     * @return array List of post type slugs.
     */
    public static function getAllTypes()
    {
        return array_merge(
            self::builtInSlugs(),
            self::customSlugs()
        );
    }

    /**
     * Retrieve post‑type slugs that are publicly queryable.
     *
     * Combines built‑in post types (excluding attachments) with custom
     * post types whose `publicly_queryable` flag is true.
     *
     * @return array List of post‑type slugs that can be queried on the front‑end.
     */
    public static function getQueryableTypes()
    {
        return array_merge(
            self::getBuiltIn(),
            self::getCustomTypes(true)
        );
    }

    /**
     * Retrieve the slugs for built‑in public post types.
     *
     * Excludes the 'attachment' type because media items are not counted
     * as standalone content in WP‑Statistics reports.
     *
     * @return array List of built‑in post type slugs.
     */
    public static function builtInSlugs()
    {
        $builtinPostTypes = get_post_types(
            [
                'public'   => true,
                '_builtin' => true
            ],
            'names'
        );

        return array_diff($builtinPostTypes, ['attachment']);
    }

    /**
     * Retrieve built‑in public types.
     *
     * Excludes attachments.
     *
     * @return array List of built-in post type slugs.
     */
    public static function getBuiltIn()
    {
        return array_values(self::builtInSlugs());
    }

    /**
     * Retrieve slugs for custom (non‑built‑in) post types.
     *
     * @param bool $isQueryable Optional. When true, return only publicly‑queryable
     *                          custom types; when false, return all public custom
     *                          types regardless of their queryability. Default false.
     * @return array List of custom post‑type slugs.
     */
    public static function customSlugs($isQueryable = false)
    {
        $args = [
            'public'   => true,
            '_builtin' => false,
        ];

        if ($isQueryable) {
            $args['publicly_queryable'] = true;
        }

        return get_post_types($args, 'names');
    }

    /**
     * Retrieve registered custom types.
     *
     * Only includes publicly queryable types.
     *
     * @return array List of custom post type slugs.
     */
    public static function getCustomTypes($isQueryable = false)
    {
        return array_values(self::customSlugs($isQueryable));
    }

    /**
     * Determine whether a slug represents a custom (non‑built‑in) post type.
     *
     * @param string $slug Post‑type slug to test.
     * @return bool True if the slug is registered as a custom type.
     */
    public static function isCustom($slug)
    {
        return in_array($slug, self::getCustomTypes()) ? true : false;
    }

    /**
     * Retrieve the plural or singular label for a given post‑type slug.
     *
     * @param string $postType Post‑type slug.
     * @param bool $singular Optional. When true, return the singular label;
     *                           otherwise return the plural form. Default false.
     * @return string Empty string if the post type is unknown.
     */
    public static function getName($postType, $singular = false)
    {
        $postTypeObj = get_post_type_object($postType);

        if (empty($postTypeObj)) {
            return '';
        }

        return $singular === true
            ? $postTypeObj->labels->singular_name
            : $postTypeObj->labels->name;
    }

    /**
     * Count published items for a given post type.
     *
     * @param string $postType Optional. Post‑type slug. Default 'post'.
     * @return int             Number of published posts.
     */
    public static function countPublished(string $postType = 'post')
    {
        $totals = wp_count_posts($postType);

        return (is_object($totals) && isset($totals->publish))
            ? (int)$totals->publish
            : 0;
    }
}