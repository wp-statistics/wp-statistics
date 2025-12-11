<?php

namespace WP_Statistics\Service\Admin\DashboardBootstrap\Controllers\Root\Endpoints;

use WP_Statistics\Service\Admin\DashboardBootstrap\Contracts\PageActionInterface;
use WP_Statistics\Service\AnalyticsQuery\Registry\FilterRegistry;

/**
 * Filter Options endpoint handler.
 *
 * This is a GLOBAL endpoint that provides searchable filter options
 * for the dashboard filters. It handles both static and dynamic
 * (searchable) filter options.
 *
 * Registered globally via AjaxManager::registerGlobalEndpoint() as
 * 'wp_statistics_get_filter_options' AJAX action.
 *
 * @since 15.0.0
 */
class FilterOptions implements PageActionInterface
{
    /**
     * WordPress plugin prefix for AJAX actions.
     */
    protected const PREFIX = 'wp_statistics';

    /**
     * Get the full AJAX action name.
     *
     * @return string The full AJAX action name (e.g., 'wp_statistics_get_filter_options')
     */
    public static function getActionName()
    {
        $instance = new static();
        return static::PREFIX . '_' . $instance->getEndpointName();
    }

    /**
     * Get the endpoint identifier.
     *
     * @return string The endpoint identifier
     */
    public function getEndpointName()
    {
        return 'get_filter_options';
    }

    /**
     * Register actions for this handler.
     *
     * This handler is registered globally via AjaxManager::registerGlobalEndpoint()
     * rather than through the per-page action system. Returns empty array.
     *
     * @return array<string, string> Action to method mapping
     */
    public function registerActions()
    {
        return [];
    }

    /**
     * Handle get filter options request.
     *
     * Processes requests for filter options, supporting both static
     * options and searchable (dynamic) options.
     *
     * Request parameters:
     * - filter: (required) The filter name to get options for
     * - search: (optional) Search term for searchable filters
     * - limit: (optional) Maximum number of results (default: 20)
     *
     * @return array Filter options response
     */
    public function handleQuery()
    {
        $filterName = isset($_POST['filter']) ? sanitize_text_field($_POST['filter']) : '';
        $search     = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
        $limit      = isset($_POST['limit']) ? absint($_POST['limit']) : 20;

        if (empty($filterName)) {
            return [
                'success' => false,
                'error'   => [
                    'code'    => 'missing_filter',
                    'message' => __('Filter name is required.', 'wp-statistics'),
                ],
            ];
        }

        $registry = FilterRegistry::getInstance();

        if (!$registry->has($filterName)) {
            return [
                'success' => false,
                'error'   => [
                    'code'    => 'invalid_filter',
                    'message' => __('Invalid filter name.', 'wp-statistics'),
                ],
            ];
        }

        $filter = $registry->get($filterName);

        // Check if filter is searchable
        if (!$filter->isSearchable()) {
            // For non-searchable filters, return static options
            $options = $filter->getOptions();
            return [
                'success' => true,
                'options' => $options ?: [],
            ];
        }

        // Get searchable options
        $options = $filter->searchOptions($search, $limit);

        return [
            'success' => true,
            'options' => $options,
        ];
    }
}
