<?php

namespace WP_Statistics\Service\Admin\ReactApp\Controllers\Endpoints;

use WP_Statistics\Service\Admin\ReactApp\Abstracts\AbstractAnalyticsPage;

/**
 * Unified Analytics Query endpoint handler.
 *
 * This is a GLOBAL endpoint (not page-specific) that handles all analytics data requests
 * for the entire application. It replaces the multiple per-page action handlers with a
 * single unified endpoint using the sources + group_by approach.
 *
 * Registered globally in ReactAppManager::registerAnalyticsQueryEndpoint() as
 * 'wp_statistics_analytics' AJAX action.
 *
 * Supports both single queries and batch queries for efficient dashboard loading.
 *
 * @since 15.0.0
 */
class AnalyticsQuery extends AbstractAnalyticsPage
{
    /**
     * Get the endpoint identifier.
     *
     * @return string The endpoint identifier
     */
    public function getEndpointName()
    {
        return 'analytics';
    }

    /**
     * Handle analytics query request.
     *
     * Processes both single queries and batch queries through the unified
     * AnalyticsQueryHandler. The request format supports:
     *
     * Single query:
     * {
     *   "sources": ["visitors", "views"],
     *   "group_by": ["date"],
     *   "date_from": "2024-11-01",
     *   "date_to": "2024-11-30",
     *   "compare": true,
     *   "filters": {},
     *   "page": 1,
     *   "per_page": 10
     * }
     *
     * Batch query:
     * {
     *   "date_from": "2024-11-01",
     *   "date_to": "2024-11-30",
     *   "filters": {},
     *   "queries": [
     *     { "id": "trends", "sources": ["visitors"], "group_by": ["date"] },
     *     { "id": "countries", "sources": ["visitors"], "group_by": ["country"] }
     *   ]
     * }
     *
     * @return array Query result data
     */
    public function handleQuery()
    {
        return $this->executeQueryFromRequest();
    }
}
