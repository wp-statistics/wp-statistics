<?php

namespace WP_Statistics\Service\Admin\DashboardBootstrap\Abstracts;

use WP_Statistics\Service\Admin\DashboardBootstrap\Contracts\PageActionInterface;
use WP_Statistics\Service\AnalyticsQuery\AnalyticsQueryHandler;
use WP_Statistics\Service\AnalyticsQuery\Exceptions\InvalidSourceException;
use WP_Statistics\Service\AnalyticsQuery\Exceptions\InvalidGroupByException;
use WP_Statistics\Service\AnalyticsQuery\Exceptions\InvalidDateRangeException;
use WP_Statistics\Service\AnalyticsQuery\Exceptions\InvalidFormatException;
use WP_Statistics\Utils\Request;

/**
 * Abstract base class for handlers that use the AnalyticsQuery.
 *
 * Provides common functionality for handling analytics queries from React widgets.
 * Can be used for both:
 * - Page-specific action handlers (registered per-page)
 * - Global endpoint handlers (registered application-wide)
 *
 * @since 15.0.0
 */
abstract class AbstractAnalyticsPage implements PageActionInterface
{
    /**
     * WordPress plugin prefix for AJAX actions.
     */
    protected const PREFIX = 'wp_statistics';

    /**
     * Analytics query handler instance.
     *
     * @var AnalyticsQueryHandler|null
     */
    private $queryHandler = null;

    /**
     * Get the full AJAX action name.
     *
     * Builds the action name dynamically from prefix + endpoint name.
     * This is the single source of truth for the action name used throughout
     * the application (backend registration and frontend requests).
     *
     * @return string The full AJAX action name (e.g., 'wp_statistics_analytics')
     */
    public static function getActionName()
    {
        $instance = new static();
        return static::PREFIX . '_' . $instance->getEndpointName();
    }

    /**
     * Get or create the analytics query handler.
     *
     * @return AnalyticsQueryHandler
     */
    protected function getQueryHandler(): AnalyticsQueryHandler
    {
        if ($this->queryHandler === null) {
            $this->queryHandler = new AnalyticsQueryHandler();
        }

        return $this->queryHandler;
    }

    /**
     * Execute a query from the request.
     *
     * @return array Query result data
     */
    protected function executeQueryFromRequest(): array
    {
        $query = $this->getQueryFromRequest();

        if ($query === null) {
            return [
                'success' => false,
                'error'   => [
                    'code'    => 'invalid_request',
                    'message' => __('Invalid or missing query parameter.', 'wp-statistics'),
                ],
            ];
        }

        try {
            // Check if this is a batch request with queries
            $batchQueries = $query['queries'] ?? null;
            if (is_array($batchQueries) && !empty($batchQueries)) {
                // Extract root-level parameters
                $dateFrom      = $query['date_from'] ?? null;
                $dateTo        = $query['date_to'] ?? null;
                $globalFilters = $query['filters'] ?? [];
                $globalCompare = $query['compare'] ?? false;
                $pageContext   = $query['page_context'] ?? null;

                return $this->getQueryHandler()->handleBatch($batchQueries, $dateFrom, $dateTo, $globalFilters, $globalCompare, $pageContext);
            }

            // Single query
            return $this->getQueryHandler()->handle($query);
        } catch (InvalidSourceException $e) {
            return [
                'success' => false,
                'error'   => [
                    'code'    => 'invalid_source',
                    'message' => $e->getMessage(),
                ],
            ];
        } catch (InvalidGroupByException $e) {
            return [
                'success' => false,
                'error'   => [
                    'code'    => 'invalid_group_by',
                    'message' => $e->getMessage(),
                ],
            ];
        } catch (InvalidDateRangeException $e) {
            return [
                'success' => false,
                'error'   => [
                    'code'    => 'invalid_date_range',
                    'message' => $e->getMessage(),
                ],
            ];
        } catch (InvalidFormatException $e) {
            return [
                'success' => false,
                'error'   => [
                    'code'    => 'invalid_format',
                    'message' => $e->getMessage(),
                ],
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error'   => [
                    'code'    => 'server_error',
                    'message' => __('An unexpected error occurred.', 'wp-statistics'),
                ],
            ];
        }
    }

    /**
     * Get query data from the request.
     *
     * Extracts and decodes the query from POST data.
     *
     * @return array|null Query data or null if invalid.
     */
    protected function getQueryFromRequest(): ?array
    {
        $data = Request::getRequestData();

        // If data is not empty, return it directly (handles JSON body)
        if (!empty($data)) {
            // Check for form data 'query' parameter (legacy support)
            if (isset($data['query'])) {
                $queryParam = wp_unslash($data['query']);
                if (is_string($queryParam)) {
                    $decoded = json_decode($queryParam, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        return $decoded;
                    }
                } elseif (is_array($queryParam)) {
                    return $queryParam;
                }
            }

            // Check for direct POST parameters
            if (!empty($data['sources'])) {
                return [
                    'sources'      => isset($data['sources']) ? (array) $data['sources'] : [],
                    'group_by'     => isset($data['group_by']) ? (array) $data['group_by'] : [],
                    'filters'      => isset($data['filters']) ? (array) $data['filters'] : [],
                    'date_from'    => isset($data['date_from']) ? sanitize_text_field($data['date_from']) : null,
                    'date_to'      => isset($data['date_to']) ? sanitize_text_field($data['date_to']) : null,
                    'compare'      => isset($data['compare']) && $data['compare'] === 'true',
                    'page'         => isset($data['page']) ? (int) $data['page'] : 1,
                    'per_page'     => isset($data['per_page']) ? (int) $data['per_page'] : 10,
                    'order_by'     => isset($data['order_by']) ? sanitize_text_field($data['order_by']) : null,
                    'order'        => isset($data['order']) ? strtoupper(sanitize_text_field($data['order'])) : 'DESC',
                    'columns'      => isset($data['columns']) ? (array) $data['columns'] : [],
                    'format'       => isset($data['format']) ? sanitize_text_field($data['format']) : 'table',
                    'show_totals'  => isset($data['show_totals']) ? (bool) $data['show_totals'] : false,
                    'context'      => isset($data['context']) ? sanitize_text_field($data['context']) : null,
                ];
            }

            // Return the data as-is if it's a valid array (JSON request)
            return $data;
        }

        return null;
    }
}
