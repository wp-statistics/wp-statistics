<?php

namespace WP_Statistics\Service\Assets;

use WP_Statistics\Service\Assets\Handlers\FrontendHandler;
use WP_Statistics\Service\Assets\Handlers\ReactHandler;
use WP_Statistics\Service\Assets\Handlers\LegacyHandler;

/**
 * Assets Factory.
 *
 * Factory class for creating and managing assets instances.
 * Provides methods to load React, Legacy and frontend assets.
 *
 * @package WP_STATISTICS\Service\Assets
 * @since   15.0.0
 */
class AssetsFactory
{
    /**
     * Load React admin assets.
     *
     * @return ReactHandler|null React assets instance
     */
    public static function React()
    {
        if (!class_exists(ReactHandler::class)) {
            return null;
        }

        return new ReactHandler();
    }

    /**
     * Load Legacy admin assets.
     *
     * @return LegacyHandler|null Legacy assets instance
     */
    public static function Legacy()
    {
        if (!class_exists(LegacyHandler::class)) {
            return null;
        }

        return new LegacyHandler();
    }

    /**
     * Load Frontend assets.
     *
     * @return FrontendHandler|null Frontend assets instance
     */
    public static function Frontend()
    {
        if (!class_exists(FrontendHandler::class)) {
            return null;
        }

        return new FrontendHandler();
    }
}