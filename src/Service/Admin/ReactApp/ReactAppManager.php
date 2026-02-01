<?php

namespace WP_Statistics\Service\Admin\ReactApp;

use WP_Statistics\Service\Assets\AssetsFactory;
use WP_Statistics\Service\Admin\Dashboard\Endpoints\AjaxManager;
use WP_Statistics\Service\Admin\Dashboard\Endpoints\AnalyticsQuery;
use WP_Statistics\Service\Admin\Dashboard\Endpoints\FilterOptions;
use WP_Statistics\Service\Admin\Dashboard\Endpoints\GetTermInfo;
use WP_Statistics\Service\Admin\Dashboard\Endpoints\UserPreferences;
use WP_Statistics\Service\Admin\Settings\Endpoints\SettingsEndpoints;
use WP_Statistics\Service\Admin\ReactApp\Managers\LocalizeDataManager;
use WP_Statistics\Service\Admin\ReactApp\Providers\GlobalDataProvider;
use WP_Statistics\Service\Admin\ReactApp\Providers\HeaderDataProvider;
use WP_Statistics\Service\Admin\ReactApp\Providers\LayoutDataProvider;
use WP_Statistics\Service\Admin\ReactApp\Providers\FiltersProvider;
use WP_Statistics\Service\ImportExport\Providers\ImportExportDataProvider;
use WP_Statistics\Service\Admin\ReactApp\Providers\NetworkDataProvider;
use WP_Statistics\Service\Admin\Notice\NoticeDataProvider;

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
     * Instance of SettingsEndpoints for Settings AJAX requests.
     *
     * @var SettingsEndpoints
     */
    private $settingsEndpoints;

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
     * Uses lazy loading - endpoints are only instantiated when the AJAX
     * request is actually made, not during manager initialization.
     *
     * @return void
     */
    private function initDashboardAjax()
    {
        $this->ajax = (new AjaxManager())
            ->registerGlobalEndpointClass(AnalyticsQuery::class, 'analytics')
            ->registerGlobalEndpointClass(FilterOptions::class, 'get_filter_options')
            ->registerGlobalEndpointClass(UserPreferences::class, 'user_preferences')
            ->registerGlobalEndpointClass(GetTermInfo::class, 'get_term_info');
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
        $this->settingsEndpoints = new SettingsEndpoints();
        $this->settingsEndpoints->register();
    }

    /**
     * Initialize localized data providers.
     *
     * Sets up the LocalizeDataManager and registers provider class names
     * for lazy loading. Providers are only instantiated when the filter
     * is actually triggered (when React needs the data).
     *
     * @return void
     */
    private function initLocalizeData()
    {
        $this->localizeDataManager = new LocalizeDataManager();

        // Register provider classes for lazy loading - no instantiation yet
        $this->localizeDataManager
            ->registerProviderClass(LayoutDataProvider::class)
            ->registerProviderClass(GlobalDataProvider::class)
            ->registerProviderClass(HeaderDataProvider::class)
            ->registerProviderClass(FiltersProvider::class)
            ->registerProviderClass(ImportExportDataProvider::class)
            ->registerProviderClass(NetworkDataProvider::class)
            ->registerProviderClass(NoticeDataProvider::class)
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
     * Get the Settings endpoints handler.
     *
     * @return SettingsEndpoints
     */
    public function getSettingsEndpoints()
    {
        return $this->settingsEndpoints;
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
