<?php

namespace WP_Statistics\Service\Admin\Dashboard\Endpoints;

use WP_Statistics\Components\Ajax;
use WP_Statistics\Service\Admin\AccessControl\AccessLevel;
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
 * Supports lazy loading - endpoints are only instantiated when the AJAX
 * request is actually made, not when the manager is initialized.
 *
 * All analytics queries go through the unified Analytics Query API
 * endpoint registered via registerGlobalEndpoint().
 *
 * @since 15.0.0
 */
class AjaxManager
{
    /**
     * Array of registered global endpoint instances.
     *
     * @var PageActionInterface[]
     */
    private $globalEndpoints = [];

    /**
     * Array of endpoint class names and their action names for lazy loading.
     *
     * @var array<string, array{class: string, method: string}>
     */
    private $endpointClasses = [];

    /**
     * Register a global endpoint instance.
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
            $this->handleAjaxRequest($endpoint, $handlerMethod);
        }, false);

        $this->globalEndpoints[] = $endpoint;

        return $this;
    }

    /**
     * Register a global endpoint class for lazy loading.
     *
     * The endpoint will only be instantiated when the AJAX request is made.
     * This improves performance by deferring object creation.
     *
     * @param string $className Fully qualified class name implementing PageActionInterface
     * @param string $actionName The AJAX action name (without wp_statistics_ prefix)
     * @param string $handlerMethod The method to call on the endpoint (default: 'handleQuery')
     * @return self For method chaining
     */
    public function registerGlobalEndpointClass(string $className, string $actionName, string $handlerMethod = 'handleQuery')
    {
        $this->endpointClasses[$actionName] = [
            'class'  => $className,
            'method' => $handlerMethod,
        ];

        Ajax::register($actionName, function () use ($className, $handlerMethod) {
            // Lazy instantiation - only create when AJAX request comes in
            $endpoint = new $className();
            $this->handleAjaxRequest($endpoint, $handlerMethod);
        }, false);

        return $this;
    }

    /**
     * Handle the AJAX request with security checks.
     *
     * @param PageActionInterface $endpoint The endpoint handler
     * @param string $handlerMethod The method to call
     * @return void
     */
    private function handleAjaxRequest(PageActionInterface $endpoint, string $handlerMethod)
    {
        $nonce = $_SERVER['HTTP_X_WP_NONCE'] ?? '';

        if (!wp_verify_nonce($nonce, 'wp_statistics_dashboard_nonce')) {
            wp_send_json_error([
                'code'    => 'bad_nonce',
                'message' => __('Security check failed. Please refresh the page and try again.', 'wp-statistics')
            ], 403);
        }

        if (!User::hasAccessLevel(AccessLevel::OWN_CONTENT)) {
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
    }

    /**
     * Get all registered endpoint instances.
     *
     * Note: This only returns instances that were registered with registerGlobalEndpoint(),
     * not lazy-loaded class registrations.
     *
     * @return PageActionInterface[]
     */
    public function getEndpoints()
    {
        return $this->globalEndpoints;
    }
}
