<?php

namespace WP_Statistics\Service\Admin\ReactApp\Requests;

use WP_Statistics\Components\Ajax;
use WP_Statistics\Service\Admin\ReactApp\Contracts\PageActionInterface;
use WP_Statistics\Utils\User;

/**
 * Manages AJAX request handling for the WP Statistics dashboard.
 *
 * This class is responsible for:
 * - Registering global endpoints that are available across all pages
 * - Handling security through nonce verification
 * - Managing error handling and response formatting
 *
 * All analytics queries go through the unified Analytics Query API
 * endpoint registered via registerGlobalEndpoint().
 *
 * @since 15.0.0
 */
class AjaxManager
{
    /**
     * Array of registered global endpoints.
     *
     * @var PageActionInterface[]
     */
    private $globalEndpoints = [];

    /**
     * Register a global endpoint.
     *
     * Global endpoints are available across all dashboard pages and are
     * registered with the action name: wp_statistics_{endpointName}
     *
     * Note: Ajax::register() automatically prepends 'wp_statistics_' to the action,
     * so we only pass the endpoint name here (e.g., 'analytics' becomes
     * 'wp_ajax_wp_statistics_analytics').
     *
     * @param PageActionInterface $endpoint The endpoint handler
     * @param string $handlerMethod The method to call on the endpoint (default: 'handleQuery')
     * @return self For method chaining
     */
    public function registerGlobalEndpoint(PageActionInterface $endpoint, string $handlerMethod = 'handleQuery')
    {
        $actionName = $endpoint->getEndpointName();

        Ajax::register($actionName, function () use ($endpoint, $handlerMethod) {
            $nonce = $_SERVER['HTTP_X_WP_NONCE'] ?? '';

            if (!wp_verify_nonce($nonce, 'wp_statistics_dashboard_nonce')) {
                wp_send_json_error([
                    'code'    => 'bad_nonce',
                    'message' => __('Security check failed. Please refresh the page and try again.', 'wp-statistics')
                ], 403);
            }

            if (!User::hasAccess()) {
                wp_send_json_error([
                    'code'    => 'forbidden',
                    'message' => __('You do not have permission to perform this action.', 'wp-statistics'),
                ], 403);
            }

            try {
                if (!method_exists($endpoint, $handlerMethod)) {
                    throw new \Exception("Handler method '{$handlerMethod}' not found on endpoint.");
                }

                $response = $endpoint->$handlerMethod();

                if (isset($response['success']) && $response['success'] === false) {
                    wp_send_json_error($response['error'] ?? $response);
                } else {
                    wp_send_json($response);
                }
            } catch (\Exception $e) {
                wp_send_json_error([
                    'code'    => 'server_error',
                    'message' => __('An unexpected error occurred.', 'wp-statistics')
                ]);
            }
        }, false);

        $this->globalEndpoints[] = $endpoint;

        return $this;
    }
}