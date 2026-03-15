<?php

namespace WP_Statistics\Service\AnalyticsQuery;

/**
 * Helper class for common AnalyticsQuery patterns.
 *
 * Provides static convenience methods for frequently-used analytics queries,
 * ensuring consistent join patterns and query construction across the codebase.
 *
 * Use this class when:
 * - You need analytics data in non-React contexts (admin screens, cron, etc.)
 * - You need simple hit counts or visitor counts without complex grouping
 * - You need batch queries for multiple resources
 *
 * @since 15.0.0
 */
class AnalyticsQueryHelper
{
    /**
     * Add date range parameters to a request array.
     *
     * @param array       $request  The request array to modify (passed by reference).
     * @param string|null $dateFrom Start date (Y-m-d format).
     * @param string|null $dateTo   End date (Y-m-d format).
     */
    private static function addDateRangeToRequest(array &$request, ?string $dateFrom, ?string $dateTo): void
    {
        if ($dateFrom !== null) {
            $request['date_from'] = $dateFrom;
        }
        if ($dateTo !== null) {
            $request['date_to'] = $dateTo;
        }
    }
    /**
     * Get hit count (views or visitors) for a specific resource.
     *
     * Uses AnalyticsQueryHandler internally to ensure consistent join patterns
     * with the React-based analytics reports.
     *
     * @param int         $resourceId   The WordPress resource ID (post ID, term ID, etc.).
     * @param string      $resourceType The resource type (e.g., 'post', 'page', 'category').
     * @param string      $metric       The metric to retrieve: 'views' or 'visitors'.
     * @param string|null $dateFrom     Start date (Y-m-d format). Defaults to all time.
     * @param string|null $dateTo       End date (Y-m-d format). Defaults to today.
     *
     * @return int The hit count for the specified resource.
     */
    public static function getResourceHits(
        int $resourceId,
        string $resourceType,
        string $metric = 'views',
        ?string $dateFrom = null,
        ?string $dateTo = null
    ): int {
        $results = self::getResourceHitsBatch(
            [$resourceId],
            $resourceType,
            $metric,
            $dateFrom,
            $dateTo
        );

        return $results[$resourceId] ?? 0;
    }

    /**
     * Get hit counts for multiple resources in a single query (batch).
     *
     * Uses AnalyticsQueryHandler internally to ensure consistent join patterns.
     * This is more efficient than calling getResourceHits() in a loop.
     *
     * @param array       $resourceIds  Array of WordPress resource IDs.
     * @param string      $resourceType The resource type (e.g., 'post', 'page', 'category').
     * @param string      $metric       The metric to retrieve: 'views' or 'visitors'.
     * @param string|null $dateFrom     Start date (Y-m-d format). Defaults to all time.
     * @param string|null $dateTo       End date (Y-m-d format). Defaults to today.
     *
     * @return array Associative array of [resourceId => hitCount].
     */
    public static function getResourceHitsBatch(
        array $resourceIds,
        string $resourceType,
        string $metric = 'views',
        ?string $dateFrom = null,
        ?string $dateTo = null
    ): array {
        if (empty($resourceIds)) {
            return [];
        }

        // Normalize IDs to integers
        $resourceIds = array_map('intval', $resourceIds);
        $resourceIds = array_filter($resourceIds);

        if (empty($resourceIds)) {
            return [];
        }

        // Initialize results with zeros for all requested IDs
        $results = array_fill_keys($resourceIds, 0);

        // Validate metric
        $source = in_array($metric, ['visitors', 'visitor'], true) ? 'visitors' : 'views';

        // Build filters
        $filters = [
            'post_type'   => $resourceType,
            'resource_id' => ['in' => $resourceIds],
        ];

        // Build request parameters
        $request = [
            'sources'     => [$source],
            'group_by'    => ['page'],
            'filters'     => $filters,
            'format'      => 'table',
            'show_totals' => false,
            'per_page'    => count($resourceIds),
            'page'        => 1,
        ];

        self::addDateRangeToRequest($request, $dateFrom, $dateTo);

        // Execute query
        $handler  = new AnalyticsQueryHandler(false);
        $response = $handler->handle($request);

        // Map results back to resource IDs
        // Table format returns data.rows, not data directly
        $rows = $response['data']['rows'] ?? [];
        foreach ($rows as $row) {
            $wpId = $row['page_wp_id'] ?? null;
            if ($wpId !== null && isset($results[(int) $wpId])) {
                $results[(int) $wpId] = (int) ($row[$source] ?? 0);
            }
        }

        return $results;
    }

