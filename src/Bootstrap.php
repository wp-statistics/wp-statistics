<?php

namespace WP_Statistics;

use WP_Statistics\Service\Admin\AdminBar;
use WP_Statistics\Service\Admin\AdminMenuManager;
use WP_Statistics\Service\Admin\DashboardBootstrap\DashboardManager;
use WP_Statistics\Service\Admin\Settings\SettingsManager;
use WP_Statistics\Service\Assets\Handlers\FrontendHandler;
use WP_Statistics\Service\Database\Managers\MigrationHandler;
use WP_Statistics\Service\EmailReport\EmailReportManager;
use WP_Statistics\Service\HooksManager;
use WP_Statistics\Service\Tracking\TrackerControllerFactory;
use WP_Statistics\Service\CronEventManager;

defined('ABSPATH') || exit;

// Load global functions
require_once __DIR__ . '/functions.php';

/**
 * Bootstrap class for WP Statistics v15.
 *
 * This class handles initialization and decides whether to load
 * v14 (legacy) or v15 (new) architecture based on migration status.
 *
 * v15 = Pure new React-based architecture from /src/
 *       Only Dashboard and Settings pages
 * v14 = Legacy PHP architecture from /includes/
 *
 * @since 15.0.0
 */
class Bootstrap
{
    /**
     * Whether v15 mode is active.
     *
     * @var bool
     */
    private static $isV15 = false;

    /**
     * Main entry point - decides v14 or v15 loading.
     *
     * @return void
     */
    public static function init()
    {
        // Load Option class first to check migration status
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-option.php';

        /**
         * Check if migration is complete to determine which architecture to load.
         * TODO: Remove '|| true' when v15 is stable and ready for production.
         *
         * @since 15.0.0
         */
        $migrationComplete = \WP_STATISTICS\Option::getOptionGroup('db', 'migrated', false);

        if ($migrationComplete || true) { // TODO: Remove '|| true' when v15 is stable
            self::$isV15 = true;
            self::registerHooks();

            // Initialize HooksManager early to catch obfuscated asset requests
            // This must happen before plugins_loaded fires
            new HooksManager();

            add_action('plugins_loaded', [__CLASS__, 'pluginSetup'], 10);
        } else {
            self::$isV15 = false;
            // Load legacy v14 architecture
            require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics.php';
            \WP_Statistics::instance();
        }
    }

    /**
     * Plugin setup - called on plugins_loaded hook.
     *
     * @return void
     */
    public static function pluginSetup()
    {
        add_action('init', [__CLASS__, 'loadTextdomain']);
        self::initV15();
        do_action('wp_statistics_loaded');
    }

    /**
     * Register activation/deactivation hooks for v15 mode.
     *
     * @return void
     */
    private static function registerHooks()
    {
        register_activation_hook(WP_STATISTICS_MAIN_FILE, [__CLASS__, 'install']);
        register_deactivation_hook(WP_STATISTICS_MAIN_FILE, [__CLASS__, 'uninstall']);
    }

    /**
     * Plugin activation handler.
     *
     * @param bool $network_wide Whether the plugin is being activated network-wide.
     * @return void
     */
    public static function install($network_wide)
    {
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-db.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-install.php';

        $installer = new \WP_STATISTICS\Install();
        $installer->install($network_wide);
    }

    /**
     * Plugin deactivation handler.
     *
     * @return void
     */
    public static function uninstall()
    {
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-db.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-option.php';
        require_once WP_STATISTICS_DIR . 'src/Components/AssetNameObfuscator.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-uninstall.php';

        new \WP_STATISTICS\Uninstall();
    }

    /**
     * Load plugin text domain for translations.
     *
     * @return void
     */
    public static function loadTextdomain()
    {
        load_plugin_textdomain('wp-statistics', false, WP_STATISTICS_DIR . 'languages');
    }

    /**
     * Check if v15 mode is active.
     *
     * @return bool
     */
    public static function isV15()
    {
        return self::$isV15;
    }

    /**
     * Initialize v15 architecture.
     *
     * Pure React-based architecture with only:
     * - Dashboard (React SPA)
     * - Settings (React SPA)
     *
     * NO legacy /includes/ UI components.
     *
     * @return void
     */
    private static function initV15()
    {
        // Load minimal core utilities needed for v15
        self::loadCoreUtilities();

        // Initialize tracking (works on frontend)
        TrackerControllerFactory::createController();

        // Initialize cron events
        new CronEventManager();

        // Initialize migration handler
        MigrationHandler::init();

        // Initialize admin bar (works on both admin and frontend)
        new AdminBar();

        // Initialize frontend assets (tracker.js, mini-chart, etc.)
        if (!is_admin()) {
            new FrontendHandler();
        }

        // Admin-only: Dashboard and Settings
        if (is_admin()) {
            self::initAdminServices();
        }
    }

    /**
     * Load minimal core utility classes.
     *
     * Only load what's absolutely necessary for v15.
     *
     * @return void
     */
    private static function loadCoreUtilities()
    {
        // Core utilities needed for tracking and database
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-helper.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-db.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-timezone.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-user.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-country.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-ip.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-geoip.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-visitor.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-pages.php';

        // Template functions (for compatibility)
        require_once WP_STATISTICS_DIR . 'includes/template-functions.php';
        require_once WP_STATISTICS_DIR . 'functions.php';
    }

    /**
     * Initialize v15 admin services.
     *
     * Only Dashboard and Settings - both React-based.
     *
     * @return void
     */
    private static function initAdminServices()
    {
        // v15 Admin Menu (Dashboard + Settings only)
        new AdminMenuManager();

        // v15 Dashboard AJAX endpoints and React assets
        new DashboardManager();

        // v15 Settings (additional AJAX handlers if needed)
        new SettingsManager();

        // v15 Email Report Manager (AJAX handlers for email builder)
        new EmailReportManager();
    }

    /**
     * Log a message (compatibility with legacy WP_Statistics class).
     *
     * @param string $message Log message.
     * @param string $level Log level.
     * @return void
     */
    public static function log($message, $level = 'info')
    {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("WP Statistics [{$level}]: {$message}");
        }
    }
}
