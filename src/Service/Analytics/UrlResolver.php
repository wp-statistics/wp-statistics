<?php

namespace WP_Statistics\Service\Analytics;

/**
 * Resolves page type and ID from URL path for SPA tracking support.
 *
 * When tracking hits via REST API, the client sends the page_uri parameter.
 * This class resolves that URI to the correct source_type and source_id,
 * fixing the issue where SPA navigations send stale client-side values.
 */
class UrlResolver
{
    /**
     * Resolve page type and ID from URL path
     *
     * @param string $pageUri URL path (e.g., "/blog/my-post/")
     * @return array ['type' => string, 'id' => int, 'search_query' => string]
     */
    public static function resolve($pageUri)
    {
        $result = ['type' => 'unknown', 'id' => 0, 'search_query' => ''];

        if (empty($pageUri)) {
            return $result;
        }

        // Normalize URI
        $pageUri = '/' . ltrim($pageUri, '/');

        // 1. Home page
        if ($pageUri === '/') {
            return ['type' => 'home', 'id' => 0, 'search_query' => ''];
        }

        // 2. Search page
        if (preg_match('/[?&]s=([^&]*)/', $pageUri, $matches)) {
            return [
                'type'         => 'search',
                'id'           => 0,
                'search_query' => sanitize_text_field(urldecode($matches[1]))
            ];
        }

        // 3. Try to resolve as post/page using WordPress
        $fullUrl = home_url($pageUri);
        $postId  = url_to_postid($fullUrl);

        if ($postId > 0) {
            $postType = get_post_type($postId);
            $type     = self::mapPostType($postType);

            return ['type' => $type, 'id' => $postId, 'search_query' => ''];
        }

        // 4. Try to resolve as taxonomy term
        $termResult = self::resolveTaxonomy($pageUri);
        if ($termResult) {
            return $termResult;
        }

        // 5. Try to resolve as author
        $authorResult = self::resolveAuthor($pageUri);
        if ($authorResult) {
            return $authorResult;
        }

        // 6. Return unknown - fallback to client values
        return $result;
    }

    /**
     * Map WordPress post type to WP Statistics source type
     *
     * @param string $postType WordPress post type
     * @return string WP Statistics source type
     */
    private static function mapPostType($postType)
    {
        if ($postType === 'post') {
            return 'post';
        }
        if ($postType === 'page') {
            return 'page';
        }
        if ($postType === 'attachment') {
            return 'attachment';
        }
        if ($postType === 'product') {
            return 'product';
        }

        return 'post_type_' . $postType;
    }

    /**
     * Resolve taxonomy term from URL
     *
     * @param string $pageUri URL path
     * @return array|null Resolved taxonomy data or null
     */
    private static function resolveTaxonomy($pageUri)
    {
        // Get category base
        $categoryBase = get_option('category_base');
        if (empty($categoryBase)) {
            $categoryBase = 'category';
        }

        // Check category
        if (preg_match('#/' . preg_quote($categoryBase, '#') . '/([^/]+)/?#', $pageUri, $matches)) {
            $term = get_term_by('slug', $matches[1], 'category');
            if ($term && !is_wp_error($term)) {
                return ['type' => 'category', 'id' => $term->term_id, 'search_query' => ''];
            }
        }

        // Get tag base
        $tagBase = get_option('tag_base');
        if (empty($tagBase)) {
            $tagBase = 'tag';
        }

        // Check tag
        if (preg_match('#/' . preg_quote($tagBase, '#') . '/([^/]+)/?#', $pageUri, $matches)) {
            $term = get_term_by('slug', $matches[1], 'post_tag');
            if ($term && !is_wp_error($term)) {
                return ['type' => 'post_tag', 'id' => $term->term_id, 'search_query' => ''];
            }
        }

        // Check custom taxonomies
        $customTaxonomies = get_taxonomies(['public' => true, '_builtin' => false], 'objects');
        foreach ($customTaxonomies as $taxonomy) {
            $rewriteSlug = isset($taxonomy->rewrite['slug']) ? $taxonomy->rewrite['slug'] : $taxonomy->name;

            if (preg_match('#/' . preg_quote($rewriteSlug, '#') . '/([^/]+)/?#', $pageUri, $matches)) {
                $term = get_term_by('slug', $matches[1], $taxonomy->name);
                if ($term && !is_wp_error($term)) {
                    return ['type' => 'tax_' . $taxonomy->name, 'id' => $term->term_id, 'search_query' => ''];
                }
            }
        }

        return null;
    }

    /**
     * Resolve author from URL
     *
     * @param string $pageUri URL path
     * @return array|null Resolved author data or null
     */
    private static function resolveAuthor($pageUri)
    {
        global $wp_rewrite;

        $authorBase = !empty($wp_rewrite->author_base) ? $wp_rewrite->author_base : 'author';

        if (preg_match('#/' . preg_quote($authorBase, '#') . '/([^/]+)/?#', $pageUri, $matches)) {
            $author = get_user_by('slug', $matches[1]);
            if ($author) {
                return ['type' => 'author', 'id' => $author->ID, 'search_query' => ''];
            }
        }

        return null;
    }
}
