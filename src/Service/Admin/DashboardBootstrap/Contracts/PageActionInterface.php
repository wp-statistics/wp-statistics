<?php

namespace WP_Statistics\Service\Admin\DashboardBootstrap\Contracts;

/**
 * Interface for global endpoint handlers.
 *
 * Endpoints implementing this interface are registered application-wide
 * via AjaxManager::registerGlobalEndpoint().
 *
 * @since 15.0.0
 */
interface PageActionInterface
{
    /**
     * Get the endpoint name.
     *
     * Returns a unique identifier for this handler (e.g., 'analytics', 'get_filter_options').
     * This is used to build the AJAX action name: wp_statistics_{endpointName}
     *
     * @return string The endpoint identifier
     */
    public function getEndpointName();
}

