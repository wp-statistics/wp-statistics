<?php

namespace WP_Statistics\Service\Admin\Assets;

use WP_Statistics\Service\Admin\Assets\Handlers\ReactHandler;
use WP_Statistics\Service\Admin\Assets\Handlers\LegacyHandler;

/**
 * Admin Assets Factory
 *
 * Factory class for creating and managing admin assets instances.
 * Provides methods to load React and Legacy admin assets.
 *
 * @package WP_STATISTICS\Service\Admin\Assets
 * @since   15.0.0
 */
class AdminAssetsFactory
{
    /**
     * Load React admin assets
     *
     * @return ReactHandler|null React assets instance
     * @since 15.0.0
     */
    public static function React()
    {
        if (!class_exists(ReactHandler::class)) {
            return null;
        }

        return new ReactHandler();
    }

    /**
     * Load Legacy admin assets
     *
     * @return LegacyHandler|null Legacy assets instance
     * @since 15.0.0
     */
    public static function Legacy()
    {
        if (!class_exists(LegacyHandler::class)) {
            return null;
        }

        return new LegacyHandler();
    }
}