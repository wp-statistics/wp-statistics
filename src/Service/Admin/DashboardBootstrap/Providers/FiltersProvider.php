<?php

namespace WP_Statistics\Service\Admin\DashboardBootstrap\Providers;

use WP_Statistics\Service\Admin\DashboardBootstrap\Contracts\LocalizeDataProviderInterface;
use WP_Statistics\Service\AnalyticsQuery\Registry\FilterRegistry;

/**
 * Provider for available filters data.
 *
 * Exposes the filter definitions to the React frontend so it can
 * dynamically build filter UI components.
 *
 * @since 15.0.0
 */
class FiltersProvider implements LocalizeDataProviderInterface
{
    /**
     * Get filter data for the dashboard.
     *
     * @return array Array of filter definitions
     */
    public function getData()
    {
        $registry = FilterRegistry::getInstance();

        $data = [
            'available' => $registry->getAllAsArray(),
            'operators' => $this->getOperatorDefinitions(),
        ];

        /**
         * Filter the filters data before sending to React.
         *
         * @param array $data Array of filter data
         * @since 15.0.0
         */
        return apply_filters('wp_statistics_dashboard_filters_data', $data);
    }

    /**
     * Get operator definitions for the frontend.
     *
     * @return array
     */
    private function getOperatorDefinitions(): array
    {
        return [
            'is'          => ['label' => __('Is', 'wp-statistics'), 'type' => 'single'],
            'is_not'      => ['label' => __('Is not', 'wp-statistics'), 'type' => 'single'],
            'is_null'     => ['label' => __('Is empty', 'wp-statistics'), 'type' => 'single'],
            'in'          => ['label' => __('Is one of', 'wp-statistics'), 'type' => 'multiple'],
            'not_in'      => ['label' => __('Is not one of', 'wp-statistics'), 'type' => 'multiple'],
            'contains'    => ['label' => __('Contains', 'wp-statistics'), 'type' => 'single'],
            'starts_with' => ['label' => __('Starts with', 'wp-statistics'), 'type' => 'single'],
            'ends_with'   => ['label' => __('Ends with', 'wp-statistics'), 'type' => 'single'],
            'gt'          => ['label' => __('Greater than', 'wp-statistics'), 'type' => 'single'],
            'gte'         => ['label' => __('Greater than or equal', 'wp-statistics'), 'type' => 'single'],
            'lt'          => ['label' => __('Less than', 'wp-statistics'), 'type' => 'single'],
            'lte'         => ['label' => __('Less than or equal', 'wp-statistics'), 'type' => 'single'],
            'between'     => ['label' => __('Between', 'wp-statistics'), 'type' => 'range'],
            'before'      => ['label' => __('Before', 'wp-statistics'), 'type' => 'single'],
            'after'       => ['label' => __('After', 'wp-statistics'), 'type' => 'single'],
            'in_the_last' => ['label' => __('In the last', 'wp-statistics'), 'type' => 'single'],
        ];
    }

    /**
     * Get the localize data key.
     *
     * @return string
     */
    public function getKey()
    {
        return 'filters';
    }
}
