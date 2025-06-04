<?php

namespace WP_Statistics\Service\Admin\DashboardBootstrap\Requests;

use WP_Statistics\Abstracts\BaseDashboardController;
use WP_Statistics\Components\Ajax;

/**
 * Manages AJAX request handling for the WP Statistics dashboard.
 *
 * This class is responsible for:
 * - Registering AJAX endpoints for dashboard controllers
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
     * @since 15.0.0
     * @var array
     */
    private $controllers;

    /**
     * Initialize the AJAX manager with a set of controllers.
     *
     * This method sets up the controllers and registers their AJAX actions
     * with WordPress. Each controller's actions will be prefixed with the
     * controller name for uniqueness.
     *
     * @param array $controllers Array of controller instances
     * @return void
     * @since 15.0.0
     */
    public function init($controllers)
    {
        $this->controllers = $controllers;
        $this->registerActions();
    }

    /**
     * Register AJAX actions for all controllers.
     *
     * Iterates through all registered controllers and registers their
     * AJAX actions with WordPress. Each action is registered with proper
     * security checks and error handling.
     *
     * @return void
     * @since 15.0.0
     * @todo: the controller should be loaded based on page slug after frontend is ready.
     */
    private function registerActions()
    {
        foreach ($this->controllers as $name => $controller) {
            if (!($controller instanceof BaseDashboardController)) {
                continue;
            }

            $controller->initFilters();

            $actions = $controller->getActions();
            foreach ($actions as $action) {
                $this->registerAction($name, $action, $controller);
            }
        }
    }

    /**
     * Register a single AJAX action for a controller.
     *
     * Sets up the WordPress AJAX action with:
     * - Proper action name prefixing
     * - Nonce verification for security
     * - Error handling and JSON response formatting
     * - Method existence verification
     *
     * @param string $controllerName The name of the controller
     * @param string $action The action name to register
     * @param BaseDashboardController $controller The controller instance
     * @return void
     * @since 15.0.0
     */
    private function registerAction($controllerName, $action, $controller)
    {
        $actionName = "{$controllerName}_{$action}";

        Ajax::register($actionName, function () use ($action, $controller) {
            check_ajax_referer('wp_statistics_dashboard_nonce', 'nonce');

            try {
                if (!method_exists($controller, $action)) {
                    throw new \Exception("Action method not found");
                }

                $response = $controller->$action();
                wp_send_json_success($response);
            } catch (\Exception $e) {
                wp_send_json_error([
                    'message' => $e->getMessage()
                ]);
            }
        }, false);
    }
}