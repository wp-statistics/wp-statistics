<?php

namespace WP_Statistics\Service\AnalyticsQuery\GroupBy;

/**
 * Page group by - groups by page/URL.
 *
 * @since 15.0.0
 */
class PageGroupBy extends AbstractGroupBy
{
    protected $name         = 'page';
    protected $column       = 'resource_uris.uri';
    protected $alias        = 'page_uri';
    protected $extraColumns = [
        'resource_uris.ID AS page_uri_id',
        'resources.ID AS resource_id',
        'resources.cached_title AS page_title',
        'resources.resource_id AS page_wp_id',
        'resources.resource_type AS page_type',
        'resources.cached_date AS published_date',
        'resources.cached_terms AS cached_terms',
    ];
    protected $joins        = [
        [
            'table' => 'resource_uris',
            'alias' => 'resource_uris',
            'on'    => 'views.resource_uri_id = resource_uris.ID',
        ],
        [
            'table' => 'resources',
            'alias' => 'resources',
            'on'    => 'resource_uris.resource_id = resources.ID',
            'type'  => 'LEFT',
        ],
    ];
    protected $groupBy      = 'resource_uris.ID';
    protected $requirement  = 'views';

    /**
     * Columns added by postProcess (not in SQL query).
     *
     * @var array
     */
    protected $postProcessedColumns = [
        'comments',
        'thumbnail_url',
        'author_id',
        'author_name',
        'modified_date',
        'post_type_label',
        'edit_url',
        'permalink',
    ];

