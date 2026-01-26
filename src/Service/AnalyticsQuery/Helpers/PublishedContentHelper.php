<?php

namespace WP_Statistics\Service\AnalyticsQuery\Helpers;

use WP_Statistics\Utils\PostType;

/**
 * Helper class for querying published content.
 *
 * Provides shared functionality for counting WordPress posts by date range,
 * used by both PublishedContentSource (for SQL expressions) and ChartFormatter
 * (for filling missing dates).
 *
 * @since 15.0.0
 */
class PublishedContentHelper
{
    /**
     * Get post types from filters array or return default queryable types.
     *
     * @param array $filters Array of filter configurations.
     * @return array Array of post type slugs.
     */
    public static function getPostTypesFromFilters(array $filters = []): array
    {
        // Check for post_type filter in various formats
        if (isset($filters['post_type'])) {
            $postTypeFilter = $filters['post_type'];

            // Format 1: Normalized simple value - ['post_type' => 'post']
            if (is_string($postTypeFilter)) {
                return [$postTypeFilter];
            }

            // Format 2: API format with operator - ['post_type' => ['is' => 'post']]
            if (is_array($postTypeFilter)) {
                // Handle 'is' operator (single value)
                if (isset($postTypeFilter['is'])) {
                    return [$postTypeFilter['is']];
                }
                // Handle 'in' operator (multiple values)
                if (isset($postTypeFilter['in']) && is_array($postTypeFilter['in'])) {
                    return $postTypeFilter['in'];
                }
                // Handle 'is_not' - return all types except the excluded one
                if (isset($postTypeFilter['is_not'])) {
                    $excludedType = $postTypeFilter['is_not'];
                    $types = PostType::getQueryableTypes();
                    return array_filter($types, function($type) use ($excludedType) {
                        return $type !== $excludedType;
                    });
                }
            }
        }

        // Check for legacy format: [['column' => 'post_type', 'value' => 'post']]
        foreach ($filters as $filter) {
            if (isset($filter['column']) && $filter['column'] === 'post_type') {
                $value = $filter['value'] ?? null;
                if ($value) {
                    return is_array($value) ? $value : [$value];
                }
            }
        }

        // Default: use queryable post types
        $types = PostType::getQueryableTypes();
        if (!empty($types)) {
            return $types;
        }

        return ['post', 'page'];
    }

    /**
     * Build SQL IN clause for post types.
     *
     * @param string $column  The column name (e.g., 'p.post_type').
     * @param array  $filters Array of filter configurations.
     * @return string SQL clause like "p.post_type IN ('post', 'page')".
     */
    public static function getPostTypeClause(string $column, array $filters = []): string
    {
        $types = self::getPostTypesFromFilters($filters);
        $escaped = array_map(function ($t) {
            return "'" . esc_sql($t) . "'";
        }, $types);

        return "{$column} IN (" . implode(', ', $escaped) . ")";
    }

    /**
     * Get taxonomy type from filters array.
     *
     * @param array $filters Array of filter configurations.
     * @return string|null Taxonomy type slug or null if not set.
     */
    public static function getTaxonomyTypeFromFilters(array $filters = []): ?string
    {
        // Check if taxonomy_type filter is set with 'is' operator
        if (isset($filters['taxonomy_type']['is'])) {
            return $filters['taxonomy_type']['is'];
        }
        return null;
    }

    /**
     * Count published posts for a specific date or date range.
     *
     * @param string $dateFrom Start date (Y-m-d format).
     * @param string $dateTo   End date (Y-m-d format).
     * @param array  $filters  Array of filter configurations.
     * @return int Number of published posts.
     */
    public static function countPublishedContent(string $dateFrom, string $dateTo, array $filters = []): int
    {
        global $wpdb;

        $postTypes = self::getPostTypesFromFilters($filters);
        $postTypesList = implode("','", array_map('esc_sql', $postTypes));

        // Check for taxonomy_type filter
        $taxonomyType = self::getTaxonomyTypeFromFilters($filters);

        if ($taxonomyType) {
            // Query with taxonomy join - count posts that have terms in this taxonomy
            $count = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(DISTINCT p.ID)
                FROM {$wpdb->posts} p
                INNER JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
                INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
                WHERE DATE(p.post_date) >= %s
                AND DATE(p.post_date) <= %s
                AND p.post_status = 'publish'
                AND p.post_type IN ('{$postTypesList}')
                AND tt.taxonomy = %s",
                $dateFrom,
                $dateTo,
                $taxonomyType
            ));
        } else {
            // Original query without taxonomy filter
            $count = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*)
                FROM {$wpdb->posts}
                WHERE DATE(post_date) >= %s
                AND DATE(post_date) <= %s
                AND post_status = 'publish'
                AND post_type IN ('{$postTypesList}')",
                $dateFrom,
                $dateTo
            ));
        }

        return (int) $count;
    }

    /**
     * Get published content counts for multiple dates.
     *
     * @param array  $dates       Array of date labels.
     * @param string $groupByType Type of grouping (date, week, month).
     * @param array  $filters     Array of filter configurations.
     * @return array Associative array of date => count.
     */
    public static function getPublishedContentByDates(array $dates, string $groupByType, array $filters = []): array
    {
        if (empty($dates)) {
            return [];
        }

        $results = [];

        foreach ($dates as $date) {
            // Determine date range based on groupByType
            switch ($groupByType) {
                case 'week':
                    // $date is the Monday of the week (Y-m-d format)
                    $startDate = $date;
                    $endDate = date('Y-m-d', strtotime($date . ' +6 days'));
                    break;

                case 'month':
                    // $date is in Y-m format
                    $startDate = $date . '-01';
                    $endDate = date('Y-m-t', strtotime($startDate));
                    break;

                default: // 'date'
                    $startDate = $date;
                    $endDate = $date;
                    break;
            }

            $results[$date] = self::countPublishedContent($startDate, $endDate, $filters);
        }

        return $results;
    }
}
