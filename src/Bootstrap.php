<?php

namespace WP_Statistics;

use WP_Statistics\Container\ServiceContainer;
use WP_Statistics\Container\CoreServiceProvider;
use WP_Statistics\Container\AdminServiceProvider;
use WP_Statistics\Service\HooksManager;
use WP_Statistics\Service\Installation\InstallManager;

defined('ABSPATH') || exit;

/**
 * Bootstrap class for WP Statistics.
 *
 * Main plugin initialization using modern architecture with:
 * - ServiceContainer for lazy loading
 * - React-based Dashboard and Settings
 * - Modular service providers
 *
 * @since 15.0.0
 */
class Bootstrap
{
    /**
     * Whether plugin is initialized.
     *
     * @var bool
     */
    private static $initialized = false;

    /**
     * Service Container instance.
     *
     * @var ServiceContainer|null
     */
    private static $container = null;

    /**
     * Service providers to register.
     *
     * @var array
     */
    private static $providers = [
        CoreServiceProvider::class,
        AdminServiceProvider::class,
    ];

    /**
     * Main entry point for plugin initialization.
     *
     * @return void
     */
    public static function init()
    {
        if (self::$initialized) {
            return;
        }

        self::$initialized = true;

        // Register activation/deactivation hooks
        self::registerLifecycleHooks();

        // Initialize HooksManager early to catch obfuscated asset requests
        new HooksManager();

        add_action('plugins_loaded', [__CLASS__, 'setup'], 10);
    }

    /**
     * Plugin setup - called on plugins_loaded hook.
     *
     * @return void
     */
    public static function setup()
    {
        add_action('init', [__CLASS__, 'loadTextdomain']);
        self::initializeServices();
        do_action('wp_statistics_loaded');
    }

    /**
     * Register activation/deactivation hooks.
     *
     * @return void
     */
    private static function registerLifecycleHooks()
    {
        register_activation_hook(WP_STATISTICS_MAIN_FILE, [__CLASS__, 'activate']);
        register_deactivation_hook(WP_STATISTICS_MAIN_FILE, [__CLASS__, 'deactivate']);
    }

    /**
     * Plugin activation handler.
     *
     * @param bool $networkWide Whether the plugin is being activated network-wide.
     * @return void
     */
    public static function activate($networkWide)
    {
        // Load required dependencies for activation
        self::loadActivationDependencies();

        InstallManager::activate((bool) $networkWide);
    }

    /**
     * Plugin deactivation handler.
     *
     * @return void
     */
    public static function deactivate()
    {
        // Load required dependencies for deactivation
        self::loadActivationDependencies();

        InstallManager::deactivate();
    }

    /**
     * Load dependencies needed for activation/deactivation.
     *
     * @return void
     */
    private static function loadActivationDependencies()
    {
        // Database operations
        require_once WP_STATISTICS_DIR . 'src/Service/Database/DatabaseManager.php';
        require_once WP_STATISTICS_DIR . 'src/Service/Database/Managers/TransactionHandler.php';
        require_once WP_STATISTICS_DIR . 'src/Service/Database/AbstractDatabaseOperation.php';
        require_once WP_STATISTICS_DIR . 'src/Service/Database/Operations/AbstractTableOperation.php';
        require_once WP_STATISTICS_DIR . 'src/Service/Database/Operations/Create.php';
        require_once WP_STATISTICS_DIR . 'src/Service/Database/Operations/Inspect.php';
        require_once WP_STATISTICS_DIR . 'src/Service/Database/DatabaseFactory.php';
        require_once WP_STATISTICS_DIR . 'src/Service/Database/Schema/Manager.php';
        require_once WP_STATISTICS_DIR . 'src/Service/Database/Managers/TableHandler.php';

        // Installation manager
        require_once WP_STATISTICS_DIR . 'src/Service/Installation/InstallManager.php';
    }

    /**
     * Load plugin text domain for translations.
     *
     * @return void
     */
    public static function loadTextdomain()
    {
        load_plugin_textdomain('wp-statistics', false, WP_STATISTICS_DIR . 'resources/languages');
    }