    /**
     * Get visitor count matching filter criteria.
     *
     * Uses AnalyticsQueryHandler internally to ensure consistent query patterns.
     *
     * @param array       $filters  Filters to apply (e.g., ['country' => 'US', 'browser' => 'Chrome']).
     * @param string|null $dateFrom Start date (Y-m-d format). Defaults to last 30 days.
     * @param string|null $dateTo   End date (Y-m-d format). Defaults to today.
     *
     * @return int The visitor count matching the criteria.
     */
    public static function getVisitorCount(
        array $filters = [],
        ?string $dateFrom = null,
        ?string $dateTo = null
    ): int {
        // Build request parameters
        $request = [
            'sources'     => ['visitors'],
            'group_by'    => [],
            'filters'     => $filters,
            'format'      => 'flat',
            'show_totals' => false,
        ];

        self::addDateRangeToRequest($request, $dateFrom, $dateTo);

        // Execute query
        $handler  = new AnalyticsQueryHandler(false);
        $response = $handler->handle($request);

        return (int) ($response['data']['visitors'] ?? 0);
    }

    /**
     * Get view count matching filter criteria.
     *
     * Uses AnalyticsQueryHandler internally to ensure consistent query patterns.
     *
     * @param array       $filters  Filters to apply.
     * @param string|null $dateFrom Start date (Y-m-d format).
     * @param string|null $dateTo   End date (Y-m-d format).
     *
     * @return int The view count matching the criteria.
     */
    public static function getViewCount(
        array $filters = [],
        ?string $dateFrom = null,
        ?string $dateTo = null
    ): int {
        // Build request parameters
        $request = [
            'sources'     => ['views'],
            'group_by'    => [],
            'filters'     => $filters,
            'format'      => 'flat',
            'show_totals' => false,
        ];

        self::addDateRangeToRequest($request, $dateFrom, $dateTo);

        // Execute query
        $handler  = new AnalyticsQueryHandler(false);
        $response = $handler->handle($request);

        return (int) ($response['data']['views'] ?? 0);
    }

    /**
     * Get session count matching filter criteria.
     *
     * Uses AnalyticsQueryHandler internally to ensure consistent query patterns.
     *
     * @param array       $filters  Filters to apply.
     * @param string|null $dateFrom Start date (Y-m-d format).
     * @param string|null $dateTo   End date (Y-m-d format).
     *
     * @return int The session count matching the criteria.
     */
    public static function getSessionCount(
        array $filters = [],
        ?string $dateFrom = null,
        ?string $dateTo = null
    ): int {
        // Build request parameters
        $request = [
            'sources'     => ['sessions'],
            'group_by'    => [],
            'filters'     => $filters,
            'format'      => 'flat',
            'show_totals' => false,
        ];

        self::addDateRangeToRequest($request, $dateFrom, $dateTo);

        // Execute query
        $handler  = new AnalyticsQueryHandler(false);
        $response = $handler->handle($request);

        return (int) ($response['data']['sessions'] ?? 0);
    }

    /**
     * Get the canonical v15 join pattern for views → resources.
     *
     * This returns the SQL join pattern that should be used when building
     * custom SQL queries that need to access resource data from views.
     *
     * Join chain: views.resource_uri_id → resource_uris.ID → resources.ID
     *
     * Use this constant when you must write custom SQL (e.g., for ORDER BY subqueries)
     * but need to ensure consistency with AnalyticsQuery.
     *
     * @return string The JOIN clause for views → resource_uris → resources.
     */
    public static function getViewsResourceJoinPattern(): string
    {
        return 'JOIN %1$sstatistics_resource_uris ru ON v.resource_uri_id = ru.ID '
             . 'JOIN %1$sstatistics_resources r ON ru.resource_id = r.ID AND r.is_deleted = 0';
    }

    /**
     * Get the canonical v15 join pattern for views → visitors (via sessions).
     *
     * This returns the SQL join pattern for counting distinct visitors from views.
     *
     * Join chain: views.session_id → sessions.ID → sessions.visitor_id
     *
     * @return string The JOIN clause for views → sessions.
     */
    public static function getViewsVisitorJoinPattern(): string
    {
        return 'JOIN %1$sstatistics_sessions s ON v.session_id = s.ID';
    }
}
