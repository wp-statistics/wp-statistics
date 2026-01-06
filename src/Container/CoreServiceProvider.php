<?php

namespace WP_Statistics\Container;

use WP_Statistics\Service\HooksManager;
use WP_Statistics\Service\Privacy\PrivacyManager;
use WP_Statistics\Service\Cron\CronManager;
use WP_Statistics\Service\CLI\CLIManager;
use WP_Statistics\Service\Shortcode\ShortcodeService;
use WP_Statistics\Service\Blocks\BlocksManager;
use WP_Statistics\Service\Tracking\TrackerControllerFactory;
use WP_Statistics\Service\Database\Managers\MigrationHandler;
use WP_Statistics\Service\Assets\Handlers\FrontendHandler;
use WP_Statistics\Service\CustomEvent\CustomEventHandler;
use WP_Statistics\Service\Ajax\AjaxDispatcher;

/**
 * Core Service Provider.
 *
 * Registers core plugin services that are needed on every request.
 *
 * @since 15.0.0
 */
class CoreServiceProvider implements ServiceProvider
{
    /**
     * Register services with the container.
     *
     * @param ServiceContainer $container The service container.
     * @return void
     */
    public function register(ServiceContainer $container): void
    {
        // HooksManager - registered as singleton (already instantiated early)
        $container->register('hooks', function () {
            return new HooksManager();
        });

        // Tracking - lazy loaded
        $container->register('tracking', function () {
            return TrackerControllerFactory::createController();
        });

        // Cron Manager - lazy loaded
        $container->register('cron', function () {
            return new CronManager();
        });

        // Privacy Manager - lazy loaded
        $container->register('privacy', function () {
            return new PrivacyManager();
        });

        // Shortcodes - lazy loaded
        $container->register('shortcodes', function () {
            return new ShortcodeService();
        });

        // Gutenberg Blocks - lazy loaded
        $container->register('blocks', function () {
            return new BlocksManager();
        });

        // Frontend Handler - lazy loaded, only on frontend
        $container->register('frontend', function () {
            return new FrontendHandler();
        });

        // Custom Event Handler - handles batch tracking custom events
        $container->register('custom_events', function () {
            return new CustomEventHandler();
        });

        // AJAX Dispatcher - processes wp_statistics_ajax_list filter
        $container->register('ajax', function () {
            return new AjaxDispatcher();
        });

        // Aliases for common access patterns
        $container->alias('tracker', 'tracking');
    }

    /**
     * Bootstrap services.
     *
     * @param ServiceContainer $container The service container.
     * @return void
     */
    public function boot(ServiceContainer $container): void
    {
        // Initialize tracking controller
        $container->get('tracking');

        // Initialize cron manager
        $container->get('cron');

        // Initialize CLI commands (only if WP-CLI is active)
        if (defined('WP_CLI') && WP_CLI) {
            CLIManager::register();
        }

        // Initialize migration handler
        MigrationHandler::init();

        // Initialize privacy handlers
        $container->get('privacy');

        // Initialize shortcodes
        $container->get('shortcodes');

        // Initialize Gutenberg blocks
        $container->get('blocks');

        // Initialize frontend assets (only on frontend)
        if (!is_admin()) {
            $container->get('frontend');
        }

        // Initialize custom event handler
        $container->get('custom_events');

        // Initialize AJAX dispatcher (must be after tracking to collect all handlers)
        $container->get('ajax');
    }
}
