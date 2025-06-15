<?php

namespace WP_Statistics\Service\Admin\DashboardBootstrap;

use WP_Statistics\Service\Admin\Assets\AdminAssetsFactory;
use WP_Statistics\Service\Admin\DashboardBootstrap\Controllers\MigrationPageController;
use WP_Statistics\Service\Admin\DashboardBootstrap\Requests\AjaxManager;

/**
 * Manages the initialization and coordination of the WP Statistics dashboard components.
 *
 * This class serves as the main entry point for the dashboard functionality, responsible for:
 * - Initializing dashboard controllers (e.g., MigrationPageController)
 * - Setting up AJAX request handling through AjaxManager
 * - Loading required React assets for the dashboard UI
 *
 * The class follows a modular architecture where each controller handles specific
 * dashboard functionality, and the AjaxManager coordinates all AJAX communications.
 *
 * @since 15.0.0
 */
class DashboardManager
{
    /**
     * Instance of AjaxManager handling AJAX requests for all controllers.
     *
     * @since 15.0.0
     * @var AjaxManager
     */
    private $ajax;

    /**
     * Array of controller instances handling different dashboard functionalities.
     *
     * Each controller is responsible for a specific section of the dashboard
     * and must extend BaseDashboardController.
     *
     * @since 15.0.0
     * @var array<string, BaseDashboardController>
     */
    private $controllers;

    /**
     * Initialize the dashboard manager.
     *
     * Sets up all necessary components in the following order:
     * 1. Initializes dashboard controllers
     * 2. Sets up AJAX request handling
     * 3. Loads React assets for the dashboard UI
     *
     * @since 15.0.0
     */
    public function __construct()
    {
        $this->initControllers();
        $this->initAjax();

        AdminAssetsFactory::React();
    }

    /**
     * Initialize dashboard controllers.
     *
     * Creates instances of all required dashboard controllers. Currently includes:
     * - MigrationPageController: Handles database migration functionality
     *
     * @todo Add additional controllers as dashboard functionality expands
     * @since 15.0.0
     */
    private function initControllers()
    {
        $this->controllers = [
            'migration' => new MigrationPageController(),
        ];
    }

    /**
     * Initialize AJAX request handling.
     *
     * Sets up the AjaxManager with all registered controllers to handle
     * their respective AJAX endpoints securely.
     *
     * @since 15.0.0
     */
    public function initAjax()
    {
        $this->ajax = (new AjaxManager())->init($this->controllers);
    }
}