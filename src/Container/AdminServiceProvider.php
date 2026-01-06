<?php

namespace WP_Statistics\Container;

use WP_Statistics\Service\Admin\AdminBar;
use WP_Statistics\Service\Admin\AdminMenuManager;
use WP_Statistics\Service\Admin\AnonymizedUsageData\AnonymizedUsageDataManager;
use WP_Statistics\Service\Admin\CommandPalette\CommandPaletteHandler;
use WP_Statistics\Service\Admin\ReactApp\ReactAppManager;
use WP_Statistics\Service\Admin\FilterHandler\FilterManager;
use WP_Statistics\Service\Admin\LicenseManagement\LicenseManagementManager;
use WP_Statistics\Service\Admin\Network\NetworkMenuManager;
use WP_Statistics\Service\Admin\Notification\NotificationManager;
use WP_Statistics\Service\Admin\Posts\PostsManager;
use WP_Statistics\Service\Admin\SiteHealth\SiteHealthInfo;
use WP_Statistics\Service\Admin\SiteHealth\SiteHealthTests;
use WP_Statistics\Service\Admin\Tools\Endpoints\ToolsEndpoints;
use WP_Statistics\Service\Admin\Notice\NoticeManager;
use WP_Statistics\Service\Admin\Notice\Notices\DiagnosticNotice;
use WP_Statistics\Service\EmailReport\EmailReportManager;
use WP_Statistics\Service\ImportExport\ImportExportManager;

/**
 * Admin Service Provider.
 *
 * Registers admin-specific services (lazy loaded when needed).
 *
 * @since 15.0.0
 */
class AdminServiceProvider implements ServiceProvider
{
    /**
     * Register services with the container.
     *
     * @param ServiceContainer $container The service container.
     * @return void
     */
    public function register(ServiceContainer $container): void
    {
        // Admin Bar - works on both frontend and admin
        $container->register('admin_bar', function () {
            return new AdminBar();
        });

        // Admin Menu Manager
        $container->register('admin_menu', function () {
            return new AdminMenuManager();
        });

        // React App Manager (Dashboard + Settings SPA)
        // Handles both Dashboard and Settings React pages, including:
        // - React asset loading
        // - Dashboard AJAX endpoints (analytics, filters, preferences)
        // - Settings AJAX endpoints (get/save settings, email preview)
        // - Localized data providers
        $container->register('react_app', function () {
            return new ReactAppManager();
        });

        // Email Report Manager
        $container->register('email_reports', function () {
            return new EmailReportManager();
        });

        // Command Palette (WordPress Cmd+K integration)
        $container->register('command_palette', function () {
            return new CommandPaletteHandler();
        });

        // Site Health Info (WordPress Site Health debug information)
        $container->register('site_health_info', function () {
            return SiteHealthInfo::instance();
        });

        // Site Health Tests (WordPress Site Health status tests)
        $container->register('site_health_tests', function () {
            return SiteHealthTests::instance();
        });

        // License Management (add-ons licensing and updates)
        $container->register('license_management', function () {
            return new LicenseManagementManager();
        });

        // Posts Manager (hits column, post meta tracking)
        $container->register('posts', function () {
            return new PostsManager();
        });

        // Filter Manager (AJAX filter handling)
        $container->register('filters', function () {
            return new FilterManager();
        });

        // Notification Manager (admin notifications)
        $container->register('notifications', function () {
            return new NotificationManager();
        });

        // Anonymized Usage Data Manager (opt-in telemetry)
        $container->register('anonymized_data', function () {
            return new AnonymizedUsageDataManager();
        });

        // Import/Export Manager (data import, export, backup)
        $container->register('import_export', function () {
            return new ImportExportManager();
        });

        // Tools Endpoints (system info, scheduled tasks, schema)
        $container->register('tools_endpoints', function () {
            $endpoints = new ToolsEndpoints();
            $endpoints->register();
            return $endpoints;
        });
        
        // Network Menu Manager (multisite network admin menu)
        $container->register('network_menu', function () {
            return new NetworkMenuManager();
        });

        // Global Notice Manager (admin notices for React and non-React pages)
        $container->register('notice_manager', function () {
            // Initialize the notice manager
            NoticeManager::init();

            // Register notice generators
            NoticeManager::registerGenerator(new DiagnosticNotice());

            return NoticeManager::class;
        });
    }

    /**
     * Bootstrap services.
     *
     * @param ServiceContainer $container The service container.
     * @return void
     */
    public function boot(ServiceContainer $container): void
    {
        // Admin bar is loaded on both frontend and admin
        $container->get('admin_bar');

        // Admin-only services
        if (is_admin()) {
            $container->get('admin_menu');
            $container->get('react_app');
            $container->get('email_reports');
            $container->get('command_palette');
            $container->get('site_health_info');
            $container->get('site_health_tests');
            $container->get('license_management');
            $container->get('posts');
            $container->get('filters');
            $container->get('notifications');
            $container->get('anonymized_data');
            $container->get('import_export');
            $container->get('tools_endpoints');
            $container->get('network_menu');
            $container->get('notice_manager');
        }
    }
}
