<?php

namespace WP_Statistics\Service\Admin\ReactApp;

use WP_Statistics\Service\Assets\AssetsFactory;
use WP_Statistics\Service\Admin\ReactApp\Controllers\Endpoints\AnalyticsQuery;
use WP_Statistics\Service\Admin\ReactApp\Controllers\Endpoints\FilterOptions;
use WP_Statistics\Service\Admin\ReactApp\Controllers\Endpoints\UserPreferences;
use WP_Statistics\Service\Admin\ReactApp\Managers\LocalizeDataManager;
use WP_Statistics\Service\Admin\ReactApp\Providers\GlobalDataProvider;
use WP_Statistics\Service\Admin\ReactApp\Providers\HeaderDataProvider;
use WP_Statistics\Service\Admin\ReactApp\Providers\LayoutDataProvider;
use WP_Statistics\Service\Admin\ReactApp\Providers\FiltersProvider;
use WP_Statistics\Service\Admin\ReactApp\Requests\AjaxManager;
use WP_Statistics\Service\Admin\Settings\SettingsAjaxHandler;

/**
 * React Application Manager for WP Statistics.
 *
 * Manages the entire React Single Page Application (SPA) that handles
 * both Dashboard and Settings pages. This class coordinates:
 * - React asset loading via AssetsFactory
 * - AJAX endpoint registration for Dashboard and Settings
 * - Localized data providers for React components
 *
 * The React SPA uses hash-based routing:
 * - #/overview - Dashboard overview
 * - #/settings/general - Settings pages
 *
 * Menu registration is handled by AdminMenuManager.
 *
 * @since 15.0.0
 */
class ReactAppManager
{
    /**
     * Instance of AjaxManager handling Dashboard AJAX requests.
     *
     * @var AjaxManager
     */
    private $ajax;

    /**
     * Instance of SettingsAjaxHandler for Settings AJAX requests.
     *
     * @var SettingsAjaxHandler
     */
    private $settingsAjax;

    /**
     * Instance of LocalizeDataManager handling data sent to React.
     *
     * @var LocalizeDataManager
     */
    private $localizeDataManager;

    /**
     * Initialize the React Application Manager.
     *
     * Sets up:
     * 1. Dashboard AJAX endpoints (analytics, filters, preferences)
     * 2. Settings AJAX endpoints (get/save settings, email preview)
     * 3. Localized data providers for React
     * 4. React asset loading
     */
    public function __construct()
    {
        $this->initDashboardAjax();
        $this->initSettingsAjax();
        $this->initLocalizeData();

        AssetsFactory::React();
    }

    /**
     * Initialize Dashboard AJAX endpoints.
     *
     * Registers global endpoints available across all React pages:
     * - wp_statistics_analytics: Unified analytics query endpoint
     * - wp_statistics_get_filter_options: Filter options search endpoint
     * - wp_statistics_user_preferences: User preferences save/reset endpoint
     *
     * @return void
     */
    private function initDashboardAjax()
    {
        $this->ajax = (new AjaxManager())
            ->registerGlobalEndpoint(new AnalyticsQuery())
            ->registerGlobalEndpoint(new FilterOptions())
            ->registerGlobalEndpoint(new UserPreferences());
    }

    /**
     * Initialize Settings AJAX endpoints.
     *
     * Registers settings-specific endpoints:
     * - wp_statistics_settings_get: Get all settings
     * - wp_statistics_settings_save: Save settings
     * - wp_statistics_email_preview: Generate email preview
     * - wp_statistics_email_send_test: Send test email
     *
     * @return void
     */
    private function initSettingsAjax()
    {
        $this->settingsAjax = new SettingsAjaxHandler();
        $this->settingsAjax->register();
    }

    /**
     * Initialize localized data providers.
     *
     * Sets up the LocalizeDataManager and registers all data providers
     * that will send data to React components via window.wps_react.
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

    /**
     * Get the Dashboard AJAX manager.
     *
     * @return AjaxManager
     */
    public function getAjaxManager()
    {
        return $this->ajax;
    }

    /**
     * Get the Settings AJAX handler.
     *
     * @return SettingsAjaxHandler
     */
    public function getSettingsAjaxHandler()
    {
        return $this->settingsAjax;
    }

    /**
     * Get the localized data manager.
     *
     * @return LocalizeDataManager
     */
    public function getLocalizeDataManager()
    {
        return $this->localizeDataManager;
    }
}
