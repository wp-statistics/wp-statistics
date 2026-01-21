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
        // Check if post_type filter is set
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
