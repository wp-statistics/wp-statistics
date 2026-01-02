<?php

namespace WP_Statistics;

use WP_Statistics\Service\Admin\Settings\SettingsManager;

defined('ABSPATH') || exit;

/**
 * Bootstrap class for WP Statistics.
 *
 * This class handles initialization and decides whether to load
 * v14 (legacy) or v15 (new) architecture based on migration status.
 *
 * v15 = Pure new architecture from /src/ (PSR-4 autoloaded)
 * v14 = Legacy architecture from /includes/
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
            self::initV15();
        } else {
            self::$isV15 = false;
            // Load legacy v14 architecture
            require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics.php';
            \WP_Statistics::instance();
        }
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
     * Pure new architecture - NO legacy /includes/ dependencies.
     *
     * @return void
     */
    private static function initV15()
    {
        // TODO: Implement pure v15 services here
        // All services should be from /src/ with PSR-4 autoloading

        // For now, initialize settings manager
        if (is_admin()) {
            new SettingsManager();
        }
    }
}
