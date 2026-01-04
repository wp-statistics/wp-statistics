<?php

namespace WP_Statistics\Container;

use WP_Statistics\Service\Admin\AdminBar;
use WP_Statistics\Service\Admin\AdminMenuManager;
use WP_Statistics\Service\Admin\CommandPalette\CommandPaletteHandler;
use WP_Statistics\Service\Admin\DashboardBootstrap\DashboardManager;
use WP_Statistics\Service\Admin\Settings\SettingsManager;
use WP_Statistics\Service\Admin\Network\NetworkManager;
use WP_Statistics\Service\Admin\SiteHealth\SiteHealthInfo;
use WP_Statistics\Service\Admin\SiteHealth\SiteHealthTests;
use WP_Statistics\Service\EmailReport\EmailReportManager;

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

        // Dashboard Manager (React SPA)
        $container->register('dashboard', function () {
            return new DashboardManager();
        });

        // Settings Manager
        $container->register('settings', function () {
            return new SettingsManager();
        });

        // Email Report Manager
        $container->register('email_reports', function () {
            return new EmailReportManager();
        });

        // Network Manager (Multisite)
        $container->register('network', function () {
            return new NetworkManager();
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
            $container->get('dashboard');
            $container->get('settings');
            $container->get('email_reports');
            $container->get('network');
            $container->get('command_palette');
            $container->get('site_health_info');
            $container->get('site_health_tests');
        }
    }
}
