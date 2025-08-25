<?php

namespace WP_Statistics\Core;

use WP_Statistics\Core\Operations\Updater;

/**
 * Factory class offering static methods to create core service instances and helpers.
 *
 * @package WP_Statistics\Core
 */
class CoreFactory
{
    /**
     * Creates an instance of the Updater class.
     *
     * @return Updater
     */
    public static function updater()
    {
        return new Updater();
    }

    /**
     * Determines if the plugin is marked as freshly installed.
     *
     * @return bool.
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