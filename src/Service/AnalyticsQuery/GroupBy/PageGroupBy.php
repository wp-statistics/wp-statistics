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
    protected $postProcessedColumns = ['comments', 'thumbnail_url'];

    /**
     * {@inheritdoc}
     *
     * Enriches page data with comment counts and thumbnails from WordPress.
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

        // Fetch comment counts and thumbnails in a single query
        $placeholders = implode(',', array_fill(0, count($postIds), '%d'));
        $query = $wpdb->prepare(
            "SELECT
                p.ID as post_id,
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
        foreach ($postData as $post) {
            $postIndex[$post['post_id']] = $post;
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

        // Enrich rows with comment counts and thumbnails
        foreach ($rows as &$row) {
            $postId = $row['page_wp_id'] ?? null;

            if ($postId && isset($postIndex[$postId])) {
                $row['comments'] = (int) $postIndex[$postId]['comment_count'];

                // Get thumbnail URL if available
                $thumbnailId = $postIndex[$postId]['thumbnail_id'] ?? null;
                if ($thumbnailId && isset($thumbnailUrls[$thumbnailId])) {
                    $row['thumbnail_url'] = $thumbnailUrls[$thumbnailId];
                } else {
                    $row['thumbnail_url'] = null;
                }
            } else {
                $row['comments'] = 0;
                $row['thumbnail_url'] = null;
            }
        }

        return $rows;
    }
}
