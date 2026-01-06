<?php

namespace WP_Statistics\Service\Admin\Dashboard\Endpoints;

use WP_Statistics\Service\Admin\ReactApp\Abstracts\AbstractAnalyticsPage;
use WP_Statistics\Service\Admin\Network\NetworkStatsService;
use WP_Statistics\Utils\Request;

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
 * Also handles network (multisite) queries when 'network: true' is passed.
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
     * Network query (multisite cross-site stats):
     * {
     *   "network": true,
     *   "date_from": "2024-11-01",
     *   "date_to": "2024-11-30"
     * }
     *
     * @return array Query result data
     */
    public function handleQuery()
    {
        $data = Request::getRequestData();

        // Check if this is a network query (multisite cross-site stats)
        if (!empty($data['network']) && $data['network'] === true) {
            return $this->handleNetworkQuery($data);
        }

        return $this->executeQueryFromRequest();
    }

    /**
     * Handle network (multisite) analytics query.
     *
     * Uses NetworkStatsService to aggregate statistics across all sites.
     *
     * @param array $data Request data.
     * @return array Network stats response.
     */
    private function handleNetworkQuery(array $data)
    {
        // Check permissions
        if (!is_multisite()) {
            return [
                'success' => false,
                'error'   => [
                    'code'    => 'not_multisite',
                    'message' => __('This query requires WordPress Multisite.', 'wp-statistics'),
                ],
            ];
        }

        if (!is_super_admin()) {
            return [
                'success' => false,
                'error'   => [
                    'code'    => 'forbidden',
                    'message' => __('Super admin access required for network queries.', 'wp-statistics'),
                ],
            ];
        }

        $dateFrom = isset($data['date_from']) ? sanitize_text_field($data['date_from']) : date('Y-m-d', strtotime('-30 days'));
        $dateTo   = isset($data['date_to']) ? sanitize_text_field($data['date_to']) : date('Y-m-d');

        $networkService = new NetworkStatsService();
        return $networkService->getNetworkStats($dateFrom, $dateTo);
    }
}