    /**
     * {@inheritdoc}
     *
     * Enriches page data with comment counts, thumbnails, and content metadata from WordPress.
     */
    public function postProcess(array $rows, \wpdb $wpdb): array
    {
        if (empty($rows)) {
            return $rows;
        }

        // Collect all WordPress post IDs
        $postIds = [];
        foreach ($rows as $row) {
            if (!empty($row['page_wp_id'])) {
                $postIds[] = (int) $row['page_wp_id'];
            }
        }

        if (empty($postIds)) {
            return $rows;
        }

        // Fetch post data including comment count, thumbnail, author, and modification date
        $placeholders = implode(',', array_fill(0, count($postIds), '%d'));
        $query = $wpdb->prepare(
            "SELECT
                p.ID as post_id,
                p.post_type,
                p.post_author,
                p.post_modified,
                p.comment_count,
                pm.meta_value as thumbnail_id
            FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_thumbnail_id'
            WHERE p.ID IN ($placeholders)",
            $postIds
        );

        $postData = $wpdb->get_results($query, ARRAY_A);

        // Index by post ID for quick lookup
        $postIndex = [];
        $authorIds = [];
        foreach ($postData as $post) {
            $postIndex[$post['post_id']] = $post;
            if (!empty($post['post_author'])) {
                $authorIds[] = (int) $post['post_author'];
            }
        }

        // Fetch author names
        $authorNames = [];
        if (!empty($authorIds)) {
            $authorIds = array_unique($authorIds);
            $authorPlaceholders = implode(',', array_fill(0, count($authorIds), '%d'));
            $authorQuery = $wpdb->prepare(
                "SELECT ID, display_name FROM {$wpdb->users} WHERE ID IN ($authorPlaceholders)",
                $authorIds
            );
            $authorResults = $wpdb->get_results($authorQuery, ARRAY_A);
            foreach ($authorResults as $author) {
                $authorNames[$author['ID']] = $author['display_name'];
            }
        }

        // Get thumbnail URLs if we have thumbnail IDs
        $thumbnailIds = array_filter(array_column($postData, 'thumbnail_id'));
        $thumbnailUrls = [];

        if (!empty($thumbnailIds)) {
            $thumbPlaceholders = implode(',', array_fill(0, count($thumbnailIds), '%d'));
            $thumbQuery = $wpdb->prepare(
                "SELECT ID, guid FROM {$wpdb->posts} WHERE ID IN ($thumbPlaceholders)",
                $thumbnailIds
            );
            $thumbResults = $wpdb->get_results($thumbQuery, ARRAY_A);

            foreach ($thumbResults as $thumb) {
                // Index by attachment ID (not post_id)
                $thumbnailUrls[$thumb['ID']] = $thumb['guid'];
            }
        }

        // Build post type labels cache
        $postTypeLabels = [];

        // Enrich rows with all metadata
        foreach ($rows as &$row) {
            $postId = $row['page_wp_id'] ?? null;

            if ($postId && isset($postIndex[$postId])) {
                $postInfo = $postIndex[$postId];

                // Comment count
                $row['comments'] = (int) $postInfo['comment_count'];

                // Thumbnail URL
                $thumbnailId = $postInfo['thumbnail_id'] ?? null;
                if ($thumbnailId && isset($thumbnailUrls[$thumbnailId])) {
                    $row['thumbnail_url'] = $thumbnailUrls[$thumbnailId];
                } else {
                    $row['thumbnail_url'] = null;
                }

                // Author name and ID
                $authorId = $postInfo['post_author'] ?? null;
                if ($authorId && isset($authorNames[$authorId])) {
                    $row['author_id'] = (int) $authorId;
                    $row['author_name'] = $authorNames[$authorId];
                } else {
                    $row['author_id'] = null;
                    $row['author_name'] = null;
                }

                // Modified date
                $row['modified_date'] = $postInfo['post_modified'] ?? null;

                // Post type label (cached to avoid repeated function calls)
                $postType = $postInfo['post_type'] ?? null;
                if ($postType) {
                    if (!isset($postTypeLabels[$postType])) {
                        $postTypeObj = get_post_type_object($postType);
                        $postTypeLabels[$postType] = $postTypeObj ? $postTypeObj->labels->singular_name : ucfirst($postType);
                    }
                    $row['post_type_label'] = $postTypeLabels[$postType];
                } else {
                    $row['post_type_label'] = null;
                }

                // Permalink and edit URL
                $row['permalink'] = esc_url(get_permalink($postId));
                $row['edit_url'] = esc_url(get_edit_post_link($postId, 'raw'));
            } else {
                // Defaults for non-existent posts
                $row['comments'] = 0;
                $row['thumbnail_url'] = null;
                $row['author_id'] = null;
                $row['author_name'] = null;
                $row['modified_date'] = null;
                $row['post_type_label'] = null;
                $row['permalink'] = null;
                $row['edit_url'] = null;
            }

            // Enrich cached_terms with term details
            $row['cached_terms'] = $this->enrichCachedTerms($row['cached_terms'] ?? null, $wpdb);
        }

        return $rows;
    }

    /**
     * Enrich cached terms with full term details.
     *
     * @param string|null $cachedTerms Comma-separated term IDs.
     * @param \wpdb $wpdb WordPress database object.
     * @return array Array of term objects with term_id, name, slug, taxonomy.
     */
    private function enrichCachedTerms($cachedTerms, \wpdb $wpdb): array
    {
        if (empty($cachedTerms)) {
            return [];
        }

        // Parse comma-separated term IDs
        $termIds = array_filter(array_map('intval', explode(',', str_replace(' ', '', $cachedTerms))));

        if (empty($termIds)) {
            return [];
        }

        // Fetch term details from WordPress
        $placeholders = implode(',', array_fill(0, count($termIds), '%d'));
        $query = $wpdb->prepare(
            "SELECT t.term_id, t.name, t.slug, tt.taxonomy
            FROM {$wpdb->terms} t
            INNER JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id
            WHERE t.term_id IN ($placeholders)",
            $termIds
        );

        $termResults = $wpdb->get_results($query, ARRAY_A);

        if (empty($termResults)) {
            return [];
        }

        // Format terms as objects
        $terms = [];
        foreach ($termResults as $term) {
            $terms[] = [
                'term_id'  => (int) $term['term_id'],
                'name'     => $term['name'],
                'slug'     => $term['slug'],
                'taxonomy' => $term['taxonomy'],
            ];
        }

        return $terms;
    }
}
