<?php

namespace WP_Statistics\Service\Admin\DashboardBootstrap\Abstracts;

use WP_Statistics\Service\Admin\DashboardBootstrap\Contracts\PageActionInterface;
use WP_Statistics\Service\AnalyticsQuery\AnalyticsQueryHandler;
use WP_Statistics\Service\AnalyticsQuery\Exceptions\InvalidSourceException;
use WP_Statistics\Service\AnalyticsQuery\Exceptions\InvalidGroupByException;
use WP_Statistics\Service\AnalyticsQuery\Exceptions\InvalidDateRangeException;
use WP_Statistics\Service\AnalyticsQuery\Exceptions\InvalidFormatException;

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

                return $this->getQueryHandler()->handleBatch($batchQueries, $dateFrom, $dateTo, $globalFilters, $globalCompare);
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
        // Check for JSON body
        $rawBody = file_get_contents('php://input');
        if (!empty($rawBody)) {
            $decoded = json_decode($rawBody, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        // Check for form data 'query' parameter
        if (isset($_POST['query'])) {
            $queryParam = wp_unslash($_POST['query']);
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
        if (!empty($_POST['sources'])) {
            return [
                'sources'   => isset($_POST['sources']) ? (array) $_POST['sources'] : [],
                'group_by'  => isset($_POST['group_by']) ? (array) $_POST['group_by'] : [],
                'filters'   => isset($_POST['filters']) ? (array) $_POST['filters'] : [],
                'date_from' => isset($_POST['date_from']) ? sanitize_text_field($_POST['date_from']) : null,
                'date_to'   => isset($_POST['date_to']) ? sanitize_text_field($_POST['date_to']) : null,
                'compare'   => isset($_POST['compare']) && $_POST['compare'] === 'true',
                'page'      => isset($_POST['page']) ? (int) $_POST['page'] : 1,
                'per_page'  => isset($_POST['per_page']) ? (int) $_POST['per_page'] : 10,
                'order_by'  => isset($_POST['order_by']) ? sanitize_text_field($_POST['order_by']) : null,
                'order'     => isset($_POST['order']) ? strtoupper(sanitize_text_field($_POST['order'])) : 'DESC',
            ];
        }

        return null;
    }
}
