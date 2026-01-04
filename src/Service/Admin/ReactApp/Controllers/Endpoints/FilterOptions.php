<?php

namespace WP_Statistics\Service\Admin\ReactApp\Controllers\Endpoints;

use WP_Statistics\Service\Admin\ReactApp\Contracts\PageActionInterface;
use WP_Statistics\Service\AnalyticsQuery\Registry\FilterRegistry;
use WP_Statistics\Utils\Request;

/**
 * Filter Options endpoint handler.
 *
 * Provides searchable filter options for the dashboard filters.
 * Handles both static and dynamic (searchable) filter options.
 *
 * Registered via AjaxManager::registerGlobalEndpoint() as
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
        $data = Request::getRequestData();

        $filterName = isset($data['filter']) ? sanitize_text_field($data['filter']) : '';
        $search     = isset($data['search']) ? sanitize_text_field($data['search']) : '';
        $limit      = isset($data['limit']) ? absint($data['limit']) : 20;

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
