<?php

namespace WP_Statistics\Service\Admin\DashboardBootstrap\Controllers\Root;

use WP_Statistics\Abstracts\BaseDashboardController;
use WP_Statistics\Service\Admin\DashboardBootstrap\Contracts\PageActionInterface;
use WP_Statistics\Service\Admin\DashboardBootstrap\Controllers\Root\Pages\AuthorAnalytics;
use WP_Statistics\Service\Admin\DashboardBootstrap\Controllers\Root\Pages\CategoryAnalytics;
use WP_Statistics\Service\Admin\DashboardBootstrap\Controllers\Root\Pages\ContentAnalytics;
use WP_Statistics\Service\Admin\DashboardBootstrap\Controllers\Root\Pages\Devices;
use WP_Statistics\Service\Admin\DashboardBootstrap\Controllers\Root\Pages\Geographics;
use WP_Statistics\Service\Admin\DashboardBootstrap\Controllers\Root\Pages\Overview;
use WP_Statistics\Service\Admin\DashboardBootstrap\Controllers\Root\Pages\PageInsights;
use WP_Statistics\Service\Admin\DashboardBootstrap\Controllers\Root\Pages\Referrals;
use WP_Statistics\Service\Admin\DashboardBootstrap\Controllers\Root\Pages\VisitorInsights;
use WP_Statistics\Service\Admin\DashboardBootstrap\Views\Root;

/**
 * Controller for handling the Dashboard Root page.
 *
 * This controller is responsible for wiring the dashboard "Root" view
 * to the admin bootstrap. It extends the base dashboard controller
 * and keeps the controller layer simple by delegating:
 * - Data handling to dedicated providers through LocalizeDataManager
 * - Page-specific actions to dedicated page handler classes
 *
 * The controller acts as a registry for page action handlers, collecting
 * and managing all AJAX actions from different pages in the Root dashboard.
 *
 * @since 15.0.0
 */
class RootController extends BaseDashboardController
{
    /**
     * The view class for the Dashboard Root page.
     *
     * @var string|null
     */
    protected $pageView = Root::class;

    /**
     * Collection of page action handlers.
     *
     * @var array<string, PageActionInterface>
     */
    private $pageHandlers = [];

    /**
     * Initialize the controller and register page handlers.
     */
    public function __construct()
    {
        parent::__construct();
        $this->registerPageHandlers();
    }

    /**
     * Register all page action handlers.
     *
     * Add new page handlers here to automatically include their actions
     * in the AJAX registration system. The page name from getPageName() method
     * will be used as the array key.
     *
     * @return void
     */
    private function registerPageHandlers()
    {
        $pages = [
            new Overview(),
            new VisitorInsights(),
            new PageInsights(),
            new Referrals(),
            new ContentAnalytics(),
            new AuthorAnalytics(),
            new CategoryAnalytics(),
            new Geographics(),
            new Devices()
        ];

        foreach ($pages as $page) {
            $this->pageHandlers[$page->getPageName()] = $page;
        }
    }

    /**
     * Get AJAX actions handled by RootController.
     *
     * Collects and returns all action names from registered page handlers.
     * Returns an associative array where keys are page names and values are action name arrays.
     * These actions are automatically registered with WordPress AJAX system.
     *
     * Format: ['page_name' => ['action1', 'action2']]
     *
     * @return array<string, array> List of available AJAX actions grouped by page
     */
    public function getActions()
    {
        $actions = [];

        foreach ($this->pageHandlers as $handler) {
            $pageName = $handler->getPageName();
            // Get only the action names (keys) from the mapping
            $actions[$pageName] = array_keys($handler->registerActions());
        }

        return $actions;
    }

    /**
     * Get the method name for a given action.
     *
     * Searches through all page handlers to find the method name that
     * corresponds to the given action name.
     *
     * @param string $action Action name (in snake_case)
     * @return string|null Method name (in camelCase) or null if not found
     */
    public function getMethodForAction(string $action)
    {
        foreach ($this->pageHandlers as $handler) {
            $actions = $handler->registerActions();
            
            if (isset($actions[$action])) {
                return $actions[$action];
            }
        }

        return null;
    }

    /**
     * get page controller for a given page name.
     *
     * @return array<string, PageActionInterface>
     */
    public function getPageObject($pageName)
    {
        if (!isset($this->pageHandlers[$pageName])) {
            return null;
        }

        return $this->pageHandlers[$pageName];
    }

    /**
     * Magic method to delegate action calls to appropriate page handlers.
     *
     * When an AJAX action is called, this method finds the corresponding
     * page handler and invokes the action method on it.
     *
     * @param string $method Method name (action name in snake_case)
     * @param array $arguments Method arguments
     * @return mixed Result from the page handler method
     * @throws \BadMethodCallException If action method is not found
     */
    public function __call(string $method, array $arguments)
    {
        // Try to find and call the method in page handlers.
        foreach ($this->pageHandlers as $handler) {
            $actions = $handler->registerActions();
            
            // Check if this action is registered in this handler
            if (isset($actions[$method])) {
                $methodName = $actions[$method];
                if (method_exists($handler, $methodName)) {
                    return call_user_func_array([$handler, $methodName], $arguments);
                }
            }
        }

        throw new \BadMethodCallException(
            sprintf('Action method "%s" not found in any page handler', $method)
        );
    }
}