    /**
     * Get the service container.
     *
     * @return ServiceContainer
     */
    public static function container(): ServiceContainer
    {
        if (self::$container === null) {
            self::$container = ServiceContainer::getInstance();
        }

        return self::$container;
    }

    /**
     * Get a service from the container.
     *
     * @param string $id Service identifier.
     * @return object|null
     */
    public static function get(string $id)
    {
        return self::container()->get($id);
    }

    /**
     * Initialize all services.
     *
     * @return void
     */
    private static function initializeServices()
    {
        // Load legacy utilities that haven't been fully migrated yet
        self::loadLegacyUtilities();

        // Initialize installation hooks (multisite, upgrades)
        InstallManager::init();

        // Initialize service container
        $container = self::container();

        // Register all service providers
        $providers = [];
        foreach (self::$providers as $providerClass) {
            $provider = new $providerClass();
            $provider->register($container);
            $providers[] = $provider;
        }

        // Boot all service providers
        foreach ($providers as $provider) {
            $provider->boot($container);
        }
    }

    /**
     * Load legacy utility classes for backward compatibility with add-ons.
     *
     * These files provide the WP_STATISTICS namespace classes that add-ons depend on.
     * They will be removed when add-ons migrate to the new WP_Statistics namespace.
     *
     * For the authoritative list of legacy APIs/tables/add-on dependencies, see:
     * `docs/COMPATIBILITY.md`
     *
     * ============================================================================
     * ADD-ON COMPATIBILITY LAYER
     * ============================================================================
     *
     * Classes used by add-ons (verified via grep analysis):
     * - Option (23 usages)      -> class-wp-statistics-option.php
     * - Menus (27 usages)       -> class-wp-statistics-menus.php
     * - Helper (18 usages)      -> class-wp-statistics-helper.php
     * - Admin_Template (16)     -> admin/class-wp-statistics-admin-template.php
     * - Admin_Assets (7)        -> admin/class-wp-statistics-admin-assets.php
     * - User (5 usages)         -> class-wp-statistics-user.php
     * - Visitor (4 usages)      -> class-wp-statistics-visitor.php
     * - Country (4 usages)      -> class-wp-statistics-country.php
     * - TimeZone (2 usages)     -> class-wp-statistics-timezone.php
     * - DB (2 usages)           -> class-wp-statistics-db.php
     * - IP (1 usage)            -> class-wp-statistics-ip.php
     * - Schedule (dependency)   -> class-wp-statistics-schedule.php (Helper uses it)
     *
     * Also kept:
     * - defines/                -> Country codes, robots list (data files)
     *
     * @return void
     */
    private static function loadLegacyUtilities()
    {
        $legacyDir = WP_STATISTICS_DIR . 'includes/';

        // Legacy classes required by add-ons (WP_STATISTICS namespace)
        $legacyFiles = [
            // Core utilities (used by add-ons)
            'class-wp-statistics-option.php',
            'class-wp-statistics-db.php',
            'class-wp-statistics-timezone.php',
            'class-wp-statistics-user.php',
            'class-wp-statistics-helper.php',
            'class-wp-statistics-country.php',
            'class-wp-statistics-ip.php',
            'class-wp-statistics-visitor.php',
            'class-wp-statistics-menus.php',
            'class-wp-statistics-schedule.php',
            'class-wp-statistics-pages.php',

            // Admin UI (used by add-ons)
            'admin/class-wp-statistics-admin-template.php',
            'admin/class-wp-statistics-admin-assets.php',
        ];

        foreach ($legacyFiles as $file) {
            $filePath = $legacyDir . $file;
            if (file_exists($filePath)) {
                require_once $filePath;
            }
        }
    }

    /**
     * Get a background process instance.
     *
     * @param string $key Process key.
     * @return object|null
     */
    public static function getBackgroundProcess(string $key)
    {
        return self::container()->get('background.' . $key);
    }
}
