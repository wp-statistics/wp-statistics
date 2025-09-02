<?php

namespace WP_Statistics\Core;

use WP_Statistics\Core\Operations\Activator;
use WP_Statistics\Core\Operations\Loader;
use WP_Statistics\Core\Operations\Uninstaller;
use WP_Statistics\Core\Operations\Updater;

/**
 * Factory class offering static methods to create core service instances and helpers.
 *
 * @package WP_Statistics\Core
 */
class CoreFactory
{
    /**
     * Create and return the updater service.
     *
     * @return Updater Updater service instance.
     */
    public static function updater()
    {
        return new Updater();
    }

    /**
     * Create and return the activator service.
     *
     * @param bool $networkWide Whether activation is network‑wide on multisite.
     * @return Activator Activator service instance.
     */
    public static function activator($networkWide)
    {
        return new Activator($networkWide);
    }

    /**
     * Create and return the loader service.
     *
     * @return Loader Loader service instance.
     */
    public static function loader()
    {
        return new Loader();
    }

    /**
     * Create and return the uninstaller service.
     *
     * @return Uninstaller Uninstaller service instance.
     */
    public static function uninstaller()
    {
        return new Uninstaller();
    }

    /**
     * Check whether the plugin is marked as a fresh install.
     *
     * @return bool True if the fresh-install flag is set, false otherwise.
     */
    public static function isFresh()
    {
        $isFresh = get_option('wp_statistics_is_fresh', false);

        if ($isFresh) {
            return true;
        }

        return false;
    }
}