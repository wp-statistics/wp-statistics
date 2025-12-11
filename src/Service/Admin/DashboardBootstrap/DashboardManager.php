<?php

namespace WP_Statistics\Service\Admin\DashboardBootstrap;

use WP_Statistics\Service\Assets\AssetsFactory;
use WP_Statistics\Service\Admin\DashboardBootstrap\Controllers\Root\RootController;
use WP_Statistics\Service\Admin\DashboardBootstrap\Controllers\Root\Endpoints\AnalyticsQuery;
use WP_Statistics\Service\Admin\DashboardBootstrap\Controllers\Root\Endpoints\FilterOptions;
use WP_Statistics\Service\Admin\DashboardBootstrap\Managers\LocalizeDataManager;
use WP_Statistics\Service\Admin\DashboardBootstrap\Providers\GlobalDataProvider;
use WP_Statistics\Service\Admin\DashboardBootstrap\Providers\HeaderDataProvider;
use WP_Statistics\Service\Admin\DashboardBootstrap\Providers\LayoutDataProvider;
use WP_Statistics\Service\Admin\DashboardBootstrap\Providers\FiltersProvider;
use WP_Statistics\Service\Admin\DashboardBootstrap\Requests\AjaxManager;

/**
 * Manages the initialization and coordination of the WP Statistics dashboard components.
 *
 * This class serves as the main entry point for the dashboard functionality, responsible for:
 * - Initializing dashboard controllers (e.g., RootController)
 * - Setting up AJAX request handling through AjaxManager
 * - Managing localized data providers for React components
 * - Loading required React assets for the dashboard UI
 *
 * The class follows a modular architecture where:
 * - Each controller handles specific dashboard functionality
 * - AjaxManager coordinates all AJAX communications
 * - LocalizeDataManager collects data from providers for React
 *
 * @since 15.0.0
 */
class DashboardManager
{
    /**
     * Instance of AjaxManager handling AJAX requests for all controllers.
     *
     * @var AjaxManager
     */
    private $ajax;

    /**
     * Instance of LocalizeDataManager handling data sent to React.
     *
     * @var LocalizeDataManager
     */
    private $localizeDataManager;

    /**
     * Array of controller instances handling different dashboard functionalities.
     *
     * Each controller is responsible for a specific section of the dashboard
     * and must extend BaseDashboardController.
     *
     * @var array<string, \WP_Statistics\Abstracts\BaseDashboardController>
     */
    private $controllers;

    /**
     * Initialize the dashboard manager.
     *
     * Sets up all necessary components in the following order:
     * 1. Initializes dashboard controllers
     * 2. Sets up AJAX request handling (including global endpoints)
     * 3. Initializes localized data providers
     * 4. Loads React assets for the dashboard UI
     */
    public function __construct()
    {
        $this->initControllers();
        $this->initAjax();
        $this->initLocalizeData();

        AssetsFactory::React();
    }

    /**
     * Initialize dashboard controllers.
     *
     * Creates instances of all required dashboard controllers. Currently includes:
     * - RootController: Handles the main dashboard page
     *
     * Controllers are kept simple and delegate data handling to dedicated providers.
     */
    private function initControllers()
    {
        $this->controllers = [
            'root' => new RootController(),
        ];
    }

    /**
     * Initialize AJAX request handling.
     *
     * Sets up the AjaxManager with all registered controllers to handle
     * their respective AJAX endpoints securely. Also registers global
     * endpoints that are available across all dashboard pages.
     *
     * Global endpoints:
     * - wp_statistics_analytics: Unified analytics query endpoint
     * - wp_statistics_get_filter_options: Filter options search endpoint
     *
     * @return void
     */
    private function initAjax()
    {
        $this->ajax = (new AjaxManager())
            ->registerGlobalEndpoint(new AnalyticsQuery())
            ->registerGlobalEndpoint(new FilterOptions());
    }

    /**
     * Initialize localized data providers.
     *
     * Sets up the LocalizeDataManager and registers all data providers
     * that will send data to React components. Each provider is responsible
     * for a specific type of data (e.g., sidebar, user info, settings).
     *
     * New providers can be added here to extend the localized data
     * sent to the React dashboard.
     *
     * @return void
     */
    private function initLocalizeData()
    {
        $this->localizeDataManager = new LocalizeDataManager();

        $this->localizeDataManager
            ->registerProvider(new LayoutDataProvider())
            ->registerProvider(new GlobalDataProvider())
            ->registerProvider(new HeaderDataProvider())
            ->registerProvider(new FiltersProvider())
            ->init();
    }
}
