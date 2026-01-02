<?php

namespace WP_Statistics\Service\Admin\DashboardBootstrap;

use WP_Statistics\Service\Assets\AssetsFactory;
use WP_Statistics\Service\Admin\DashboardBootstrap\Controllers\Root\Endpoints\AnalyticsQuery;
use WP_Statistics\Service\Admin\DashboardBootstrap\Controllers\Root\Endpoints\FilterOptions;
use WP_Statistics\Service\Admin\DashboardBootstrap\Controllers\Root\Endpoints\UserPreferences;
use WP_Statistics\Service\Admin\DashboardBootstrap\Managers\LocalizeDataManager;
use WP_Statistics\Service\Admin\DashboardBootstrap\Providers\GlobalDataProvider;
use WP_Statistics\Service\Admin\DashboardBootstrap\Providers\HeaderDataProvider;
use WP_Statistics\Service\Admin\DashboardBootstrap\Providers\LayoutDataProvider;
use WP_Statistics\Service\Admin\DashboardBootstrap\Providers\FiltersProvider;
use WP_Statistics\Service\Admin\DashboardBootstrap\Requests\AjaxManager;

/**
 * Manages the initialization and coordination of the WP Statistics dashboard components.
 *
 * V15 version - does NOT instantiate controllers that use legacy menu filters.
 * Menu registration is handled by AdminMenuManager.
 *
 * This class is responsible for:
 * - Setting up AJAX request handling through AjaxManager
 * - Managing localized data providers for React components
 * - Loading required React assets for the dashboard UI
 *
 * @since 15.0.0
 */
class DashboardManager
{
    /**
     * Instance of AjaxManager handling AJAX requests.
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
     * Initialize the dashboard manager.
     *
     * Sets up:
     * 1. AJAX request handling (global endpoints)
     * 2. Localized data providers
     * 3. React assets for the dashboard UI
     */
    public function __construct()
    {
        $this->initAjax();
        $this->initLocalizeData();

        AssetsFactory::React();
    }

    /**
     * Initialize AJAX request handling.
     *
     * Registers global endpoints that are available across all dashboard pages:
     * - wp_statistics_analytics: Unified analytics query endpoint
     * - wp_statistics_get_filter_options: Filter options search endpoint
     * - wp_statistics_user_preferences: User preferences save/reset endpoint
     *
     * @return void
     */
    private function initAjax()
    {
        $this->ajax = (new AjaxManager())
            ->registerGlobalEndpoint(new AnalyticsQuery())
            ->registerGlobalEndpoint(new FilterOptions())
            ->registerGlobalEndpoint(new UserPreferences());
    }

    /**
     * Initialize localized data providers.
     *
     * Sets up the LocalizeDataManager and registers all data providers
     * that will send data to React components.
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
