<?php

namespace WP_Statistics\Service\Admin\DashboardBootstrap;

use WP_Statistics\Components\Ajax;
use WP_Statistics\Service\Assets\AssetsFactory;
use WP_Statistics\Service\Admin\DashboardBootstrap\Controllers\Root\RootController;
use WP_Statistics\Service\Admin\DashboardBootstrap\Managers\LocalizeDataManager;
use WP_Statistics\Service\Admin\DashboardBootstrap\Providers\GlobalDataProvider;
use WP_Statistics\Service\Admin\DashboardBootstrap\Providers\HeaderDataProvider;
use WP_Statistics\Service\Admin\DashboardBootstrap\Providers\LayoutDataProvider;
use WP_Statistics\Service\Admin\DashboardBootstrap\Providers\FiltersProvider;
use WP_Statistics\Service\Admin\DashboardBootstrap\Requests\AjaxManager;
use WP_Statistics\Service\AnalyticsQuery\Registry\FilterRegistry;
use WP_Statistics\Utils\User;

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
     * 2. Sets up AJAX request handling
     * 3. Initializes localized data providers
     * 4. Loads React assets for the dashboard UI
     */
    public function __construct()
    {
        $this->initControllers();
        $this->initAjax();
        $this->initGlobalAjax();
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
     * their respective AJAX endpoints securely.
     *
     * @return void
     */
    private function initAjax()
    {
        $this->ajax = (new AjaxManager())->init($this->controllers);
    }

    /**
     * Initialize global AJAX actions.
     *
     * Registers AJAX actions that are not page-specific and available globally
     * across all dashboard pages.
     *
     * @return void
     */
    private function initGlobalAjax()
    {
        // Global filter options endpoint - available for all pages with filters
        Ajax::register('get_filter_options', function () {
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
                $response = $this->handleGetFilterOptions();
                wp_send_json_success($response);
            } catch (\Exception $e) {
                wp_send_json_error([
                    'message' => $e->getMessage()
                ]);
            }
        }, false);
    }

    /**
     * Handle get filter options request.
     *
     * @return array Filter options response
     */
    private function handleGetFilterOptions()
    {
        $filterName = isset($_POST['filter']) ? sanitize_text_field($_POST['filter']) : '';
        $search     = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
        $limit      = isset($_POST['limit']) ? absint($_POST['limit']) : 20;

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
