<?php

namespace WP_Statistics\Service\Admin\DashboardBootstrap\Requests;

use WP_Statistics\Abstracts\BaseDashboardController;
use WP_Statistics\Components\Ajax;
use WP_Statistics\Service\Admin\DashboardBootstrap\Contracts\PageActionInterface;
use WP_Statistics\Utils\User;

/**
 * Manages AJAX request handling for the WP Statistics dashboard.
 *
 * This class is responsible for:
 * - Registering AJAX endpoints for dashboard controllers
 * - Registering global endpoints that are available across all pages
 * - Handling security through nonce verification
 * - Managing error handling and response formatting
 * - Coordinating between controllers and the WordPress AJAX system
 *
 * The class works in conjunction with BaseDashboardController to provide
 * a structured way of handling AJAX requests in the dashboard interface.
 * Each controller can define its own set of AJAX actions that will be
 * automatically registered with WordPress.
 *
 * @since 15.0.0
 */
class AjaxManager
{

    /**
     * Array of registered dashboard controllers.
     *
     * Each controller must extend BaseDashboardController and implement
     * the getActions() method to define its available AJAX endpoints.
     *
     * @var array
     */
    private $controllers = [];

    /**
     * Array of registered global endpoints.
     *
     * @var PageActionInterface[]
     */
    private $globalEndpoints = [];

    /**
     * Initialize the AJAX manager with a set of controllers.
     *
     * This method sets up the controllers and registers their AJAX actions
     * with WordPress. Each controller's actions will be prefixed with the
     * controller name for uniqueness.
     *
     * @param array $controllers Array of controller instances
     * @return self For method chaining
     */
    public function init($controllers)
    {
        $this->controllers = $controllers;
        $this->registerActions();

        return $this;
    }

    /**
     * Register a global endpoint.
     *
     * Global endpoints are available across all dashboard pages and are
     * registered with the action name from the endpoint's getActionName() method.
     *
     * @param PageActionInterface $endpoint The endpoint handler
     * @param string $handlerMethod The method to call on the endpoint (default: 'handleQuery')
     * @return self For method chaining
     */
    public function registerGlobalEndpoint(PageActionInterface $endpoint, string $handlerMethod = 'handleQuery')
    {
        $endpointClass = get_class($endpoint);
        $actionName    = $endpointClass::getActionName();

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

    /**
     * Register AJAX actions for all controllers.
     *
     * Iterates through all registered controllers and registers their
     * AJAX actions with WordPress. Each action is registered with proper
     * security checks and error handling.
     *
     * Actions are grouped by page name: ['page_name' => ['action1', 'action2']]
     * The final action name will be: controller_page_action
     *
     * @return void
     * @todo: the controller should be loaded based on page slug after frontend is ready.
     */
    private function registerActions()
    {
        foreach ($this->controllers as $name => $controller) {
            if (!($controller instanceof BaseDashboardController)) {
                continue;
            }

            $actions = $controller->getActions();

            // Handle grouped actions: ['page_name' => ['action1', 'action2']]
            foreach ($actions as $pageName => $pageActions) {
                $pageController = $controller->getPageObject($pageName);

                foreach ($pageActions as $action) {
                    $this->registerAction($name, $action, $pageController, $controller, $pageName);
                }
            }
        }
    }

    /**
     * Register a single AJAX action for a controller.
     *
     * Sets up the WordPress AJAX action with:
     * - Proper action name prefixing (controller_page_action)
     * - Nonce verification for security
     * - Error handling and JSON response formatting
     * - Method existence verification
     * - Mapping from snake_case action names to camelCase method names
     *
     * @param string $controllerName The name of the controller
     * @param string $action The action name to register (snake_case)
     * @param PageActionInterface $pageController The page controller instance
     * @param BaseDashboardController $controller The controller instance
     * @param string $pageName Page name for the action
     * @return void
     */
    private function registerAction($controllerName, $action, $pageController, $controller, $pageName)
    {
        // Build action name: controller_page_action
        $actionName = "{$controllerName}_{$pageName}_{$action}";

        // Get the actual method name from the controller (camelCase)
        $method = method_exists($controller, 'getMethodForAction') 
            ? $controller->getMethodForAction($action) 
            : $action;

        Ajax::register($actionName, function () use ($method, $pageController) {
            $nonce = $_SERVER['HTTP_X_WP_NONCE'] ?? '';
            
            if (! wp_verify_nonce($nonce, 'wp_statistics_dashboard_nonce')) {
                wp_send_json_error([
                    'code'    => 'bad_nonce',
                    'message' => __('Security check failed. Please refresh the page and try again.', 'wp-statistics')
                ], 403 );
            }

            if (! User::hasAccess()) {
                wp_send_json_error([
                    'code'    => 'forbidden',
                    'message' => __('You do not have permission to perform this action.', 'wp-statistics'),
                ], 403);
            }

            try {
                if (!$method || !method_exists($pageController, $method)) {
                    throw new \Exception("Action method not found");
                }

                $response = $pageController->$method();

                wp_send_json_success($response);
            } catch (\Exception $e) {
                wp_send_json_error([
                    'message' => $e->getMessage()
                ]);
            }
        }, false);
    }
}